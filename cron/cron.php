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
 * @modified   28/09/2013
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

// Set 'not started' jobs to 'in progress'
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
	$output_filename = TEMP_FILE_DIR.'/'.$jobs[$i]['filekey'].'.txt';
	if(!$ohandle = fopen($output_filename, 'w'))
	{
		$mail .= "<p>ERROR: Cannot open file ($output_filename)</p>\r\n";
		$success = false;
	}
	$boundary = hash("sha256", uniqid(time()));
	$mail = "<h1>CoNtRol Output</h1>\r\n";
	$mail .= "==============\r\n\r\n";
	$mail .= '<p>Version: '.CONTROL_VERSION."<br />\r\n";

	// Initialise some variables
	$line_ending = "\n";
	if(strpos($jobs[$i]['remote_user_agent'], 'Windows;') !== false) $line_ending = "\r".$line_ending;
	if(strpos($jobs[$i]['remote_user_agent'], 'Macintosh;') !== false) $line_ending = "\r";

	// Write $somecontent to our opened file.
	if(fwrite($ohandle, "CoNtRol Output$line_ending==============".$line_ending.$line_ending."Version: ".CONTROL_VERSION.$line_ending) === false)
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}
	
	$tests_enabled = explode(';', $jobs[$i]['tests_enabled']);
	$mail .= 'Tests enabled:';

	// Write $somecontent to our opened file.
	if(fwrite($ohandle, "Tests enabled:")===false)
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}

	foreach($tests_enabled as $test)
	{
		$mail .= " $test";
		// Write $somecontent to our opened file.
		if(fwrite($ohandle, " $test") === false)
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	$mail .= "<br />\r\nDetailed test output: ";
	// Write $somecontent to our opened file.
	if(fwrite($ohandle, $line_ending."Detailed test output: ") === false)
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}
	$detailed_output = false;
	if($jobs[$i]['detailed_output'] == 1)
	{
		$detailed_output = true;
		$mail .= 'True';
		// Write $somecontent to our opened file.
		if(fwrite($ohandle, "true") === false)
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	else 
	{	
		$mail .= 'False';
		// Write $somecontent to our opened file.
		if(fwrite($ohandle, "false") === false)
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	$mail .= "<br />\r\nMass action only: ";
	// Write $somecontent to our opened file.
	if(fwrite($ohandle, $line_ending ."Mass action only: ") === false)
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}
	$mass_action_only = false;
	if($jobs[$i]['mass_action_only'] == 1)
	{
		$mass_action_only = true;
		$mail .= 'True';
		// Write $somecontent to our opened file.
		if(fwrite($ohandle, "true") === false)
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	else 
	{	
		$mail .= 'False';
		// Write $somecontent to our opened file.
		if(fwrite($ohandle, "false") === false)
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	$mail .= "<br />\r\nBatch submission time: ".$jobs[$i]['creation_timestamp']."</p>\r\n\r\n";
	// Write $somecontent to our opened file.
	if(fwrite($ohandle, $line_ending .'Batch submission time: '.$jobs[$i]['creation_timestamp'].$line_ending.$line_ending) === false)
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}

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
		// Special case: Sauro (6) has a single file with one network per LINE
		if ($jobs[$i]['file_format'] == 6)
		{
			// If the user is on a Mac, this folder might be present so we should ignore it to prevent errors
			$mac_dir_pos = array_search('__MACOSX', $extracted_files);
			if ($mac_dir_pos !== false)
			{
				array_map('unlink', glob($dirname.'/__MACOSX/{,.}*', GLOB_BRACE));				
				unset($extracted_files[$mac_dir_pos]);
				if (!rmdir($dirname.'/__MACOSX'))
				{
					$mail .= "<p>ERROR: Couldn't delete hidden files from zip created on Mac OS X.</p>";
					$success = false;
				}
				// "Re-index" the array, as this isn't automatic, and our sauro file is now in $extracted_files[3], whereas code below assumes index 2
				$extracted_files = array_values($extracted_files);
			}
			if (count($extracted_files) !== 3) // 3 due to sauro file, . and ..
			{
				$mail .= "<p>ERROR: Found ".(count($extracted_files) - 2)." files - Sauro archive must contain only one file (with one network per line).</p>\r\n";
				$success = false;
			}
			else 
			{
				$fhandle = fopen($dirname.'/'.$extracted_files[2], 'r');	
				$fileLabel = 1;
				while(!feof($fhandle))
				{
					$line = fgets($fhandle);
					$networkString = trim(preg_replace('/\s+/', ' ', $line));
					if($networkString and strpos($line, '#') !== 0) // If not empty line or comment create a file with this line
					{
						file_put_contents($dirname.'/'.$fileLabel, $networkString);
						++$fileLabel;
					}
				}
				fclose($fhandle);
				unlink($dirname.'/'.$extracted_files[2]);
				$extracted_files = scandir($dirname); // Refill the file array with the new files
			}
		}
		$file_found = false;
		if($extracted_files !== false)
		{
			foreach($extracted_files as $file)
			{
				if(!is_dir($file))
				{
					$file_found = true;
					// Write $somecontent to our opened file.
					if(fwrite($ohandle, $line_ending."## FILE: ".end(explode('/', $file))." ##".$line_ending.$line_ending."Processing start time: ".date('Y-m-d H:i:s')) === false)
					{
						$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
						$success = false;
					}						
					$fhandle = fopen($dirname.'/'.$file, 'r');
					//$mail .= "\r\n<h2>FILE: ".end(explode('/', $file))."</h2>\r\n\r\n<p>Processing start time: ".date('Y-m-d H:i:s')."<br />\r\nFile contents:</p>";
					$reaction_network = new ReactionNetwork();		
					switch($jobs[$i]['file_format'])
					{
						case 1: //Net stoichiometry
							$mimetype = get_mime($dirname.'/'.$file);
							if ($mimetype === 'text/plain')
							{
								$matrix = array();
								$mail .= "\r\n<p>WARNING: You uploaded a stoichiometry file. The output below will not be correct if any reactants appear on both sides of a reaction.</p>\r\n";
								//$mail .= '<pre>';
								while(!feof($fhandle))
								{
									$line = fgets($fhandle);
									// Write $somecontent to our opened file.
									/*if(fwrite($ohandle, "$line_ending$line") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}*/
									//$mail .= "\r\n$line";
									$row = trim(preg_replace('/\s+/', ' ', $line));
									if($row and strpos($row, '#') !== 0) $matrix[] = explode(' ', $row);
								}
								//$mail .= '</pre>';
								if(!$reaction_network->parseStoichiometry($matrix))
								{
									$mail .= "\r\n<p>ERROR: An error was detected in the stoichiometry file.</p>\r\n";
									$success = false;
								}
							}
							else $file_found = false;
							break; // End of case 1, net stoichiometry
						case 2: //Net stoichiometry + V
							$mimetype = get_mime($dirname.'/'.$file);
							if ($mimetype === 'text/plain')
							{}
							else $file_found = false;
							break;
						case 3: //Source + target + V
							$mimetype = get_mime($dirname.'/'.$file);
							if ($mimetype === 'text/plain')
							{}
							else $file_found = false;
							break;
						case 4: //Source + target
							$mimetype = get_mime($dirname.'/'.$file);
							if ($mimetype === 'text/plain')
							{
								//$mail .= '<pre>';
								$sourceMatrix = array();
								$targetMatrix = array();
								$row = '';
								while (!feof($fhandle) and mb_strtoupper(trim($row)) !== 'S MATRIX')
								{
									$row = fgets($fhandle);
									// Write $somecontent to our opened file.
									if(fwrite($ohandle, "$line_ending$row") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}
									//$mail .= "\r\n$row";
									//error_log($row."\n",3,'/var/tmp/crn.log');
								}
					
								while(!feof($fhandle) and mb_strtoupper($row) !== 'T MATRIX')
								{
									$row = trim(preg_replace('/\s+/', ' ', fgets($fhandle)));
									// Write $somecontent to our opened file.
									/*if(fwrite($ohandle, "$line_ending$row") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}
									//$mail .= "\r\n$row"; */
									if($row and strpos($row, '#') !== 0 and mb_strtoupper($row)!=='T MATRIX') $sourceMatrix[] = explode(' ', $row);
									//error_log($row."\n",3,'/var/tmp/crn.log');
								}
								while(!feof($fhandle))
								{
									$row = trim(preg_replace('/\s+/', ' ', fgets($fhandle)));
									// Write $somecontent to our opened file.
									/*if(fwrite($ohandle, "$line_ending$row") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}
									//$mail .= "\r\n$row";*/
									if($row and strpos($row, '#') !== 0) $targetMatrix[] = explode(' ', $row);
									//error_log($row."\n",3,'/var/tmp/crn.log');
								}
								//$mail .= "\r\n</pre>";
								if(!$reaction_network->parseSourceTargetStoichiometry($sourceMatrix, $targetMatrix))
								{
									$mail .= "<p>An error was detected in the stoichiometry file. </p>\r\n";
									//error_log(print_r($sourceMatrix, true), 3, '/var/tmp/crn.log');
									//error_log(print_r($targetMatrix, true), 3, '/var/tmp/crn.log');
									$success = false;
								}
							}
							else $file_found = false;
							break; // End of case 4, source + target stoichiometry

						case 5: // SBML (all levels)
							$mimetype = get_mime($dirname.'/'.$file);
							if ($mimetype === 'application/xml')
							{
								/*while(!feof($fhandle))
								{
									$lineString = fgets($fhandle);
									if(fwrite($ohandle, "$lineString") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}				
								}*/
								if (!$reaction_network->parseSBML($dirname.'/'.$file))
								{
									$mail .= "<p>An error was detected in the SBML file. </p>\r\n";
								}
							}
							else $file_found = false;
							break;
		
						// NB Sauro also handled above as each LINE represents a network, not each file
						case 6:
							while(!feof($fhandle))
							{
								$lineString = fgets($fhandle);
								if(fwrite($ohandle, "$line_ending$lineString") === false)
								{
									$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
									$success = false;
								}				
							}
							if (!$reaction_network->parseSauro($lineString))
							{
								$mail .= "<p>An error was detected in the Sauro file. </p>\r\n";
							}
							break;
						
						case 0: //Human
							//Fall through
						default: //Assume 'human' if unsure
							$mimetype = get_mime($dirname.'/'.$file);
							if ($mimetype === 'text/plain')
							{
								while(!feof($fhandle))
								{
									$reactionString = fgets($fhandle);
									//$mail .= '<pre>';
									// Write $somecontent to our opened file.
									/*if(fwrite($ohandle, "$line_ending$reactionString") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}*/
									//$mail .= "\r\n$reactionString";
									//$mail .= '</pre>';
									if($reactionString and strpos($reactionString, '#') !== 0)
									{
										$newReaction = Reaction::parseReaction($reactionString);
										if($newReaction) $reaction_network->addReaction($newReaction);
										elseif($success)
										{
											$mail .= "<p>ERROR: An error occurred while adding a reaction from the file.</p>\r\n";
											$success = false;
										}
									}
								}
							}
							else $file_found = false;		
							break;
					} // end of switch ($file_format)

					fclose($fhandle);
					// Write $somecontent to our opened file.
					if(fwrite($ohandle, $line_ending."Reaction network:$line_ending".$reaction_network->exportReactionNetworkEquations($line_ending)) === false)
					{
						$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
						$success = false;
					}
					//$mail .= "\r\n<p>Reaction network:</p>\r\n<pre>";
					//$mail .= $reaction_network->exportReactionNetworkEquations("\r\n");
					//$mail .= '</pre>';
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
								// Write $somecontent to our opened file.
								if(fwrite($ohandle, $line_ending. "### TEST: ".$currentTest->getShortName()." ###".$line_ending.$line_ending."Test start time: ".date('Y-m-d H:i:s').$line_ending.$line_ending) === false)
								{
									$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
									$success = false;
								}
								//$mail .= "\r\n<h3>TEST: ".$currentTest->getShortName()."</h3>\r\n\r\n<p>Test start time: ".date('Y-m-d H:i:s')."</p>\r\n\r\n";
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
									// Write $somecontent to our opened file.
									if(fwrite($ohandle, "Output:$line_ending-------$line_ending") === false)
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}
									//$mail .= "<p>Output:</p>\r\n-------\r\n";
									$exec_string .= ' '.$test_filename;
									if(isset($detailed_output) and $detailed_output) $exec_string .= ' 2>&1';
									else $exec_string .= ' 2> /dev/null';
									exec($exec_string, $output, $returnValue);
									//$mail .= '<pre>';
									if ($returnValue)
									{
										if(fwrite($ohandle, 'ERROR: Test failed, probably due to timeout.') === false)
										{
											$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
											$success = false;
										}
									}
									else
									{
										foreach($output as $line)
										{
											if(fwrite($ohandle, preg_replace('@(<a)(.+)(href=")(.+)(">)(.+)(</a>)@', '$6 [$4]', $line_ending.$line)) === false)
											{
												$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
												$success = false;
											}
										}
									}
									//foreach($output as $line) $mail .= "\r\n$line";
									// Write $somecontent to our opened file.						
									//$mail .= "\r\n</pre>";
								}
								if(fwrite($ohandle, $line_ending.$line_ending.'### END OF TEST: '.$currentTest->getShortName().' ###'.$line_ending.$line_ending) === false)
								{
									$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
									$success = false;
								}
								//$mail .= "\r\n\r\n<h3>END OF TEST: ".$currentTest->getShortName()."</h3>\r\n\r\n";
							} // foreach($tests_enabled as $currentTest)
						} // if($success)
					} // if($success)
					if(fwrite($ohandle, '## END OF FILE: '.end(explode('/', $file)).' ##'.$line_ending.$line_ending) === false)
					{
						$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
						$success = false;
						//$mail .= "<h2>END OF FILE: ".end(explode('/', $file))."</h2>\r\n\r\n";
					}
				} // if(!is_dir($file))
			} // foreach($extracted_files as $file)
		} // if($extracted_files !== false)
	} // if($success)
	$mail .= "\r\n<p>CoNtRol batch output is ready for download from <a href=\"".SITE_URL."download.php?filekey=".$jobs[$i]['filekey']."\">".SITE_URL."download.php?filekey=".$jobs[$i]['filekey']."</a>. Your results will be stored for one week. </p>\r\n<p>This auto-generated message was sent to you because someone requested processing of a batch job from IP address ".$jobs[$i]['remote_ip'].". If you did not make the request yourself please delete this email. Queries should be addressed to ".ADMIN_EMAIL.".</p>\r\n";
	// Set email headers.
	$admin_email_split = explode('@', ADMIN_EMAIL);
	$extra_headers =  "From: CoNtRol <".ADMIN_EMAIL.">\r\n";
	$extra_headers .= "MIME-Version: 1.0\r\n";
	$extra_headers .= "Content-Type: multipart/alternative;\r\n boundary=\"$boundary\"\r\n";
	$extra_headers .= "Message-ID: <".time().'-'.substr(hash('sha512', ADMIN_EMAIL.$jobs[$i]['email']), -10).'@'.end($admin_email_split).">\r\n";
	$extra_headers .= 'X-Originating-IP: ['.$jobs[$i]['remote_ip']."]\r\n";
	$sendmail_params = '-f'.ADMIN_EMAIL;

	// Create HTML and plain text versions of mail
	$plain_text_search = array('<br />', '<h1>', '<h2>', '<h3>', '<p>', '<pre>', '</h1>', '</h2>', '</h3>', '</p>', '</pre>');
	$plain_text_replace = array('', '', '## ', '### ', '', '', '', ' ##', ' ###', '', '');
	$html_search = array('<-->', '<--', '-->', "\r\n-------\r\n", "\r\n==============\r\n\r\n");
	$html_replace = array('&lt;--&gt;', '&lt;--', '--&gt;', "\r\n", "\r\n");
	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/plain; charset=utf-8;\r\n format=flowed\r\n";
	$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	// Remove HTML tags and replace links with bare URLs
	$body .= preg_replace('@(<a)(.+)(href=")(.+)(">)(.+)(</a>)@', '$6 [$4]', str_replace($plain_text_search, $plain_text_replace, $mail));
	$body .= "\r\n\r\n--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=utf-8;\r\n format=flowed\r\n";
	$body .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
	// Remove problematic plain text code and replace admin email with link
	$body .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\r\n".'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">'."\r\n<head>\r\n".'<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'."\r\n<title>CoNtRol output</title>\r\n</head>\r\n<body>\r\n".str_replace(ADMIN_EMAIL, '<a href="mailto:'.ADMIN_EMAIL.'">'.ADMIN_EMAIL.'</a>', str_replace($html_search, $html_replace, $mail))."</body>\r\n</html>\r\n";

	// Set the job to complete and remove the files
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
	fclose($ohandle);

	$zip = new ZipArchive();
	$zipfilename = TEMP_FILE_DIR."/".$jobs[$i]['filekey'].'.zip';
	if ($zip->open($zipfilename, ZipArchive::CREATE)!==TRUE)
	{
	    exit("cannot open <$zipfilename>\n");
	}
	$zip->addFile(TEMP_FILE_DIR."/".$jobs[$i]['filekey'].'.txt','control_output.txt');
	$zip->close();
	unlink(TEMP_FILE_DIR."/".$jobs[$i]['filekey'].'.txt');
} // for($i = 0; $i < $number_of_jobs; ++$i)

// Status 3 = output file downloaded; set them to status 4 once files removed
// Status 5 = unconfirmed; also remove these files since the job isn't going to be run
$query = 'SELECT id, filekey FROM '.DB_PREFIX.'batch_jobs WHERE status = 3 OR status = 5';
$statement = $controldb->prepare($query);
$statement->execute();
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result)
{
	unlink(TEMP_FILE_DIR."/".$result['filekey'].".zip");
	$query = 'UPDATE '.DB_PREFIX.'batch_jobs SET status = 4, update_timestamp = :timestamp WHERE id = :id';
	$statement = $controldb->prepare($query);
	$statement->bindValue(':timestamp', date('Y-m-d H:i:s'), PDO::PARAM_STR);
	$statement->bindParam(':id', $result['id'], PDO::PARAM_INT);
	$statement->execute();
}
