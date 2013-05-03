<?php
/**
 * CoNtRol reaction network file import
 *
 * Imports an uploaded text file describing the reaction
 * network, and attempts to analyse it.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    10/10/2012
 * @modified   24/04/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/session.php');

$errors = array();
$mimetype = '';

if(isset($_FILES) and count($_FILES) and isset($_FILES['upload_network_file_input']) and count($_FILES['upload_network_file_input']) and isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	switch($_FILES['upload_network_file_input']['error'])
	{
		case UPLOAD_ERR_OK:
			$finfo = new finfo(FILEINFO_MIME_TYPE);
			if($finfo)
			{
				$mimetype = $finfo->file($_FILES['upload_network_file_input']['tmp_name']);
				if($mimetype !== 'text/plain') $errors[] = 'File not in plain text format';
			}
			else
			{
				// Throw an exception?
				$errors[] = 'Failed to open fileinfo database';
			}
			//$finfo->close();
			break;
		case UPLOAD_ERR_INI_SIZE:
			// fall through
		case UPLOAD_ERR_FORM_SIZE:
			$errors[] = 'File too large';
			break;
		case UPLOAD_ERR_PARTIAL:
			$errors[] = 'File only partially uploaded';
			break;
		case UPLOAD_ERR_NO_FILE:
			$errors[] = 'No file uploaded';
			break;
		case UPLOAD_ERR_NO_TMP_DIR:
			$errors[] = 'Temporary folder missing';
			break;
		case UPLOAD_ERR_CANT_WRITE:
			$errors[] = 'Failed to write file to disk';
			break;
		case UPLOAD_ERR_EXTENSION:
			$errors[] = 'Extension prevented file upload';
			break;
		default: // an unknown error occurred
			$errors[] = 'Unknown error occurred';
			break;
	}
}
else $errors[] = 'No file uploaded';
if(!(isset($_POST['upload_network_file_format']) and $_POST['upload_network_file_format'])) $errors[] = 'File format not specified';

if(!count($errors))
{
	unset($_SESSION['errors']);
	$_SESSION['upload_file_format'] = $_POST['upload_network_file_format'];
	$reaction_network = new ReactionNetwork();
	$fhandle = fopen($_FILES['upload_network_file_input']['tmp_name'], 'r');
	switch($_POST['upload_network_file_format'])
	{
		case 'stoichiometry':
			$matrix = array();
			$_SESSION['errors'][] = 'Warning: You uploaded a stoichiometry file. The output below will not be correct if any reactants appear on both sides of a reaction.';
			while(!feof($fhandle))
			{
				$row = trim(fgets($fhandle));
				if($row and strpos($row, '#') !== 0) $matrix[] = explode(' ', $row);
			}
			if(!$reaction_network->parseStoichiometry($matrix)) $_SESSION['errors'][] = 'An error was detected in the stoichiometry file. Please check that the output below is as expected.';
			break;
		case 'S+T+V':
			$file = array();
			while (!feof($fhandle))
			{
				$row = trim(fgets($fhandle));
				if($row and strpos($row, '#') !== 0)
				{
				//TO DO: Implement this import		
				} 
			}	
			break;
		default: // assume 'human' if unsure
			$error = false;
			while(!feof($fhandle))
			{
				$reactionString = fgets($fhandle);
				if($reactionString and strpos($reactionString, '#') !== 0)
				{
					$newReaction = Reaction::parseReaction($reactionString);
					if($newReaction) $reaction_network->addReaction($newReaction);
					elseif(!$error)
					{
						$_SESSION['errors'][] = 'An error occurred while adding a reaction from the file. Please check that the output below is as expected.';
						$error = true;
					}
				}
			}
			break;
	}
	fclose($fhandle);
	$_SESSION['reaction_network'] = $reaction_network;
}

if(CRNDEBUG)
{
	echo '<pre>$_FILES:', CLIENT_LINE_ENDING;
	print_r($_FILES);
	echo CLIENT_LINE_ENDING, CLIENT_LINE_ENDING, '$errors:', CLIENT_LINE_ENDING;
	print_r($errors);
	echo CLIENT_LINE_ENDING, CLIENT_LINE_ENDING, '$mimetype:', CLIENT_LINE_ENDING;
	echo $mimetype;
	echo CLIENT_LINE_ENDING, CLIENT_LINE_ENDING, '$_SESSION:', CLIENT_LINE_ENDING;
	print_r($_SESSION);
	echo CLIENT_LINE_ENDING, '</pre>';
}
else
{
	header('Location: '.SITE_URL);
}
