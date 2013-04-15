<?php
/**
 * CoNtRol reaction network test handler
 *
 * For the current reaction network, runs each enabled test in turn.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-13
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    08/10/2012
 * @modified   14/04/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(isset($_SESSION['reactionNetwork']))
{
	$currentTest=null;

	for ($i=0;$i<count($_SESSION['standardtests']);++$i)
	{
		if ($_SESSION['standardtests'][$i]->getIsEnabled())
	 {
	 	$_SESSION['standardtests'][$i]->disableTest();
	 	$currentTest=$_SESSION['standardtests'][$i];
	 	++$_SESSION['currenttest'];
	  break;
	 }
	}

	if($currentTest)
	{
		$extension = '';
		$temp = '';

		// Need to split this into net stoichiometry versus source/target stoichiometry?
		// How best to treat reversible vs irreversible reactions in stoichiometry case?
		if(in_array('stoichiometry', $currentTest->getInputFileFormats())) $extension = '.sto';
		if(in_array('stoichiometry+V', $currentTest->getInputFileFormats())) $extension = '.s+v';
		if(in_array('human', $currentTest->getInputFileFormats())) $extension = '.hmn';

		if(!$extension) $temp = 'This test does not support any valid file formats. Test aborted.';
		else
		{
			$filename = $_SESSION['tempfile'].$extension;
			$binary = BINARY_FILE_DIR.$currentTest->getExecutableName();
			$output = array();
			$returnValue = 0;
			$exec_string = './'.$binary;
			if(isset($_SESSION['mass_action_only']) and $_SESSION['mass_action_only'])
			{
				if($currentTest->supportsMassAction()) $exec_string .= ' --mass-action-only';
				else $temp = "WARNING: you requested testing mass-action kinetics only, but this test always tests general kinetics.\n";
			}
			else
			{
				if(!$currentTest->supportsGeneralKinetics()) $temp = "WARNING: you requested testing general kinetics, but this test only supports mass-action kinetics.\n";
			}
			$exec_string .= ' '.$filename.' 2>&1';
			exec($exec_string, $output, $returnValue);
			foreach($output as $line) $temp .= "\n$line";
		}

		$_SESSION['testoutput'][$currentTest->getShortName()] = $temp;
		echo '<p>Completed test ',$_SESSION['currenttest'],' of ',$_SESSION['numberOfTests'], '.</p>';
	}

	else echo '<p>All tests completed. Redirecting to results.</p>';
}
