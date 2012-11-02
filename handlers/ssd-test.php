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

if (isset($_SESSION['reactionNetwork']))
{
	$filename = $_SESSION['tempfile'].'.SSDonly';
	$ssdBinary = BINARY_FILE_DIR.'test';
	$output = '';
	$returnValue = 0;

	// In our example we're opening $filename in append mode.
	// The file pointer is at the bottom of the file hence
	// that's where $somecontent will go when we fwrite() it.
	if (!$handle = fopen($filename, 'w'))
	{
		echo "<p>Cannot open file ($filename)</p>";
	        exit;
	}

	// Write $somecontent to our opened file.
	if (fwrite($handle, $_SESSION['reactionNetwork']->exportReactionNetworkEquations()) === false)
	{
		echo "<p>Cannot write to file ($filename)</p>";
		exit;
	}

    	fclose($handle);
	exec('./'.$ssdBinary.' '.$filename.' 2>&1', $output, $returnValue);
  	echo '<p>';
	foreach($output as $line) echo sanitise($line), '<br />';
	echo '</p>';
}
