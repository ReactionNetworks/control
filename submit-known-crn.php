<?php
/**
 * CoNtRol known CRN submission page
 *
 * This is the default page for CoNtRol
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    08/08/2014
 * @modified   13/08/2014
 */

$title = 'CoNtRol - submit new CRN details';

/**
 * Standard HTML headers
 */
require_once( 'includes/header.php' );

if( !ACCEPT_KNOWN_CRN_SUBMISSIONS ) die( '<p>Sorry, this site does not accept CRN submissions.</p></div></div>' );

// N.B. whitespace included to make textarea placeholder newline hack work, found at
// http://stackoverflow.com/questions/7312623/insert-line-break-inside-placeholder-attribute-of-a-textarea
$default_crn_description = "Enter the CRN description here, and its reactions below.                                                                                       \r\n                                                                                       \r\nPlease include the name of the model, if it has one (e.g. &ldquo;Brusselator&rdquo;, &ldquo;Oregonator&rdquo;, etc), a clear and concise description of its interesting properties, and references.";
if( isset( $_SESSION['crn_description'] ) and trim( $_SESSION['crn_description'] ) ) $crn_description = sanitise( trim( $_SESSION['crn_description'] ) );
else $crn_description = '';
?>
				<div id="reaction_input_holder">
					<form id="reaction_input_form" action="handlers/submit-known-crn.php" method="post">
						<p>
							<textarea id="crn_description" name="crn_description" required cols="40" rows="10" placeholder="<?php echo $default_crn_description; ?>"><?php echo $crn_description; ?></textarea><br />
							<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>" />
							<a class="button" id="add_reaction_button" href="#" title="Add New Reaction">+</a>
							<a class="button<?php if(!isset($_SESSION['reaction_network']) or $_SESSION['reaction_network']->getNumberOfReactions() < 2) echo ' disabled'; ?>" id="remove_reaction_button" href="#" title="Remove Last Reaction">&ndash;</a>
							<a class="button<?php if(!isset($_SESSION['reaction_network']) or !$_SESSION['reaction_network']->getNumberOfReactions()) echo ' disabled'; ?>" id="reset_reaction_button" href="#" title="Reset All Reactions">--</a>
						</p>
<?php
echo $_SESSION['reaction_network']->generateFieldsetHTML();
?>
						<div id="tools_holder" style="display:none;"><!-- Hack to get add reaction button working --></div>
						<p>
<?php
if( REQUIRE_CAPTCHA ):
?>
							<label>Enter security code (required):</label><br />
							<span class="bold"><label for="batch_security_code" id="batch_security_code_label"><?php batch_captcha(); ?></label></span>
							<input type="text" required name="batch_security_code" id="batch_security_code" autocomplete="off" spellcheck="false" placeholder="XXXXX" /><br /><br />
<?php
endif;
?>
							<label>Your name (optional, for credit/attribution):</label><br />
							<input id="submitter" name="submitter" size="24" maxlength="127" type="text"<?php if( isset( $_SESSION['submitter'] ) and trim( $_SESSION['submitter'] ) ) echo ' value="', sanitise( $_SESSION['submitter'] ), '"'; ?> placeholder="Name" /><br />
							<input id="submit_known_crn" type="submit" value="Submit CRN" />
						</p>
					</form>
				</div><!-- reaction_input_holder -->
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
/**
 * Standard HTML footer
 */
require_once( 'includes/footer.php' );
