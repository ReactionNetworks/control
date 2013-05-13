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
 * @modified   13/05/2013
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
	$mail = "CoNtRol Output\r\n";
	$mail .= "==============\r\n\r\n";
	$mail .= "Version: ".CONTROL_VERSION."\r\n";

	// Initialise some variables
	$line_ending = "\n";
	if(strpos($jobs[$i]['remote_user_agent'], 'Windows;') !== false) $line_ending = "\r".$line_ending;
	if(strpos($jobs[$i]['remote_user_agent'], 'Macintosh;') !== false) $line_ending = "\r";

	$tests_enabled = explode(';', $jobs[$i]['tests_enabled']);
	$mail .= "Tests enabled:";
	foreach($tests_enabled as $test) $mail .= " $test";
	$mail .= "\r\nMass action only: ";
	$mass_action_only = false;
	if($jobs[$i]['mass_action_only'] == 1)
	{
		$mass_action_only = true;
		$mail .= "True";
	}
	else $mail .= "False";
	$mail .= "\r\nBatch submission time: ".$jobs[$i]['creation_timestamp']."\r\n\r\n";
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
			$mail .= "ERROR: Unsupported archive type: $mimetype\r\n";
			break;
	}
	if (!$success) $mail .= "ERROR: Failed to open archive $filename\r\n";
	else
	{
		$success = mkdir($dirname, 0700, true);
		if ($success)
		{
			$archive->extractTo($dirname);
			$archive->close();
		}
		else $mail .= "ERROR: Failed to create temporary directory\r\n";
	}
	if ($success)
	{
		$extracted_files = scandir($dirname);
		$file_found = false;
		if ($extracted_files !== false)
		{
			foreach($extracted_files as $file)
			{
				if(!is_dir($file))
				{
					$mimetype = get_mime($dirname.'/'.$file);
					if ($mimetype === 'text/plain')
					{
						$file_found = true;
						$mail .= "\r\n## FILE: ".end(explode('/', $file))." ##\r\n\r\nProcessing start time: ".date('Y-m-d H:i:s')."\r\nFile contents:\r\n";
						$reaction_network = new ReactionNetwork();
						$fhandle = fopen($dirname.'/'.$file, 'r');
						switch($jobs[$i]['file_format'])
						{
							case 1: //Net stoichiometry
								$matrix = array();
								$mail .= "WARNING: You uploaded a stoichiometry file. The output below will not be correct if any reactants appear on both sides of a reaction.\r\n";
								while(!feof($fhandle))
								{
									$line = fgets($fhandle);
									$mail .= "\r\n$line";
									$row = trim($line);
									if($row) $matrix[] = explode(' ', $row);
								}
								if(!$reaction_network->parseStoichiometry($matrix))
								{
									$mail .= "ERROR: An error was detected in the stoichiometry file.\r\n";
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
									$mail .= "\r\n$reactionString";
									if($reactionString)
									{
										$newReaction = Reaction::parseReaction($reactionString);
										if($newReaction) $reaction_network->addReaction($newReaction);
										elseif($success)
										{
											$mail .= "ERROR: An error occurred while adding a reaction from the file.\r\n";
											$success = false;
										}
									}
								}
								break;
						}
						fclose($fhandle);
						$mail .= "\r\nReaction network:\r\n";
						$mail .= $reaction_network->exportReactionNetworkEquations("\r\n");
						if ($success)
						{
							// Create human-readable descriptor file
							$filename = $filename.'.hmn';

							// In our example we're opening $filename in append mode.
							// The file pointer is at the bottom of the file hence
							// that's where $somecontent will go when we fwrite() it.
							if(!$handle = fopen($filename, 'w'))
							{
								$mail .= "ERROR: Cannot open file ($temp_filename)\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportReactionNetworkEquations()) === false)
							{
								$mail .= "ERROR: Cannot write to file ($temp_filename)\r\n";
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
								$mail .= "ERROR: Cannot open file ($temp_filename)\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportStoichiometryMatrix()) === false)
							{
								$mail .= "ERROR: Cannot write to file ($temp_filename)\r\n";
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
								$mail .= "ERROR: Cannot open file ($temp_filename)\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportStoichiometryAndVMatrix()) === false)
							{
								$mail .= "ERROR: Cannot write to file ($temp_filename)\r\n";
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
								$mail .= "ERROR: Cannot open file ($temp_filename)\r\n";
								$success = false;
							}

							// Write $somecontent to our opened file.
							if(fwrite($handle, $reaction_network->exportSourceAndTargetStoichiometryAndVMatrix()) === false)
							{
								$mail .= "ERROR: Cannot write to file ($temp_filename)\r\n";
								$success = false;
							}
							fclose($handle);

							if($success)
							{
								/*$number_of_tests = 0;
								$test_output = array();
								$current_test = 0;

								for($i = 0; $i < count($standard_tests); ++$i)
								{
									if($standard_tests[$i]->getIsEnabled()) ++$number_of_tests;
								}*/
								foreach($standardTests as $test)
								{
									foreach($tests_enabled as &$enabled_test) if($enabled_test === $test->getShortName()) $enabled_test = $test;
								}
								foreach($tests_enabled as $currentTest)
								{
									$extension = '';
									$temp = '';
									$mail .= "\r\n### TEST: ".$currentTest->getShortName()." ###\r\n\r\nTest start time: ".date('Y-m-d H:i:s')."\r\n\r\n";

									// Need to split this into net stoichiometry versus source/target stoichiometry?
									// How best to treat reversible vs irreversible reactions in stoichiometry case?
									if(in_array('stoichiometry', $currentTest->getInputFileFormats())) $extension = '.sto';
									if(in_array('stoichiometry+V', $currentTest->getInputFileFormats())) $extension = '.s+v';
									if(in_array('S+T+V', $currentTest->getInputFileFormats())) $extension = '.stv';
									if(in_array('human', $currentTest->getInputFileFormats())) $extension = '.hmn';

									if(!$extension) $mail .= "ERROR: This test does not support any valid file formats. Test aborted.\r\n";
									else
									{
										$test_filename = $filename.$extension;
										$exec_string = 'cd '.BINARY_FILE_DIR.' && '.NICENESS.'./'.$currentTest->getExecutableName();
										$output = array();
										$returnValue = 0;
										//$exec_string = NICENESS.$binary;
										if(isset($mass_action_only) and $mass_action_only)
										{
											if($currentTest->supportsMassAction()) $exec_string .= ' --mass-action-only';
											else $mail .= "WARNING: you requested testing mass-action kinetics only, but this test always tests general kinetics.\r\n";
										}
										else
										{
											if(!$currentTest->supportsGeneralKinetics()) $mail .= "WARNING: you requested testing general kinetics, but this test only supports mass-action kinetics.\r\n";
										}
										$mail .= "Output:\r\n-------\r\n";
										$exec_string .= ' '.$filename.' 2>&1';
										exec($exec_string, $output, $returnValue);
										foreach($output as $line) $mail .= "\r\n$line";
									}
									$mail .= "\r\n\r\n### END OF TEST: ".$currentTest->getShortName()." ###\r\n\r\n";
								} //foreach($tests_enabled as $currentTest)
							} //if($success)
						} //if($success)
						$mail .= "## END OF FILE: ".end(explode('/', $file))." ##\r\n\r\n";
					}	//if($mimetype === 'text/plain')
				} //if(!is_dir($file))
			} //foreach($extracted_files as $file)
		} //if($extracted_files !== false)
	} //if($success)
	$mail .= "\r\nThis auto-generated message was sent to you because someone requested processing of a batch job from IP address ".$jobs[$i]['remote_ip'].". If you did not make the request yourself please delete this email. Queries should be addressed to ".ADMIN_EMAIL.".\r\n";

	// Set email headers.
	$extra_headers =  "From: CoNtRol <".ADMIN_EMAIL.">\r\n";
	$extra_headers .= "MIME-Version: 1.0\r\n";
	$extra_headers .= "Content-type: text/plain; charset=utf-8; format=flowed\r\n";
	$extra_headers .= "Content-Transfer-Encoding: 8bit\r\n";
	$extra_headers .= "Message-ID: <".time().'-'.substr(hash('sha512', ADMIN_EMAIL.$jobs[$i]['email']), -10).'@'.end(explode('@', ADMIN_EMAIL)).">\r\n";
	$extra_headers .= 'X-Originating-IP: ['.$jobs[$i]['remote_ip']."]\r\n";
	$sendmail_params = '-f'.ADMIN_EMAIL;

	if (!mail('<'.$jobs[$i]['email'].'>', 'CoNtRol Batch Output', $mail, $extra_headers, $sendmail_params)) echo "\$sendmail_params: $sendmail_params\r\n\$extra_headers: $extra_headers\r\n\$mail: $mail";
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
} //for($i=0; $i < $number_of_jobs; ++$i)
