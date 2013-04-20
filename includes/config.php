<?php
/**
 * CoNtRol configuration file
 *
 * Configuration details for CoNtRol. This file is included at the top of header.php, and
 * hence is automatically included in every page that produces HTML output. It must be
 * included separately in each handler page.
 *
 * Note: when configuring your site, make sure to set SITE_DIR to the correct relative path.
 *
 * @author     Pete Donnell <pete dot donnell at port at ac at uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   20/04/2013
 */

$protocol = 'http';
if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] and $_SERVER['HTTPS'] != 'off') $protocol .= 's';

define('SITE_DIR', 'control', false);

if(isset($_SERVER['HTTP_HOST'])) define('SITE_URL', $protocol.'://'.$_SERVER['HTTP_HOST'].'/'.SITE_DIR.'/', false);

define('DEFAULT_PAGE_TITLE', 'CoNtRol - Chemical Reaction Network analysis tool', false);

define('DEFAULT_PAGE_DESCRIPTION', 'Allows the user to input a chemical reaction network. Produces a DSR graph and carries out mathematical analysis of network.', false);

// Default to UNIX line ending
$line_ending = "\n";
if(isset($_SERVER['HTTP_USER_AGENT']))
{
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Windows;') !== false) $line_ending = "\r".$line_ending;
	if(strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh;') !== false) $line_ending = "\r";
}

define('CLIENT_LINE_ENDING', $line_ending, false);

define('CRNDEBUG', false, false);

define('TEMP_FILE_DIR', '/var/tmp/', false);

define('BINARY_FILE_DIR', '../bin/', false);

define('DB_STRING', 'mysql:host=localhost;dbname=control;charset=utf8', false);
define('DB_USER', 'control', false);
define('DB_PASS', 'password', false);
//Include the following two lines if you're using mysql for the database.
if(!defined(PHP_VERSION_ID) or PHP_VERSION_ID < 50306) $db_options=array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8');
else $db_options=null;
define('DB_PREFIX', '', false);

define('ADMIN_EMAIL', 'control@reaction-networks.net', false);

$supported_batch_file_types = array(
	array('extension' => 'zip', 'mimetype' => 'application/zip', 'binary' => '/usr/bin/unzip'),
//	array('extension' => 'rar', 'mimetype' => 'application/rar', 'binary' => '/usr/bin/unrar e')
);
