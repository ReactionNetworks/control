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
 * @modified   02/05/2013
 */
require_once('config.php');
require_once('functions.php');
require_once('session.php');
require_once('version.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
		<base href="<?php echo SITE_URL; ?>" />
		<title><?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?></title>
		<link href="styles/reset.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="styles/jquery.fancybox-1.3.4.css" rel="stylesheet" type="text/css" media="screen" />
		<link href="styles/default.css" rel="stylesheet" type="text/css" media="screen" />
		<!--[if IE gt 8]>--><link href="styles/mobile.css" rel="stylesheet" type="text/css" media="screen and (max-width: 800px)" /><!--<![endif]-->
		<meta name="author" content="Murad Banaji, Pete Donnell, Anca Marginean, Casian Pantea" />
		<meta name="date" content="2013-04-24T15:59:06+0100" />
		<meta name="language" content="en" />
		<meta name="title" content="<?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?>" />
		<meta name="description" content="<?php if(isset($description) and $description) echo sanitise($description); else echo sanitise(DEFAULT_PAGE_DESCRIPTION); ?>" />
		<!--[if gt IE 8]><!-->
		<!--<![endif]-->
		<script type="text/javascript" src="scripts/deployJava.js"></script>
		<script type="text/javascript" src="scripts/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="scripts/jquery.fancybox-1.3.4.js"></script>
		<script type="text/javascript" src="scripts/control.js"></script>
		<script type="text/javascript">
			// <![CDATA[
			var siteURL = '<?php echo SITE_URL; ?>';
			var csrf_token = '<?php echo $_SESSION['csrf_token']; ?>';
			// ]]>
		</script>
	</head>
	<body>
		<div id="container">
			<div id="header">
				<h1 title="<?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?>"><?php if(isset($title) and $title) echo sanitise($title); else echo sanitise(DEFAULT_PAGE_TITLE); ?></h1>
				<p id="version">
<?php
echo '					Version ', CONTROL_VERSION;
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
