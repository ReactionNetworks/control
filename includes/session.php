<?php
/**
 * CoNtRol session handling
 *
 * Common code for managing user sessions.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    10/10/2012
 * @modified   10/10/2012
 */

require_once('config.php');
require_once('classes.php');
require_once('standard-tests.php');

// Disable session IDs in GET parameters
ini_set('session.use_trans_sid', false);
// Enable session tracking in cookies
ini_set('session.use_cookies', true);
// Force all sessions to use cookies
ini_set('session.use_only_cookies', true);
// Check if the client is on a secure connection
$https = false;
if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] != 'off') $https = true;
// Set session cookie, restrict it to the CoNtRol directory and host, if client is on secure connection then restrict cookie to secure connections too, and disallow JavaScript access
session_set_cookie_params(0, '/'.SITE_DIR.'/', $_SERVER['HTTP_HOST'], $https, true);
// Name the session, to avoid possible conflicts with other sessions on the same host
session_name('control');
session_start();
if (!isset($_SESSION['tempfile'])) $_SESSION['tempfile'] = TEMP_FILE_DIR.uniqid();
if (!isset($_SESSION['tests'])) $_SESSION['tests'] = array();
if (!isset($_SESSION['standardtests'])) $_SESSION['standardtests'] = $standardTests;