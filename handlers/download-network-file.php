<?php
/**
 * CoNtRol reaction network file export
 *
 * Generates a text file describing the reaction network,
 * suitable for upload to CoNtRol at a later date, or for
 * use with offline CRN analysis tools.
 *
 * @author     Pete Donnell <pete.donnell@port.ac.uk>
 * @copyright  University of Portsmouth 2012
 * @created    08/10/2012
 * @modified   08/10/2012
 */

require_once('../includes/config.php');
require_once('../includes/classes.php');

/*if(!count($_POST))
{
	header('Location: '.SITE_URL);
	die();
}*/

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
}

if(CRNDEBUG)
{
	echo '<pre>', PHP_EOL;
	echo '$_POST:', PHP_EOL;
	print_r($_POST);
	echo PHP_EOL, PHP_EOL, '$reactions:', PHP_EOL;
	print_r($reactions);
	die('</pre>');
}
else
{
	$reactions->exportTextFile();
}
