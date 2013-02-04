<?php
/**
 * CoNtRol reaction network ssd test handler
 *
 * For the current reaction network, runs Murad Banaji's C program
 * to test whether the Jacobian matrix is strongly sign determined.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    08/10/2012
 * @modified   02/11/2012
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if (isset($_SESSION['reactionNetwork']))
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
	
	$filename = $_SESSION['tempfile'].'.hmn';	
	$binary = BINARY_FILE_DIR.$currentTest->getExecutableName();
	$output = array();
	$returnValue = 0;
	exec('./'.$binary.' '.$filename.' 2>&1', $output, $returnValue);
	$temp = '';
	foreach($output as $line) $temp .= " $line";
	$_SESSION['testoutput'][]=$temp;
  echo '<p>Completed test ',$_SESSION['currenttest'],' of ',$_SESSION['numberOfTests'], '.</p>';
		
  }
 
 else echo '<p>All tests completed. Redirecting to results.</p>';
 
}