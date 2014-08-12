<?php
/**
 * CoNtRol HTML header
 *
 * Standard header included on all pages within CoNtRol
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   12/08/2014
 */

header( 'Expires: Sat, 26 Jul 1997 05:00:00 GMT' );
header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
header( 'Cache-Control: no-store, no-cache, must-revalidate' );
header( 'Cache-Control: post-check=0, pre-check=0', false );
header( 'Pragma: no-cache' );

/**
 * Standard include
 */
require_once( 'config.php' );

// Force redirect if not being accessed on the correct URL
$currentURL = explode( '/', $_SERVER['REQUEST_URI'] );
$current_page = end( $currentURL );
if( $current_page === 'index.php' ) $current_page = '';
$protocol = 'http';
if( isset( $_SERVER['HTTPS'] ) and $_SERVER['HTTPS'] !== 'off' ) $protocol .= 's';
if( SITE_URL . $current_page !== $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] )
{
	header( 'Location: ' . SITE_URL . $current_page );
	die();
}
require_once( 'functions.php' );
require_once( 'session.php' );
require_once( 'version.php' );
?>
<!DOCTYPE html>
<html lang="en-gb">
	<head>
		<meta charset="utf-8" />
		<base href="<?php echo SITE_URL; ?>" />
		<title><?php if( isset( $title ) and $title ) echo sanitise( $title ); else echo sanitise( DEFAULT_PAGE_TITLE ); ?></title>
		<link href="styles/reset.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="styles/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="styles/default.css" rel="stylesheet" type="text/css" media="screen" />
		<!--[if gt IE 8]><!-->
		<link href="styles/mobile.css" rel="stylesheet" type="text/css" media="screen and (max-width: 800px)" />
		<!--<![endif]-->
		<meta name="author" content="Pete Donnell, Murad Banaji, Anca Marginean, Casian Pantea" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<meta name="description" content="<?php if( isset( $description ) and $description ) echo sanitise( $description ); else echo sanitise( DEFAULT_PAGE_DESCRIPTION ); ?>" />
		<script type="text/javascript" src="scripts/deployJava.js"></script>
		<script type="text/javascript" src="scripts/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="scripts/jquery.fancybox-1.3.4.js"></script>
		<script type="text/javascript" src="scripts/control.js"></script>
		<script type="text/javascript">
			// <![CDATA[
			var siteURL = '<?php echo SITE_URL; ?>';
			var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';
			var test_timeout_limit = <?php echo TEST_TIMEOUT_LIMIT; ?>;
			var timer_id = 0;
			var number_of_reactions = <?php echo $_SESSION['reaction_network']->getNumberOfReactions(); ?>;
			if( !number_of_reactions ) ++number_of_reactions;
			// ]]>
		</script>
	</head>
	<body>
		<div id="container">
			<div id="header">
				<h1><a href="<?php echo SITE_URL; ?>" title="CoNtRol main page">CoNtRol</a></h1>
				<h2><a href="<?php echo SITE_URL; ?>" title="CoNtRol main page">Chemical <span class="non_mobile"><br /></span>Reaction <span class="non_mobile"><br /></span>Network <span class="non_mobile"><br /></span>analysis tool</a></h2>
			</div>
			<div id="content">
				<noscript><p>Sorry, this page requires JavaScript to work correctly.</p></noscript>
				<div id="error_message_holder">
<?php
if( isset( $_SESSION['errors'] ) )
{
	foreach( $_SESSION['errors'] as $error ) echo '					<p>', sanitise( $error ), "</p>\n";
	unset( $_SESSION['errors'] );
}
?>
				</div><!-- error_message_holder -->
