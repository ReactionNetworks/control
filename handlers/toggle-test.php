<?php
/**
 * CoNtRol reaction network test enable/disable handler
 *
 * Handles ajax requests to enable/disable tests.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    18/01/2013
 * @modified   29/04/2014
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
require_once('../includes/session.php');

/**
 * Standard include
 */
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

if(CRNDEBUG) print_r($_POST);
