<?php
/**
 * CoNtRol reaction network test enable/disable handler
 *
 * Handles ajax requests to enable/disable tests.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    18/01/2013
 * @modified   24/04/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	if(!isset($_SESSION['tests'])) $_SESSION['tests'] = array();
	
	foreach($standardTests as $test)
	{
		if(isset($_POST['testName']) and $_POST['testName'] === $test->getShortName())
		{
			$_SESSION['tests'][$test->getShortName()] = (bool) $_POST['active'];
		}
	}
}

if(CRN_DEBUG) print_r($_POST);
