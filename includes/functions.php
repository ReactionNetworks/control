<?php
/**
 * CoNtRol standard functions
 *
 * Assorted helper functions used within CoNtRol. This file is included at the top of
 * header.php, and hence is automatically included in every page that produces HTML
 * output. It must be included separately in each handler page.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   22/04/2013
 */

/**
 * HTML output sanitiser
 *
 * Sanitises text for output to HTML
 *
 * @param   string  $text  The text to be sanitised
 * @return  string         The sanitised version of the text
 */
function sanitise($text)
{
	return htmlspecialchars($text, ENT_QUOTES, 'UTF-8', false);
}

function printMatrix($matrix)
{
	$text = '';
	foreach($matrix as $row)
	{
		foreach($row as $element) $text = $text.' '.$element;	
		$text.=PHP_EOL;	
	}
	return $text;		
}

/**
 * Convert file size to bytes
 *
 * Corrected from version on http://php.net/manual/en/function.ini-get.php
 *
 * @param   string  $val  File size as a string, eg. 1M 
 * @return  int           File size in bytes
 */
	function return_bytes($val) 
	{
		$val = trim($val);
		$last = strtolower($val[strlen($val) - 1]);
		$val = (int)substr($val,0,strlen($val) - 1);    
		switch($last)
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
 * Get the mime type of a file
 *
 * Taken from http://stackoverflow.com/questions/134833/how-do-i-find-the-mime-type-of-a-file-with-php
 *
 * @param   string  $file  The file name to check.
 * @return  mixed   $mime  If the mimetype could be determined, return the it as a string. Else return FALSE.
 */
function get_mime($file) 
{
	if(function_exists("finfo_file"))
	{
		$finfo = finfo_open(FILEINFO_MIME_TYPE); // return mime type à la mimetype extension
		$mime = finfo_file($finfo, $file);
		finfo_close($finfo);
		return $mime;
	}
	elseif(function_exists("mime_content_type"))
	{
		return mime_content_type($file);
	}
	elseif(!stristr(ini_get("disable_functions"), "shell_exec"))
	{
		// http://stackoverflow.com/a/134930/1593459
		$file = escapeshellarg($file);
		$mime = shell_exec("file -bi " . $file);
		return $mime;
	}
	else
	{
		return false;
	}
}

/**
 * Recursively remove directory
 *
 * Taken from http://lixlpixel.org/recursive_function/php/recursive_directory_delete/
 *
	// ------------ lixlpixel recursive PHP functions -------------
	// recursive_remove_directory( directory to delete, empty )
	// expects path to directory and optional TRUE / FALSE to empty
	// of course PHP has to have the rights to delete the directory
	// you specify and all files and folders inside the directory
	// ------------------------------------------------------------

	// to use this function to totally remove a directory, write:
	// recursive_remove_directory('path/to/directory/to/delete');

	// to use this function to empty a directory, write:
	// recursive_remove_directory('path/to/full_directory',TRUE);
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
