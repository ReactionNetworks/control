<?php
/**
 * CoNtRol configuration file
 *
 * Configuration details for CoNtRol. This file is included at the top of header.php, and
 * hence is automatically included in every page that produces HTML output. It must be
 * included separately in each handler page.
 *
 * Note: while the defaults *should* work on most systems, it is strongly recommended to
 * review the settings from the first section, and preferably also the second section.
 *
 * @author     Pete Donnell <pete dot donnell at port at ac at uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   09/08/2014
 */

/////////////////////////////////////////
// THINGS YOU SHOULD DEFINITELY CHANGE //
/////////////////////////////////////////

/**
 * Site subdirectory.
 */
define( 'SITE_DIR', 'control' );

/**
 * Site URL.
 *
 * If you want to run CoNtRol in the top level directory, remove SITE_DIR from this.
 */
define( 'SITE_URL', 'http://127.0.0.1/' . SITE_DIR . '/' );

/**
 * Email address for the site admin.
 *
 * All emails sent by CoNtRol are sent from this address.
 */
define( 'ADMIN_EMAIL', 'control@example.com' );

/**
 * Database connection information.
 *
 * You definitely need to change this if you want to use batch processing.
 */

// Example 1: MySQL on the same server.
//define( 'DB_STRING', 'mysql:host=localhost;dbname=control;charset=utf8' );

// Example 2: MySQL on a different server.
//define( 'DB_STRING', 'mysql:host=1.2.3.4;port=3306;dbname=control;charset=utf8' );

// Example 3: SQLite.
define( 'DB_STRING', 'sqlite:/var/www/html/control/sql/control.sqlite' );

/**
 * Database user
 *
 * Not required for SQLite.
 */
define( 'DB_USER', 'control' );

/**
 * Database password
 *
 * Not required for SQLite.
 */
define( 'DB_PASS', 'password' );

/**
 * Database table prefix
 *
 * Optional, useful for MySQL on hosting with a limited number of MySQL databases.
 */
define( 'DB_PREFIX', '' );



/////////////////////////////////////
// THINGS YOU MIGHT WANT TO CHANGE //
/////////////////////////////////////

/**
 * Require CAPTCHA for submission of batch jobs or known CRNs?
 *
 * It is STRONGLY recommended that you set this to true for a server that
 * is publicly accessible to the internet.
 */
define( 'REQUIRE_CAPTCHA', true );

/**
 * Accept online submissions of known CRNs?
 */
define( 'ACCEPT_KNOWN_CRN_SUBMISSIONS', true );

/**
 * Notify administrator of new CRN submissions?
 */
define( 'NOTIFY_ADMIN_OF_CRN_SUBMISSION', true );

/**
 * Location for the executables used by CoNtRol
 *
 * You may want to change this to a directory that is not web-accessible for extra security.
 */
define( 'BINARY_FILE_DIR', '../bin/' );

/**
 * Debugging variable. Set to true when debugging.
 */
define( 'CRNDEBUG', false );

/**
 * Niceness level to use when running tests
 *
 * Note trailing space.
 */

// Example 1: Value for dedicated server, i.e. normal priority.
//define( 'NICENESS', '' );

// Example 2: Value for shared server without ionice. N.B. trailing space!
define( 'NICENESS', 'nice -n 19 ' );

// Example 3: Value for shared server with ionice. N.B. trailing space!
//define( 'NICENESS', 'nice -n 19 ionice -c3 ' );

/**
 * Location for temporary files
 *
 * The default should work but you may wish to change it.
 */
define( 'TEMP_FILE_DIR', '/var/tmp/' );

/**
 * The maximum amount of time a test will run before being cancelled
 *
 * This is required because tests are run via calls to exec(),
 * which doesn't count towards max_execution_time.
 */
define( 'TEST_TIMEOUT_LIMIT', 60 );

/**
 * The maximum number of reactions that CoNtRol will accept without warning.
 *
 * This option is included because some of the tests struggle with large
 * networks. The analysereacs test currently doesn't complete in a sensible
 * amount of time for networks of more than 16 reactions, so this is the
 * recommended default maximum size. The dsr test has a tendency to crash
 * for large networks, although generally larger than 16. The endotacticity
 * test using glpsol rather than scip also frequently takes a long time for
 * larger networks.
 */
define( 'MAX_REACTIONS_PER_NETWORK', 16 );

/**
 * The server timezone.
 *
 * PHP will issue warnings if the timezone is not set. Autodetection code taken
 * from http://bojanz.wordpress.com/2014/03/11/detecting-the-system-timezone-php/
 * Note that this code will not work on Windows. In general, it will be more
 * efficient to replace 'UTC' with your actual timezone, e.g. 'Europe/London' or
 * 'America/New_York', and to comment out the autodetection code.
 */
$timezone = 'UTC';
if( is_link( '/etc/localtime' ) )
{
	// Mac OS X (and older Linuxes)
	// /etc/localtime is a symlink to the timezone in /usr/share/zoneinfo.
	$filename = readlink( '/etc/localtime' );
	if( strpos( $filename, '/usr/share/zoneinfo/' ) === 0 ) $timezone = trim( substr( $filename, 20 ) );
}
elseif( file_exists( '/etc/timezone' ) )
{
	// Ubuntu/Debian
	$data = file_get_contents( '/etc/timezone' );
	if( $data ) $timezone = trim( $data );
}
elseif( file_exists( '/etc/sysconfig/clock' ) )
{
	// RHEL/CentOS
	$data = parse_ini_file( '/etc/sysconfig/clock' );
	if( !empty( $data['ZONE'] ) ) $timezone = trim( $data['ZONE'] );
}
date_default_timezone_set( $timezone );


//////////////////////////////////////////////////
// THINGS YOU ALMOST CERTAINLY SHOULDN'T CHANGE //
//////////////////////////////////////////////////

/**
 * Default page title.
 */
define( 'DEFAULT_PAGE_TITLE', 'CoNtRol - Chemical Reaction Network analysis tool' );

/**
 * Default page meta description tag.
 */
define( 'DEFAULT_PAGE_DESCRIPTION', 'CoNtRol is a web application to analyse chemical reaction networks. It also generates LaTeX markup describing their associated DSR graphs and equations.' );

/*
 * Client computer line ending (Windows/Mac/UNIX)
 *
 * Work out which line ending to use for file exports.
 * Don't change this: CoNtRol auto-detects the client line ending when appropriate.
 */
if( isset( $_SERVER['HTTP_USER_AGENT'] ) )
{
	if( strpos( $_SERVER['HTTP_USER_AGENT'], 'Windows;' ) !== false ) $line_ending = "\r\n";
	elseif( strpos( $_SERVER['HTTP_USER_AGENT'], 'Macintosh;' ) !== false ) $line_ending = "\r";
	else $line_ending = "\n";
}
// Default to server line ending when client line ending not available.
else $line_ending = PHP_EOL;
/**
 * Client computer line ending (Windows/Mac/UNIX)
 */
define( 'CLIENT_LINE_ENDING', $line_ending );

/*
 * Required to support cross-platform line endings on file import.
 */
ini_set( 'auto_detect_line_endings', '1' );

/*
 * Extra database options.
 *
 * It shouldn't be necessary to change this. Older versions of MySQL did not
 * support the charset=utf8 connection option, in which case it's a good idea
 * to switch to UTF8 mode manually when first opening the database connection.
 */
if( strpos( 'mysql', DB_STRING ) === 0 and ( !defined( PHP_VERSION_ID ) or PHP_VERSION_ID < 50306 ) ) $db_options = array( PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8' );
else $db_options = null;
