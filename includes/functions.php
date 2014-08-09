<?php
/**
 * CoNtRol standard functions
 *
 * Assorted helper functions used within CoNtRol. This file is included at the top of
 * header.php, and hence is automatically included in every page that produces HTML
 * output. It must be included separately in each handler page.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   09/08/2014
 */

/**
 * Generate a simple CAPTCHA and display it as HTML
 *
 * @return  string  $output  HTML code representing the CAPTCHA text
 */
function batch_captcha()
{
	$output = '';
	$_SESSION['batch-captcha'] = captcha_random_string( 5 );
	$char_array = str_split( $_SESSION['batch-captcha'] );
	// Encode in ASCII to trick bots
	foreach( $char_array as $char )
	{
		$output .= '&shy;&#' . ord( $char ) . ';';
	}
	echo $output;
}

/**
 * Generate a random string to use as a captcha
 *
 * Based upon http://www.marksanborn.net/php/random-password-string-generator-for-php/
 */
function captcha_random_string( $length = 10 )
{
	$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	$randomString = '';

	$charLength = strlen( $chars ) - 1;

	for( $i = 0; $i < $length; $i++ )
	{
		$randomString .= $chars[mt_rand( 0, $charLength )];
	}

	return $randomString;
}

/**
 * Verify that a file has the correct mimetype
 *
 * @param   string  $file               Path to the file for testing
 * @param   string  $expected_mimetype  The desired mimetype for the file
 * @return  bool    $success            Return TRUE if the file was successfully opened and had the correct mimetype, FALSE otherwise.
 */
function check_file_format( $file, $expected_mimetype )
{
	$success = true;
	$finfo = new finfo( FILEINFO_MIME_TYPE );
	if( $finfo )
	{
		$mimetype = $finfo->file( $file );
		if( $mimetype !== $expected_mimetype ) $success = false;
	}
	else $success = false;
	return $success;
}

/**
 * Convert HTML links to plain text
 *
 * Primarily intended to help generate plain text version of test output for sending via email.
 * Input link format 1:
 * <a class="some_class" href="http://example.com/" title="Some Title">Example</a>
 * Output plain text format 1:
 * Some Title [http://example.com/]
 * Input link format 2:
 * <a class="some_class" href="http://example.com/">Example</a>
 * Output plain text format 2:
 * Example [http://example.com/]
 * I.e. if title attribute is present, this is output followed by the link href location in
 * square brackets. If the title attribute is not present, the link text is output followed
 * by the link href location in square brackets. All other attributes are ignored.
 *
 * @param   string  $intext   Text including links to convert to plain text
 * @return  string  $outtext  Same text with links converted to plain text
 */
function convert_links_to_plain_text( $intext )
{
	if( strpos( $intext, '|' ) === false ) $delimiter = '|';
	elseif( strpos( $intext, '`' ) === false ) $delimiter = '`';
	elseif( strpos( $intext, '~' ) === false ) $delimiter = '~';
	elseif( strpos( $intext, '@' ) === false ) $delimiter = '@';
	else return $intext;
	// Replace link text with link title if title attribute present
	$outtext = preg_replace( $delimiter . '(<a)(.+?)(title=")(.+?)(")(.*?)(>)(.+?)(</a>)' . $delimiter, '$1$2$6$7$4$9', $intext );
	// Strip out link HTML
	$outtext = preg_replace( $delimiter . '(<a)(.+?)(href=")(.+?)(")(.*?)(>)(.+?)(</a>)' . $delimiter, '$8 [$4]', $outtext );
	return $outtext;
}

/**
 * Get the mime type of a file
 *
 * Taken from http://stackoverflow.com/questions/134833/how-do-i-find-the-mime-type-of-a-file-with-php
 *
 * @param   string  $file  The file name to check.
 * @return  mixed   $mime  If the mimetype could be determined, return it as a string. Else return FALSE.
 */
function get_mime( $file )
{
	if( function_exists( 'finfo_file' ) )
	{
		$finfo = finfo_open( FILEINFO_MIME_TYPE ); // return mime type à la mimetype extension
		$mime = finfo_file( $finfo, $file );
		finfo_close( $finfo );
		return $mime;
	}
	else if( function_exists( 'mime_content_type' ) )
	{
		return mime_content_type( $file );
	}
	else if( !stristr( ini_get( 'disable_functions' ), 'shell_exec' ) )
	{
		// http://stackoverflow.com/a/134930/1593459
		$file = escapeshellarg( $file );
		$mime = shell_exec( 'file -bi ' . $file );
		return $mime;
	}
	else
	{
		return false;
	}
}

/**
 * Hide email address for display in web page
 *
 * Provides very basic email address obfuscation, to reduce the risk of email addresses
 * being harvested by spam bots. It replaces all instances of '@' with the word ' at ',
 * and all instances of '.' with the word ' dot '.
 *
 * @param   string  $email  The email address in its original form
 * @return  string          The same email address in obfuscated form
 */
function hide_email_address( $email )
{
	return str_replace( '@', ' at ', str_replace( '.', ' dot ', $email ) );
}

/**
 * Convert a matrix into text
 *
 * @param   array   $matrix  A matrix represented as a 2D array / array of arrays
 * @return  string  $text    The matrix represented as text
 */
function printMatrix( $matrix )
{
	$text = '';
	foreach( $matrix as $row )
	{
		foreach( $row as $element ) $text = $text . ' ' . $element;
		$text .= PHP_EOL;
	}
	return $text;
}

/**
 * Recursively remove directory
 *
 * Taken from http://lixlpixel.org/recursive_function/php/recursive_directory_delete/
 *
 * ------------ lixlpixel recursive PHP functions -------------
 * recursive_remove_directory( directory to delete, empty )
 * expects path to directory and optional TRUE / FALSE to empty
 * of course PHP has to have the rights to delete the directory
 * you specify and all files and folders inside the directory
 * ------------------------------------------------------------
 *
 * to use this function to totally remove a directory, write:
 * recursive_remove_directory('path/to/directory/to/delete');
 *
 * to use this function to empty a directory, write:
 * recursive_remove_directory('path/to/full_directory',TRUE);
 */

function recursive_remove_directory($directory, $empty = FALSE)
{
	// if the path has a slash at the end we remove it here
	if(substr($directory,-1) == '/')
	{
		$directory = substr($directory,0,-1);
	}

	// if the path is not valid or is not a directory ...
	if(!file_exists($directory) || !is_dir($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... if the path is not readable
	}
	elseif(!is_readable($directory))
	{
		// ... we return false and exit the function
		return FALSE;

	// ... else if the path is readable
	}
	else
	{
		// we open the directory
		$handle = opendir($directory);

		// and scan through the items inside
		while (FALSE !== ($item = readdir($handle)))
		{
			// if the filepointer is not the current directory
			// or the parent directory
			if($item != '.' && $item != '..')
			{
				// we build the new path to delete
				$path = $directory.'/'.$item;

				// if the new path is a directory
				if(is_dir($path))
				{
					// we call this function with the new path
					recursive_remove_directory($path);

				// if the new path is a file
				}
				else
				{
					// we remove the file
					unlink($path);
				}
			}
		}
		// close the directory
		closedir($handle);

		// if the option to empty is not set to true
		if($empty == FALSE)
		{
			// try to delete the now empty directory
			if(!rmdir($directory))
			{
				// return false if not possible
				return FALSE;
			}
		}
		// return success
		return TRUE;
	}
}

/**
 * Convert file size to bytes
 *
 * Corrected from version on http://php.net/manual/en/function.ini-get.php
 *
 * @param   string  $val  File size as a string, eg. 1M
 * @return  int           File size in bytes
 */
function return_bytes( $val )
{
	$val = trim( $val );
	$last = strtolower( $val[strlen( $val ) - 1] );
	$val = (int) substr( $val, 0, strlen( $val ) - 1 );
	switch( $last )
	{
		case 'g':
			$val *= 1024;
			// fall through
		case 'm':
			$val *= 1024;
			// fall through
		case 'k':
			$val *= 1024;
			// no default
	}
	return $val;
}

/**
 * HTML output sanitiser
 *
 * Sanitises text for output to HTML
 *
 * @param   string  $text  The text to be sanitised
 * @return  string         The sanitised version of the text
 */
function sanitise( $text )
{
	return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8', false );
}
