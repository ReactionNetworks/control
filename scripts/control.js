/**
 * Main CoNtRol JavaScript file
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   12/09/2014
 */

/**
 * Adds a row to the reaction input form
 */
function addReaction()
{
	++number_of_reactions;

	$( '#tools_holder' ).before( '<fieldset class="reaction_input_row">' + number_of_reactions + '. <input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" spellcheck="false" placeholder="&empty;" /> <select class="reaction_direction" name="reaction_direction[]"><option value="left">&larr;</option><option value="both" selected="selected">&#x21cc;</option><option value="right">&rarr;</option></select> <input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" spellcheck="false" placeholder="&empty;" /> </fieldset>' );

	$( '.reaction_left_hand_side, .reaction_right_hand_side' ).each( function()
	{
		// Prevent double binding
		$( this ).off( 'keydown keyup' );
		// Capture Enter key
		$( this ).on( 'keydown', function( e )
		{
			if( e.which == 13 ) e.preventDefault();
		} );
		// Validate input and check for network submission
		$( this ).on( 'keyup', function( e )
		{
			if( validateKeyPress( $( this ) ) )
			{
				if( e.which == 13 )
				{
					$( '#process_network_button' ).click();
				}
			}
		} );
	} );
}

/**
 * Get the size of the visible area of the browser
 */
function detectWindowSize()
{
	if( $( window ).innerWidth() > 800 ) popupWidth = $( window ).innerWidth() - 256;
	else popupWidth = $( window ).innerWidth() - 16;
	if( $( window ).innerHeight() > 800 ) popupHeight = $( window ).innerHeight() - 256;
	else popupHeight = $( window ).innerHeight() - 16;
}

/**
 * Disables reaction reset, DSR graph and analysis buttons
 */
function disableButtons()
{
	$( '#dsr_graph_button' ).addClass( 'disabled' );
	$( '#process_network_button' ).addClass( 'disabled' );
	$( '#download_network_file_button' ).addClass( 'disabled' );
	$( '#latex_output_button' ).addClass( 'disabled' );
	$( '#reset_reaction_button' ).addClass( 'disabled' );
}

/**
 * Enables reaction reset, DSR graph and analysis buttons
 */
function enableButtons()
{
	// Remove reaction button doesn't need to be enabled, as it is automatically enabled/disabled based on the number of reactions
	$( '#dsr_graph_button' ).removeClass( 'disabled' );
	$( '#process_network_button' ).removeClass( 'disabled' );
	$( '#download_network_file_button' ).removeClass( 'disabled' );
	$( '#download_network_file_button' ).removeAttr( 'disabled' );
	$( '#latex_output_button' ).removeClass( 'disabled' );
	$( '#reset_reaction_button' ).removeClass( 'disabled' );
}

/**
 * Generates LaTeX markup for a set of reactions and displays it in a popover
 */
function generateLaTeX()
{
	var numberOfRows = $( '.reaction_input_row' ).length;
	var numberOfColumns = 0;
	var textOutput = '\\begin{array}{rcl}\n';
	$( '.reaction_input_row' ).each( function( index, element )
	{
		if( $( '.reaction_left_hand_side', $( this ) ).val() == '' || $( '.reaction_left_hand_side', $( this ) ).val() == ' ' || $( '.reaction_left_hand_side', $( this ) ).val() == '  ' ) textOutput += '\\emptyset';
		else textOutput += $( '.reaction_left_hand_side', $( this ) ).val().replace( '&', '\\&amp;' );
		textOutput += ' &amp; ';
		switch( $( 'select.reaction_direction option:selected', $( this ) ).val())
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
		if( $( '.reaction_right_hand_side', $( this ) ).val() == '' || $( '.reaction_right_hand_side', $( this ) ).val() == ' ' || $( '.reaction_right_hand_side', $( this ) ).val() == '  ' ) textOutput += '\\emptyset';
		else textOutput += $( '.reaction_right_hand_side', $( this ) ).val().replace( '&', '\\&' );
		textOutput = textOutput.replace( '$', '\\$' );
		textOutput += ' ';
		if( index < numberOfRows - 1 ) textOutput += '\\\\';
		textOutput += '\n';
	});
	var allLines = textOutput.split( '\n' );
	for( i = 0; i < allLines.length; ++i )
	{
		if( allLines[i].length > numberOfColumns ) numberOfColumns = allLines[i].length;
	}
	numberOfColumns *= 2;
	numberOfRows += 3;
	textOutput = '<p>Reactions:</p><textarea rows="' + numberOfRows + '" cols="' + numberOfColumns + '">\n' + textOutput + '\\end{array}</textarea><p>Stoichiometry matrix:</p><textarea rows="' + numberOfRows*2 + '" cols="' + numberOfColumns + '">\\Gamma = ';
	var url = 'handlers/get-net-stoichiometry.php';
	var data = { csrf_token: csrf_token };
	$.post( url, data, function( returndata )
	{
		textOutput += returndata.replace( '&', '&amp;' ) + '\n</textarea>\n<p>Reaction rate Jacobian:</p><textarea rows="' + numberOfRows*2 + '" cols="' + numberOfColumns + '">V^T = ';
		url = 'handlers/get-v-matrix.php';
		$.post( url, data, function( returndata )
		{
			textOutput += returndata.replace( '&', '&amp;' ) + '\n</textarea>\n';
			$( '#latex_output_holder' ).html( textOutput );
		} );
	} );
}

/**
 * Calls the test handler for all selected tests and then redirects to the results
 */
function processTests( test_number )
{
	setTimeout( function()
	{
		var url = 'handlers/process-tests.php';
		data = { csrf_token: csrf_token };
		var timeout_countdown = test_timeout_limit;
		$( '#calculation_output_holder' ).append( '<p id="timeout_countdown_holder">Processing test ' + test_number + '... <span id="timeout_countdown">' + test_timeout_limit + '</span> seconds until timeout.</p>' );
		clearInterval( timer_id );
		timer_id = setInterval( function()
		{
			if ( timeout_countdown )
			{
				--timeout_countdown;
				$( '#timeout_countdown') .html( timeout_countdown );
			}
			else
			{
				$( '#calculation_output_holder' ).append( '<p>Test timed out.</p>' );
				clearInterval( timer_id );
			}
		}, 1000 );
		$.post( url, data, function( returndata )
		{
			showTestOutput( returndata );
			$( '#timeout_countdown_holder' ).remove();
			// All tests complete:
			if( returndata == '<p>All tests completed. Redirecting to results.</p>' ) window.location.href = 'results.php';
			// A bug occurred:
			else if( returndata == '<p>Error: CSRF detected or CRN not set up.</p>' ) window.location.reload();
			// Proceed to the next test:
			else processTests( ++test_number );
		} );
	}, 100 );
}

/**
 * Removes a row from the reaction input form
 *
 * N.B. This function does NOT check whether there is only one reaction left.
 * Consequently, calling it when there is only one reaction left will result
 * in no reactions being left. Calling it again may trigger a JavaScript error
 * in the user's browser.
 */
function removeReaction( notify_user )
{
	if( notify_user )
	{
		$( '#removed_reaction_span' ).html( number_of_reactions );
		var position = $( '#remove_reaction_button' ).position();
		$( '#removed_reaction_warning' ).css( 'top', position.top + 24 );
		$( '#removed_reaction_warning' ).css( 'left', position.left - 56 );
		$( '#removed_reaction_warning' ).show();
		setTimeout( function()
		{
			$( '#removed_reaction_warning' ).hide();
		}, 1500 );
	}

	$( '#reaction_input_form fieldset' ).filter( ':last' ).remove();
	--number_of_reactions;
	saveNetwork();
}

/**
 * Clears the results popup.
 */
function resetPopup()
{
	$( '#calculation_output_holder' ).html( '<p>Processing selected tests. This may take some time, please be patient. Do not close this popup window!</p>' );
}

/**
 * Resets all reactions in the input form
 *
 * Disables the various reaction network processing buttons, clears all reactions in the form,
 * removes all reactions except the first, and clears any saved reactions from the session.
 */
function resetReactions()
{
	$( '#reaction_input_form fieldset input' ).val( '' );
	$( '#reaction_input_form fieldset select option[value=both]' ).attr( 'selected', true );
	disableButtons();
	while( $( '#reaction_input_form fieldset' ).length - 1 ) removeReaction( false );
	$( '#remove_reaction_button' ).addClass( 'disabled' );
	var url = 'handlers/reset-reactions.php';
	var data = { reset_reactions: 1, csrf_token: csrf_token };
	$.post( url, data );
	if( $( '#error_message_holder' ) ) $( '#error_message_holder' ).hide();
	if( $( '#results_link' ) ) $( '#results_link' ).hide();
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
	$.each( $( '.reaction_left_hand_side' ), function( index, value )
	{
		reactionsLeftHandSide.push( value.value );
	} );
	var reactionsRightHandSide = new Array();
	$.each( $( '.reaction_right_hand_side' ), function( index, value )
	{
		reactionsRightHandSide.push( value.value );
	} );
	var reactionsDirection = new Array();
	$.each( $( '.reaction_direction :selected' ), function( index, value )
	{
		reactionsDirection.push( value.value );
	} );
	var testSettings = new Array();
	$.each( $( '.test' ), function( index, v )
	{
		testSettings.push( { name: $( this ).attr( 'name' ), value: $( this ).val() } );
	} );
	var data = { 'reaction_left_hand_side[]': reactionsLeftHandSide, 'reaction_right_hand_side[]': reactionsRightHandSide, 'reaction_direction[]': reactionsDirection, 'test_settings': testSettings, csrf_token: csrf_token };
	$.post( url, data, function( returndata )
	{
		if( returndata.length )
		{
			showTestOutput( '<p>' + returndata + '</p>' );
			validNetwork = false;
		}
	} );
	return validNetwork;
}

/**
 * Adds output from a test to the progress popover
 */
function showTestOutput( output )
{
	$( '#calculation_output_holder' ).append( output );
}

/**
 * Enables/disables detailed test output via AJAX
 */
function toggleDetailedOutput( newStatus )
{
	var url = 'handlers/toggle-detailed-output.php';
	var data = { detailed_output: newStatus, csrf_token: csrf_token };
	$.post( url, data );
}

/**
 * Enables/disables the specified test via AJAX
 */
function toggleTest( testName, newStatus )
{
	var url = 'handlers/toggle-test.php';
	var data = { testName: testName, active: newStatus, csrf_token: csrf_token };
	$.post( url, data );
}

/**
 * Validates an email address
 */
function validateEmailAddress( emailAddress )
{
	var atPos = emailAddress.indexOf( '@' );
	if( atPos < 1 ) return false;
	if( emailAddress.indexOf( '.', atPos ) > ( atPos + 1 ) && emailAddress.charAt( emailAddress.length - 1 ) != '.' ) return true;
	return false;
}

/**
 * Warns about invalid character input
 */
function validateKeyPress( inputElement )
{
	var invalidCharacters = new Array( '<', '>', '-', '=' );
	for( i = 0; i < invalidCharacters.length; ++i )
	{
		if( inputElement.val().indexOf( invalidCharacters[i] ) > -1 )
		{
			inputElement.val( inputElement.val().replace( invalidCharacters[i], '' ) );
			$( '#invalid_character_span' ).html( invalidCharacters[i] );
			var position = inputElement.position();
			$( '#hidden_character_warning' ).css( 'top', position.top + 48 );
			$( '#hidden_character_warning' ).css( 'left', position.left );
			$( '#hidden_character_warning' ).show();
			setTimeout( function() { $( '#hidden_character_warning' ).hide(); }, 1500);
		}
	}
	var validInput = true;
	var totalChars = 0;
	$( '#missing_reactant_warning' ).hide();
	$( '.reaction_left_hand_side, .reaction_right_hand_side' ).each( function()
	{
		$( this ).css( 'border-color', '' );
		totalChars += $( this ).val().length;
		lhs = $.trim( $( this ).val() );
		if( lhs.indexOf( '+' ) == 0 || lhs[lhs.length - 1] == '+' || lhs.indexOf( '++' ) > -1 || lhs.indexOf( '+ +' ) > -1 || lhs.indexOf( '+  +' ) > -1)
		{
			validInput = false;
			$( this ).css( 'border-color', 'red' );
			var position = inputElement.position();
			$( '#missing_reactant_warning' ).css( 'top', position.top + 48 );
			$( '#missing_reactant_warning' ).css( 'left', position.left );
			$( '#missing_reactant_warning' ).show();
		}
	} );
	if( validInput && totalChars )
	{
		saveNetwork();
		enableButtons();
		return true;
	}
	else
	{
		disableButtons();
		return false;
	}
}

var networkSubmitted = false;
var popupWidth = 800;
var popupHeight = 600;
var popupMargin = 16;

$( document ).ready( function()
{
	// Set some useful variables
	if( $( window ).innerWidth() > 800 ) popupWidth = $( window ).innerWidth() - 256;
	else popupWidth = $( window ).innerWidth() - 16;
	if( $( window ).innerHeight() > 800 ) popupHeight = $( window ).innerHeight() - 256;
	else popupHeight = $( window ).innerHeight() - 16;
	var buttonSize = 0;

	// Enable DSR Java app for browsers with Java installed
	if( navigator.userAgent.indexOf( 'Android' ) == -1 && navigator.userAgent.indexOf( 'iOS' ) == -1 && deployJava.getJREs().length ) $( '#dsr_graph_button' ).removeClass( 'fancybox' );

	// File inputs slide down out of the File Input header on clock
	var toolsShown = false;
	$( '#tools_show' ).click( function()
	{
		if( !toolsShown )
		{
			$( '#tools_buttons_slidedown' ).slideDown();
			$( '#tools_show' ).html( 'Hide' );
			toolsShown = true;
		}
		else
		{
			$( '#tools_buttons_slidedown' ).slideUp();
			$( '#tools_show' ).html( 'Show' );
			toolsShown = false;
		}
	} );

	// Similarly, more analysis options slide down
	var moreActionsShown = false;
	$( '#more_actions_show' ).click( function()
	{
		if( !moreActionsShown )
		{
			$( '#more_actions_slidedown' ).slideDown();
			$( '#more_actions_show' ).html( 'Less' );
			moreActionsShown = true;
		}
		else
		{
			$(' #more_actions_slidedown' ).slideUp();
			$(' #more_actions_show' ).html( 'More' );
			moreActionsShown = false;
		}
	} );

	$( '#add_reaction_button' ).click( function()
	{
		addReaction();
		$( '#remove_reaction_button' ).removeClass( 'disabled' );
		return false;
	} );

	$( '#remove_reaction_button' ).click( function()
	{
		if( !$( this ).hasClass( 'disabled' ) )
		{
			if( $( '#reaction_input_form > fieldset' ).length > 1 ) removeReaction( true );
			if( $( '#reaction_input_form > fieldset' ).length == 1 ) $( this ).addClass( 'disabled' );
			else $(this).removeClass('disabled');
		}
		if( $( '#reaction_input_form > fieldset' ).length == 1 && $( '#reaction_input_form > fieldset .reaction_left_hand_side' ).val() == '' && $( '#reaction_input_form > fieldset .reaction_right_hand_side' ).val() == '') $( '#reset_reaction_button' ).addClass( 'disabled' );
		return false;
	} );

	$( '#reset_reaction_button' ).click( function()
	{
		if( !$( this ).hasClass( 'disabled' ) ) resetReactions();
		return false;
	} );

	$( '.reaction_direction' ).each( function()
	{
		// Prevent double binding
		$( this ).off();
		// Bind change handler
		$( this ).change( function()
		{
			saveNetwork();
		} );
	} );

	$( '.reaction_left_hand_side, .reaction_right_hand_side' ).each( function()
	{
		// Prevent double binding
		$( this ).off( 'keydown keyup' );
		// Capture Enter key
		$( this ).on( 'keydown', function( e )
		{
			if( e.which == 13 ) e.preventDefault();
		} );
		// Validate input and check for network submission
		$( this ).on( 'keyup', function( e )
		{
			if( validateKeyPress( $( this ) ) )
			{
				if( e.which == 13 )
				{
					$( '#process_network_button' ).click();
				}
			}
		} );
	} );

	if( $( '#add_reaction_button' ).height() > buttonSize) buttonSize = $( '#add_reaction_button' ).height();
	if( $( '#add_reaction_button' ).width() > buttonSize) buttonSize = $( '#add_reaction_button' ).width();
	if( $( '#remove_reaction_button' ).height() > buttonSize ) buttonSize = $( '#remove_reaction_button' ).height();
	if( $( '#remove_reaction_button' ).width() > buttonSize ) buttonSize = $( '#remove_reaction_button' ).width();
	if( $( '#reset_reaction_button' ).height() > buttonSize ) buttonSize = $( '#reset_reaction_button' ).height();
	if( $( '#reset_reaction_button' ).width() > buttonSize ) buttonSize = $( '#reset_reaction_button' ).width();
	$( '#add_reaction_button' ).height( buttonSize );
	$( '#add_reaction_button' ).width( buttonSize );
	$( '#remove_reaction_button' ).height( buttonSize );
	$( '#remove_reaction_button' ).width( buttonSize );
	$( '#reset_reaction_button' ).height( buttonSize );
	$( '#reset_reaction_button' ).width( buttonSize );

	$( '.fancybox' ).fancybox( { autoDimensions: true, width: popupWidth, height: popupHeight } );
	$( '.fancybox_dynamic' ).fancybox( { autoDimensions: false, width: popupWidth, height: popupHeight } );

	$( '#crn_description' ).focus( function()
	{
		$( this ).select();
	} );

	$( '#detailed_output_checkbox' ).change( function()
	{
		var activated = 0;
		if( $( this ).is( ':checked' ) ) activated = 1;
		toggleDetailedOutput( activated );
	} );

	$( '#download_network_file_button' ).click( function( e )
	{
		if( $( this ).hasClass( 'disabled' ) ) e.preventDefault();
	} );

	$( '#dsr_graph_button' ).click( function( e )
	{
		if( $( this ).hasClass( 'disabled' ) ) e.preventDefault();
		else if( navigator.userAgent.indexOf( 'Android' ) == -1 && navigator.userAgent.indexOf( 'iOS' ) == -1 && deployJava.getJREs().length )
		{
			e.preventDefault();
			window.location.replace( 'jnlp.php' );
		}
	} );

	$( '#email_results_form' ).submit( function( e )
	{
		e.preventDefault();
		$( '#email_results_button' ).addClass( 'disabled' );
		$( '#email_results_button' ).attr( 'disabled', 'disabled' );
		var url = 'handlers/mail-results.php';
		var email = $( '#results_email' ).val();
		var label = $( '#results_label' ).val();
		var data = {email: email, label: label, csrf_token: csrf_token};
		$.post( url, data, function( returndata )
		{
			if( returndata.length ) $( '#email_results_error' ).html( returndata );
			window.setTimeout( function()
			{
				$( '#email_results_error' ).html( '&nbsp;' );
				$( '#email_results_button' ).removeClass( 'disabled' );
				$( '#email_results_button' ).removeAttr( 'disabled' );
			}, 1000 );
		} );
	} );

	$( '#email_results_form_button' ).click( function( e )
	{
		$( '#results_email' ).select();
		if( validateEmailAddress( $( '#results_email' ).val() ) )
		{
			$( '#email_results_error' ).html( '&nbsp;' );
			$( '#email_results_button' ).removeClass( 'disabled' );
			$( '#email_results_button' ).removeAttr( 'disabled' );
		}
		else
		{
			$( '#email_results_error' ).html( 'Invalid email address' );
			$( '#email_results_button' ).addClass( 'disabled' );
			$( '#email_results_button' ).addAttr( 'disabled' );
		}
	} );

	$( '#latex_output_button' ).click( function( e )
	{
		if( !$( this ).hasClass( 'disabled' ) )
		{
			$.when( saveNetwork() ).then( generateLaTeX() );
		}
	} );

	$( '#option_holder input[name*="test_checkbox"]' ).change( function()
	{
		var testName = $( this ).attr( 'name' ).slice( 14, -1 );
		var activated = 0;
		if( $( this ).is( ':checked' ) ) activated = 1;
		toggleTest( testName, activated );
	} );

	$( '#process_network_button' ).click( function()
	{
		if( !networkSubmitted && !$( this ).hasClass( 'disabled' ) )
		{
			resetPopup();
			$.when( saveNetwork() ).then( processTests( 1 ) );
			networkSubmitted = true;
		}
		return false;
	} );

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
	} );

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
	} );

	$( 'th.test_checkboxes' ).click( function()
	{
		$( 'input[name*=test_checkbox]' ).each( function() { $( this ).prop( 'checked', true ).trigger( 'change' ) } );
	} );

	$('#upload_batch_file_email').change(function()
	{
		if(validateEmailAddress($('#upload_batch_file_email').val()))
		{
			$('#upload_batch_file_email_error').html('&nbsp;');
			if($('#upload_batch_file_input').val() != '')
			{
				$('#upload_batch_file_button').removeClass('disabled');
				$('#upload_batch_file_button').removeAttr('disabled');
			}
		}
		else $('#upload_batch_file_email_error').html('Invalid email address');
	} );

	$('#upload_batch_file_email').keyup(function()
	{
		if(validateEmailAddress($('#upload_batch_file_email').val()))
		{
			$('#upload_batch_file_email_error').html('&nbsp;');
			if($('#upload_batch_file_input').val() != '')
			{
				$('#upload_batch_file_button').removeClass('disabled');
				$('#upload_batch_file_button').removeAttr('disabled');
			}
		}
		else
		{
			$('#upload_batch_file_email_error').html('Invalid email address');
			$('#upload_batch_file_button').addClass('disabled');
			$('#upload_batch_file_button').attr('disabled', 'disabled');
		}
	} );

	$('#upload_batch_file_input').change(function()
	{
		if(validateEmailAddress($('#upload_batch_file_email').val()))
		{
			$('#upload_batch_file_button').removeClass('disabled');
			$('#upload_batch_file_button').removeAttr('disabled');
		}
	} );

	$('#upload_network_file_input').change(function()
	{
		$('#upload_network_file_button').removeClass('disabled');
		$('#upload_network_file_button').removeAttr('disabled');
	} );

	$( window ).resize( function() { detectWindowSize(); } );

	if( navigator.userAgent.indexOf( 'Android' ) == -1 && navigator.userAgent.indexOf( 'iOS' ) == -1 )
	{
		$( '.reaction_left_hand_side' ).first().select();
	}
} );
