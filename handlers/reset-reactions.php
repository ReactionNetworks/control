<?php
/**
 * Reset reaction handler
 *
 * Handler to receive AJAX requests to reset all stored reactions
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    11/06/2013
 * @modified   11/06/2013
 */

require_once('../includes/session.php');
if(isset($_POST['csrf_token']) and $_POST['csrf_token'] === $_SESSION['csrf_token'])
{
	if(isset($_POST['reset_reactions'])
	   and $_POST['reset_reactions']
	   and isset($_SESSION['reaction_network']))
		unset($_SESSION['reaction_network']);
}
else die('CSRF attempt detected');
