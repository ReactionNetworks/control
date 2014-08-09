<?php
/**
 * CoNtRol batch upload confirmation page
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    11/04/2013
 * @modified   09/08/2014
 */

$title = 'CoNtRol - batch submission acknowledgement';

/**
 * Standard HTML headers
 */
require_once( 'includes/header.php' );

if( isset( $_POST['cancel'] ) )
{
	if( isset( $_SESSION['batch_job_id'] ) )
	{
		try
		{
			$controldb = new PDO( DB_STRING, DB_USER, DB_PASS, $db_options );
		}
		catch( PDOException $exception )
		{
			die( 'Unable to open database. Error: ' . str_replace( DB_PASS, '********', $exception ) . '. Please contact the system administrator at ' . hide_email_address( ADMIN_EMAIL ) . '.' );
		}
		$query = 'UPDATE ' . DB_PREFIX . 'batch_jobs SET status = 4 WHERE id = ' . $_SESSION['batch_job_id'];
		$statement = $controldb->prepare( $query );
		$statement->execute();
		// Delete the temporary files associated with this job
		$query = 'SELECT * FROM ' . DB_PREFIX . 'batch_jobs WHERE id = ' . $_SESSION['batch_job_id'];
		$statement = $controldb->prepare( $query );
		$statement->execute();
		$entry = $statement->fetch( PDO::FETCH_ASSOC );
		array_map( 'unlink', glob( $entry['filename'] . '*' ) );
?>
		<div id="results">
			<h2>Batch job cancelled</h2>
			<p>Your job has been cancelled.</p>
			<br /><a class="button" href=".">Back to main page</a>
		</div>
<?php
	}
	else
	{
?>
		<div id="results">
			<h2>Error cancelling batch job</h2>
			<p>The job you attempted to cancel could not be found.</p>
			<br /><a class="button" href=".">Back to main page</a>
		</div>
<?php
	}
}

else
{
	if( !( isset( $_SESSION['tempfile']) and isset( $_SESSION['email'] ) ) ) die( "\t\t\t\t<p>No uploaded files found.</p>\n\t\t\t</div><!-- content -->\n\t\t</div><!-- container -->\n\t</body>\n</html>\n" );
?>
		<div id="results">
			<h2>Batch upload acknowledgement</h2>
<?php
	// If no errors or warnings found, then send job straight through. Otherwise the job must be confirmed
	if( !$_SESSION['format_warning'] ):
?>
			<p>Your batch job has been added to the queue. Results will be sent to you at <?php echo sanitise($_SESSION['email']); ?> once processing is complete. If you have any problems please email the site admin at <?php echo hide_email_address( ADMIN_EMAIL ); ?>.</p>
			<p>
				<br /><a class="button" href=".">Back to main page</a>
			</p>
<?php
	else: // ( $_SESSION['format_warning'] )
?>
			<p>There are warnings/errors about this job; please check the above messages. If you believe the results will not be of use to you, please cancel the job. Otherwise, results will be sent to you at <?php echo sanitise($_SESSION['email']); ?> once processing is complete. If you have any problems please email the site admin at <?php echo hide_email_address( ADMIN_EMAIL ); ?>.</p>
			<p>
				<form id="cancel_job_form" method="post">
					<a class="button" href=".">Back to main page</a>
					<input type="hidden" name="cancel" />
					<input type="submit" value="Cancel job" class="button" id="cancel_job_button" />
				</form>
			</p>
<?php
	endif; // ( !$_SESSION['format_warning'] )
	unset($_SESSION['format_warning']);
?>
		</div><!-- results -->
	<?php
}

/**
 * Standard HTML footers
 */
require_once( 'includes/footer.php' );
