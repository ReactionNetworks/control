<?php
/**
 * CoNtRol standard tests
 *
 * List of standard tests and their options
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @author     Murad Banaji <murad-dot-banaji-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    18/01/2013
 * @modified   09/08/2014
 */

$supportsIniFile = true;
$doesNotSupportIniFile = false;

$standardTests = array(
	/*
	// Fake test for debugging purposes
	new NetworkTest
	(
		'dummy',
		'Dummy',
		'Dummy test for development purposes. This test should be disabled in production versions of CoNtRol. Implemented by Pete Donnell.',
		'dummy',
		array( 'human', 'stoichiometry' ),
		$supportsIniFile
	),
	*/
	new NetworkTest
	(
		'dsr',
		'DSR',
		'Checks condition (*) for the DSR graph. Implemented using <a href="https://www.gnu.org/software/octave/">GNU Octave</a> by Casian Pantea, based on <a href="http://projecteuclid.org/euclid.cms/1264434136">M. Banaji and G. Craciun, &ldquo;Graph-theoretic approaches to injectivity and multiple equilibria in systems of interacting elements.&rdquo;</a>',
		'dsr',
		array( 'stoichiometry+V' ),
		$doesNotSupportIniFile
	),

	new NetworkTest
	(
		'analysereacs',
		'General analysis',
		'Runs a number of tests on the system. These are mainly matrix-tests, and relate to multistationarity, stability and persistence. Implemented by Murad Banaji.',
		'analysereacs --html',
		array( 'human' ),
		$doesNotSupportIniFile
	),


	new NetworkTest
	(
		'endotactic',
		'Endotactic',
		'Tests whether the network is endotactic, strongly endotactic, or not endotactic. Implemented using <a href="https://www.gnu.org/software/glpk/">GLPK</a> and <a href="http://scip.zib.de/">SCIP</a> by Matthew Johnston, Pete Donnell and Casian Pantea, based on <a href="http://arxiv.org/abs/1412.4662">&ldquo;A computational approach to persistence, permanence, and endotacticity of biochemical reaction systems&rdquo;</a>.',
		'endotactic',
		array( 'GLPK' ),
		$doesNotSupportIniFile
	),


	new NetworkTest
	(
		'isomorphic',
		'Isomorphism lookup',
		'Tests whether the network is isomorphic to a known network with interesting properties. Implemented in <a href="http://www.sagemath.org/">Sage</a> by Pete Donnell, Casian Pantea and Murad Banaji.',
		'isomorphic',
		array( 'human' ),
		$doesNotSupportIniFile
	),


	new NetworkTest
	(
		'calc-jacobian',
		'Jacobian matrix',
		'Pseudo-test: this test calculates the Jacobian matrix and its second additive compound symbolically, but does not perform any analysis on either. Can be useful in spotting CRNs that are cooperative, competitive, otherwise monotone (in forward or backwards time) with respect to an orthant ordering, contractive and/or nonexpansive, or in ruling out Hopf bifurcations via the DSR<sup>[2]</sup> condition. Implemented in <a href="http://maxima.sourceforge.net/">Maxima</a> by Pete Donnell.',
		'calc-jacobian',
		array( 'stoichiometry+V' ),
		$doesNotSupportIniFile
	),

	/*
	// Add new tests here, in the following format:
	new NetworkTest
	(
		'shortname' (no spaces or unusual characters allowed),
		'Human Readable Name',
		'Full description', // (may include HTML, but make sure it's valid!)
		'executable filename', // (i.e. the name of the binary or shell script in bin/ that belongs to this test)
		array( 'human' ), // (supported file formats)
		$supportsIniFile // (if the binary supports --inifile option)
	),
	*/
);
