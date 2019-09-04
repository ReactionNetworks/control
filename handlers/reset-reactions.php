<?php
/**
 * CoNtRol reaction network reset handler
 *
 * Deletes current reaction network data from the session
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2019 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    16/04/2013
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

if( verify_csrf_token() and isset( $_POST['reset_reactions'] ) )
{
	if( isset( $_SESSION['reaction_network'] ) ) unset( $_SESSION['reaction_network'] );
	if( isset( $_SESSION['test_output'] ) ) unset( $_SESSION['test_output'] );
}

if( CRNDEBUG ) print_r( $_POST );

///:~
