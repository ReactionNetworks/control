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
	/*if (isset($_POST['ajax']) and $_POST['ajax']==='true')
	{
		
	}
	else 
	{
		$filename = $_SESSION['tempfile']; 
		$ssdBinary = 'test';
		$output = '';
		$returnValue = 0;

		// Let's make sure the file exists and is writable first.
	//	if (is_writable($filename)) 	
if (true)	
		{
    // In our example we're opening $filename in append mode.
    // The file pointer is at the bottom of the file hence
    // that's where $somecontent will go when we fwrite() it.
	    if (!$handle = fopen($filename, 'w')) 
	    {
	         echo "Cannot open file ($filename)";
	         exit;
	    }

    // Write $somecontent to our opened file.
	    if (fwrite($handle, printMatrix($reactions->generateStoichiometryMatrix())) === FALSE) 
	    {
	        echo "Cannot write to file ($filename)";
	        exit;
	    }

   		echo "<pre> Success, wrote to file ($filename)";
    	fclose($handle);
			exec('./'.$ssdBinary.' '.$filename.' 2>&1', $output, $returnValue);
			echo '$returnValue=', $returnValue;
			echo '$output=';
			print_r($output);
		} 
		else 
		{
    	echo "The file $filename is not writable";
    }
		//echo '<pre>', printMatrix($reactions->generateStoichiometryMatrix());
	}	*/
}