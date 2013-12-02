<?php
/**
 * CoNtRol HTML footer
 *
 * Standard footer included on all pages within CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-13
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   02/12/2013
 */
?>
			</div><!-- content -->
			<div id="footer">
				<div id="credits_hider">
					<div id="feedback_holder">
						<h2>Feedback</h2>
						<p>
							Please send bug reports, feature requests and other comments to <em><?php echo str_replace('@', ' at ', str_replace('.', ' dot ', ADMIN_EMAIL)); ?></em>. When reporting bugs, please describe the problem in detail, and provide screenshots if possible.
						</p>
					</div>
					<div id="privacy_holder">
						<h2>Privacy Statement</h2>
						<p>
							CoNtRol requires the use of temporary session cookies to provide functionality. It does not store any identifiable user information when used in interactive mode (all data displayed in browser). Some user data is stored when using the batch processing mode. Please do not use batch processing if you are concerned about this.
						</p>
					</div>
					<div id="credits_holder">
						<h2>Credits, Acknowledgments &amp; Licensing</h2>
						<h3>Credits</h3>
						<ul>
							<li>Web programming: Pete Donnell &amp; Kitson Consulting</li>
							<li>DSR graph applet: Anca Marginean &amp; Casian Pantea</li>
							<li>DSR test: Casian Pantea</li>
							<li>General analysis tests: Murad Banaji</li>
							<li>Graphic design: Olaf Mayer &amp; Kitson Consulting</li>
						</ul>
						<h3>Acknowledgments</h3>
						<ul>
							<li>This work was supported by grants F/07 058/BU (Leverhulme, to C.P. and M.B.) and EP/J008826/1 (EPSRC, to P.D.) and also by <a href="http://kitson-consulting.co.uk/" title="Science and IT Consultants">Kitson Consulting Limited</a>.</li>
							<li>PD wishes to acknowledge Mark Donnell, Polly Hember, Matt Kingston, Olaf Mayer and Casian Pantea for their assistance with typing.</li>
							<!--li>PD wishes to acknowledge Barnaby Menage, Oliver Butterfield, Andrew Burbanks, James Burridge, Elisenda Feliu, Kirk Jackson, Matt Kingston, Joe Parker, Kate Oliver and Nick Hatter for testing and feature requests.</li-->
						</ul>
						<h3>Licensing &amp; Copyright</h3>
						<ul>
							<li><a href="https://www.java.com/js/deployJava.txt"><code>deployJava.js</code></a> is &copy; 2006, 2011 Oracle. Used and distributed under Oracle's licence terms.</li>
							<li><a href="http://jquery.com/">jQuery v1.8.3</a> is &copy; 2012 the jQuery Foundation et al. Used and distributed under the <a href="http://opensource.org/licenses/MIT">MIT licence</a>.</li>
							<li><a href="http://fancybox.net/">Fancybox 1.3.4</a> is &copy; 2008&ndash;2010 Janis Skarnelis. Used and distributed under the <a href="http://www.gnu.org/licenses/gpl.html">GPL v3 or later</a>.</li>
							<li><a href="http://jung.sourceforge.net/site/jung-graph-impl/project-summary.html">JUNG</a> is &copy; the JUNG development team. Used and distributed under the <a href="http://jung.sourceforge.net/site/jung-graph-impl/license.html">BSD Licence</a>.</li>
							<li><a href="https://github.com/megamattron/collections-generic">Collections</a> is &copy; Matt Hall. Used and distributed under the <a href="https://github.com/megamattron/collections-generic/blob/master/LICENSE.txt">Apache Licence</a>.</li>
							<li>Icons from the GNOME, KDE and XFCE desktop icon sets.</li>
							<li>All other code and content is &copy; 2012&ndash;<?php echo date('Y'); ?> the authors and released under the <a href="http://www.gnu.org/licenses/gpl.html">GPL v3 or later</a>.</li>
						</ul>
					</div><!-- credits_holder -->
				</div><!-- credits_hider -->
				<p id="credits">
					<a href="#feedback_holder" class="fancybox">Feedback</a>
					&bull;
					<a href="#privacy_holder" class="fancybox">Privacy</a>
					&bull;
					<a href="http://reaction-networks.net/wiki/CoNtRol" title="View documentation for CoNtRol in a new tab">Documentation</a>
					&bull;
					<a href="#credits_holder" class="fancybox">Credits</a>
				</p><!-- credits -->
			</div><!-- footer -->
		</div><!-- container -->
	</body>
</html>
