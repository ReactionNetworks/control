<?php
/**
 * CoNtRol reaction network show reaction rate Jacobian
 *
 * Outputs LaTeX version of reaction rate Jacobian matrix
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    02/06/2013
 * @modified   02/06/2013
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');
require_once('../includes/functions.php');
require_once('../includes/session.php');
require_once('../includes/standard-tests.php');

if(isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	if(isset($_SESSION['reaction_network'])) die($_SESSION['reaction_network']->exportVMatrix(true));
	else die('No reaction network found');
}
else die('CSRF attempt detected');
