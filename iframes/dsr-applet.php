<?php
/**
 * CoNtRol DSR applet iFrame
 *
 * This iFrame holds the DSR graph for CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    04/10/2012
 * @modified   04/10/2012
 */

$title = 'Reaction network DSR graph';
require_once('../includes/header.php');
?>
				<div id="dsr_graph_applet_holder">
					<img src="images/dsr-applet.jpg" alt="Reaction network DSR graph" title="Sample reaction network DSR graph. This is just a static image, to demonstrate how the Java applet will appear." width="943" height="616" />
				</div><!-- dsr_graph_applet_holder -->
<?php
require_once('../includes/footer.php');
