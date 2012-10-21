<?php
/**
 * CoNtRol main page
 *
 * This is the default page for CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   10/10/2012
 */

require_once('includes/header.php');
?>
			<ul class="tabs">
				<li><a href="#reaction_input_form">Input reactions manually</a></li>
				<li><a href="#reaction_upload_form">Upload reaction file</a></li>
			</ul>

			<div id="reaction_input_holder">
				<form id="reaction_input_form" action="handlers/process-network.php" method="post">
					<p>
						<a class="button" id="add_reaction_button" href="#">Add new reaction</a>
						<a class="button <?php 	if(!isset($_SESSION['reactionNetwork'])) echo 'disabled'; ?>" id="remove_reaction_button" href="#">Remove last reaction</a>
						<a class="button <?php 	if(!isset($_SESSION['reactionNetwork'])) echo 'disabled'; ?>" id="reset_reaction_button" href="#">Reset all reactions</a>
					</p>
					<?php
					if(isset($_SESSION['reactionNetwork'])) echo $_SESSION['reactionNetwork']->generateFieldsetHTML();
					else 
					{
						?>

					<fieldset class="reaction_input_row">
						<input type="text" size="32" maxlength="128" class="reaction_left_hand_side" name="reaction_left_hand_side[]" />
						<select class="reaction_direction" name="reaction_direction[]">
							<option value="left">&larr;</option>
							<option value="both" selected="selected">&#x21cc;</option>
							<option value="right">&rarr;</option>
						</select>
						<input type="text" size="32" maxlength="128" class="reaction_right_hand_side" name="reaction_right_hand_side[]" />
					</fieldset><!-- reaction_input_row -->
					<?php
					}
					?>
					<p id="reaction_input_submit_buttons">
						<a class="button <?php 	if(!isset($_SESSION['reactionNetwork'])) echo 'disabled'; ?> " id="dsr_graph_button" href="#dsr_graph_applet_holder">View DSR graph</a>
						<a class="button <?php 	if(!isset($_SESSION['reactionNetwork'])) echo 'disabled'; ?> fancybox" id="process_network_button" href="#calculation_output_holder">Analyse reaction network</a>
						<!--a class="button disabled" id="download_network_file_button" href="handlers/download-network-file.php">Download reaction network file</a-->
						<button class="button disabled" id="download_network_file_button" type="submit" disabled="disabled">Download reaction network file</button>
					</p>
				</form>
				
				<form id="reaction_upload_form" action="handlers/upload-network-file.php" method="post" enctype="multipart/form-data">
					<p>
						<label for="reaction_upload_file">Choose a file to upload:</label>
						<input type="file" id="upload_network_file_input" name="upload_network_file_input" size="48" />
					</p>
					<p>
						<button class="button disabled" id="upload_network_file_button" type="submit" disabled="disabled">Upload and process reaction network</button>
					</p>
				</form>
			</div>
<div id="dsr_graph_applet_holder">	<a href="#" id="dsr_graph_close_button" > <img src="styles/close.png" alt="X" /></a><script type="text/javascript">
    var attributes = {codebase:'<?php echo SITE_URL; ?>',
                      code:'dsr.DsrDraw.class',
                      archive:'dsr_0.1.3A.jar',
                      width:1000, height:800} ;
    var parameters = {fontSize:16} ;
    var version = '1.6' ;
    deployJava.runApplet(attributes, parameters, version);
		</script></div>
			<div id="calculation_output_hider">
				
				<div id="calculation_output_holder">
					<p>
						Processing...<span class="blink">_</span>
					</p>
				</div>
			</div>
<?php
require_once('includes/footer.php');
