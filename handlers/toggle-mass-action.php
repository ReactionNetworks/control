<?php
/**
 * CoNtRol reaction network mass action enable/disable handler
 *
 * Handles ajax requests to enable/disable mass action only flag for tests.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    14/02/2013
 * @modified   14/02/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(!isset($_SESSION['mass_action_only'])) $_SESSION['mass_action_only'] = false;

if(isset($_POST['mass_action_only']))
{
	$_SESSION['mass_action_only'] = (bool) $_POST['mass_action_only'];
}

if(CRN_DEBUG) print_r($_POST);
