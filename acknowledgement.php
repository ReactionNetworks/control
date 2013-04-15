<?php
/**
 * CoNtRol batch upload confirmation page
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    11/04/2013
 * @modified   15/04/2013
 */

require_once('includes/header.php');

if(!(isset($_SESSION['tempfile']) and isset($_SESSION['email']))) die('No uploaded files found.');
?>
				<div id="results">
						<h2>Batch upload acknowledgement</h2>
						<p>Your batch job has been added to the queue. Results will be sent to you at <?php echo sanitise($_SESSION['email']); ?> once processing is complete. If you have any problems please email the site admin at <?php echo str_replace('@', ' at ', str_replace('.', ' dot ', ADMIN_EMAIL)); ?>. <a href=".">Back to main page</a>.</p>
				</div><!-- results -->
<?php
require_once('includes/footer.php');
