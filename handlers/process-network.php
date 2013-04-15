<?php
/**
 * CoNtRol reaction network ajax handler
 *
 * Saves the reaction network to the session
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    17/01/2013
 * @modified   15/04/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if (count($_POST))
{
	$reactions = new ReactionNetwork();
	$output = '';
	$numberOfReactions = count($_POST['reaction_direction']);

	for($i = 0; $i < $numberOfReactions; ++$i)
	{
		switch($_POST['reaction_direction'][$i])
		{
			case 'both':
				$reversible = true;
				$leftHandSide = $_POST['reaction_left_hand_side'][$i];
				$rightHandSide = $_POST['reaction_right_hand_side'][$i];
				break;

			case 'right':
				$reversible = false;
				$leftHandSide = $_POST['reaction_left_hand_side'][$i];
				$rightHandSide = $_POST['reaction_right_hand_side'][$i];
				break;

			case 'left':
				$reversible = false;
				$leftHandSide = $_POST['reaction_right_hand_side'][$i];
				$rightHandSide = $_POST['reaction_left_hand_side'][$i];
				break;

			default:
				// Throw exception?
				break;
		}
		$reaction = new Reaction($leftHandSide, $rightHandSide, $reversible);
		if (!$reactions->addReaction($reaction) or !$reaction->getReactants()) $output .= 'Reaction '.($i+1).' is invalid.<br />';
	}
	$_SESSION['reactionNetwork']=$reactions;
	if (strlen($output)) echo $output;
	if (CRNDEBUG) print_r($_SESSION['reactionNetwork']);

	// Create human-readable descriptor file
	$filename = $_SESSION['tempfile'].'.hmn';

	// In our example we're opening $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where $somecontent will go when we fwrite() it.
	if(!$handle = fopen($filename, 'w'))
	{
		echo "<p>Cannot open file ($filename)</p>";
		exit;
	}

	// Write $somecontent to our opened file.
	if(fwrite($handle, $_SESSION['reactionNetwork']->exportReactionNetworkEquations()) === false)
	{
		echo "<p>Cannot write to file ($filename)</p>";
		exit;
	}
	fclose($handle);

	// Create net stoichiometry descriptor file
	$filename = $_SESSION['tempfile'].'.sto';

	// In our example we're opening $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where $somecontent will go when we fwrite() it.
	if(!$handle = fopen($filename, 'w'))
	{
		echo "<p>Cannot open file ($filename)</p>";
		exit;
	}

	// Write $somecontent to our opened file.
	if(fwrite($handle, $_SESSION['reactionNetwork']->exportStoichiometryMatrix()) === false)
	{
		echo "<p>Cannot write to file ($filename)</p>";
		exit;
	}
	fclose($handle);

	// Create net stoichiometry + V matrix descriptor file
	$filename = $_SESSION['tempfile'].'.s+v';

	// In our example we're opening $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where $somecontent will go when we fwrite() it.
	if(!$handle = fopen($filename, 'w'))
	{
		echo "<p>Cannot open file ($filename)</p>";
		exit;
	}

	// Write $somecontent to our opened file.
	if(fwrite($handle, $_SESSION['reactionNetwork']->exportStoichiometryAndVMatrix()) === false)
	{
		echo "<p>Cannot write to file ($filename)</p>";
		exit;
	}
	fclose($handle);

	// Create source stoichiometry + target stoichiometry + V matrix descriptor file
	$filename = $_SESSION['tempfile'].'.stv';

	// In our example we're opening $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where $somecontent will go when we fwrite() it.
	if(!$handle = fopen($filename, 'w'))
	{
		echo "<p>Cannot open file ($filename)</p>";
		exit;
	}

	// Write $somecontent to our opened file.
	if(fwrite($handle, $_SESSION['reactionNetwork']->exportSourceAndTargetStoichiometryAndVMatrix()) === false)
	{
		echo "<p>Cannot write to file ($filename)</p>";
		exit;
	}
	fclose($handle);

	foreach($_SESSION['tests'] as $testname => $test)
	{
		if($test)
		{
			foreach($_SESSION['standardtests'] as &$standardTest)
			if ($testname === $standardTest->getShortName()) $standardTest->enableTest();
		}
		else
		{
			foreach($_SESSION['standardtests'] as &$standardTest)
			if($testname === $standardTest->getShortName()) $standardTest->disableTest();
		}
	}

	$_SESSION['numberOfTests'] = 0;
	$_SESSION['testoutput'] = array();
	$_SESSION['currenttest'] = 0;

	for($i = 0; $i < count($_SESSION['standardtests']); ++$i)
	{
		if($_SESSION['standardtests'][$i]->getIsEnabled()) ++$_SESSION['numberOfTests'];
	}
}
