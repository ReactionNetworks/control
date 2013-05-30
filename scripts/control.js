/**
 * Main CoNtRol JavaScript file
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   30/05/2013
 */

/**
 * Adds a row to the reaction input form
 */
function addReaction()
{
	$('#tools_holder').before('<fieldset class="reaction_input_row"> <input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" /> <select class="reaction_direction" name="reaction_direction[]"><option value="left">&larr;</option><option value="both" selected="selected">&#x21cc;</option><option value="right">&rarr;</option></select> <input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" /> </fieldset>');

	$('.reaction_left_hand_side').each(function()
	{
		$(this).keyup(function() { validateKeyPress($(this)); });
		$(this).change(function() { validateKeyPress($(this)); });
		$(this).blur(function() { validateKeyPress($(this)); });
	});

	$('.reaction_right_hand_side').each(function()
	{
		$(this).keyup(function() { validateKeyPress($(this)); });
		$(this).change(function() { validateKeyPress($(this)); });
		$(this).blur(function() { validateKeyPress($(this)); });
	});
	$('#reset_reaction_button').removeClass('disabled');
}

/**
 * Get the size of the visible area of the browser
 */
function detectWindowSize()
{
	if($(window).innerWidth() > 800) popupWidth = $(window).innerWidth() - 256;
	else popupWidth = $(window).innerWidth() - 16;
	if($(window).innerHeight() > 800) popupHeight = $(window).innerHeight() - 256;
	else popupHeight = $(window).innerHeight() - 16;
}

/**
 * Disables reaction reset, DSR graph and analysis buttons
 */
function disableButtons()
{
	$('#dsr_graph_button').addClass('disabled');
	$('#process_network_button').addClass('disabled');
	$('#download_network_file_button').addClass('disabled');
	$('#latex_output_button').addClass('disabled');
	$('#reset_reaction_button').addClass('disabled');
}

/**
 * Enables reaction reset, DSR graph and analysis buttons
 */
function enableButtons()
{
	// Remove reaction button doesn't need to be enabled, as it is automatically enabled/disabled based on the number of reactions
	$('#dsr_graph_button').removeClass('disabled');
	$('#process_network_button').removeClass('disabled');
	$('#download_network_file_button').removeClass('disabled');
	$('#download_network_file_button').removeAttr('disabled');
	$('#latex_output_button').removeClass('disabled');
	$('#reset_reaction_button').removeClass('disabled');
}

/**
 * Generates LaTeX markup for a set of reactions and displays it in a popover
 */
function generateLaTeX()
{
	var numberOfRows = 0;
	var numberOfColumns = 0;
	var textOutput = '\\begin{array}{rcl}\n';
	$('.reaction_input_row').each(function()
	{
		++numberOfRows;
		if($('.reaction_left_hand_side', $(this)).val() == '' || $('.reaction_left_hand_side', $(this)).val() == ' ' || $('.reaction_left_hand_side', $(this)).val() == '  ') textOutput += '\\emptyset';
		else textOutput += $('.reaction_left_hand_side', $(this)).val().replace('&', '\\&amp;');
		textOutput += ' &amp; ';
		switch($('select.reaction_direction option:selected', $(this)).val())
		{
			case 'left':
				textOutput += '\\leftarrow';
				break;
			case 'right':
				textOutput += '\\rightarrow';
				break;
			case 'both':
				textOutput += '\\rightleftharpoons';
				break;
			default:
				textOutput += ' ? ';
		}
		textOutput += ' &amp; ';
		if($('.reaction_right_hand_side', $(this)).val() == '' || $('.reaction_right_hand_side', $(this)).val() == ' ' || $('.reaction_right_hand_side', $(this)).val() == '  ') textOutput += '\\emptyset';
		else textOutput += $('.reaction_right_hand_side', $(this)).val().replace('&', '\\&');
		textOutput = textOutput.replace('$', '\\$');
		textOutput += ' \\\\\n';
	});
	var allLines = textOutput.split('\n');
	for(i = 0; i < allLines.length; ++i)
	{
		if(allLines[i].length > numberOfColumns) numberOfColumns = allLines[i].length;
	}
	numberOfColumns *= 2;
	numberOfRows += 2;
	textOutput = '<textarea rows="' + numberOfRows + '" cols="' + numberOfColumns + '">\n' + textOutput + '\\end{array}</textarea>\n';
	$('#latex_output_holder').html(textOutput);
}

/**
 * Calls the test handler for all selected tests and then redirects to the results
 */
function processTests()
{
	var url = 'handlers/process-tests.php';
	data = {csrf_token: csrf_token};
	$.post(url, data, function(returndata)
	{
		showTestOutput(returndata);
		if(returndata == '<p>All tests completed. Redirecting to results.</p>') window.location.href='results.php';
		else processTests()
	});
}

/**
 * Removes a row from the reaction input form
 *
 * N.B. This function does NOT check whether there is only one reaction left.
 * Consequently, calling it when there is only one reaction left will result
 * no reactions being left. Calling it again may trigger a JavaScript error
 * in the user's browser.
 */
function removeReaction()
{
	$('#reaction_input_form fieldset').filter(':last').remove();
}

/**
 * Clears the results popup.
 */
function resetPopup()
{
	$('#calculation_output_holder').html('<p>Processing...<span class="blink">_</span></p>');
}

/**
 * Resets all reactions in the input form
 *
 * Disables the various reaction network processing buttons, clears all reactions in the form,
 * removes all reactions except the first, and clears any saved reactions from the session.
 */
function resetReactions()
{
	$('#reaction_input_form fieldset input').val('');
	$('#reaction_input_form fieldset select option[value=both]').attr('selected', true);
	disableButtons();
	while($('#reaction_input_form fieldset').length -1) removeReaction();
	$('#remove_reaction_button').addClass('disabled');
	var url = 'handlers/reset-reactions.php';
	var data = {reset_reactions: 1, csrf_token: csrf_token};
	$.post(url, data);
	if($('#results_link')) $('#results_link').hide();
}

/**
 * Saves the network in the session via AJAX
 */
var validNetwork = true;
function saveNetwork()
{
	validNetwork = true;
	var url = 'handlers/process-network.php';
	var reactionsLeftHandSide = new Array();
	$.each($('.reaction_left_hand_side'), function(index, value)
	{
		reactionsLeftHandSide.push(value.value);
	});
  var reactionsRightHandSide = new Array();
	$.each($('.reaction_right_hand_side'), function(index, value)
	{
		reactionsRightHandSide.push(value.value);
	});
  var reactionsDirection = new Array();
	$.each($('.reaction_direction :selected'), function(index, value)
	{
		reactionsDirection.push(value.value);
	});
	var testSettings = new Array();
	$.each($('.test'), function(index, v)
	{
		testSettings.push({name: $(this).attr('name'), value: $(this).val()});
	});
	var data = {'reaction_left_hand_side[]': reactionsLeftHandSide, 'reaction_right_hand_side[]': reactionsRightHandSide, 'reaction_direction[]': reactionsDirection, 'test_settings': testSettings, csrf_token: csrf_token};
	$.post(url, data, function(returndata)
	{
		if(returndata.length)
		{
			showTestOutput('<p>' + returndata + '</p>');
			validNetwork = false;
		}
	});
	return validNetwork;
}

/**
 * Adds output from a test to the progress popover
 */
function showTestOutput(output)
{
	$('#calculation_output_holder').append(output);
}

/**
 * Enables/disables detailed test output via AJAX
 */
function toggleDetailedOutput(newStatus)
{
	var url = 'handlers/toggle-detailed-output.php';
	var data = {detailed_output: newStatus, csrf_token: csrf_token};
	$.post(url, data);
}

/**
 * Enables/disables the --mass-action-only flag via AJAX
 */
function toggleMassAction(newStatus)
{
	var url = 'handlers/toggle-mass-action.php';
	var data = {mass_action_only: newStatus, csrf_token: csrf_token};
	$.post(url, data);
}

/**
 * Enables/disables the specified test via AJAX
 */
function toggleTest(testName, newStatus)
{
	var url = 'handlers/toggle-test.php';
	var data = {testName: testName, active: newStatus, csrf_token: csrf_token};
	$.post(url, data);
}

/**
 * Validates an email address
 */
function validateEmailAddress(emailAddress)
{
	var atPos=emailAddress.indexOf('@');
 if( atPos< 1) return false;
 if( emailAddress.indexOf('.', atPos) > (atPos + 1) && emailAddress.charAt(emailAddress.length - 1) != '.') return true;
 return false;
} 
 
/**
 * Warns about invalid character input
 */
function validateKeyPress(inputElement)
{
	var invalidCharacters = new Array('<', '>', '-', '=');
	for(i = 0; i < invalidCharacters.length; ++i)
	{
		if (inputElement.val().indexOf(invalidCharacters[i]) > -1)
		{
			inputElement.val( inputElement.val().replace(invalidCharacters[i], ''));
			$('#invalid_character_span').html(invalidCharacters[i]);
			var position = inputElement.position();
			$('#hidden_character_warning').css('top', position.top + 48);
			$('#hidden_character_warning').css('left', position.left);
			$('#hidden_character_warning').show();
			setTimeout(function()
			{
				$('#hidden_character_warning').hide();
			}, 1500);
		}
	}
	var validInput = true;
	var totalChars = 0;
	$('#missing_reactant_warning').hide();
	$('.reaction_left_hand_side').each(function()
	{
		$(this).css('border-color', '');
		totalChars += $(this).val().length;
		lhs = $.trim($(this).val());
		if(lhs.indexOf('+') == 0 || lhs[lhs.length - 1] == '+' || lhs.indexOf('++') > -1 || lhs.indexOf('+ +') > -1 || lhs.indexOf('+  +') > -1)
		{
			validInput = false;
			$(this).css('border-color', 'red');
			var position = inputElement.position();
			$('#missing_reactant_warning').css('top', position.top + 48);
			$('#missing_reactant_warning').css('left', position.left);
			$('#missing_reactant_warning').show();
		}
	});
	$('.reaction_right_hand_side').each(function()
	{
		$(this).css('border-color', '');
		totalChars += $(this).val().length;
		rhs = $.trim($(this).val());
		if(rhs.indexOf('+') == 0 || rhs[rhs.length - 1] == '+' || rhs.indexOf('++') > -1 || rhs.indexOf('+ +') > -1 || rhs.indexOf('+  +') > -1)
		{
			validInput = false;
			$(this).css('border-color', 'red');
			var position = inputElement.position();
			$('#missing_reactant_warning').css('top', position.top + 48);
			$('#missing_reactant_warning').css('left', position.left);
			$('#missing_reactant_warning').show();
		}
	});
	if(validInput && totalChars)
	{
		enableButtons();
		saveNetwork();
	}
	else disableButtons();
} 

var popupWidth = 800;
var popupHeight = 600;
var popupMargin = 16;

$(document).ready(function()
{
	// Set some useful variables
	if($(window).innerWidth() > 800) popupWidth = $(window).innerWidth() - 256;
	else popupWidth = $(window).innerWidth() - 16;
	if($(window).innerHeight() > 800) popupHeight = $(window).innerHeight() - 256;
	else popupHeight = $(window).innerHeight() - 16;
	var buttonSize = 0;

	// Enable DSR applet for browsers with Java installed
	if(navigator.userAgent.indexOf('Android') == -1 && navigator.userAgent.indexOf('iOS') == -1 && deployJava.getJREs().length) $('#dsr_graph_button').removeClass('fancybox');
	
	$('#add_reaction_button').click(function()
	{
		addReaction();
		$('#remove_reaction_button').removeClass('disabled');
		return false;
	});

	$('#remove_reaction_button').click(function()
	{
		if(!$(this).hasClass('disabled'))
		{
			if($('#reaction_input_form > fieldset').length > 1) removeReaction();
			if($('#reaction_input_form > fieldset').length == 1) $(this).addClass('disabled');
			else $(this).removeClass('disabled');
		}
		if($('#reaction_input_form > fieldset').length == 1 && $('#reaction_input_form > fieldset .reaction_left_hand_side').val() == '' && $('#reaction_input_form > fieldset .reaction_right_hand_side').val() == '') $('#reset_reaction_button').addClass('disabled');
		return false;
	});

	$('#reset_reaction_button').click(function()
	{
		if(!$(this).hasClass('disabled')) resetReactions();
		return false;
	});

	$('.reaction_left_hand_side').each(function()
	{
		$(this).keyup(function() { validateKeyPress($(this)); });
	});

	$('.reaction_right_hand_side').each(function()
	{
		$(this).keyup(function() { validateKeyPress($(this));	});
	});

	if($('#add_reaction_button').height() > buttonSize) buttonSize = $('#add_reaction_button').height();
	if($('#add_reaction_button').width() > buttonSize) buttonSize = $('#add_reaction_button').width();
	if($('#remove_reaction_button').height() > buttonSize) buttonSize = $('#remove_reaction_button').height();
	if($('#remove_reaction_button').width() > buttonSize) buttonSize = $('#remove_reaction_button').width();
	if($('#reset_reaction_button').height() > buttonSize) buttonSize = $('#reset_reaction_button').height();
	if($('#reset_reaction_button').width() > buttonSize) buttonSize = $('#reset_reaction_button').width();
	$('#add_reaction_button').height(buttonSize);
	$('#add_reaction_button').width(buttonSize);
	$('#remove_reaction_button').height(buttonSize);
	$('#remove_reaction_button').width(buttonSize);
	$('#reset_reaction_button').height(buttonSize);
	$('#reset_reaction_button').width(buttonSize);

	$('.fancybox').fancybox({autoDimensions: true, width: popupWidth, height: popupHeight});

	$('#detailed_output_checkbox').change(function()
	{
		var activated = 0;
		if($(this).is(':checked')) activated = 1;
		toggleDetailedOutput(activated);
	});
	
	$('#download_network_file_button').click(function(e)
	{
		if($(this).hasClass('disabled')) e.preventDefault();
	});

	$('#dsr_graph_button').click(function(e)
	{
		if($(this).hasClass('disabled')) e.preventDefault();
		else if(navigator.userAgent.indexOf('Android') == -1 && navigator.userAgent.indexOf('iOS') == -1 && deployJava.getJREs().length)
		{
			e.preventDefault();
			window.location.replace('jnlp.php');
		}
	});

	$('#email_results_form').submit(function(e)
	{
		e.preventDefault();
		$('#email_results_button').addClass('disabled');
		$('#email_results_button').attr('disabled', 'disabled');
		var url = 'handlers/mail-results.php';
		var email = $('#results_email').val();
		var data = {email: email, csrf_token: csrf_token};
		$.post(url, data, function(returndata)
		{
			if(returndata.length) $('#email_results_error').html(returndata);
			window.setTimeout(function()
			{
				$('#email_results_error').html('&nbsp;');
				$('#email_results_button').removeClass('disabled');
				$('#email_results_button').removeAttr('disabled');
			}, 1000);
		});
	});

	$('#email_results_form_button').click(function(e)
	{
		$('#results_email').select();
		if(validateEmailAddress($('#results_email').val()))
		{
			$('#email_results_error').html('&nbsp;');
			$('#email_results_button').removeClass('disabled');
			$('#email_results_button').removeAttr('disabled');
		}
		else
		{
			$('#email_results_error').html('Invalid email address');
			$('#email_results_button').addClass('disabled');
			$('#email_results_button').addAttr('disabled');
		}		
	});

	$('#latex_output_button').click(function(e)
	{
		if(!$(this).hasClass('disabled')) generateLaTeX();
	});

	$('#mass_action_checkbox').change(function()
	{
		var activated = 0;
		if($(this).is(':checked')) activated = 1;
		toggleMassAction(activated);
	});
	
	$('#option_holder input[name*="test_checkbox"]').change(function()
	{
		var testName = $(this).attr('name').slice(14, -1);
		var activated = 0;
		if($(this).is(':checked')) activated = 1;
		toggleTest(testName, activated);
	});

	$('#process_network_button').click(function()
	{
		if(!$(this).hasClass('disabled')) 
		{		
			resetPopup();
			saveNetwork();
			processTests();
		}
		return false;
	});	

	$('#results_email').change(function()
	{
		if(validateEmailAddress($('#results_email').val()))
		{
			$('#email_results_error').html('&nbsp;');
			$('#email_results_button').removeClass('disabled');
			$('#email_results_button').removeAttr('disabled');
		}
		else
		{
			$('#email_results_error').html('Invalid email address');
			$('#email_results_button').addClass('disabled');
			$('#email_results_button').attr('disabled', 'disabled');
		}		
	});
	
	$('#results_email').keyup(function()
	{
		if(validateEmailAddress($('#results_email').val()))
		{
			$('#email_results_error').html('&nbsp;');
			$('#email_results_button').removeClass('disabled');
			$('#email_results_button').removeAttr('disabled');
		}
		else
		{
			$('#email_results_error').html('Invalid email address');
			$('#email_results_button').addClass('disabled');
			$('#email_results_button').attr('disabled', 'disabled');
		}		
	});
	
	$('#upload_batch_file_email').change(function()
	{
		if(validateEmailAddress($('#upload_batch_file_email').val()))
		{
			$('#upload_batch_file_email_error').html('&nbsp;');
			if($('#upload_batch_file_input').val() != '') 
			{
				$('#upload_network_file_button').removeClass('disabled');
				$('#upload_network_file_button').removeAttr('disabled');
			}
		}
		else $('#upload_batch_file_email_error').html('Invalid email address');		
	});
	
	$('#upload_batch_file_email').keyup(function()
	{
		if(validateEmailAddress($('#upload_batch_file_email').val()))
		{
			$('#upload_batch_file_email_error').html('&nbsp;');
			if($('#upload_batch_file_input').val() != '') 
			{
				$('#upload_network_file_button').removeClass('disabled');
				$('#upload_network_file_button').removeAttr('disabled');
			}
		}
		else $('#upload_batch_file_email_error').html('Invalid email address');		
	});
	
	$('#upload_batch_file_input').change(function()
	{
		if(validateEmailAddress($('#upload_batch_file_email').val()))
		{
			$('#upload_batch_file_button').removeClass('disabled');
			$('#upload_batch_file_button').removeAttr('disabled');
		}
	});

	$('#upload_network_file_input').change(function()
	{
		$('#upload_network_file_button').removeClass('disabled');
		$('#upload_network_file_button').removeAttr('disabled');
	});

	$(window).resize(function() { detectWindowSize(); });
	$('.reaction_left_hand_side').first().select();
});
