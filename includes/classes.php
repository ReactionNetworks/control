<?php
/**
 * CoNtRol standard classes
 *
 * Assorted classes used within CoNtRol.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   30/05/2013
 */

class Reaction
{
	private $leftHandSide = array();
	private $rightHandSide = array();
	private $reversible = true;

	/*
	 * Constructor
	 *
	 * @param  mixed  $leftHandSide   The left hand side of the reaction, either a string to parse, or an array of pre-parsed strings
	 * @param  mixed  $rightHandSide  The right hand side of the reaction, either a string to parse, or an array of pre-parsed strings
	 * @param  bool   $reversible     TRUE if the reaction is reversible, false otherwise
	 */
	function __construct($leftHandSide, $rightHandSide, $reversible)
	{
		switch(gettype($leftHandSide))
		{
			case 'array':
				$this->leftHandSide = $leftHandSide;
				break;
			case 'string':
				$this->leftHandSide = Reaction::parseReactants($leftHandSide);
				break;
			default:
				// Throw an exception?
				break;
		}

		switch(gettype($rightHandSide))
		{
			case 'array':
				$this->rightHandSide = $rightHandSide;
				break;
			case 'string':
				$this->rightHandSide = Reaction::parseReactants($rightHandSide);
				break;
			default:
				// Throw an exception?
				break;
		}

		$this->reversible = $reversible;
	}



	/*
	 * Parse a string describing one side of a reaction
	 *
	 * @param   string  $reactionString  The string describing the reaction.
	 * @return  mixed   $reactants       If there is no error, returns an array of strings, each of which is a reactant. Otherwise returns FALSE.
	 */
	private static function parseReactants($reactantString)
	{
		// Remove preceding/trailing whitespace
		$reactantString = trim($reactantString);

		// Check there are no invalid characters
		if((strpos($reactantString, '>') !== false) or (strpos($reactantString, '-') !== false) or
		   (strpos($reactantString, '<') !== false) or (strpos($reactantString, '=') !== false)) return false;
		else
		{
			$temp = '';
			$reactantStringLength = strlen($reactantString);
			// Remove whitespace
			for($i = 0; $i < $reactantStringLength; ++$i)
			{
				if($reactantString{$i} !== ' ') $temp .= $reactantString{$i};
			}
			$reactants = explode('+', $temp);
		}
		$numberOfReactants = count($reactants);
		$reactantStoichiometries = array();
		for($i = 0; $i < $numberOfReactants; ++$i)
		{
			if(is_numeric($reactants[$i])) return false;
			else if($reactants[$i] and !is_numeric($reactants[$i][0]))
			{
				$reactant_found = false;
				foreach($reactantStoichiometries as $reactant => $stoichiometry)
				{
					if($reactants[$i] == $reactant) $reactant_found = true;
				}
				if($reactant_found) $reactantStoichiometries[$reactants[$i]] += 1;
				else $reactantStoichiometries[$reactants[$i]] = 1;
			}
			else
			{
				$reactantLength = strlen($reactants[$i]);
				$characterPos = 0;
				for($j = 0; $j < $reactantLength; ++$j)
				{
					if(!is_numeric($reactants[$i][$j])) $characterPos = $j;
					if($characterPos) break;
				}
				$reactant_found = false;
				foreach($reactantStoichiometries as $reactant => $stoichiometry)
				{
					if(substr($reactants[$i], $characterPos) == $reactant) $reactant_found = true;
				}
				if($reactant_found) $reactantStoichiometries[substr($reactants[$i], $characterPos)] += substr($reactants[$i], 0, $characterPos);
				else $reactantStoichiometries[substr($reactants[$i], $characterPos)] = substr($reactants[$i], 0, $characterPos);
			}
		}
		return $reactantStoichiometries;
	}


	/*
	 * Parse a string describing both sides of a reaction
	 *
	 * @param   string  $reactionString  The string describing the reaction.
	 * @return  mixed   $reaction        If there is no error, returns a reaction object. Otherwise returns FALSE.
	 */
	public static function parseReaction($reactionString)
	{
		$temp = '';
		$reversible = true;
		$reactionStringLength = strlen($reactionString);
		// Remove whitespace
		for($i = 0; $i < $reactionStringLength; ++$i)
		{
			if($reactionString{$i} !== ' ' and $reactionString{$i} !== '-' and $reactionString{$i} !== '=') $temp .= $reactionString{$i};
		}

		$leftArrowPos = strpos($temp, '<');
		$rightArrowPos = strpos($temp, '>');

		if ($leftArrowPos === false and $rightArrowPos === false) return false;
		else
		{
			if ($leftArrowPos !== false and $rightArrowPos !== false)
			{
				if ($leftArrowPos === $rightArrowPos-1)
				{
					$lhs = Reaction::parseReactants(substr($temp, 0, $leftArrowPos));
					$rhs = Reaction::parseReactants(substr($temp, $rightArrowPos + 1));
				}
				else return false;
			}
			else if ($leftArrowPos!==false)
			{
				$rhs = Reaction::parseReactants(substr($temp, 0, $leftArrowPos));
				$lhs = Reaction::parseReactants(substr($temp, $leftArrowPos + 1));
				$reversible = false;
			}
			else
			{
				$lhs = Reaction::parseReactants(substr($temp, 0, $rightArrowPos));
				$rhs = Reaction::parseReactants(substr($temp, $rightArrowPos + 1));
				$reversible = false;
			}
		 }
	 	return new Reaction($lhs,$rhs,$reversible);
	}

	/*
	 * Export Reaction as HTML
	 *
	 * @return  string  $text  HTML markup describing the reaction.
	 */
	public function exportAsHTML()
	{
		$text = '';
		$text .= $this->exportLHSAsText();
		if($this->reversible) $text .= ' &#x21cc; ';
		else $text .= ' &rarr; ';
		$text .= $this->exportRHSAsText();
		$text .= '<br />'.CLIENT_LINE_ENDING;
		return $text;
	}

	/*
	 * Export Reaction as plain text
	 *
	 * @return  string  $text  Text describing the reaction.
	 */
	public function exportAsText($line_ending = PHP_EOL)
	{
		$text = '';
		$text.=$this->exportLHSAsText();
		if($this->reversible) $text .= ' <--> ';
		else $text .= ' --> ';
		$text.=$this->exportRHSAsText();
		$text .= $line_ending;
		$text = str_replace('&empty;', '0', $text);
		return $text;
	}

	/*
	 * Export the left hand side of the reaction as plain text
	 *
	 * @return  string  $text  Text describing the reaction's LHS.
	 */
	public function exportLHSAsText()
	{
		$text = '';

		foreach($this->leftHandSide as $reactant => $stoichiometry)
		{
			if(strlen($text)) $text .= ' + ';
			if($stoichiometry == 1) $text .= $reactant;
			else if($stoichiometry) $text = $text.$stoichiometry.$reactant;
		}
		if(!$text) $text = '&empty;';

		return $text;
	}

	/*
	 * Export the right hand side of the reaction as plain text
	 *
	 * @return  string  $text  Text describing the reaction's RHS.
	 */
	public function exportRHSAsText()
	{
		$text = '';

		foreach($this->rightHandSide as $reactant => $stoichiometry)
		{
			if (strlen($text)) $text .= ' + ';
			if ($stoichiometry == 1) $text .= $reactant;
			else if($stoichiometry) $text = $text.$stoichiometry.$reactant;
		}
		if(!$text) $text = '&empty;';

		return $text;
	}

	/*
	 * Check whether the Reaction is reversible
	 *
	 * @return  bool  TRUE if the reaction is reversible, FALSE otherwise.
	 */
	public function isReversible()
	{
		return $this->reversible;
	}

	/*
	 * Get the reactants as an array
	 *
	 * @return  array  $reactants  An associative array with each reactant name/label
	 *                             as a key, and its stoichiometry as the value.

TO DO: this function isn't correct for reactions where a reactant appears on both sides

	 */
	public function getReactants()
	{
		$reactants = array();

		if($this->leftHandSide)
		{
			foreach($this->leftHandSide as $reactant => $stoichiometry)
			{
				$reactants[] = $reactant;
			}
		}
		else return false;

		if($this->rightHandSide)
		{
			foreach($this->rightHandSide as $reactant => $stoichiometry)
			{
				$reactants[] = $reactant;
			}
		}
		else return false;
		return $reactants;
	}

	public function getLeftHandSide()
	{
		return $this->leftHandSide;
	}

	public function getRightHandSide()
	{
		return $this->rightHandSide;
	}
}

class ReactionNetwork
{
	private $reactions = array();

	/*
	 * Constructor
	 *
	 * @param  array  $reactions   An array of Reactions
	 */
	function __construct($reactions = array())
	{
		$this->reactions = $reactions;
	}

	/*
	 * Add a reaction
	 *
	 * @param  Reaction  $reaction   The Reaction to add
	 */
	public function addReaction($reaction)
	{
		if($reaction)
		{
			$this->reactions[] = $reaction;
			return true;
		}
		else return false;
	}

	/*
	 * Export function for reaction network descriptor
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical equations
	 */
	public function exportReactionNetworkEquations($line_ending = PHP_EOL, $LaTeX = false)
	{
		$equations = '';
		foreach($this->reactions as $reaction) $equations .= $reaction->exportAsText($line_ending);
		return $equations;
	}

	/*
	 * Export function for reaction network net stoichiometry and V matrix descriptor
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical matrices
	 */
	public function exportStoichiometryAndVMatrix($LaTeX = false)
	{
		$equations = 'S MATRIX'.PHP_EOL;
		$equations .= $this->exportStoichiometryMatrix();
		$equations .= PHP_EOL.'V MATRIX'.PHP_EOL;
		$equations .= $this->exportVMatrix();
		return $equations;
	}

	/*
	 * Export function for reaction network source and target stoichiometry and V matrix descriptor
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical matrices
	 */
	public function exportSourceAndTargetStoichiometryAndVMatrix($LaTeX = false)
	{
		$equations = 'S MATRIX'.PHP_EOL;
		$equations .= $this->exportSourceStoichiometryMatrix();
		$equations .= PHP_EOL.PHP_EOL.'T MATRIX'.PHP_EOL;
		$equations .= $this->exportTargetStoichiometryMatrix();
		$equations .= PHP_EOL.PHP_EOL.'V MATRIX'.PHP_EOL;
		$equations .= $this->exportVMatrix();
/* TO DO: add REVERSIBLE section to output
		$equations .= PHP_EOL.PHP_EOL.'REVERSIBLE'.PHP_EOL;
		foreach($this->reactions)*/
		return $equations;
	}

	/*
	 * HTML export function for reaction network descriptor
	 *
	 * @return  string  $equations  HTML version of reaction network chemical equations
	 */
	public function exportAsHTML()
	{
		$equations = '';
		$numberOfReactions = count($this->reactions);
		for($i = 0; $i < $numberOfReactions; ++$i) $equations .= $this->reactions[$i]->exportAsHTML();
		return $equations;
	}

	/*
	 * Export function for reaction network net stoichiometry
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical matrix
	 */
	public function exportStoichiometryMatrix($LaTeX = false)
	{
		$equations = '';
		$stoichiometryMatrix = $this->generateStoichiometryMatrix();
		if($LaTeX)
		{
			$equations .= '\\left(\\begin{array}{';
			for($i = 0; $i < count($stoichiometryMatrix[0]); ++$i) $equations .= 'r';
			$equations .= "}\n";
		}
		for($i = 0; $i < count($stoichiometryMatrix); ++$i)
		{
			$row = $stoichiometryMatrix[$i];
			$equations .= $row[0];
			for($j = 1; $j < count($row); ++$j)
			{
				$equations .= ' ';
				if($LaTeX) $equations .= '& ';
				$equations .= $row[$j];
			}
			if($LaTeX and ($i < (count($stoichiometryMatrix) - 1))) $equations .= ' \\\\';
			$equations .= PHP_EOL;
		}
		if($LaTeX) $equations .= "\\end{array}\\right)\n";
		return $equations;
	}

	public function exportSourceStoichiometryMatrix()
	{
		$equations = '';
		$stoichiometryMatrix = $this->generateSourceStoichiometryMatrix();
		foreach($stoichiometryMatrix as $row)
		{
			$equations .= $row[0];
			for($i = 1; $i < count($row); ++$i) $equations .= ' '.$row[$i];
			$equations .= PHP_EOL;
		}
		return $equations;
	}

	public function exportTargetStoichiometryMatrix()
	{
		$equations = '';
		$stoichiometryMatrix = $this->generateTargetStoichiometryMatrix();
		foreach($stoichiometryMatrix as $row)
		{
			$equations .= $row[0];
			for($i = 1; $i < count($row); ++$i) $equations .= ' '.$row[$i];
			$equations .= PHP_EOL;
		}
		return $equations;
	}

	public function exportVMatrix($LaTeX = false)
	{
		$equations = '';
		$VMatrix = $this->generateReactionRateJacobianMatrix();
		if($LaTeX)
		{
			$equations .= '\\left(\\begin{array}{';
			for($i = 0; $i < count($VMatrix[0]); ++$i) $equations .= 'r';
			$equations .= "}\n";
		}
		for($i = 0; $i < count($VMatrix); ++$i)
		{
			$row = $VMatrix[$i];
			if($LaTeX)
			{
				switch($row[0])
				{
					case 0:
						$equations .= '0';
						break;
					case -1:
						$equations .= '-';
						break;
					case 1:
						$equations .= '+';
						break;
					case 2:
						$equations .= '\\pm';
						break;
					default:
						$equations .= '?';
						break;
				}
			}
			else $equations .= $row[0];
			for($j = 1; $j < count($row); ++$j)
			{
				$equations .= ' ';
				if($LaTeX)
				{
					$equations .= '& ';
					switch($row[$j])
					{
						case 0:
							$equations .= '0';
							break;
						case -1:
							$equations .= '-';
							break;
						case 1:
							$equations .= '+';
							break;
						case 2:
							$equations .= '\\pm';
							break;
						default:
							$equations .= '?';
							break;
					}
				}
				else $equations .= $row[$j];
			}
			if($LaTeX and ($i < (count($VMatrix) - 1))) $equations .= ' \\\\';
			$equations .= PHP_EOL;
		}
		if($LaTeX) $equations .= "\\end{array}\\right)\n";
		return $equations;
	}

	public function exportTextFile($line_ending = PHP_EOL)
	{
		// Send headers for download
		header('Content-Type: text/plain');
		header('Content-Disposition: Attachment; filename=crn.txt');
		header('Pragma: no-cache');
		echo $this->exportReactionNetworkEquations($line_ending);
	}

	public function generateFieldsetHTML()
	{
		if(count($this->reactions))
		{
			foreach($this->reactions as $reaction)
			{
				echo '						<fieldset class="reaction_input_row">
							<input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" value="', str_replace('&empty;', '', $reaction->exportLHSAsText()), '" />
							<select class="reaction_direction" name="reaction_direction[]">
								<option value="left">&larr;</option>
								<option value="both"';
							if($reaction->isReversible()) echo ' selected="selected"';
							echo '>&#x21cc;</option>
								<option value="right"';
							if(!$reaction->isReversible()) echo ' selected="selected"';
							echo '>&rarr;</option>
							</select>
							<input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" value="', str_replace('&empty;', '', $reaction->exportRHSAsText()), '" />
						</fieldset><!-- reaction_input_row -->', PHP_EOL;
			}
		}
		else echo '<fieldset class="reaction_input_row">
						<input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" />
						<select class="reaction_direction" name="reaction_direction[]">
							<option value="left">&larr;</option>
							<option value="both" selected="selected">&#x21cc;</option>
							<option value="right">&rarr;</option>
						</select>
						<input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" />
					</fieldset><!-- reaction_input_row -->', PHP_EOL;
	}

	private function generateReactantList()
	{
		$reactantList=array();
		foreach($this->reactions as $reaction)
		{
			$reactants = $reaction->getReactants();
			if($reactants) foreach($reactants as $reactant) if (!in_array($reactant,$reactantList)) $reactantList[]=$reactant;
		}
		return $reactantList;
	}

	public function generateSourceStoichiometryMatrix()
	{
		$sourceStoichiometryMatrix=array();
		$reactantList=$this->generateReactantList();
		$numberOfReactants=count($reactantList);
		for($i = 0; $i < $numberOfReactants; ++$i)
		{
			$sourceStoichiometryMatrix[]=array();

			foreach($this->reactions as $reaction)
			{
				$matrixEntry = 0;
				foreach($reaction->getLeftHandSide() as $reactant => $stoichiometry)
				{
					if ($reactantList[$i] === $reactant) $matrixEntry = $stoichiometry;
				}
				$sourceStoichiometryMatrix[$i][] = $matrixEntry;
			}
		}
		return $sourceStoichiometryMatrix;
	}

	public function generateTargetStoichiometryMatrix()
	{
		$targetStoichiometryMatrix = array();
		$reactantList = $this->generateReactantList();
		$numberOfReactants = count($reactantList);
		for($i = 0; $i < $numberOfReactants; ++$i)
		{
			$targetStoichiometryMatrix[] = array();

			foreach($this->reactions as $reaction)
			{
				$matrixEntry = 0;
				foreach($reaction->getRightHandSide() as $reactant => $stoichiometry)
				{
					if($reactantList[$i] === $reactant) $matrixEntry = $stoichiometry;
				}
				$targetStoichiometryMatrix[$i][] = $matrixEntry;
			}
		}
		return $targetStoichiometryMatrix;
	}

	public function generateStoichiometryMatrix()
	{
		$stoichiometryMatrix = $this->generateTargetStoichiometryMatrix();
		$sourceStoichiometryMatrix = $this->generateSourceStoichiometryMatrix();
		$numberOfReactants = count($stoichiometryMatrix);
		$numberOfReactions = count($stoichiometryMatrix[0]);
		for($i = 0; $i < $numberOfReactants; ++$i)
		{
			for($j = 0; $j < $numberOfReactions; ++$j) $stoichiometryMatrix[$i][$j] -= $sourceStoichiometryMatrix[$i][$j];
		}
		return $stoichiometryMatrix;
	}

	public function parseStoichiometry($matrix)
	{
		$success = true;
		if(gettype($matrix) == 'array' and count($matrix))
		{
			$allReactants = array();
			$reactantPrefix = '';
			$numberOfReactants = count($matrix);
			$numberOfReactions = count($matrix[0]);
			for($i = 0; $i < $numberOfReactants; ++$i)
			{
				if(count($matrix[$i]) !== $numberOfReactions) $success = false;
				if(floor($i/26)) $reactantPrefix = chr((floor($i/26)%26)+65);
				$allReactants[] = $reactantPrefix.chr(($i%26)+65);
			}
			for($i = 0; $i < $numberOfReactions; ++$i)
			{
				$lhs = array();
				$rhs = array();
				for($j = 0; $j < $numberOfReactants; ++$j)
				{
					if(!(is_numeric($matrix[$j][$i]) and (int)$matrix[$j][$i] == $matrix[$j][$i]))
					{
						error_log('$success: '.$success.PHP_EOL.'$numberOfReactants: '.$numberOfReactants.PHP_EOL.'$numberOfReactions: '.$numberOfReactions.PHP_EOL.'count($matrix): '.count($matrix).PHP_EOL.'$i: '.$i.PHP_EOL.'$j: '.$j.PHP_EOL.'$matrix[$j][$i]: '.$matrix[$j][$i].PHP_EOL.PHP_EOL, 3, '/var/tmp/crn.log');
						$success = false;
					}
					elseif($matrix[$j][$i] < 0) $lhs[$allReactants[$j]] = ($matrix[$j][$i] * -1);
					elseif($matrix[$j][$i] > 0) $rhs[$allReactants[$j]] = $matrix[$j][$i];
				}
				$this->addReaction(new Reaction($lhs, $rhs, false));
			}
		}
		else $success = false;
		return $success;
	}

	public function parseSourceTargetStoichiometry($sourceMatrix,$targetMatrix)
	{
		$success = true;
		if(gettype($sourceMatrix) == 'array' and count($sourceMatrix) and gettype($targetMatrix) == 'array' and count($targetMatrix) === count($sourceMatrix) and count($sourceMatrix[0])===count($targetMatrix[0]))
		{
			$allReactants = array();
			$reactantPrefix = '';
			$numberOfReactants = count($sourceMatrix);
			$numberOfReactions = count($sourceMatrix[0]);
			for($i = 0; $i < $numberOfReactants; ++$i)
			{
				if(count($matrix[$i]) !== $numberOfReactions) $success = false;
				if(floor($i/26)) $reactantPrefix = chr((floor($i/26)%26)+65);
				$allReactants[] = $reactantPrefix.chr(($i%26)+65);
			}
			for($i = 0; $i < $numberOfReactions; ++$i)
			{
				$lhs = array();
				$rhs = array();
				for($j = 0; $j < $numberOfReactants; ++$j)
				{
					if(!(is_numeric($sourceMatrix[$j][$i]) and (int)$sourceMatrix[$j][$i] == $sourceMatrix[$j][$i] and $sourceMatrix[$j][$i]>=0))
					{
						error_log('$success: '.$success.PHP_EOL.'$numberOfReactants: '.$numberOfReactants.PHP_EOL.'$numberOfReactions: '.$numberOfReactions.PHP_EOL.'count($sourceMatrix): '.count($sourceMatrix).PHP_EOL.'$i: '.$i.PHP_EOL.'$j: '.$j.PHP_EOL.'$sourceMatrix[$j][$i]: '.$sourceMatrix[$j][$i].PHP_EOL.PHP_EOL, 3, '/var/tmp/crn.log');
						$success = false;
					}
					elseif(!(is_numeric($targetMatrix[$j][$i]) and (int)$targetMatrix[$j][$i] == $targetMatrix[$j][$i] and $targetMatrix[$j][$i]>=0))
					{
						error_log('$success: '.$success.PHP_EOL.'$numberOfReactants: '.$numberOfReactants.PHP_EOL.'$numberOfReactions: '.$numberOfReactions.PHP_EOL.'count($targetMatrix): '.count($targerMatrix).PHP_EOL.'$i: '.$i.PHP_EOL.'$j: '.$j.PHP_EOL.'$targetMatrix[$j][$i]: '.$targetMatrix[$j][$i].PHP_EOL.PHP_EOL, 3, '/var/tmp/crn.log');
						$success = false;
					}

					else
					  {					    
					    if ($sourceMatrix[$j][$i] > 0) $lhs[$allReactants[$j]] = $sourceMatrix[$j][$i];
					    if($targetMatrix[$j][$i] > 0) $rhs[$allReactants[$j]] = $targetMatrix[$j][$i];
					  }
				}
				$this->addReaction(new Reaction($lhs, $rhs, false));
			}
		}
		else $success = false;
		return $success;
	}


	/*
	 * Generate V^T
	 *
	 * @return  array  $V  The transpose of V matrix as an array of arrays
	 */
	public function generateReactionRateJacobianMatrix()
	{
		$sourceStoichiometryMatrix = $this->generateSourceStoichiometryMatrix();
		$targetStoichiometryMatrix = $this->generateTargetStoichiometryMatrix();
		$V = array();
		for($i = 0; $i < count($sourceStoichiometryMatrix); ++$i)
		{
			$V[] = array();
			for ($j = 0; $j<count($sourceStoichiometryMatrix[$i]); ++$j)
			{
				if($this->reactions[$j]->isReversible())
				{
					if($sourceStoichiometryMatrix[$i][$j] > 0 && $targetStoichiometryMatrix[$i][$j] > 0) $V[$i][] = 2;
					elseif($sourceStoichiometryMatrix[$i][$j] > 0) $V[$i][] = 1;
					elseif($targetStoichiometryMatrix[$i][$j] > 0) $V[$i][] = -1;
					else $V[$i][] = 0;
				}
				else
				{
					if($sourceStoichiometryMatrix[$i][$j] > 0) $V[$i][] = 1;
					else $V[$i][] = 0;
				}
			}
		}
		return $V;
	}

	/*
	 * Get the number of reactions
	 *
	 * @return  int  $numberOfReactions  The number of reactions in the network
	 */
	public function getNumberOfReactions()
	{
		return count($this->reactions);
	}
}

class NetworkTest
{
	private $shortName = '';
	private $longName = '';
	private $description = '';
	private $supportsMassAction = true;
	private $supportsGeneralKinetics = true;
	private $executableName = '';
	private $inputFileFormats = array('human', 'stoichiometry');
	private $isEnabled = true;

	/**
	 * Constructor
	 */
	function __construct($shortName, $longName, $description, $executableName, $inputFileFormats, $supportsMassAction = false, $supportsGeneralKinetics = true)
	{
		$this->shortName = $shortName;
		$this->longName = $longName;
		$this->description = $description;
		$this->executableName = $executableName;
		$this->inputFileFormats = $inputFileFormats;
		$this->supportsMassAction = $supportsMassAction;
		$this->supportsGeneralKinetics = $supportsGeneralKinetics;
	}
	public function getShortName()
	{
		return $this->shortName;
	}

	public function getLongName()
	{
		return $this->longName;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getExecutableName()
	{
		return $this->executableName;
	}
	public function enableTest()
	{
		$this->isEnabled=true;
	}
	public function disableTest()
	{
		$this->isEnabled=false;
	}
	public function getIsEnabled()
	{
		return $this->isEnabled;
	}
	public function getInputFileFormats()
	{
		return $this->inputFileFormats;
	}

	public function supportsMassAction()
	{
		return $this->supportsMassAction;
	}
	public function supportsGeneralKinetics()
	{
		return $this->supportsGeneralKinetics;
	}
}
