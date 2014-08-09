<?php
/**
 * CoNtRol known (interesting) CRN submission handler
 *
 * Imports information about an interesting CRN and stores it for in the
 * list of known CRNs for use in the isomorphism test.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    08/08/2014
 * @modified   08/08/2014
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

$_SESSION['errors'] = array();

// Check that the security code was entered correctly
if( REQUIRE_CAPTCHA and ( !( isset( $_POST['batch_security_code'] ) ) or ( $_POST['batch_security_code'] !== $_SESSION['batch-captcha'] ) ) ) $_SESSION['errors'][] = 'The security code entered was not correct - please try again.';

// Check that the CSRF code was correct
if( !isset( $_POST['csrf_token'] ) or $_POST['csrf_token'] !== $_SESSION['csrf_token'] ) $_SESSION['errors'][] = 'CSRF check failed, please try again.';

// Check that a CRN description was entered
if( isset( $_POST['crn_description'] ) ) $_SESSION['crn_description'] = trim( $_POST['crn_description'] );
if( !isset( $_POST['crn_description'] ) or
   strlen( trim( $_POST['crn_description'] ) ) < 32 or
   strpos( trim( $_POST['crn_description'] ), 'Enter the CRN description here, and its reactions below.' ) !== false ) $_SESSION['errors'][] = 'CRN description missing. Please check and try again.';

// Check that a nonempty reaction network was entered
if( !( isset( $_SESSION['reaction_network'] ) ) or !$_SESSION['reaction_network']->getNumberOfReactions() or !$_SESSION['reaction_network']->getNumberOfReactants() ) $_SESSION['errors'][] = 'The CRN appears to be empty. Please check and try again.';

// Store the submitter and description, in case there was an error and the user needs to try again, or they want to submit another CRN
if( isset( $_POST['submitter'] ) and trim( $_POST['submitter'] ) )
{
	$_SESSION['submitter'] = trim( $_POST['submitter'] );
}

// If no errors were detected, store the network in the database
if( !count( $_SESSION['errors'] ) )
{
	// Attempt to open the database and throw an exception if unable to do so
	try
	{
		$controldb = new PDO(DB_STRING, DB_USER, DB_PASS, $db_options);
	}
	catch(PDOException $exception)
	{
		die( 'Unable to open database. Error: ' . $exception . '. Please contact the system administrator at ' . str_replace( '@', ' at ', str_replace( '.', ' dot ', ADMIN_EMAIL ) ) . '.' );
	}

	// Store the new result in the database
	$statement = $controldb->prepare( 'INSERT INTO ' . DB_PREFIX . 'known_crns (submitter, number_of_reactions, number_of_species, sauro_string, result, remote_ip, remote_user_agent, creation_timestamp, update_timestamp) VALUES (:submitter, :number_of_reactions, :number_of_species, :sauro_string, :result, :remote_ip, :remote_user_agent, :creation_timestamp, :update_timestamp)' );
	$statement->bindValue( ':submitter', trim( $_POST['submitter'] ), PDO::PARAM_STR );
	$statement->bindValue( ':number_of_reactions', $_SESSION['reaction_network']->getNumberOfReactions(), PDO::PARAM_INT );
	$statement->bindValue( ':number_of_species', $_SESSION['reaction_network']->getNumberOfReactants(), PDO::PARAM_INT );
	$statement->bindValue( ':sauro_string', $_SESSION['reaction_network']->exportSauroEdges(), PDO::PARAM_STR );
	$statement->bindValue( ':result', sanitise( trim( $_POST['crn_description'] ) ), PDO::PARAM_STR );
	$statement->bindParam( ':remote_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR );
	$statement->bindParam( ':remote_user_agent', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR );
	$statement->bindValue( ':creation_timestamp', date( 'Y-m-d H:i:s' ), PDO::PARAM_STR );
	$statement->bindValue( ':update_timestamp', date( 'Y-m-d H:i:s' ), PDO::PARAM_STR );
	//if( !$statement->execute() ) die( print_r( $statement->errorInfo(), true ) );
	$controldb = null;

	// Notify the administrator, if this option has been configured
	// Set an acknowledgment message to the user and reset the fields
	$_SESSION['errors'][] = 'Your CRN submission was successfully received. Thank you for helping to make CoNtRol more useful!';
	unset( $_SESSION['crn_description'] );
	unset( $_SESSION['reaction_network'] );
}

// Redirect back to the submission page so the user can see if their submission was successful
header( 'Location: ' . SITE_URL . 'submit-known-crn.php' );
die();
