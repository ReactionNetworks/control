<?php
/**
 * CoNtRol reaction network show net stoichiometry
 *
 * Outputs LaTeX version of stoichiometry matrix
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    02/06/2013
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
	if(isset($_SESSION['reaction_network'])) die($_SESSION['reaction_network']->exportStoichiometryMatrix(true));
	else die('No reaction network found');
}
else die('CSRF attempt detected');
