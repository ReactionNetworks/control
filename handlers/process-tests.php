<?php
/**
 * CoNtRol reaction network test handler
 *
 * For the current reaction network, runs each enabled test in turn.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    08/10/2012
 * @modified   09/08/2014
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

if( isset( $_SESSION['reaction_network'] ) and $_SESSION['reaction_network']->getNumberOfReactions() and isset( $_POST['csrf_token'] ) and $_POST['csrf_token'] === $_SESSION['csrf_token'] )
{
	$currentTest = null;
	$inifilename = $_SESSION['tempfile'] . '.ini';
	touch( $inifilename );

	for( $i = 0; $i < count( $_SESSION['standard_tests'] ); ++$i )
	{
		if( $_SESSION['standard_tests'][$i]->getIsEnabled() )
		{
			$_SESSION['standard_tests'][$i]->disableTest();
			$currentTest = $_SESSION['standard_tests'][$i];
			++$_SESSION['current_test'];
			break;
		}
	}

	if($currentTest)
	{
		$extension = '';
		$temp = '';

		// Need to split this into net stoichiometry versus source/target stoichiometry?
		// How best to treat reversible vs irreversible reactions in stoichiometry case?
		if( in_array( 'stoichiometry', $currentTest->getInputFileFormats() ) ) $extension = '.sto';
		if( in_array( 'stoichiometry+V', $currentTest->getInputFileFormats() ) ) $extension = '.s+v';
		if( in_array( 'S+T+V', $currentTest->getInputFileFormats() ) ) $extension = '.stv';
		if( in_array( 'GLPK', $currentTest->getInputFileFormats() ) ) $extension = '.glpk';
		if( in_array( 'human', $currentTest->getInputFileFormats() ) ) $extension = '.hmn';

		if( !$extension ) $temp = 'This test does not support any valid file formats. Test aborted.';
		else
		{
			$filename = $_SESSION['tempfile'] . $extension;
			$exec_string = 'cd ' . BINARY_FILE_DIR . ' && ' . NICENESS . 'timeout ' . TEST_TIMEOUT_LIMIT;
			$exec_string .= ' ./' . $currentTest->getExecutableName() . " '$filename'";
			if( $currentTest->supportsIniFile() ) $exec_string .= ' --inifile=' . $inifilename;
			$output = array();
			$returnValue = 0;
			if( isset( $_SESSION['detailed_output'] ) and $_SESSION['detailed_output'] ) $exec_string .= ' 2>&1';
			else $exec_string .= ' 2> /dev/null';
			exec( $exec_string, $output, $returnValue );
			foreach( $output as $line ) $temp .= "\n$line";
		}

		$_SESSION['test_output'][$currentTest->getShortName()] = $temp;
		echo '<p>Completed test ', $_SESSION['current_test'], ' of ', $_SESSION['number_of_tests'], '.</p>';
	}

	else
	{
		// Delete temporary files
		array_map( 'unlink', glob( $_SESSION['tempfile'] . '*' ) );
		echo '<p>All tests completed. Redirecting to results.</p>';
	}
}
else
{
	die( '<p>Error: CSRF detected or CRN not set up.</p>' );
}
