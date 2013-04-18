#!/usr/bin/env php
<?php 
/**
 * CoNtR ol batch processing script
 *
 * This script checks the database for unprocessed batch jobs and processes them.
 * The results are then emailed to the batch originator. It is intended to be called via a cron job.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    18/04/2013
 * @modified   18/04/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');

// Set email headers.
$extra_headers =  "From: CoNtRol <".ADMIN_EMAIL.">\r\n";
$extra_headers .= "MIME-Version: 1.0\r\n";
$extra_headers .= "Content-type: text/plain; charset=utf-8; format=flowed\r\n";
$extra_headers .= "Content-Transfer-Encoding: 8bit\r\n";
$sendmail_params = '-f'.ADMIN_EMAIL;

$mail = "CoNtRol Output\r\n";
$mail .= "\r\n";
$mail .= "CoNtRol Version ".CONTROL_VERSION."\r\n";
$mail .= "\r\n";

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

for($i=0; $i < $number_of_jobs; ++$i)
{
	$query = 'UPDATE '.DB_PREFIX. 'batch_jobs SET status = 1, update_timestamp = :timestamp WHERE id = :id';
	$statement = $controldb->prepare($query);
	$statement->bindParam(':id', $jobs[$i]['id'], PDO::PARAM_INT);
	$statement->bindValue(':timestamp', date('Y-m-d H:i:s'), PDO::PARAM_STR);
	$statement->execute();
}

for($i=0; $i < $number_of_jobs; ++$i)
{
	// Initialise some other variables.
	$mass_action_only = false;
	if($jobs[$i]['mass_action_only'] == 1) $mass_action_only = true;
	$tests_enabled = explode($jobs[$i]['tests_enabled'], ';');
	$filename = $jobs[$i]['filename'];
	$mimetype = get_mime($filename);
	switch($mimetype)
	{
		case 'application/zip':
			break;
		case 'application/rar':
			break;
		default:
			break;
	}
}