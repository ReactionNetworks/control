<?php
/**
 * CoNtRol reaction network mass action enable/disable handler
 *
 * Handles ajax requests to enable/disable mass action only flag for tests.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @link       https://reaction-networks.net/control/download/
 * @package    CoNtRol
 * @created    14/02/2013
 * @modified   29/04/2014
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(!isset($_SESSION['mass_action_only'])) $_SESSION['mass_action_only'] = false;

if(isset($_POST['mass_action_only']) and isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	$_SESSION['mass_action_only'] = (bool) $_POST['mass_action_only'];
}

if(CRNDEBUG) print_r($_POST);
