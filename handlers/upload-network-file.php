<?php
/**
 * CoNtRol reaction network file import
 *
 * Imports an uploaded text file describing the reaction
 * network, and attempts to analyse it.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    10/10/2012
 * @modified   10/10/2012
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/session.php');

$errors = array();
$mimetype = '';

if(isset($_FILES) and count($_FILES) and isset($_FILES['upload_network_file_input']) and count($_FILES['upload_network_file_input']))
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

if(!count($errors))
{
	$reactionNetwork = new ReactionNetwork();
	$fhandle = fopen($_FILES['upload_network_file_input']['tmp_name'], 'r');
	while(!feof($fhandle))
	{
		$reactionString = fgets($fhandle);
		$newReaction = Reaction::parseReaction($reactionString);
		if($newReaction) $reactionNetwork->addReaction($newReaction);
	}
	fclose($fhandle);
	$_SESSION['reactionNetwork']=$reactionNetwork;
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
	//header('Location: '.SITE_URL);
}
