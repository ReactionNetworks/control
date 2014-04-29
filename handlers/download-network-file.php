<?php
/**
 * CoNtRol reaction network file export
 *
 * Generates a text file describing the reaction network,
 * suitable for upload to CoNtRol at a later date, or for
 * use with offline CRN analysis tools.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    08/10/2012
 * @modified   29/04/2014
 */

/**
 * Standard include
 */
require_once('../includes/config.php');

/**
 * Standard include
 */
require_once('../includes/classes.php');

$reactions = new ReactionNetwork();

$numberOfReactions = count($_POST['reaction_direction']);

for($i = 0; $i < $numberOfReactions; ++$i)
{
	switch($_POST['reaction_direction'][$i])
	{
		case 'both':
			$reversible = true;
			$leftHandSide = $_POST['reaction_left_hand_side'][$i];
			$rightHandSide = $_POST['reaction_right_hand_side'][$i];
			break;

		case 'right':
			$reversible = false;
			$leftHandSide = $_POST['reaction_left_hand_side'][$i];
			$rightHandSide = $_POST['reaction_right_hand_side'][$i];
			break;

		case 'left':
			$reversible = false;
			$leftHandSide = $_POST['reaction_right_hand_side'][$i];
			$rightHandSide = $_POST['reaction_left_hand_side'][$i];
			break;

		default:
			// Throw exception?
			break;
	}

	$reactions->addReaction(new Reaction($leftHandSide, $rightHandSide, $reversible));
	$_SESSION['reaction_network'] = $reactions;
}

if(CRNDEBUG)
{
	echo '<pre>', PHP_EOL, '$_POST:', PHP_EOL;
	print_r($_POST);
	echo PHP_EOL, PHP_EOL, '$reactions:', PHP_EOL;
	print_r($reactions);
	echo PHP_EOL, PHP_EOL, '$sourceStoichiometryMatrix:', PHP_EOL;
	print_r($reactions->generateSourceStoichiometryMatrix());
	echo PHP_EOL, PHP_EOL, '$targetStoichiometryMatrix:', PHP_EOL;
	print_r($reactions->generateTargetStoichiometryMatrix());
	echo PHP_EOL, PHP_EOL, '$stoichiometryMatrix:', PHP_EOL;
	print_r($reactions->generateStoichiometryMatrix());
	die('</pre>');
}
else
{
	$reactions->exportTextFile(CLIENT_LINE_ENDING);
}
