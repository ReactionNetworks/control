<?php
/**
 * CoNtRol reaction network detailed test output enable/disable handler
 *
 * Handles ajax requests to enable/disable detailed output for tests.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2019 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    28/05/2013
 * @modified   04/09/2019
 */

/**
 * Standard include
 */
require_once( '../includes/config.php' );

/**
 * Standard include
 */
require_once( '../includes/classes.php' );

/**
 * Standard include
 */
require_once( '../includes/functions.php' );

/**
 * Standard include
 */
require_once( '../includes/session.php' );

/**
 * Standard include
 */
require_once( '../includes/standard-tests.php' );

if( !isset( $_SESSION['detailed_output'] ) ) $_SESSION['detailed_output'] = false;

if( verify_csrf_token() and isset( $_POST['detailed_output'] ) )
{
	$_SESSION['detailed_output'] = (bool) $_POST['detailed_output'];
}

if( CRNDEBUG ) print_r( $_POST );

///:~
