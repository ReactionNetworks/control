<?php
/**
 * CoNtRol HTML header
 *
 * Standard header included on all pages within CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-13
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   02/12/2013
 */

header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: '.gmdate( 'D, d M Y H:i:s' ).' GMT');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');

require_once('config.php');
// Force redirect if not being accessed on the correct URL
$currentURL = explode('/', $_SERVER['REQUEST_URI']);
$current_page = end($currentURL);
if($current_page === 'index.php') $current_page = '';
$protocol = 'http';
if(isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] !== off) $protocol .= 's';
if(SITE_URL.$current_page !== $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'])
{
	header('Location: '.SITE_URL.$current_page);
	die();
}
require_once('functions.php');
require_once('session.php');
require_once('version.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<base href="<?php echo SITE_URL; ?>" />
		<title><?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?></title>
		<link href="styles/reset.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="styles/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="styles/default.css" rel="stylesheet" type="text/css" media="screen" />
		<!--[if gt IE 8]><!-->
		<link href="styles/mobile.css" rel="stylesheet" type="text/css" media="screen and (max-width: 800px)" />
		<!--<![endif]-->
		<meta name="author" content="Matt Kingston" />
		<meta name="date" content="2013-12-02T19:05:14+0000" />
		<meta name="language" content="en" />
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
		<meta name="title" content="<?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?>" />
		<meta name="description" content="<?php if(isset($description) and $description) echo sanitise($description); else echo sanitise(DEFAULT_PAGE_DESCRIPTION); ?>" />
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
			if (!number_of_reactions) ++number_of_reactions;
			// ]]>
		</script>
	</head>
	<body>
		<div id="container">
			<div id="header">
				<!--h1 title="<?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?>"><?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?></h1!-->
				<h1>CoNtRol</h1>
				<h2>Chemical Reaction Network<br />analysis tool</h2>
				<p id="version">
<?php
echo '					Version ', CONTROL_VERSION, PHP_EOL;
?>
				</p><!-- version -->
			</div>
			<div id="content">
				<noscript><p>Sorry, this page requires JavaScript to work correctly.</p></noscript>
				<div id="error_message_holder">
<?php
if(isset($_SESSION['errors']))
{
	foreach($_SESSION['errors'] as $error) echo '					<p>', sanitise($error), "</p>\n";
	unset($_SESSION['errors']);
}
?>
				</div><!-- error_message_holder -->
