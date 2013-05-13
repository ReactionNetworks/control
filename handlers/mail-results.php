<?php
/**
 * CoNtRol reaction network results mailer
 *
 * Sends completed results to the specified email address.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    08/05/2013
 * @modified   13/05/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');
require_once('../includes/version.php');

if(isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	// Check if a valid email address was submitted
	$valid = true;
	if(isset($_POST['email']) and trim($_POST['email']) and !strstr($_POST['email'], "\n"))
	{
		$sender_address = trim($_POST['email']);
		if(!strpos($sender_address, '@')) $valid = false;
		else
		{
			list($sender_username, $sender_domain) = explode('@', $sender_address);
			if(!(strpos($sender_domain, '.'))) $valid = false;
			elseif(function_exists('checkdnsrr'))
			{
				if(!(checkdnsrr($sender_domain.'.', 'MX') or checkdnsrr($sender_domain.'.', 'A'))) $valid = false;				
			}
		}
	}
	else $valid = false;
	if(!$valid) die('Invalid email address');

	$mail = "CoNtRol Output\r\n";
	$mail .= "==============\r\n\r\n";
	$mail .= "Version: ".CONTROL_VERSION."\r\n";

	$mail .= "Tests enabled:";
	foreach($_SESSION['test_output'] as $test => $result) $mail .= " $test";

	$mail .= "\r\nMass action only: ";
	$mass_action_only = false;
	if(isset($_SESSION['mass_action_only']) and $_SESSION['mass_action_only'] == true) $mail .= "True";
	else $mail .= "False";

	$mail .= "\r\nDownload request time: ".date('Y-m-d H:i:s')."\r\n\r\n";

	foreach($_SESSION['test_output'] as $test => $result)
	{
		$mail .= "\r\n### TEST: ".$test." ###\r\n\r\n";
		$mail .= "Output:\r\n-------\r\n";
		$mail .= "\r\n$result";
		$mail .= "\r\n\r\n### END OF TEST: ".$test." ###\r\n\r\n";
	}

	$mail .= "\r\nThis auto-generated message was sent to you because someone requested a set of results from IP address ".$_SERVER['REMOTE_ADDR'].". If you did not make the request yourself please delete this email. Queries should be addressed to ".ADMIN_EMAIL.".\r\n";

	// Set email headers.
	$extra_headers =  "From: CoNtRol <".ADMIN_EMAIL.">\r\n";
	$extra_headers .= "MIME-Version: 1.0\r\n";
	$extra_headers .= "Content-type: text/plain; charset=utf-8; format=flowed\r\n";
	$extra_headers .= "Content-Transfer-Encoding: 8bit\r\n";
	$extra_headers .= "Message-ID: <".time().'-'.substr(hash('sha512', ADMIN_EMAIL.$_POST['email']), -10).'@'.end(explode('@', ADMIN_EMAIL)).">\r\n";
	$extra_headers .= 'X-Originating-IP: ['.$_SERVER['REMOTE_ADDR']."]\r\n";
	$sendmail_params = '-f'.ADMIN_EMAIL;

	if (!mail('<'.trim($_POST['email']).'>', 'CoNtRol Results', $mail, $extra_headers, $sendmail_params)) die('Failed to send email');
	else die('Mail sent successfully');	  
}
else die('CSRF attempt detected');
