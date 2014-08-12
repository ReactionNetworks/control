<?php
/**
 * CoNtRol HTML footer
 *
 * Standard footer included on all pages within CoNtRol
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   12/08/2014
 */
?>
			</div><!-- content -->
			<div id="footer">
				<div id="credits_hider">
					<div id="feedback_holder">
						<h2>Feedback</h2>
						<p>
							Please send bug reports, feature requests and other comments to <strong><em><?php echo hide_email_address( ADMIN_EMAIL ); ?></em></strong>. When reporting bugs, please describe the problem in as much detail as possible, and provide screenshots where relevant.
						</p>
<?php
if( ACCEPT_KNOWN_CRN_SUBMISSIONS ):
?>
						<p>
							To submit a CRN with known interesting properties to the CoNtRol database for the isomorphism test, please use the <a href="submit-known-crn.php">CRN submission form</a>.
						</p>
<?php
endif;
?>
					</div>
					<div id="privacy_holder">
						<h2>Privacy Statement</h2>
						<p>
							CoNtRol requires the use of temporary session cookies to provide functionality. It does not store any identifiable user information when used in interactive mode (all data displayed in browser).
						</p>
						<p>
							Some user data is permanently stored when using the batch processing mode, or when <a href="submit-known-crn.php">submitting new CRNs for the isomorphism test</a>. Data is stored solely for the purposes of providing functionality, security, spam prevention, and attributing submissions. Please do not use batch processing or submit new CRN data if you are concerned about this.
						</p>
					</div>
					<div id="credits_holder">
						<h2>Credits, Acknowledgements &amp; Licensing</h2>
						<h3>Credits</h3>
						<ul>
							<li>DSR graph Java application: Anca Marginean &amp; Casian Pantea</li>
							<li>DSR test: Casian Pantea</li>
							<li>General analysis tests: Murad Banaji</li>
							<li>Endotacticity test: Matthew Johnston, Pete Donnell &amp; Casian Pantea</li>
							<li>Isomorphism test: Pete Donnell, Casian Pantea &amp; Murad Banaji</li>
							<li>Graphic design: Pete Donnell &amp; Kitson Consulting</li>
							<li>Web interface architecture &amp; programming: Pete Donnell &amp; Kitson Consulting</li>
						</ul>
						<h3>Acknowledgements</h3>
						<ul>
							<li>This work was supported by grants F/07 058/BU (Leverhulme, to C.P. and M.B.) and EP/J008826/1 (EPSRC, to P.D. and M.B.) and also by <a href="http://kitson-consulting.co.uk/" title="Science and IT Consultants">Kitson Consulting Limited</a>.</li>
							<li>P.D. wishes to acknowledge Mark Donnell, Polly Hember, Matt Kingston, Olaf Mayer and Casian Pantea for their assistance with typing.</li>
						</ul>
						<h3>Licensing &amp; Copyright</h3>
						<ul>
							<li><a href="https://github.com/megamattron/collections-generic">Collections</a> is &copy; Matt Hall. Used and distributed under the <a href="https://github.com/megamattron/collections-generic/blob/master/LICENSE.txt">Apache Licence</a>.</li>
							<li><a href="https://www.java.com/js/deployJava.txt"><code>deployJava.js</code></a> is &copy; 2006, 2012 Oracle. Used and distributed under Oracle's licence terms.</li>
							<li><a href="http://fancybox.net/">Fancybox 1.3.4</a> is &copy; 2008&ndash;2010 Janis Skarnelis. Used and distributed under the <a href="http://www.gnu.org/licenses/gpl.html">GPL v3 or later</a>.</li>
							<li><a href="http://jquery.com/">jQuery v1.8.3</a> is &copy; 2012 the jQuery Foundation et al. Used and distributed under the <a href="http://opensource.org/licenses/MIT">MIT Licence</a>.</li>
							<li><a href="http://jung.sourceforge.net/site/jung-graph-impl/project-summary.html">JUNG</a> is &copy; the JUNG development team. Used and distributed under the <a href="http://jung.sourceforge.net/site/jung-graph-impl/license.html">BSD Licence</a>.</li>
							<li><a href="https://www.theleagueofmoveabletype.com/orbitron">Orbitron</a> is &copy; 2009 <a href="http://pixelspread.com/">Matt McInerney</a> and released under the <a href="http://scripts.sil.org/OFL" >Open Font Licence</a>.</li>
							<li>Icons from the GNOME, KDE and XFCE desktop icon sets.</li>
							<li>All other code and content is &copy; 2012&ndash;<?php echo date('Y'); ?> the authors and released under the <a href="http://www.gnu.org/licenses/gpl.html">GPL v3 or later</a>.</li>
						</ul>
					</div><!-- credits_holder -->
				</div><!-- credits_hider -->
				<p id="credits">
					<a href="<?php echo $_SERVER['REQUEST_URI']; ?>#" title="Start of page">&#x21e7;</a>
					<a href="#feedback_holder" class="fancybox">Feedback</a>
					<span class="non_mobile">&bull;</span>
					<a href="submit-known-crn.php">Submit a CRN</a>
					<span class="non_mobile">&bull;</span>
					<a href="#privacy_holder" class="fancybox">Privacy</a>
					<span class="non_mobile">&bull;</span>
					<a href="http://reaction-networks.net/wiki/CoNtRol" title="View documentation for CoNtRol in a new tab">Documentation</a>
					<span class="non_mobile">&bull;</span>
					<a href="#credits_holder" class="fancybox">Credits</a>
					<span class="non_mobile">&bull;</span>
					<span id="version"><?php echo 'Version ', CONTROL_VERSION; ?></span>
				</p><!-- credits -->
			</div><!-- footer -->
		</div><!-- container -->
