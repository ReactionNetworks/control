<?php
/**
 * CoNtRol reaction network file export
 *
 * Generates a text file describing the reaction network,
 * suitable for upload to CoNtRol at a later date, or for
 * use with offline CRN analysis tools.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    08/10/2012
 * @modified   10/10/2012
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

		// Let's make sure the file exists and is writable first.
	//	if (is_writable($filename)) 	

    // In our example we're opening $filename in append mode.
    // The file pointer is at the bottom of the file hence
    // that's where $somecontent will go when we fwrite() it.
	    if (!$handle = fopen($filename, 'w')) 
	    {
	         echo "<p>Cannot open file ($filename)</p>";
	         exit;
	    }

    // Write $somecontent to our opened file.
	    if (fwrite($handle, printMatrix($_SESSION['reactionNetwork']->generateStoichiometryMatrix())) === FALSE) 
	    {
	        echo "<p>Cannot write to file ($filename)</p>";
	        exit;
	    }

    	fclose($handle);
			exec('./'.$ssdBinary.' '.$filename.' 2>&1', $output, $returnValue);
	  //	print_r($output);
	  	echo '<p>';
	  	foreach($output as $line) echo sanitise($line), '<br />';
	  	echo '</p>';
}
