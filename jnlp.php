<?php
/**
 * CoNtRol Java webstart xml
 *
 * Sets parameters to launch the DSR Java application
 *
 * @author     Anca Marginean <anca-dot-marginean-at-cs-dot-utcluj-dot-ro>
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 Technical University of Cluj & University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @link       https://reaction-networks.net/control/download/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   29/04/2014
 */

header('Content-Type: application/x-java-jnlp-file');
header('Content-Disposition: Attachment; filename=dsr.jnlp');
require_once('includes/config.php');
require_once('includes/session.php');
echo '<?xml version="1.0" encoding="UTF-8"?>', PHP_EOL;
?>
<jnlp spec="1.0+" codebase="<?php echo SITE_URL;?>" href="">
	<information>
		<title>DSR Graph</title>
		<vendor>reaction-networks.net</vendor>
	</information>
	<resources>
		<j2se version="1.6+" href="http://java.sun.com/products/autodl/j2se"/>
		<jar href="applets/dsr.jar" main="true"/>
		<jar href="applets/jung-algorithms-2.0.1.jar" main="false"/>
		<jar href="applets/jung-api-2.0.1.jar" main="false"/>
		<jar href="applets/jung-graph-impl-2.0.1.jar" main="false" />
		<jar href="applets/jung-visualization-2.0.1.jar" main="false" />
		<jar href="applets/collections-generic-4.01.jar" main="false" />
	</resources>
	<application-desc name="DSR Graph" main-class="dsr.DsrDraw" width="960" height="700">
		<argument><?php echo htmlspecialchars(str_replace(' ', '', str_replace(PHP_EOL, '.', $_SESSION['reaction_network']->exportReactionNetworkEquations()))); ?></argument>
	</application-desc>
	<update check="background"/>
</jnlp>
