<?php
/**
 * CoNtRol standard functions
 *
 * Assorted helper functions used within CoNtRol. This file is included at the top of
 * header.php, and hence is automatically included in every page that produces HTML
 * output. It must be included separately in each handler page.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   10/10/2012
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
 * Modified from http://php.net/manual/en/function.ini-get.php
 *
 * @param   string  $val  file size as a string, eg. 1M 
 * @return  int           file size in bytes
 */
 function return_bytes($val) {
    $val = trim($val);
    $last = strtolower($val[strlen($val)-1]);
					$val = (int)substr($val,0,strlen($val) - 1);    
    switch($last) {
        // The 'G' modifier is available since PHP 5.1.0
        case 'g':
            $val *= 1024;
        case 'm':
            $val *= 1024;
        case 'k':
            $val *= 1024;
    }

    return $val;
}