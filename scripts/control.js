/**
 * Main CoNtRol JavaScript file
 *
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   07/02/2013
 */

/**
 * Warns about invalid character input
 */
function validateKeyPress(inputElement)
{
	//if(inputElement.val()[0] == '+') alert('A set of reactants cannot begin with a +');
	var invalidCharacters = new Array('<', '>', '-', '=');
	for(i=0; i<invalidCharacters.length;++i)
	{
		if (inputElement.val().indexOf(invalidCharacters[i]) > -1)
		{
			inputElement.val( inputElement.val().replace(invalidCharacters[i], ''));
			//alert('You entered the following invalid character: '+invalidCharacters[i]);
			$('#invalid_character_span').html(invalidCharacters[i]);
			var position = inputElement.position();
			$('#hidden_character_warning').css('top', position.top + 48);
			$('#hidden_character_warning').css('left', position.left);
			$('#hidden_character_warning').show();
			setTimeout(function() {$('#hidden_character_warning').hide();}, 1500);
		}
	}
	var validInput = true;
	$('#missing_reactant_warning').hide();
	$('.reaction_left_hand_side').each(function()
	{
		$(this).css('border-color', '');
		if($(this).val().indexOf('+') == 0 || $(this).val()[$(this).val().length - 1] == '+' || $(this).val().indexOf('++') > -1 || $(this).val().indexOf('+ +') > -1 || $(this).val().indexOf('+  +') > -1)
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
		if($(this).val().indexOf('+') == 0 || $(this).val()[$(this).val().length - 1] == '+' || $(this).val().indexOf('++') > -1 || $(this).val().indexOf('+ +') > -1 || $(this).val().indexOf('+  +') > -1)
		{
			validInput = false;
			$(this).css('border-color', 'red');
			var position = inputElement.position();
			$('#missing_reactant_warning').css('top', position.top + 48);
			$('#missing_reactant_warning').css('left', position.left);
			$('#missing_reactant_warning').show();
		}
	});
	if(validInput) enableButtons();
	else disableButtons();
} 
 
/**
 * Adds a row to the reaction input form
 */
function addReaction()
{
	$('#remove_reaction_button').parent().before('<fieldset class="reaction_input_row"> <input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" /> <select class="reaction_direction" name="reaction_direction[]"><option value="left">&larr;</option><option value="both" selected="selected">&#x21cc;</option><option value="right">&rarr;</option></select> <input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" /> </fieldset>');

	/*$('select.reaction_direction').each(function()
	{
		$(this).change(function()
		{
			enableButtons();
		});
	}); */

	$('.reaction_left_hand_side').each(function()
	{
		$(this).keyup(function()
		{
			//enableButtons();
			validateKeyPress($(this));
		});
	});

	$('.reaction_right_hand_side').each(function()
	{
		$(this).keyup(function()
		{
			//enableButtons();
			validateKeyPress($(this));
		});
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
 * Resets all reactions in the input form
 *
 * Does not change the number of reactions in the input form, just clears
 * each reaction's LHS and RHS, and sets the direction to reversible. It
 * also disables the various reaction network processing buttons.
 */
function resetReactions()
{
	$('#reaction_input_form fieldset input').val('');
	$('#reaction_input_form fieldset select option[value=both]').attr('selected', true);
	disableButtons();
	while($('#reaction_input_form fieldset').length -1) removeReaction();
}

/*
 * Disables reaction reset, DSR graph and analysis buttons
 */
function disableButtons()
{
	$('#reset_reaction_button').addClass('disabled');
	$('#dsr_graph_button').addClass('disabled');
	$('#process_network_button').addClass('disabled');
	$('#download_network_file_button').addClass('disabled');
	$('#latex_output_button').addClass('disabled');
}

/*
 * Enables reaction reset, DSR graph and analysis buttons
 */
function enableButtons()
{
	// Remove reaction button doesn't need to be enabled, as it is automatically enabled/disabled based on the number of reactions
	//if($('#reaction_input_form > fieldset').length > 1) $('#remove_reaction_button').removeClass('disabled');
	$('#reset_reaction_button').removeClass('disabled');
	$('#dsr_graph_button').removeClass('disabled');
	$('#process_network_button').removeClass('disabled');
	$('#download_network_file_button').removeClass('disabled');
	$('#download_network_file_button').removeAttr('disabled');
	$('#latex_output_button').removeClass('disabled');
}

function generateLaTeX()
{
	var numberOfRows=0;
	var numberOfColumns=0;
	var textOutput='\\begin{array}{rcl}\n';
	$('.reaction_input_row').each(function()
	{
		++numberOfRows;
		textOutput += $('.reaction_left_hand_side', $(this)).val().replace('&', '\&');
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
		textOutput += $('.reaction_right_hand_side', $(this)).val().replace('&', '\\&');
		textOutput = textOutput.replace('$', '\\$');
		textOutput += ' \\\\\n';
	});
	var allLines= textOutput.split('\n');
	for(i=0;i<allLines.length;++i)
	{
		if(allLines[i].length > numberOfColumns) numberOfColumns = allLines[i].length;
	}
	numberOfColumns *= 2;
	numberOfRows += 2;
	textOutput = '<textarea rows="' + numberOfRows + '" cols="' + numberOfColumns + '">\n' + textOutput + '\\end{array}</textarea>\n';
	//alert(textOutput);
	$('#latex_output_holder').html(textOutput);
}

function processTests()
{
	var url = 'handlers/process-tests.php';
	$.get(url, null, function(returndata) {showTestOutput(returndata);
	if(returndata == '<p>All tests completed. Redirecting to results.</p>') window.location.href='handlers/test.php';
	else processTests()});
}

function testTests()
{
	var url = 'handlers/test.php';
	$.get(url, null, function(returndata) {showTestOutput(returndata);});
}

function showTestOutput(output)
{
	$('#calculation_output_holder').append(output);
}

function resetPopup()
{
	$('#calculation_output_holder').html('<p>Processing...<span class="blink">_</span></p>');
}

var validNetwork=true;
function saveNetwork()
{
	validNetwork=true;
	var url = 'handlers/process-network.php';
	var reactionsLeftHandSide = new Array();
	$.each($('.reaction_left_hand_side'), function(index,value){reactionsLeftHandSide.push(value.value)}); 		
  var reactionsRightHandSide = new Array();
	$.each($('.reaction_right_hand_side'), function(index,value){reactionsRightHandSide.push(value.value)});
  var reactionsDirection = new Array();
	$.each($('.reaction_direction :selected'), function(index,value){reactionsDirection.push(value.value)});
	var testSettings = new Array();
	$.each($('.test'), function(index, v){testSettings.push({name: $(this).attr('name'), value: $(this).val()})});
	$.post(url, {'reaction_left_hand_side[]':reactionsLeftHandSide, 'reaction_right_hand_side[]':reactionsRightHandSide, 'reaction_direction[]':reactionsDirection, 'test_settings':testSettings}, function(returndata) {if (returndata.length) {showTestOutput('<p>' + returndata + '</p>');validNetwork=false;}});
	return validNetwork;
}

function toggleTest(testName, newStatus)
{
	var url = 'handlers/toggle-test.php';
	$.post(url, {testName: testName, active: newStatus});
}

$(document).ready(function()
{
	var buttonDivOffset=-Math.floor($('#reaction_input_submit_buttons').height()/2);
	$('#reaction_input_submit_buttons').css('margin-top', buttonDivOffset.toString() + 'px');
	if(navigator.userAgent.indexOf('Android') == -1 && navigator.userAgent.indexOf('iOS') == -1 && deployJava.getJREs().length) $('#dsr_graph_button').removeClass('fancybox');
	
	$('#add_reaction_button').click(function()
	{
		addReaction();
		$('#remove_reaction_button').removeClass('disabled');
		//$(this).preventDefault();
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
		return false;
	});

	$('#reset_reaction_button').click(function()
	{
		if(!$(this).hasClass('disabled')) resetReactions();
		return false;
	});

	/*$('ul.tabs').each(function()
	{
		// For each set of tabs, we want to keep track of
		// which tab is active and its associated content
		var active, content, links = $(this).find('a');

		// If the location.hash matches one of the links, use that as the active tab.
		// If no match is found, use the first link as the initial active tab.
		active = $(links.filter('[href="' + location.hash + '"]')[0] || links[0]);
		active.addClass('active');
		content = $(active.attr('href'));

		// Hide the remaining content
		links.not(active).each(function ()
		{
			$($(this).attr('href')).hide();
		});

		// Bind the click event handler
		$(this).on('click', 'a', function(e)
		{
			// Make the old tab inactive.
			active.removeClass('active');
			content.hide();

			// Update the variables with the new link and content
			active = $(this);
			content = $($(this).attr('href'));

			// Make the tab active.
			active.addClass('active');
			content.show();

			// Prevent the anchor's default click action
			e.preventDefault();
		});
	});*/

	/*$('select.reaction_direction').each(function()
	{
		$(this).change(function()
		{
			enableButtons();
		});
	});*/

	$('.reaction_left_hand_side').each(function()
	{
		$(this).keyup(function()
		{
			//enableButtons();
			validateKeyPress($(this));
		});
	});

	$('.reaction_right_hand_side').each(function()
	{
		$(this).keyup(function()
		{
			//enableButtons();
			validateKeyPress($(this));
		});
	});

	$('#download_network_file_button').click(function(e)
	{
		if($(this).hasClass('disabled')) e.preventDefault();
	});

	$('#upload_network_file_input').change(function()
	{
		$('#upload_network_file_button').removeClass('disabled');
		$('#upload_network_file_button').removeAttr('disabled');
	});

	if(screen.width > 800)var popupWidth = screen.width - 256;
	else var popupWidth = screen.width - 64;
	if(screen.height > 800)var popupHeight = screen.height - 256;
	else var popupHeight = screen.height - 64;

	var buttonSize = 0;
	if($('#add_reaction_button').height() > buttonSize) buttonSize = $('#add_reaction_button').height();
	if($('#add_reaction_button').width() > buttonSize) buttonSize = $('#add_reaction_button').width();
	if($('#remove_reaction_button').height() > buttonSize) buttonSize = $('#remove_reaction_button').height();
	if($('#remove_reaction_button').width() > buttonSize) buttonSize = $('#remove_reaction_button').width();
	$('#add_reaction_button').height(buttonSize);
	$('#add_reaction_button').width(buttonSize);
	$('#remove_reaction_button').height(buttonSize);
	$('#remove_reaction_button').width(buttonSize);

	$('#dsr_graph_applet_holder').css('width', popupWidth);
	$('#dsr_graph_applet_holder').css('margin-left', -popupWidth/2);
	$('.fancybox').fancybox({autoDimensions: false, width: popupWidth, height: popupHeight});

	$('#process_network_button').click(function()
	{
		if(!$(this).hasClass('disabled')) 
		{		
			resetPopup();
			if (saveNetwork())
			{				
				processTests();
			}
		}
		return false;
	});	

	$('#latex_output_button').click(function(e)
	{
		if(!$(this).hasClass('disabled'))
		{
			generateLaTeX();
		}
	});

	$('#dsr_graph_button').click(function(e)
	{
		e.preventDefault();
		if(!$(this).hasClass('disabled') && deployJava.getJREs().length)
		{
			var attributes = {
				codebase:siteURL,
				code:'dsr.DsrDraw.class',
				archive:'dsr_1.0.jar',
				width:popupWidth, height:popupHeight
			};
			var textOutput='';
			$('.reaction_input_row').each(function()
			{
				textOutput += $('.reaction_left_hand_side', $(this)).val();
				switch($('select.reaction_direction option:selected', $(this)).val())
				{
					case 'left':
						textOutput += '<--';
						break;
					case 'right':
						textOutput += '-->';
						break;
					case 'both':
						textOutput += '<-->';
						break;
					default:
						textOutput += '?';
				}
				textOutput += $('.reaction_right_hand_side', $(this)).val();
				textOutput += '.';
			});
			//alert(textOutput);
			var parameters = {content:textOutput};
			var version = '1.6';
			// Copied from https://forums.oracle.com/forums/thread.jspa?messageID=5521095
			var intercepted = '';
			var got = document.write;
			document.write = function(arg){intercepted += arg;}
			deployJava.runApplet(attributes, parameters, version);
			document.write = got;
			// End of copied code
			$('#dsr_graph_applet').html(intercepted);
			$('#dsr_graph_applet_holder').css('left', '50%');
		}
	});

	$('#dsr_graph_close_button').click(function(e)
	{
		e.preventDefault();
		$('#dsr_graph_applet_holder').css('left', '-10000px');
	});

	$('#option_holder input[name*="test_checkbox"]').change(function()
	{
		var testName = $(this).attr('name').slice(14, -1);
		var activated = 0;
		if($(this).is(':checked')) activated = 1;
		toggleTest(testName, activated);
		//alert(testName);
		//alert('testName = ' + testName + ', activated = ' + activated);
	})
});
