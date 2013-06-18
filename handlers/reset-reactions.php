<?php
/**
 * CoNtRol reaction network reset handler
 *
 * Deletes current reaction network data from the session
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    16/04/2013
 * @modified   18/06/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(isset($_POST['reset_reactions']) and isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	if(isset($_SESSION['reaction_network'])) unset($_SESSION['reaction_network']);
	if(isset($_SESSION['test_output'])) unset($_SESSION['test_output']);
}

if(CRN_DEBUG) print_r($_POST);
