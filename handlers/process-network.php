<?php
/**
 * CoNtRol reaction network ajax handler
 *
 * Saves the reaction network to the session
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2019 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    17/01/2013
 * @modified   04/09/2019
 */

/**
 * Standard include
 */
require_once( '../includes/config.php' );

/**
 * Standard include
 */
require_once( '../includes/classes.php' );

/**
 * Standard include
 */
require_once( '../includes/functions.php' );

/**
 * Standard include
 */
require_once( '../includes/session.php' );

/**
 * Standard include
 */
require_once( '../includes/standard-tests.php' );

if( verify_csrf_token() and count( $_POST ) )
{
	$reactions = new ReactionNetwork();
	$output = '';
	$numberOfReactions = count( $_POST['reaction_direction'] );

	for( $i = 0; $i < $numberOfReactions; ++$i )
	{
		$forwardRateConstant = null;
		$backwardRateConstant = null;
		switch( $_POST['reaction_direction'][$i] )
		{
			case 'both':
				$reversible = true;
				$leftHandSide = $_POST['reaction_left_hand_side'][$i];
				$rightHandSide = $_POST['reaction_right_hand_side'][$i];
				if( trim( $_POST['reaction_forward_rate_constant'][$i] ) ) $forwardRateConstant = (float) $_POST['reaction_forward_rate_constant'][$i];
				if( trim( $_POST['reaction_backward_rate_constant'][$i] ) ) $backwardRateConstant = (float) $_POST['reaction_backward_rate_constant'][$i];
				break;

			case 'right':
				$reversible = false;
				$leftHandSide = $_POST['reaction_left_hand_side'][$i];
				$rightHandSide = $_POST['reaction_right_hand_side'][$i];
				if( trim( $_POST['reaction_forward_rate_constant'][$i] ) ) $forwardRateConstant = (float) $_POST['reaction_forward_rate_constant'][$i];
				break;

			case 'left':
				$reversible = false;
				$leftHandSide = $_POST['reaction_right_hand_side'][$i];
				$rightHandSide = $_POST['reaction_left_hand_side'][$i];
				if( trim( $_POST['reaction_backward_rate_constant'][$i] ) ) $backwardRateConstant = (float) $_POST['reaction_backward_rate_constant'][$i];
				break;

			default:
				// Throw exception?
				break;
		}
		if( trim( $leftHandSide) === '0' ) $leftHandSide = '';
		if( trim( $rightHandSide) === '0' ) $rightHandSide = '';
		$reaction = new Reaction( $leftHandSide, $rightHandSide, $reversible, $forwardRateConstant, $backwardRateConstant );
		if( !$reactions->addReaction( $reaction ) or !$reaction->getReactants() ) $output .= 'Reaction ' . ( $i + 1 ) . ' is invalid.<br />';
	}
	$_SESSION['reaction_network'] = $reactions;
	if( strlen( $output) ) echo $output;
	if( CRNDEBUG ) print_r( $_SESSION['reaction_network'] );

	// Create human-readable descriptor file
	$filename = $_SESSION['tempfile'] . '.hmn';
	// Open $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where content will go when we fwrite() it.
	if( !$handle = fopen( $filename, 'w' ) )
	{
		echo "<p>Cannot open file ( $filename )</p>";
		exit;
	}
	// Write content to the open file.
	if( fwrite( $handle, $_SESSION['reaction_network']->exportReactionNetworkEquations() ) === false )
	{
		echo "<p>Cannot write to file ( $filename )</p>";
		exit;
	}
	fclose( $handle );

	// Create net stoichiometry descriptor file
	$filename = $_SESSION['tempfile'] . '.sto';
	if( !$handle = fopen( $filename, 'w' ) )
	{
		echo "<p>Cannot open file ( $filename )</p>";
		exit;
	}
	if( fwrite( $handle, $_SESSION['reaction_network']->exportStoichiometryMatrix() ) === false )
	{
		echo "<p>Cannot write to file ( $filename )</p>";
		exit;
	}
	fclose( $handle );

	// Create net stoichiometry + V matrix descriptor file
	$filename = $_SESSION['tempfile'] . '.s+v';
	if( !$handle = fopen( $filename, 'w' ) )
	{
		echo "<p>Cannot open file ( $filename )</p>";
		exit;
	}
	if( fwrite( $handle, $_SESSION['reaction_network']->exportStoichiometryAndVMatrix() ) === false )
	{
		echo "<p>Cannot write to file ( $filename )</p>";
		exit;
	}
	fclose( $handle );

	// Create source stoichiometry + target stoichiometry + V matrix descriptor file
	$filename = $_SESSION['tempfile'] . '.stv';
	if( !$handle = fopen( $filename, 'w' ) )
	{
		echo "<p>Cannot open file ( $filename )</p>";
		exit;
	}
	if( fwrite( $handle, $_SESSION['reaction_network']->exportSourceAndTargetStoichiometryAndVMatrix() ) === false )
	{
		echo "<p>Cannot write to file ( $filename )</p>";
		exit;
	}
	fclose( $handle );

	// Create GLPK data file
	$filename = $_SESSION['tempfile'] . '.glpk';
	if( !$handle = fopen( $filename, 'w' ) )
	{
		echo "<p>Cannot open file ( $filename )</p>";
		exit;
	}
	if( fwrite( $handle, $_SESSION['reaction_network']->exportGLPKData() ) === false )
	{
		echo "<p>Cannot write to file ( $filename )</p>";
		exit;
	}
	fclose( $handle );

	foreach( $_SESSION['tests'] as $testname => $test )
	{
		if( $test)
		{
			foreach( $_SESSION['standard_tests'] as &$standardTest )
			if( $testname === $standardTest->getShortName() ) $standardTest->enableTest();
		}
		else
		{
			foreach( $_SESSION['standard_tests'] as &$standardTest )
			if( $testname === $standardTest->getShortName() ) $standardTest->disableTest();
		}
	}

	$_SESSION['number_of_tests'] = 0;
	$_SESSION['test_output'] = array();
	$_SESSION['current_test'] = 0;

	for( $i = 0; $i < count( $_SESSION['standard_tests'] ); ++$i )
	{
		if( $_SESSION['standard_tests'][$i]->getIsEnabled() ) ++$_SESSION['number_of_tests'];
	}
}

///:~
