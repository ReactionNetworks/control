<?php
/**
 * CoNtRol results page
 *
 * This is the results page for CoNtRol
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   15/04/2013
 */

require_once('includes/header.php');

if(!isset($_SESSION['testoutput'])) die('No test results found.');

echo '				<div id="results">
						<h2>Test Results</h2>
						<p> Jump to test:', PHP_EOL;

foreach($_SESSION['tests'] as $testname => $test)
{
	if($test)
	{
		foreach($_SESSION['standardtests'] as &$standardTest)
		if ($testname === $standardTest->getShortName())
		{
			echo '							<a href="', $_SERVER['PHP_SELF'], '#test_', $standardTest->getShortName(), '" title="jump to results for ', sanitise($standardTest->getLongName()), '">', sanitise($standardTest->getLongName()), "</a>\n" ;
		}
	}
}
?>
							<span class="align_right"><a href=".">Back to main</a></span>
						</p>
						<div>
							<h3>Reaction Network Tested:</h3>
							<p>
<?php
echo $_SESSION['reactionNetwork']->exportAsHTML();
echo "							</p>
						</div><!-- reaction_network -->\n";
$currentTest=0;
foreach($_SESSION['testoutput'] as $name => $result)
{
	++$currentTest;
	echo '						<div id="test_', $name, '">', PHP_EOL;
	foreach($_SESSION['standardtests'] as &$standardTest)
	if ($name === $standardTest->getShortName())
	{
		echo '							<h3>Test ', $currentTest, ': ', sanitise($standardTest->getLongName()), "</h3>\n" ;
		echo '<p>', $standardTest->getDescription(), "</p>\n";
	}
	echo '							<h4>Results:</h4>', PHP_EOL, '<pre>', $result, '</pre>', PHP_EOL, '						</div>', PHP_EOL;
}
echo "					</div><!-- results -->\n";

require_once('includes/footer.php');
