#!/usr/bin/env php
<?php
/**
 * CoNtRol batch processing script
 *
 * This script checks the database for unprocessed batch jobs and processes them.
 * The results are then emailed to the batch originator. It is intended to be called via a cron job.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    18/04/2013
 * @modified   30/05/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/version.php');
require_once('../includes/standard-tests.php');

// Attempt to open the database and throw an exception if unable to do so
try
{
	$controldb = new PDO(DB_STRING, DB_USER, DB_PASS, $db_options);
}
catch(PDOException $exception)
{
	die('Unable to open database. Error: '.$exception.'. Please contact the system administrator at '.str_replace('@', ' at ', str_replace('.', ' dot ', ADMIN_EMAIL)).'.');
}

$query = 'SELECT * FROM '.DB_PREFIX.'batch_jobs WHERE status = 0';
$statement = $controldb->prepare($query);
$statement->execute();
$jobs = $statement->fetchAll(PDO::FETCH_ASSOC);
$number_of_jobs = count($jobs);

for($i = 0; $i < $number_of_jobs; ++$i)
{
	$query = 'UPDATE '.DB_PREFIX. 'batch_jobs SET status = 1, update_timestamp = :timestamp WHERE id = :id';
	$statement = $controldb->prepare($query);
	$statement->bindParam(':id', $jobs[$i]['id'], PDO::PARAM_INT);
	$statement->bindValue(':timestamp', date('Y-m-d H:i:s'), PDO::PARAM_STR);
	$statement->execute();
}

for($i = 0; $i < $number_of_jobs; ++$i)
{
	$boundary = hash("sha256", uniqid(time()));
	$mail = "<h1>CoNtRol Output</h1>\r\n";
	$mail .= "==============\r\n\r\n";
	$mail .= '<p>Version: '.CONTROL_VERSION."<br />\r\n";

	// Initialise some variables
	$line_ending = "\n";
	if(strpos($jobs[$i]['remote_user_agent'], 'Windows;') !== false) $line_ending = "\r".$line_ending;
	if(strpos($jobs[$i]['remote_user_agent'], 'Macintosh;') !== false) $line_ending = "\r";

	$tests_enabled = explode(';', $jobs[$i]['tests_enabled']);
	$mail .= 'Tests enabled:';
	foreach($tests_enabled as $test) $mail .= " $test";
	$mail .= "<br />\r\nDetailed test output: ";
	$detailed_output = false;
	if($jobs[$i]['detailed_output'] == 1)
	{
		$detailed_output = true;
		$mail .= 'True';
	}
	else $mail .= 'False';
	$mail .= "<br />\r\nMass action only: ";
	$mass_action_only = false;
	if($jobs[$i]['mass_action_only'] == 1)
	{
		$mass_action_only = true;
		$mail .= 'True';
	}
	else $mail .= 'False';
	$mail .= "<br />\r\nBatch submission time: ".$jobs[$i]['creation_timestamp']."</p>\r\n\r\n";
	$filename = $jobs[$i]['filename'];
	$dirname = TEMP_FILE_DIR.'control/'.$jobs[$i]['id'];
	$mimetype = get_mime($filename);
	$success = false;
	switch($mimetype)
	{
		case 'application/zip':
			$archive = new ZipArchive;
			$success = $archive->open($filename);
			break;
		default:
			$mail .= "<p>ERROR: Unsupported archive type: $mimetype</p>\r\n";
			break;
	}
	if(!$success) $mail .= "<p>ERROR: Failed to open archive $filename</p>\r\n";
	else
	{
		$success = mkdir($dirname, 0700, true);
		if ($success)
		{
			$archive->extractTo($dirname);
			$archive->close();
		}
		else $mail .= "<p>ERROR: Failed to create temporary directory</p>\r\n";
	}
	if($success)
	{
		$extracted_files = scandir($dirname);
		$file_found = false;
		if($extracted_files !== false)
		{
			foreach($extracted_files as $file)
			{
				if(!is_dir($file))
				{
					$mimetype = get_mime($dirname.'/'.$file);
					if ($mimetype === 'text/plain')
					{
						$file_found = true;
						$mail .= "\r\n## FILE: ".end(explode('/', $file))." ##\r\n\r\n<p>Processing start time: ".date('Y-m-d H:i:s')."<br />\r\nFile contents:</p>";
						$reaction_network = new ReactionNetwork();
						$fhandle = fopen($dirname.'/'.$file, 'r');
						switch($jobs[$i]['file_format'])
						{
							case 1: //Net stoichiometry
								$matrix = array();
								$mail .= "<p>WARNING: You uploaded a stoichiometry file. The output below will not be correct if any reactants appear on both sides of a reaction.</p>\r\n";
								while(!feof($fhandle))
								{
									$line = fgets($fhandle);
									$mail .= '<pre>';
									$mail .= "\r\n$line";
									$mail .= '</pre>';
									$row = trim($line);
									if($row and strpos($row, '#') !== 0) $matrix[] = explode(' ', $row);
								}
								if(!$reaction_network->parseStoichiometry($matrix))
								{
									$mail .= "<p>ERROR: An error was detected in the stoichiometry file.</p>\r\n";
									$success = false;
								}
								break;
							case 2: //Net stoichiometry + V
							case 3: //Source + target + V
							case 0: //Human
								//Fall through
							default: //Assume 'human' if unsure
								while(!feof($fhandle))
								{
									$reactionString = fgets($fhandle);
									$mail .= '<pre>';
									$mail .= "\r\n$reactionString";
									$mail .= '</pre>';
									if($reactionString and strpos($reactionString, '#') !== 0)
									{
										$newReaction = Reaction::parseReaction($reactionString);
										if($newReaction) $reaction_network->addReaction($newReaction);
										elseif($success)
										{
											$mail .= "<p>ERROR: An error occurred while adding a reaction from the file.\r\n</p>";
											$success = false;
										}
									}
								}
								break;
						}
						fclose($fhandle);
						$mail .= "\r\n<p>Reaction network:</p>\r\n<pre>";
						$mail .= $reaction_network->exportReactionNetworkEquations("\r\n");
						$mail .= '</pre>';
						if ($success)
						{
							// Create human-readable descriptor file
							$temp_filename = $filename.'.hmn';

							// In our example we're opening $filename in append mode.
							// The file pointer is at the bottom of the file hence
							// that's where $somecontent will go when we fwrite() it.
							if(!$handle = fopen($temp_filename, 'w'))
							{
								$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportReactionNetworkEquations()) === false)
							{
								$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
								$success = false;
							}
							fclose($handle);

							// Create net stoichiometry descriptor file
							$temp_filename = $filename.'.sto';

							// In our example we're opening $filename in append mode.
							// The file pointer is at the bottom of the file hence
							// that's where $somecontent will go when we fwrite() it.
							if(!$handle = fopen($temp_filename, 'w'))
							{
								$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportStoichiometryMatrix()) === false)
							{
								$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
								$success = false;
							}
							fclose($handle);

							// Create net stoichiometry + V matrix descriptor file
							$temp_filename = $filename.'.s+v';

							// In our example we're opening $filename in append mode.
							// The file pointer is at the bottom of the file hence
							// that's where $somecontent will go when we fwrite() it.
							if(!$handle = fopen($temp_filename, 'w'))
							{
								$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportStoichiometryAndVMatrix()) === false)
							{
								$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
								$success = false;
							}
							fclose($handle);

							// Create source stoichiometry + target stoichiometry + V matrix descriptor file
							$temp_filename = $filename.'.stv';

							// In our example we're opening $filename in append mode.
							// The file pointer is at the bottom of the file hence
							// that's where $somecontent will go when we fwrite() it.
							if(!$handle = fopen($temp_filename, 'w'))
							{
								$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportSourceAndTargetStoichiometryAndVMatrix()) === false)
							{
								$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
								$success = false;
							}
							fclose($handle);

							if($success)
							{
								foreach($standardTests as $test)
								{
									foreach($tests_enabled as &$enabled_test) if($enabled_test === $test->getShortName()) $enabled_test = $test;
								}
								foreach($tests_enabled as $currentTest)
								{
									$extension = '';
									$temp = '';
									$mail .= "\r\n### TEST: ".$currentTest->getShortName()." ###\r\n\r\n<p>Test start time: ".date('Y-m-d H:i:s')."</p>\r\n\r\n";

									// Need to split this into net stoichiometry versus source/target stoichiometry?
									// How best to treat reversible vs irreversible reactions in stoichiometry case?
									if(in_array('stoichiometry', $currentTest->getInputFileFormats())) $extension = '.sto';
									if(in_array('stoichiometry+V', $currentTest->getInputFileFormats())) $extension = '.s+v';
									if(in_array('S+T+V', $currentTest->getInputFileFormats())) $extension = '.stv';
									if(in_array('human', $currentTest->getInputFileFormats())) $extension = '.hmn';

									if(!$extension) $mail .= "<p>ERROR: This test does not support any valid file formats. Test aborted.</p>\r\n";
									else
									{
										$test_filename = $filename.$extension;
										$exec_string = 'cd '.BINARY_FILE_DIR.' && '.NICENESS.'timeout '.TEST_TIMEOUT_LIMIT.' ./'.$currentTest->getExecutableName();
										$output = array();
										$returnValue = 0;
										//$exec_string = NICENESS.$binary;
										if(isset($mass_action_only) and $mass_action_only)
										{
											if($currentTest->supportsMassAction()) $exec_string .= ' --mass-action-only';
											else $mail .= "<p>WARNING: you requested testing mass-action kinetics only, but this test always tests general kinetics.</p>\r\n";
										}
										else
										{
											if(!$currentTest->supportsGeneralKinetics()) $mail .= "<p>WARNING: you requested testing general kinetics, but this test only supports mass-action kinetics.</p>\r\n";
										}
										$mail .= "<p>Output:</p>\r\n-------\r\n";
										$exec_string .= ' '.$test_filename;
										if(isset($detailed_output) and $detailed_output) $exec_string .= ' 2>&1';
										else $exec_string .= ' 2> /dev/null';
										exec($exec_string, $output, $returnValue);
										$mail .= '<pre>';
										foreach($output as $line) $mail .= "\r\n$line";
										$mail .= "\r\n</pre>";
									}
									$mail .= "\r\n\r\n### END OF TEST: ".$currentTest->getShortName()." ###\r\n\r\n";
								} // foreach($tests_enabled as $currentTest)
							} // if($success)
						} // if($success)
						$mail .= "## END OF FILE: ".end(explode('/', $file))." ##\r\n\r\n";
					}	// if($mimetype === 'text/plain')
				} // if(!is_dir($file))
			} // foreach($extracted_files as $file)
		} // if($extracted_files !== false)
	} // if($success)
	$mail .= "\r\n<p>This auto-generated message was sent to you because someone requested processing of a batch job from IP address ".$jobs[$i]['remote_ip'].". If you did not make the request yourself please delete this email. Queries should be addressed to ".ADMIN_EMAIL.".</p>\r\n";

	// Set email headers.
	$admin_email_split = explode('@', ADMIN_EMAIL);
	$extra_headers =  "From: CoNtRol <".ADMIN_EMAIL.">\r\n";
	$extra_headers .= "MIME-Version: 1.0\r\n";
	$extra_headers .= "Content-Type: multipart/alternative;\r\n boundary=\"$boundary\"\r\n";
	$extra_headers .= "Message-ID: <".time().'-'.substr(hash('sha512', ADMIN_EMAIL.$jobs[$i]['email']), -10).'@'.end($admin_email_split).">\r\n";
	$extra_headers .= 'X-Originating-IP: ['.$jobs[$i]['remote_ip']."]\r\n";
	$sendmail_params = '-f'.ADMIN_EMAIL;

	// Create HTML and plain text versions of mail
	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/plain; charset=utf-8;\r\n format=flowed\r\n";
	$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$body .= str_replace('<br />', '', str_replace('<h1>', '', str_replace('</h1>', '', str_replace('<p>', '', str_replace('</p>', '', str_replace('<pre>', '', str_replace('</pre>', '', $mail)))))));
	$body .= "\r\n\r\n--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=utf-8;\r\n format=flowed\r\n";
	$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	$body .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\r\n".'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">'."\r\n<head>\r\n".'<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'."\r\n<title>CoNtRol output</title>\r\n</head>\r\n<body>\r\n".str_replace(ADMIN_EMAIL, '<a href="mailto:'.ADMIN_EMAIL.'">'.ADMIN_EMAIL.'</a>', str_replace('## ', '<h2>', str_replace(' ##', '</h2>', str_replace('### ', '<h3>', str_replace(' ###', '</h3>', str_replace('<-->', '&lt;--&gt;', str_replace('<--', '&lt;--', str_replace('-->', '--&gt;', str_replace("\r\n-------\r\n", "\r\n", str_replace("\r\n==============\r\n\r\n", "\r\n", $mail))))))))))."</body>\r\n</html>\r\n";

	if (!mail('<'.$jobs[$i]['email'].'>', 'CoNtRol Batch Output', $body, $extra_headers, $sendmail_params)) echo "\$sendmail_params: $sendmail_params\r\n\$extra_headers: $extra_headers\r\n\$mail: $mail";
	elseif($success)
	{
		$query = 'UPDATE '.DB_PREFIX.'batch_jobs SET status = 2, update_timestamp = :timestamp WHERE id = :id';
		$statement = $controldb->prepare($query);
		$statement->bindParam(':id', $jobs[$i]['id'], PDO::PARAM_INT);
		$statement->bindValue(':timestamp', date('Y-m-d H:i:s'), PDO::PARAM_STR);
		$statement->execute();
		// Remove temporary files
		array_map('unlink', glob($jobs[$i]['filename'].'*'));
	}
	// Remove decompressed files
	recursive_remove_directory($dirname);
} // for($i = 0; $i < $number_of_jobs; ++$i)
