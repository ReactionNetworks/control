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
 * @modified   09/08/2014
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
if( isset( $_POST['submitter'] ) )
{
	$_SESSION['submitter'] = trim( $_POST['submitter'] );
}

// If no errors were detected, attempt to store the network in the database
if( !count( $_SESSION['errors'] ) )
{
	// Check whether the submitted CRN is already in the database
	$crn_found = false;
	// Attempt to open the database and throw an exception if unable to do so
	try
	{
		$controldb = new PDO( DB_STRING, DB_USER, DB_PASS, $db_options );
	}
	catch(PDOException $exception)
	{
		die( 'Unable to open database. Error: ' . str_replace( DB_PASS, '********', $exception ) . '. Please contact the system administrator at ' . hide_email_address( ADMIN_EMAIL ) . '.' );
	}

	// Look up known CRNs with the same number of reactions and species from the database to check isomorphism
	$number_of_species = $_SESSION['reaction_network']->getNumberOfReactants();
	$number_of_reactions = $_SESSION['reaction_network']->getNumberOfReactionsIrreversible();
	$query = 'SELECT id, sauro_string, result FROM ' . DB_PREFIX . 'known_crns WHERE number_of_reactions = :number_of_reactions AND number_of_species = :number_of_species';
	$statement = $controldb->prepare( $query );
	$statement->bindParam( ':number_of_reactions', $number_of_reactions, PDO::PARAM_INT );
	$statement->bindParam( ':number_of_species', $number_of_species, PDO::PARAM_INT );
	$statement->execute();
	$matches = $statement->fetchAll( PDO::FETCH_ASSOC );
	$match_id = 0;

	// Check isomorphism for each potential match
	foreach( $matches as $match )
	{
		$temp_reaction_network = new ReactionNetwork();
		if( !$match_id and $temp_reaction_network->parseSauro( $match['sauro_string'] ) )
		{
			if( $_SESSION['reaction_network']->isIsomorphic( $temp_reaction_network ) )
			{
				$crn_found = true;
				$_SESSION['crn_description'] = $match['result'];
			}
		}
	}

	if( $crn_found )
	{
		$_SESSION['errors'][] = 'The CRN you submitted is already present in the database. Its description appears below. If you believe that the description is inaccurate, please notify the site administrator at ' . hide_email_address( ADMIN_EMAIL ) . '.';
	}
	else
	{
		// Store the new result in the database
		$timestamp = date( 'Y-m-d H:i:s' );
		$statement = $controldb->prepare( 'INSERT INTO ' . DB_PREFIX . 'known_crns (submitter, number_of_reactions, number_of_species, sauro_string, result, remote_ip, remote_user_agent, creation_timestamp, update_timestamp) VALUES (:submitter, :number_of_reactions, :number_of_species, :sauro_string, :result, :remote_ip, :remote_user_agent, :creation_timestamp, :update_timestamp)' );
		$statement->bindValue( ':submitter', trim( $_POST['submitter'] ), PDO::PARAM_STR );
		$statement->bindValue( ':number_of_reactions', $_SESSION['reaction_network']->getNumberOfReactionsIrreversible(), PDO::PARAM_INT );
		$statement->bindValue( ':number_of_species', $_SESSION['reaction_network']->getNumberOfReactants(), PDO::PARAM_INT );
		$statement->bindValue( ':sauro_string', $_SESSION['reaction_network']->exportSauro(), PDO::PARAM_STR );
		$statement->bindValue( ':result', sanitise( trim( $_POST['crn_description'] ) ), PDO::PARAM_STR );
		$statement->bindParam( ':remote_ip', $_SERVER['REMOTE_ADDR'], PDO::PARAM_STR );
		$statement->bindParam( ':remote_user_agent', $_SERVER['HTTP_USER_AGENT'], PDO::PARAM_STR );
		$statement->bindParam( ':creation_timestamp', $timestamp, PDO::PARAM_STR );
		$statement->bindParam( ':update_timestamp', $timestamp, PDO::PARAM_STR );
		//if( !$statement->execute() ) die( print_r( $statement->errorInfo(), true ) );
		if( !$statement->execute() ) $_SESSION['errors'][] = 'There was an error inserting the new result into the database. Please contact the site administrator.';
		$controldb = null;

		// Notify the administrator, if this option has been configured
		if( NOTIFY_ADMIN_OF_CRN_SUBMISSION )
		{
			$admin_email_split = explode( '@', ADMIN_EMAIL );
			$extra_headers =  "From: CoNtRol <" . ADMIN_EMAIL . ">\r\n";
			$extra_headers .= "MIME-Version: 1.0\r\n";
			$extra_headers .= "Content-Type: text/plain; charset=utf-8; format=flowed\r\n";
			$extra_headers .= "Content-Transfer-Encoding: 8bit\r\n";
			$extra_headers .= "Message-ID: <" . time() . '-' . substr( hash( 'sha512', ADMIN_EMAIL . uniqid() ), -10) . '@' . end($admin_email_split) . ">\r\n";
			$extra_headers .= 'X-Originating-IP: [' . $_SERVER['REMOTE_ADDR'] . "]\r\n";
			$sendmail_params = '-f' . ADMIN_EMAIL;
			if( trim( $_POST['submitter'] ) ) $submitter = sanitise( trim( $_POST['submitter'] ) );
			else $submitter = 'Anonymous';
			$sauro = $_SESSION['reaction_network']->exportSauro();
			$sauro_array = explode( ' ', $sauro );
			$body = "Details of new CRN\r\n==================\r\n\r\n";
			$body .= "Submitter: $submitter\r\n\r\n";
			$body .= "Reaction network:\r\n" . $_SESSION['reaction_network']->exportReactionNetworkEquations( "\r\n" ) . "\r\n";
			$body .= "Number of irreversible reactions: {$sauro_array[1]}\r\n\r\n";
			$body .= "Number of reactants: {$sauro_array[0]}\r\n\r\n";
			$body .= "Sauro string: $sauro\r\n\r\n\r\n";
			$body .= "Description:\r\n------------\r\n" . sanitise( trim( $_POST['crn_description'] ) ) . "\r\n\r\n\r\n-- \r\n";
			$body .= "Remote IP: " . $_SERVER['REMOTE_ADDR'] . "\r\n\r\n";
			$body .= "Remote user agent: " . sanitise( $_SERVER['HTTP_USER_AGENT'] ) . "\r\n\r\n";
			$body .= "Timestamp: $timestamp\r\n\r\n";
			if( !mail( '<' . ADMIN_EMAIL . '>', 'New CRN submitted to CoNtRol', $body, $extra_headers, $sendmail_params ) )
			{
				// TODO: add proper error logging
				$_SESSION['errors'][] = 'Your submission was successfully received, but an error occurred when notifying the site administrator. Please contact the site administrator.';
			}
		}

		// Set an acknowledgment message to the user and reset the fields
		$_SESSION['errors'][] = 'Your CRN submission was successfully received. Thank you for helping to make CoNtRol more useful!';
		unset( $_SESSION['crn_description'] );
		unset( $_SESSION['reaction_network'] );
	}
}

// Redirect back to the submission page so the user can see if their submission was successful
header( 'Location: ' . SITE_URL . 'submit-known-crn.php' );
die();
