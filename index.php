<?php
/**
 * CoNtRol main page
 *
 * This is the default page for CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   14/01/2013
 */

require_once('includes/header.php');
require_once('includes/standard-tests.php');
?>
			<!--ul class="tabs">
				<li><a href="#reaction_input_form">Input reactions manually</a></li>
				<li><a href="#reaction_upload_form">Upload reaction file</a></li>
			</ul-->
			<div id="error_message_holder">
<?php 
if(isset($_SESSION['errors']))
{
	foreach($_SESSION['errors'] as $error) echo '				<p>', sanitise($error), "</p>\n";
}
?>
			</div>
			<div id="reaction_input_holder">
				<form id="reaction_input_form" action="handlers/download-network-file.php" method="post">
					<p>
						<a class="button" id="add_reaction_button" href="#" title="Add new reaction">+</a>
					</p>
<?php
/*if(count($standardTests))
{
	foreach($standardTests as $test)
	{
		echo '<input type="hidden" name="', sanitise($test->getShortName()), '" id="test_', sanitise($test->getShortName()), '" class="test" value="1" />', "\n";
	}
}*/
if(isset($_SESSION['reactionNetwork'])) echo $_SESSION['reactionNetwork']->generateFieldsetHTML();
else 
{
?>

					<fieldset class="reaction_input_row">
						<input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" />
						<select class="reaction_direction" name="reaction_direction[]">
							<option value="left">&larr;</option>
							<option value="both" selected="selected">&#x21cc;</option>
							<option value="right">&rarr;</option>
						</select>
						<input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" />
					</fieldset><!-- reaction_input_row -->

					<?php
					}
					?>
					<p>
						<a class="button <?php 	if(!isset($_SESSION['reactionNetwork'])) echo 'disabled'; ?>" id="remove_reaction_button" href="#" title="Remove last reaction">-</a>
					</p>
					<p id="reaction_input_submit_buttons">
						<a class="button fancybox<?php 	if(!isset($_SESSION['reactionNetwork'])) echo ' disabled'; ?>" id="dsr_graph_button" href="#missing_java_warning_holder">View DSR graph</a>
						<a class="button fancybox<?php 	if(!isset($_SESSION['reactionNetwork'])) echo ' disabled'; ?>" id="process_network_button" href="#calculation_output_holder">Analyse reaction network</a>
						<!--a class="button disabled" id="download_network_file_button" href="handlers/download-network-file.php">Download reaction network file</a-->
						<button class="button disabled" id="download_network_file_button" type="submit" disabled="disabled">Download reaction network file</button>
						<a class="button fancybox<?php 	if(!isset($_SESSION['reactionNetwork'])) echo ' disabled'; ?>" href="#latex_output_holder" id="latex_output_button">Generate LaTeX</a>
						<a class="button fancybox" href="#reaction_upload_form">Upload reaction file</a>
						<a class="button <?php 	if(!isset($_SESSION['reactionNetwork'])) echo 'disabled'; ?>" id="reset_reaction_button" href="#">Reset all reactions</a>
					</p>
					<p id="advanced_options"><a class="button fancybox" href="#option_holder">Advanced options</a></p>
				</form>
				
			</div>
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') === FALSE and strpos($_SERVER['HTTP_USER_AGENT'], 'iOS') === FALSE):
?>			
			<div id="dsr_graph_applet_holder">	<a href="#" id="dsr_graph_close_button"><img src="styles/close.png" alt="X" /></a><script type="text/javascript">
    var popupWidth = screen.width - 256;
    var popupHeight = screen.height - 256;
    var attributes = {codebase:'<?php echo SITE_URL; ?>',
                      code:'dsr.DsrDraw.class',
                      archive:'dsr_0.1.3A.jar',
                      width:popupWidth, height:popupHeight};
    var parameters = {fontSize:16};
    var version = '1.5';
    if(deployJava.getJREs().length) deployJava.runApplet(attributes, parameters, version);
   // else document.write('<p>The view DSR graph feature requires Java, which does not appear to be installed on your system.</p>')
		</script></div>
<?php
endif;
?>
			<div id="popup_hider">
				<form id="reaction_upload_form" action="handlers/upload-network-file.php" method="post" enctype="multipart/form-data">
					<p>
						<label for="reaction_upload_file">Choose a file to upload:</label>
						<input type="file" id="upload_network_file_input" name="upload_network_file_input" size="48" />
					</p>
					<p class="left_centred">
						File format:<br />
						<input type="radio" name="upload_network_file_format" value="human"<?php if(!isset($_SESSION['upload_file_format']) or $_SESSION['upload_file_format'] === 'human') echo ' checked="checked"'; ?> id="upload_network_file_format_human" /> <label for="upload_network_file_format_human"> Human readable, e.g. A + 2B --&gt; C</label> <br />
						<input type="radio" name="upload_network_file_format" value="stoichiometry"<?php if(isset($_SESSION['upload_file_format']) and $_SESSION['upload_file_format'] === 'stoichiometry') echo ' checked="checked"'; ?> id="upload_network_file_format_stoichiometry" /> <label for="upload_network_file_format_stoichiometry"> Stoichiometry, e.g. -1 -2 1 </label>
					</p>
					<p>
						<button class="button disabled" id="upload_network_file_button" type="submit" disabled="disabled">Upload and process reaction network</button>
					</p>
				</form>
				<div id="missing_java_warning_holder">
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== FALSE or strpos($_SERVER['HTTP_USER_AGENT'], 'iOS') !== FALSE ) echo "<p>The DSR graph requires Java to view, which is not available on your system.</p>\n";
else echo '<p>The DSR graph requires Java to view, which is not installed on your system. Please <a href="http://java.com/">download Java</a> to enable this functionality.</p>', PHP_EOL;
?>
				</div>
				<div id="calculation_output_holder">
					<p>
						Processing...<span class="blink">_</span>
					</p>
				</div>
				<div id="latex_output_holder">
				</div>
				<form id="option_holder">
					<input type="checkbox" name="mass_action" id="mass_action_checkbox" /> <label for="mass_action_checkbox">Test mass action kinetics only</label>
					<h3>Tests:</h3>
					<p>
<?php
if(count($standardTests))
{
	foreach($standardTests as $test)
	{
		//echo '<input type="checkbox" checked="checked" name="test_checkbox[', sanitise($test->getShortName()), ']" id="test_checkbox_', sanitise($test->getShortName()), '" onChange="if(this.checked == \'checked\') document.getElementById(\'test_', sanitise($test->getShortName()), '\').value = 1; else document.getElementById(\'test_', sanitise($test->getShortName()), '\').value = 0;" /><label for="test_checkbox_', sanitise($test->getShortName()), '">', sanitise($test->getLongName()), "</label>\n";
		
		echo '<input type="checkbox"';
		if(!isset($_SESSION['tests'][$test]) or $_SESSION['tests'][$test]) echo ' checked="checked"';
		echo ' name="test_checkbox[', sanitise($test->getShortName()), ']" id="test_checkbox_', sanitise($test->getShortName()), '" /><label for="test_checkbox_', sanitise($test->getShortName()), '">', sanitise($test->getLongName()), "</label>\n";		
	}
}
?>
					</p>
				</form>
			</div>
			<div id="hidden_character_warning">
				<p>
					You entered the following invalid character: <span id="invalid_character_span"></span>
				</p>
			</div>
			<div id="missing_reactant_warning">
				<p>
					There is a reactant missing.
				</p>
			</div>
<?php
require_once('includes/footer.php');
