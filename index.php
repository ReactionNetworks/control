<?php
/**
 * CoNtRol main page
 *
 * This is the default page for CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   17/09/2013
 */

require_once('includes/header.php');
require_once('includes/standard-tests.php');
require_once('includes/file-formats.php');

if(isset($_SESSION['test_output']) and count($_SESSION['test_output'])) echo '				<div id="results_link"><a href="results.php">Back to existing results</a></div>', PHP_EOL;
?>
				<div id="reaction_input_holder">
					<form id="reaction_input_form" action="handlers/download-network-file.php" method="post">
						<p>
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
							<a class="button" id="add_reaction_button" href="#" title="Add New Reaction">+</a>
							<a class="button<?php if(!isset($_SESSION['reaction_network']) or $_SESSION['reaction_network']->getNumberOfReactions() < 2) echo ' disabled'; ?>" id="remove_reaction_button" href="#" title="Remove Last Reaction">&ndash;</a>
							<a class="button <?php if(!isset($_SESSION['reaction_network']) or !$_SESSION['reaction_network']->getNumberOfReactions()) echo 'disabled'; ?>" id="reset_reaction_button" href="#" title="Reset All Reactions">--</a>
						</p>
<?php
echo $_SESSION['reaction_network']->generateFieldsetHTML();
?>
						<div id="tools_holder">
							<div id="tools_header">
								<h2>File Input</h2>
							</div>
							<p id="tools_buttons_slidedown">
								<a class="button fancybox" href="#reaction_upload_form" id="reaction_upload_button" title="Upload a text file describing a single CRN for immediate analysis">Upload<br />CRN File</a>
								<a class="button fancybox" href="#batch_upload_form" id="batch_upload_button" title="Upload an archive containing several text files representing different CRNs for batch processing">Upload Batch<br />CRN File</a>
							</p>
							<span id="tools_show">Show</span>
						</div><!-- tools_holder -->
						<div id="actions_holder">
							<h2>Analysis</h2>
							<p>
								<a class="button fancybox_dynamic<?php if(!isset($_SESSION['reaction_network']) or !$_SESSION['reaction_network']->getNumberOfReactions()) echo ' disabled'; ?>" href="#calculation_output_holder" id="process_network_button" title="Run a number of tests on the current CRN and display the results">Analyse<br />CRN</a>
								<a class="button fancybox" href="#option_holder" id="options_button" title="Configure options such as which tests to run during analysis">Options</a>			
								<p id="more_actions_slidedown">									
									<button class="button<?php if(!isset($_SESSION['reaction_network']) or !$_SESSION['reaction_network']->getNumberOfReactions()) echo ' disabled'; ?>" id="download_network_file_button" type="submit"<?php if(!isset($_SESSION['reaction_network'])) echo ' disabled="disabled"'; ?> title="Download a text file describing the current CRN for later analysis">Download<br />CRN File</button>
									<a class="button fancybox_dynamic<?php if(!isset($_SESSION['reaction_network']) or !$_SESSION['reaction_network']->getNumberOfReactions()) echo ' disabled'; ?>" href="#latex_output_holder" id="latex_output_button" title="Automatically generate LaTeX markup describing the current CRN">Generate<br />LaTeX</a>
									<a class="button fancybox<?php if(!isset($_SESSION['reaction_network']) or !$_SESSION['reaction_network']->getNumberOfReactions()) echo ' disabled'; ?>" href="#missing_java_warning_holder" id="dsr_graph_button" title="Generate and display the DSR graph for the current CRN (note: requires Java)">View CRN<br />DSR Graph</a>
								</p>
								<span id="more_actions_show">More</span>					
							</p>
						</div><!-- actions_holder -->
					</form>
				</div><!-- reaction_input_holder -->
				<div id="popup_hider">
					<form id="reaction_upload_form" action="handlers/upload-network-file.php" method="post" enctype="multipart/form-data">
						<p>
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
							<label for="upload_network_file_input">Choose a file to upload:</label>
							<input type="file" id="upload_network_file_input" name="upload_network_file_input" size="48" />
						</p>
						<p class="left_centred">
							File format:<br /><?php
							foreach ($format_array as $format)
							{
								$format->getNetworkRadioButton();
							}
							?>
						</p>
						<p>
							<button class="button disabled" id="upload_network_file_button" type="submit" disabled="disabled">Upload reaction network</button>
						</p>
					</form><!-- reaction_upload_form -->
					<form id="batch_upload_form" action="handlers/upload-batch-file.php" method="post" enctype="multipart/form-data" class="left_centred">
						<p>
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
							<label for="upload_batch_file_input">Choose a file to upload:</label>
							<input type="file" id="upload_batch_file_input" name="upload_batch_file_input" size="48" /><br />
							Maximum file size:
<?php
if(return_bytes(ini_get('post_max_size') < return_bytes(ini_get('upload_max_filesize')))) echo ini_get('post_max_size');
else echo ini_get('upload_max_filesize');
?> <br />
							Supported archive types:
<?php
echo $supported_batch_file_types[0]['extension'];
for($i = 1; $i < count($supported_batch_file_types); ++$i) echo ', ', $supported_batch_file_types[$i]['extension'];
?>
						</p>
						<p>
							<label for="upload_batch_file_email">Email address for results:</label>
							<input type="text" id="upload_batch_file_email" name="upload_batch_file_email" size="48" <?php if(isset($_SESSION['email'])) echo 'value = "', sanitise($_SESSION['email']), '" '; ?>/><br />
							<span id="upload_batch_file_email_error">&nbsp;</span>
						</p>
						<p>
							File format:<br />
<?php							
							foreach ($format_array as $format)
							{
								$format->getBatchRadioButton();
							}
							/*<input type="radio" name="upload_batch_file_format" value="human"<?php if(!isset($_SESSION['upload_file_format']) or $_SESSION['upload_file_format'] === 'human') echo ' checked="checked"'; ?> id="upload_batch_file_format_human" /> <label for="upload_batch_file_format_human"><a href="http://reaction-networks.net/wiki/CoNtRol#Human_Readable">Human readable</a>, e.g. A + 2B --&gt; C</label> <br />
							<input type="radio" name="upload_batch_file_format" value="stoichiometry"<?php if(isset($_SESSION['upload_file_format']) and $_SESSION['upload_file_format'] === 'stoichiometry') echo ' checked="checked"'; ?> id="upload_batch_file_format_stoichiometry" /> <label for="upload_batch_file_format_stoichiometry"><a href="http://reaction-networks.net/wiki/CoNtRol#Net_Stoichiometry">Stoichiometry</a>, e.g. -1 -2 1 </label> <br />
							<input type="radio" name="upload_batch_file_format" value="SourceTarget"<?php if(isset($_SESSION['upload_file_format']) and $_SESSION['upload_file_format'] === 'SourceTarget') echo ' checked="checked"'; ?> id="upload_batch_file_format_source_target" /> <label for="upload_batch_file_format_source_target"><a href="http://reaction-networks.net/wiki/CoNtRol#Source_Stoichiometry_.2B_Target_Stoichiometry">Source and target stoichiometries</a> </label>*/?>
						</p>
						<br />
						<div>
							<p>Enter security code (required):<br />
								<label for="batch_security_code"><?php batch_captcha(); ?></label> 
		            <input type="text" name="batch_security_code" id="batch_security_code"/>
	            </p>
            </div>
						<p>
							<button class="button disabled" id="upload_batch_file_button" type="submit" disabled="disabled">Upload and process batch file</button>
						</p>
					</form><!-- batch_upload_form -->
					<div id="missing_java_warning_holder">
<?php
if(strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== FALSE or strpos($_SERVER['HTTP_USER_AGENT'], 'iOS') !== FALSE ) echo "						<p>The DSR graph requires Java to view, which is not available on your system.</p>\n";
else echo '						<p>The DSR graph requires Java to view, which is not installed on your system. Please <a href="http://java.com/">download Java</a> to enable this functionality.</p>', PHP_EOL;
?>
					</div><!-- missing_java_warning_holder -->
					<div id="calculation_output_holder">
						<p>Processing...<span class="blink">_</span></p>
					</div><!-- calculation_output_holder -->
					<div id="latex_output_holder">
					</div><!-- latex_output_holder -->
					<form id="option_holder" action=".">
						<h2>Tests:</h2>
						<p>
							Tick/untick the checkboxes to enable/disable each test.
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
						</p>
						<table summary="Control whether each test is enabled or disabled">
							<thead>
								<tr>
									<th>&#x2713;</th>
									<th>Test name</th>
									<th>Description</th>
								</tr>
							</thead>
							<tfoot>
								<tr>
									<th>&#x2713;</th>
									<th>Test name</th>
									<th>Description</th>
								</tr>
							</tfoot>
							<tbody>
<?php
if(count($standardTests))
{
	foreach($standardTests as $test)
	{
		echo "								<tr>\n";
		if (!isset($_SESSION['tests'][$test->getShortName()])) $_SESSION['tests'][$test->getShortName()]=true;
		echo '									<td><input type="checkbox"';
		if( $_SESSION['tests'][$test->getShortName()]) echo ' checked="checked"';
		echo ' name="test_checkbox[', sanitise($test->getShortName()), ']" id="test_checkbox_', sanitise($test->getShortName()), '" /></td>', PHP_EOL;
		echo '									<td><label for="test_checkbox_', sanitise($test->getShortName()), '">', sanitise($test->getLongName()), "</label></td>\n";
		echo '									<td>', $test->getDescription(), "</td>\n							</tr>\n";
	}
}
?>
							</tbody>
						</table>
						<h2>Other options:</h2>
						<p><input type="checkbox" name="mass_action" id="mass_action_checkbox"<?php if(isset($_SESSION['mass_action_only']) and $_SESSION['mass_action_only']) echo ' checked="checked"'; ?> /> <label for="mass_action_checkbox">Test mass action kinetics only (when supported)</label></p>
						<p><input type="checkbox" name="detailed_output" id="detailed_output_checkbox"<?php if(isset($_SESSION['detailed_output']) and $_SESSION['detailed_output']) echo ' checked="checked"'; ?> /> <label for="detailed_output_checkbox">Show detailed test output</label></p>
					</form><!-- option_holder -->
				</div><!-- popup_hider -->
				<div id="hidden_character_warning">
					<p>You entered the following invalid character: <span id="invalid_character_span"></span></p>
				</div><!-- hidden_character_warning -->
				<div id="missing_reactant_warning">
					<p>There is a reactant missing.</p>
				</div><!-- missing_reactant_warning -->
				<div id="removed_reaction_warning">
					<p>Removed reaction <span id="removed_reaction_span"></span></p>
				</div><!-- removed_reaction_warning -->
<?php
require_once('includes/footer.php');
