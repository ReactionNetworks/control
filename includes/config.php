<?php
/**
 * CoNtRol configuration file
 *
 * Configuration details for CoNtRol. This file is included at the top of header.php, and
 * hence is automatically included in every page that produces HTML output. It must be
 * included separately in each handler page.
 *
 * @author     Pete Donnell <pete.donnell@port.ac.uk>
 * @copyright  University of Portsmouth 2012
 * @created    01/10/2012
 * @modified   01/10/2012
 */

$protocol = 'http';
if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] and $_SERVER['HTTPS'] != 'off') $protocol .= 's';
//$directory = rtrim(dirname($_SERVER['PHP_SELF']), '/');
$directory = 'control';

define('SITE_URL', $protocol.'://'.$_SERVER['HTTP_HOST'].'/'.$directory.'/', false);

define('DEFAULT_PAGE_TITLE', 'CoNtRol - Chemical Reaction Network analysis tool', false);

define('DEFAULT_PAGE_DESCRIPTION', 'Allows the user to input a chemical reaction network. Produces a DSR graph and carries out mathematical analysis of network.', false);

// Default to UNIX line ending
$line_ending = "\n";
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Windows;') !== false) $line_ending = "\r".$line_ending;
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh;') !== false) $line_ending = "\r";

define('CLIENT_LINE_ENDING', $line_ending, false);

define('CRNDEBUG', false, false);
