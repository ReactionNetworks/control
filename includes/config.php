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
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   10/10/2012
 */

$protocol = 'http';
if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] and $_SERVER['HTTPS'] != 'off') $protocol .= 's';

define('SITE_DIR', 'control', false);

define('SITE_URL', $protocol.'://'.$_SERVER['HTTP_HOST'].'/'.SITE_DIR.'/', false);

define('DEFAULT_PAGE_TITLE', 'CoNtRol - Chemical Reaction Network analysis tool', false);

define('DEFAULT_PAGE_DESCRIPTION', 'Allows the user to input a chemical reaction network. Produces a DSR graph and carries out mathematical analysis of network.', false);

// Default to UNIX line ending
$line_ending = "\n";
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Windows;') !== false) $line_ending = "\r".$line_ending;
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Macintosh;') !== false) $line_ending = "\r";

define('CLIENT_LINE_ENDING', $line_ending, false);

define('CRNDEBUG', true, false);
