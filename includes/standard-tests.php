<?php
/**
 * CoNtRol standard tests
 *
 * List of standard tests and their options
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    18/01/2013
 * @modified   28/05/2013
 */

$standardTests = array(
/*
 Fake test for debugging purposes
	new NetworkTest
	(
		'dummy',
		'Dummy',
		'Dummy test for development purposes. This test will be removed in a later version of CoNtRol. Implemented by Pete Donnell.',
		'dummy',
		array('human', 'stoichiometry'),
		true,
		true
	),
*/
	new NetworkTest
	(
		'dsrtest',
		'DSR test',
		'Checks condition (*) for the DSR graph. Implemented by Casian Pantea, based on <a href="http://projecteuclid.org/euclid.cms/1264434136">M. Banaji and G. Craciun, Graph-theoretic approaches to injectivity and multiple equilibria in systems of interacting elements</a>.',
		'dsrTest.sh',
		array('stoichiometry+V'),
		false,
		true
	),

	new NetworkTest
	(
		'ssdonly',
		'SSD test',
		'Runs a number of matrix-related tests on the system, for example if the stoichiometric matrix is strongly sign determined (SSD). Implemented by Murad Banaji, based on <a href="http://discovery.ucl.ac.uk/149053/" title="Publication details of research paper">P matrix properties, injectivity and stability in chemical reaction systems</a>.',
		'test --html',
		array('human')
	),

/*
 Add new tests here, in the following format:
	new NetworkTest
	(
		'shortname' (no spaces or unusual characters allowed),
		'Human Readable Name',
		'Full description' (may include HTML, but make sure it's valid!),
		'executable filename' (i.e. the name of the binary or shell script in bin/ that belongs to this test),
		array('human') (supported file formats, currently 'human' or 'stoichiometry'),
		true (if the binary supports --mass-action-only option),
		true (if the binary supports general kinetics)
	),
 */
);
