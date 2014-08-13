#!/usr/bin/env php
<?php
/**
 * CoNtRol batch processing script
 *
 * This script checks the database for unprocessed batch jobs and processes them.
 * The results are then emailed to the batch originator. It is intended to be called via a cron job.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    18/04/2013
 * @modified   13/08/2014
 */

/**
 * Standard include
 */
require_once('../includes/config.php');

/**
 * Standard include
 */
require_once('../includes/classes.php');

/**
 * Standard include
 */
require_once('../includes/functions.php');

/**
 * Standard include
 */
require_once('../includes/version.php');
/**
 * Standard include
 */
require_once('../includes/standard-tests.php');

// Attempt to open the database and throw an exception if unable to do so
try
{
	$controldb = new PDO(DB_STRING, DB_USER, DB_PASS, $db_options);
}
catch(PDOException $exception)
{
	die( 'Unable to open database. Error: ' . str_replace( DB_PASS, '********', $exception ) . '. Please contact the system administrator at ' . hide_email_address( ADMIN_EMAIL ) . '.');
}

// Set 'not started' jobs to 'in progress'
$query = 'SELECT * FROM ' . DB_PREFIX . 'batch_jobs WHERE status = 0';
$statement = $controldb->prepare($query);
$statement->execute();
$jobs = $statement->fetchAll(PDO::FETCH_ASSOC);
$number_of_jobs = count($jobs);

for($i = 0; $i < $number_of_jobs; ++$i)
{
	$query = 'UPDATE ' . DB_PREFIX . 'batch_jobs SET status = 1, update_timestamp = :timestamp WHERE id = :id';
	$statement = $controldb->prepare($query);
	$statement->bindParam(':id', $jobs[$i]['id'], PDO::PARAM_INT);
	$statement->bindValue(':timestamp', date('Y-m-d H:i:s'), PDO::PARAM_STR);
	$statement->execute();
}

for($i = 0; $i < $number_of_jobs; ++$i)
{
	$success = true;
	$output_filename = TEMP_FILE_DIR.'/'.$jobs[$i]['filekey'].'.txt';
	if(!$ohandle = fopen($output_filename, 'w'))
	{
		$mail .= "<p>ERROR: Cannot open file ($output_filename)</p>\r\n";
		$success = false;
	}
	$boundary = hash( 'sha256', uniqid( time() ) );
	$mail = "<h1>CoNtRol Output</h1>\r\n";
	$mail .= "==============\r\n\r\n";
	$mail .= '<p>Version: ' . CONTROL_VERSION . "<br />\r\n";
	$mail .= '<p>Original filename: ' . sanitise( $jobs[$i]['original_filename'] ) . "<br />\r\n";

	// Initialise some variables
	$line_ending = "\n";
	if( strpos( $jobs[$i]['remote_user_agent'], 'Windows;' ) !== false ) $line_ending = "\r".$line_ending;
	if( strpos( $jobs[$i]['remote_user_agent'], 'Macintosh;' ) !== false ) $line_ending = "\r";

	if( fwrite( $ohandle, "CoNtRol Output$line_ending==============" . $line_ending . $line_ending . "Version: " . CONTROL_VERSION . $line_ending ) === false )
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}

	$tests_enabled = explode( ';', $jobs[$i]['tests_enabled'] );
	$mail .= 'Tests enabled:';

	if( fwrite( $ohandle, "Tests enabled:" ) === false )
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}

	foreach( $tests_enabled as $test )
	{
		$mail .= " $test";
		if( fwrite( $ohandle, " $test" ) === false )
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	$mail .= "<br />\r\nDetailed test output: ";
	if( fwrite( $ohandle, $line_ending."Detailed test output: " ) === false )
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}
	$detailed_output = false;
	if( $jobs[$i]['detailed_output'] == 1 )
	{
		$detailed_output = true;
		$mail .= 'Yes';
		if( fwrite( $ohandle, 'Yes' ) === false )
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}
	else
	{
		$mail .= 'No';
		if( fwrite( $ohandle, 'No' ) === false )
		{
			$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
			$success = false;
		}
	}

	$mail .= "<br />\r\nBatch submission time: ".$jobs[$i]['creation_timestamp']."</p>\r\n\r\n";
	if( fwrite( $ohandle, $line_ending . 'Batch submission time: ' . $jobs[$i]['creation_timestamp'] . $line_ending . $line_ending ) === false )
	{
		$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
		$success = false;
	}

	$filename = $jobs[$i]['filename'];
	$dirname = TEMP_FILE_DIR . 'control/' . $jobs[$i]['id'];
	$mimetype = get_mime( $filename );
	$success = false;
	switch( $mimetype )
	{
		case 'application/zip':
			$archive = new ZipArchive;
			$success = $archive->open( $filename );
			break;
		default:
			$mail .= "<p>ERROR: Unsupported archive type: $mimetype</p>\r\n";
			break;
	}
	if( !$success ) $mail .= "<p>ERROR: Failed to open archive $filename</p>\r\n";
	else
	{
		$success = mkdir( $dirname, 0700, true );
		if( $success )
		{
			$archive->extractTo( $dirname );
			$archive->close();
		}
		else $mail .= "<p>ERROR: Failed to create temporary directory</p>\r\n";
	}
	if( $success )
	{
		$extracted_files = scandir($dirname);
		// If the user is on a Mac, this folder might be present so we should ignore it to prevent errors
		$mac_dir_pos = array_search( '__MACOSX', $extracted_files );
		if( $mac_dir_pos !== false )
		{
			recursive_remove_directory( $dirname . '/__MACOSX' );
			unset( $extracted_files[$mac_dir_pos] );
			// "Re-index" the array, as this isn't automatic, and our data files start at $extracted_files[3], whereas code below assumes index 2
			$extracted_files = array_values( $extracted_files );
		}
		// Special case: Sauro (6) has a single file with one network per LINE
		if( $jobs[$i]['file_format'] == 6 )
		{
			if( count( $extracted_files ) !== 3 ) // 3 due to sauro file, . and ..
			{
				$mail .= '<p>ERROR: Found ' . ( count( $extracted_files ) - 2 ) . " files - Sauro archive must contain only one file (with one network per line).</p>\r\n";
				$success = false;
			}
			else
			{
				$fhandle = fopen( $dirname . '/' . $extracted_files[2], 'r' );
				$fileLabel = 1;
				while( !feof( $fhandle ) )
				{
					$line = fgets( $fhandle );
					$networkString = trim( preg_replace( '/\s+/', ' ', $line ) );
					if( $networkString and strpos( $line, '#' ) !== 0 and strpos( $line, '//' ) !== 0 ) // If not empty line or comment create a file with this line
					{
						file_put_contents( $dirname . '/' . $fileLabel, $networkString );
						++$fileLabel;
					}
				}
				fclose( $fhandle );
				unlink( $dirname . '/' . $extracted_files[2] );
				$extracted_files = scandir( $dirname ); // Refill the file array with the new files
			}
		}
		$file_found = false;
		if( $extracted_files !== false )
		{
			foreach( $extracted_files as $file )
			{
				$success = true;
				if( !is_dir( $file ) )
				{
					$file_found = true;
					$file_name_parts = explode( '/', $file );
					if( fwrite( $ohandle, $line_ending . "## FILE: " . end( $file_name_parts ) . " ##" . $line_ending . $line_ending . "Processing start time: " . date( 'Y-m-d H:i:s' ) ) === false )
					{
						$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
						$success = false;
					}
					$fhandle = fopen( $dirname . '/' . $file, 'r' );
					$reaction_network = new ReactionNetwork();
					switch( $jobs[$i]['file_format'] )
					{
						case 1: // Net stoichiometry
							$mimetype = get_mime( $dirname . '/' . $file );
							if( strpos( $mimetype, 'text/' ) === 0 )
							{ // Hack as sometimes files with comments get detected as a different mime type
								$matrix = array();
								$mail .= "\r\n<p>WARNING: You uploaded a stoichiometry file. The output below will not be correct if any reactants appear on both sides of a reaction.</p>\r\n";
								while( !feof( $fhandle ) )
								{
									$line = fgets( $fhandle );
									$row = trim( preg_replace( '/\s+/', ' ', $line ) );
									if( $row and strpos( $row, '#' ) !== 0 and strpos( $row, '//' ) !== 0 ) $matrix[] = explode( ' ', $row );
								}
								if( !$reaction_network->parseStoichiometry( $matrix ) )
								{
									$mail .= "\r\n<p>ERROR: An error was detected in the stoichiometry file.</p>\r\n";
									$success = false;
								}
							}
							else $file_found = false;
							break; // End of case 1, net stoichiometry
						case 2: // Net stoichiometry + V
							if( strpos( $mimetype, 'text/' ) === 0 )
							{ // Hack as sometimes files with comments get detected as a different mime type
							}
							else $file_found = false;
							break;
						case 3: // Source + target + V
							$mimetype = get_mime( $dirname . '/' . $file );
							if( strpos( $mimetype, 'text/' ) === 0 )
							{ // Hack as sometimes files with comments get detected as a different mime type
							}
							else $file_found = false;
							break;
						case 4: // Source + target
							$mimetype = get_mime( $dirname . '/' . $file );
							if( strpos( $mimetype, 'text/' ) === 0 )
							{ // Hack as sometimes files with comments get detected as a different mime type
								$sourceMatrix = array();
								$targetMatrix = array();
								$row = '';
								while( !feof( $fhandle ) and mb_strtoupper( trim( $row ) ) !== 'S MATRIX' )
								{
									$row = fgets( $fhandle );
									if( fwrite( $ohandle, "$line_ending$row" ) === false )
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}
								}
								while( !feof( $fhandle ) and mb_strtoupper( $row ) !== 'T MATRIX' )
								{
									$row = trim( preg_replace( '/\s+/', ' ', fgets( $fhandle ) ) );
									if( $row and strpos( $row, '#' ) !== 0 and strpos( $row, '//' ) !== 0 and mb_strtoupper($row ) !== 'T MATRIX' ) $sourceMatrix[] = explode( ' ', $row );
								}
								while( !feof( $fhandle ) )
								{
									$row = trim( preg_replace( '/\s+/', ' ', fgets( $fhandle ) ) );
									if( $row and strpos( $row, '#' ) !== 0 and strpos( $row, '//' ) !== 0 ) $targetMatrix[] = explode( ' ', $row );
								}
								if( !$reaction_network->parseSourceTargetStoichiometry( $sourceMatrix, $targetMatrix ) )
								{
									$mail .= "<p>An error was detected in the stoichiometry file. </p>\r\n";
									$success = false;
								}
							}
							else $file_found = false;
							break; // End of case 4, source + target stoichiometry

						case 5: // SBML (all levels)
							$mimetype = get_mime( $dirname . '/' . $file );
							if( $mimetype === 'application/xml' )
							{
								$parse_SBML_success = $reaction_network->parseSBML( $dirname . '/' . $file );
								if( $parse_SBML_success !== true )
								{
									$mail .= "<p>Warning! An error was detected in the SBML file " . end( $file_name_parts ) . ": $parse_SBML_success</p>\r\n";
								}
							}
							else $file_found = false;
							break;

						// N.B. Sauro also handled above as each LINE represents a network, not each file
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

						case 0: // Human
							// Fall through
						default: // Assume 'human' if unsure
							$mimetype = get_mime($dirname.'/'.$file);
							if( strpos( $mimetype, 'text/' ) === 0 )
							{ // Hack as sometimes files with comments get detected as a different mime type
								while(!feof($fhandle))
								{
									$reactionString = fgets($fhandle);
									if($reactionString and strpos($reactionString, '#') !== 0 and strpos($reactionString, '//') !== 0)
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
					fclose( $fhandle );

					if( !$reaction_network->getNumberOfReactions() )
					{
						fwrite( $ohandle, $line_ending . $line_ending . 'Error: Reaction network contains no reactions. Aborting tests.' );
						$success = false;
					}

					if( fwrite( $ohandle, $line_ending . $line_ending . "Reaction network:$line_ending" . $reaction_network->exportReactionNetworkEquations( $line_ending ) . $line_ending . $line_ending ) === false )
					{
						$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
						$success = false;
					}
					if( $success )
					{
						// Initialise ini file for passing results between tests
						$inifilename = $filename . '.ini';
						file_put_contents( $inifilename, '' );


						// Create human-readable descriptor file
						$temp_filename = $filename . '.hmn';

						if( !$handle = fopen( $temp_filename, 'w' ) )
						{
							$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
							$success = false;
						}
						if( fwrite( $handle, $reaction_network->exportReactionNetworkEquations() ) === false )
						{
							$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
							$success = false;
						}
						fclose( $handle );

						// Create net stoichiometry descriptor file
						$temp_filename = $filename . '.sto';

						if( !$handle = fopen( $temp_filename, 'w' ) )
						{
							$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
							$success = false;
						}
						if( fwrite( $handle, $reaction_network->exportStoichiometryMatrix() ) === false )
						{
							$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
							$success = false;
						}
						fclose($handle);

						// Create net stoichiometry + V matrix descriptor file
						$temp_filename = $filename.'.s+v';

						if(!$handle = fopen($temp_filename, 'w'))
						{
							$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
							$success = false;
						}
						if( fwrite( $handle, $reaction_network->exportStoichiometryAndVMatrix() ) === false )
						{
							$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
							$success = false;
						}
						fclose($handle);

						// Create source stoichiometry + target stoichiometry + V matrix descriptor file
						$temp_filename = $filename . '.stv';

						if( !$handle = fopen( $temp_filename, 'w' ) )
						{
							$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
							$success = false;
						}

						if( fwrite( $handle, $reaction_network->exportSourceAndTargetStoichiometryAndVMatrix() ) === false )
						{
							$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
							$success = false;
						}
						fclose($handle);

						// Create GLPK data file
						$temp_filename = $filename . '.glpk';

						if( !$handle = fopen( $temp_filename, 'w' ) )
						{
							$mail .= "<p>ERROR: Cannot open file ($temp_filename)</p>\r\n";
							$success = false;
						}

						if( fwrite( $handle, $reaction_network->exportGLPKData() ) === false )
						{
							$mail .= "<p>ERROR: Cannot write to file ($temp_filename)</p>\r\n";
							$success = false;
						}
						fclose( $handle );

						if( $success )
						{
							foreach( $standardTests as $test )
							{
								foreach( $tests_enabled as &$enabled_test ) if( $enabled_test === $test->getShortName() ) $enabled_test = $test;
							}
							foreach( $tests_enabled as $currentTest )
							{
								$extension = '';
								$temp = '';
								if( fwrite( $ohandle, $line_ending . "### TEST: {$currentTest->getShortName()} ###" . $line_ending . $line_ending . " Test start time: " . date('Y-m-d H:i:s') . $line_ending . $line_ending ) === false )
								{
									$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
									$success = false;
								}
								// Need to split this into net stoichiometry versus source/target stoichiometry?
								// How best to treat reversible vs irreversible reactions in stoichiometry case?
								if( in_array( 'stoichiometry', $currentTest->getInputFileFormats() ) ) $extension = '.sto';
								if( in_array( 'stoichiometry+V', $currentTest->getInputFileFormats() ) ) $extension = '.s+v';
								if( in_array( 'S+T+V', $currentTest->getInputFileFormats() ) ) $extension = '.stv';
								if( in_array( 'GLPK', $currentTest->getInputFileFormats() ) ) $extension = '.glpk';
								if( in_array( 'human', $currentTest->getInputFileFormats() ) ) $extension = '.hmn';
								if( !$extension ) $mail .= "<p>ERROR: This test does not support any valid file formats. Test aborted.</p>\r\n";
								else
								{
									$exec_string = 'cd ' . BINARY_FILE_DIR . ' && ' . NICENESS . 'timeout ' . TEST_TIMEOUT_LIMIT;
									$exec_string .= ' ./' . $currentTest->getExecutableName();
									if( fwrite( $ohandle, "Output:$line_ending-------$line_ending" ) === false )
									{
										$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
										$success = false;
									}
									$test_filename = $filename . $extension;
									$exec_string .= ' ' . $test_filename;
									if( $currentTest->supportsIniFile() ) $exec_string .= ' --inifile=' . $inifilename;
									if( isset( $detailed_output ) and $detailed_output ) $exec_string .= ' 2>&1';
									else $exec_string .= ' 2> /dev/null';
									$output = array();
									$returnValue = 0;
									exec( $exec_string, $output, $returnValue );
									if( $returnValue )
									{
										if(fwrite($ohandle, 'ERROR: Test failed, probably due to timeout.') === false)
										{
											$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
											$success = false;
										}
									}
									else
									{
										foreach( $output as &$line )
										{
											// Strip out extraneous HTML from calc-jacobian test
											if( strpos( $line, '|' ) === false ) $delimiter = '|';
											elseif( strpos( $line, '`' ) === false ) $delimiter = '`';
											elseif( strpos( $line, '~' ) === false ) $delimiter = '~';
											elseif( strpos( $line, '@' ) === false ) $delimiter = '@';

											$line = preg_replace( $delimiter . '(<span)(.+?)(>)' . $delimiter, '', $line);
											$line = preg_replace( $delimiter . '(</span>)' . $delimiter, '', $line);

											if( fwrite( $ohandle, convert_links_to_plain_text( $line_ending . $line ) ) === false )
											{
												$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
												$success = false;
											}
										}
									}
								}
								if(fwrite($ohandle, $line_ending.$line_ending.'### END OF TEST: '.$currentTest->getShortName().' ###'.$line_ending.$line_ending) === false)
								{
									$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
									$success = false;
								}
							} // foreach($tests_enabled as $currentTest)
						} // if($success)
					} // if($success)
					$file_name_parts = explode( '/', $file );
					if( fwrite( $ohandle, '## END OF FILE: ' . end( $file_name_parts ) . ' ##' . $line_ending . $line_ending . $line_ending ) === false )
					{
						$mail .= "<p>ERROR: Cannot write to file ($output_filename)</p>\r\n";
						$success = false;
					}
				} // if(!is_dir($file))
			} // foreach($extracted_files as $file)
		} // if($extracted_files !== false)
	} // if($success)
	$mail .= "\r\n<p>CoNtRol batch output is ready for download from <a href=\"".SITE_URL."download.php?filekey=".$jobs[$i]['filekey']."\">".SITE_URL."download.php?filekey=".$jobs[$i]['filekey']."</a>. Your results will be stored for one week.</p>\r\n\r\n<p>-- <br />This message was automatically generated by <a href=\"".SITE_URL."\">CoNtRol</a>. It was sent to you because someone at IP address ".$jobs[$i]['remote_ip']." submitted a batch processing job with your email address. If this was not you, please delete this email. Queries should be addressed to ".ADMIN_EMAIL.".</p>\r\n";

	// Set email headers
	$admin_email_split = explode('@', ADMIN_EMAIL);
	$extra_headers =  "From: CoNtRol <".ADMIN_EMAIL.">\r\n";
	$extra_headers .= "MIME-Version: 1.0\r\n";
	$extra_headers .= "Content-Type: multipart/alternative;\r\n boundary=\"$boundary\"\r\n";
	$extra_headers .= "Message-ID: <".time().'-'.substr(hash('sha512', ADMIN_EMAIL.$jobs[$i]['email']), -10).'@'.end($admin_email_split).">\r\n";
	$extra_headers .= 'X-Originating-IP: ['.$jobs[$i]['remote_ip']."]\r\n";
	$sendmail_params = '-f'.ADMIN_EMAIL;

	// Create plain text version of mail
	$body = "--$boundary\r\n";
	$body .= "Content-Type: text/plain; charset=utf-8;\r\n format=flowed\r\n";
	$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	// Remove HTML tags and replace links with bare URLs
	$plain_text_search = array( '<br />', '<h1>', '<h2>', '<h3>', '<p>', '<pre>', '</h1>', '</h2>', '</h3>', '</p>', '</pre>' );
	$plain_text_replace = array( '', '', '## ', '### ', '', '', '', ' ##', ' ###', '', '' );
	$body .= convert_links_to_plain_text( str_replace( $plain_text_search, $plain_text_replace, $mail ) );

	// Create HTML version of email
	$body .= "\r\n\r\n--$boundary\r\n";
	$body .= "Content-Type: text/html; charset=utf-8;\r\n";
	$body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
	// Set HTML headers
	$body .= '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">'."\r\n".'<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">'."\r\n<head>\r\n".'<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />'."\r\n<title>CoNtRol output</title>\r\n</head>\r\n<body>\r\n";
	// Remove problematic plain text code and replace admin email with link
	$html_search = array('<-->', '<--', '-->', "\r\n-------\r\n", "\r\n==============\r\n\r\n");
	$html_replace = array('&lt;--&gt;', '&lt;--', '--&gt;', "\r\n", "\r\n");
	$body .= str_replace( ADMIN_EMAIL, '<a href="mailto:' . ADMIN_EMAIL . '">' . ADMIN_EMAIL . '</a>', str_replace( $html_search, $html_replace, $mail ) );
	// Close HTML
	$body .= "</body>\r\n</html>\r\n\r\n--$boundary--\r\n";

	// Set the job to complete and remove the files
	$mail_subject = 'CoNtRol Batch Output (input file ' . sanitise($jobs[$i]['original_filename']);
	if($jobs[$i]['label']) $mail_subject .= ', label ' . sanitise($jobs[$i]['label']);
	$mail_subject .= ')';
	if (!mail('<'.$jobs[$i]['email'].'>', $mail_subject, $body, $extra_headers, $sendmail_params))
	{
		echo 'CoNtRol batch mail sending failed at ', date( 'Y-m-d H:i:s' ), PHP_EOL, "\$sendmail_params: $sendmail_params", PHP_EOL, "\$extra_headers: $extra_headers", PHP_EOL, "\$mail: $mail";
	}
	elseif( $success )
	{
		$query = 'UPDATE '.DB_PREFIX.'batch_jobs SET status = 2, update_timestamp = :timestamp WHERE id = :id';
		$statement = $controldb->prepare( $query );
		$statement->bindParam(':id', $jobs[$i]['id'], PDO::PARAM_INT);
		$statement->bindValue(':timestamp', date('Y-m-d H:i:s'), PDO::PARAM_STR);
		$statement->execute();
		// Remove temporary files
		array_map('unlink', glob($jobs[$i]['filename'].'*'));
	}
	// Remove decompressed files
	recursive_remove_directory( $dirname );
	fclose( $ohandle );

	$zip = new ZipArchive();
	$zipfilename = TEMP_FILE_DIR . '/' . $jobs[$i]['filekey'] . '.zip';
	if( $zip->open( $zipfilename, ZipArchive::CREATE ) !== true )
	{
		exit( "Cannot open <$zipfilename>\n" );
	}
	$zip->addFile( TEMP_FILE_DIR . '/' . $jobs[$i]['filekey'] . '.txt' , sanitise( str_replace( '.zip', '', $jobs[$i]['original_filename'] ) ) . '_output.txt' );
	$zip->close();
	unlink( TEMP_FILE_DIR . '/' . $jobs[$i]['filekey'] . '.txt' );
} // for($i = 0; $i < $number_of_jobs; ++$i)

// Status 3 = output file downloaded; set them to status 4 once files removed
// Status 5 = unconfirmed; also remove these files since the job isn't going to be run
$query = 'SELECT id, filekey FROM ' . DB_PREFIX . 'batch_jobs WHERE status = 3 OR status = 5';
$statement = $controldb->prepare( $query );
$statement->execute();
$results = $statement->fetchAll( PDO::FETCH_ASSOC );
foreach( $results as $result )
{
	unlink( TEMP_FILE_DIR . $result['filekey'] . '.zip' );
	$query = 'UPDATE ' . DB_PREFIX . 'batch_jobs SET status = 4, update_timestamp = :timestamp WHERE id = :id';
	$statement = $controldb->prepare( $query );
	$statement->bindValue( ':timestamp', date( 'Y-m-d H:i:s' ), PDO::PARAM_STR );
	$statement->bindParam( ':id', $result['id'], PDO::PARAM_INT );
	$statement->execute();
}
// Remove leftover temporary files from interactive (i.e. not batch) tests
array_map( 'unlink', glob( TEMP_FILE_DIR . '*.hmn' ) );
array_map( 'unlink', glob( TEMP_FILE_DIR . '*.glpk' ) );
array_map( 'unlink', glob( TEMP_FILE_DIR . '*.sto' ) );
array_map( 'unlink', glob( TEMP_FILE_DIR . '*.stv' ) );
array_map( 'unlink', glob( TEMP_FILE_DIR . '*.s+v' ) );
