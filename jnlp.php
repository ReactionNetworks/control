<?php
/**
 * CoNtRol Java webstart xml
 *
 * Sets parameters to launch the DSR Java application
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   24/04/2013
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
        <jar href="applets/dsr.1.3run.jar" main="true"/> 
<?php/*        <jar href="applets/jung-algorithms-2.0.1.jar" main="false"/>
        <jar href="applets/jung-api-2.0.1.jar" main="false"/>
        <jar href="applets/jung-graph-impl-2.0.1.jar" main="false" />
        <jar href="applets/jung-visualization-2.0.1.jar" main="false" /> */?>
    </resources>
    <applet-desc
         name="DSR Graph" main-class="DsrDraw.main"
         width="1000" height="700">
         <param name="content"
                 value="a-->b"/>
    </applet-desc>
  <update check="background"/>
</jnlp>