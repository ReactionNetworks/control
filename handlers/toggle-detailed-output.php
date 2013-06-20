<?php
/**
 * CoNtRol reaction network detailed test output enable/disable handler
 *
 * Handles ajax requests to enable/disable detailed output for tests.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    28/05/2013
 * @modified   28/05/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(!isset($_SESSION['detailed_output'])) $_SESSION['detailed_output'] = false;

if(isset($_POST['detailed_output']) and isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	$_SESSION['detailed_output'] = (bool) $_POST['detailed_output'];
}

if(CRN_DEBUG) print_r($_POST);
