<?php
/**
 * CoNtRol standard classes
 *
 * Assorted classes used within CoNtRol.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   27/03/2013
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
		$reactantString = trim($reactantString);
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
		for ($i=0;$i<$numberOfReactants;++$i)
		{
			if (is_numeric($reactants[$i])) return false;
			else if ($reactants[$i] and !is_numeric($reactants[$i][0])) $reactantStoichiometries[$reactants[$i]] = 1;
			else
			{
				$reactantLength = strlen($reactants[$i]);
				$characterPos = 0;
				for ($j=0;$j<$reactantLength;++$j)
				{
					if (!is_numeric($reactants[$i][$j])) $characterPos = $j;
					if ($characterPos) break;
				}
				$reactantStoichiometries[substr($reactants[$i],$characterPos)] = substr($reactants[$i],0,$characterPos);
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
					$lhs = Reaction::parseReactants(substr($temp,0,$leftArrowPos));
					$rhs = Reaction::parseReactants(substr($temp,$rightArrowPos+1));
				}
				else return false;
			}
			else if ($leftArrowPos!==false)
			{
				$rhs = Reaction::parseReactants(substr($temp,0,$leftArrowPos));
				$lhs = Reaction::parseReactants(substr($temp,$leftArrowPos+1));
				$reversible = false;
			}
			else
			{
				$lhs = Reaction::parseReactants(substr($temp,0,$rightArrowPos));
				$rhs = Reaction::parseReactants(substr($temp,$rightArrowPos+1));
				$reversible = false;
			}
		 }
	 	return new Reaction($lhs,$rhs,$reversible);
	/*	if((strpos($reactantString, '>') !== false) or (strpos($reactantString, '-') !== false) or
		   (strpos($reactantString, '<') !== false) or (strpos($reactantString, '=') !== false)) $reactants = false;
		else
		{
			$reactants = explode('+', $temp);
		}
		return $reactants;*/
	}

	public function exportAsHTML()
	{
		$text = '';
		$text.=$this->exportLHSAsText();
		if($this->reversible) $text .= ' &#x21cc; ';
		else $text .= ' &rarr; ';
		$text.=$this->exportRHSAsText();

		$text .= '<br />'.CLIENT_LINE_ENDING;

		return $text;
	}

	public function exportAsText()
	{
		$text = '';
		$text.=$this->exportLHSAsText();
		if($this->reversible) $text .= ' <--> ';
		else $text .= ' --> ';
		$text.=$this->exportRHSAsText();

		$text .= CLIENT_LINE_ENDING;

		return $text;
	}

	public function exportLHSAsText()
	{
		$text = '';

		foreach($this->leftHandSide as $reactant => $stoichiometry)
		{
			if (strlen($text)) $text .= ' + ';
			if ($stoichiometry == 1) $text .= $reactant;
			else if($stoichiometry) $text = $text.$stoichiometry.$reactant;
		}

		return $text;
	}

	public function exportRHSAsText()
	{
		$text = '';

		foreach($this->rightHandSide as $reactant => $stoichiometry)
		{
			if (strlen($text)) $text .= ' + ';
			if ($stoichiometry == 1) $text .= $reactant;
			else if($stoichiometry) $text = $text.$stoichiometry.$reactant;
		}

		return $text;
	}

	public function isReversible()
	{
		return $this->reversible;
	}

	public function getReactants()
	{
		$reactants=array();
		if ($this->leftHandSide) foreach($this->leftHandSide as $reactant => $stoichiometry) $reactants[]=$reactant;
		else return false;
		if ($this->rightHandSide) foreach($this->rightHandSide as $reactant => $stoichiometry) $reactants[]=$reactant;
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
	public function exportReactionNetworkEquations($LaTeX = false)
	{
		$equations = '';
		$numberOfReactions = count($this->reactions);
		for($i = 0; $i < $numberOfReactions; ++$i) $equations .= $this->reactions[$i]->exportAsText();
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

	public function exportStoichiometryMatrix()
	{
		return;
	}

	public function exportReactionRateJacobianMatrix()
	{
		return;
	}

	public function exportTextFile()
	{
		// Send headers for download
		header('Content-Type: text/plain');
		header('Content-Disposition: Attachment; filename=crn.txt');
		header('Pragma: no-cache');
		echo $this->exportReactionNetworkEquations();
	}

	public function generateFieldsetHTML()
	{
		if(count($this->reactions))
		{
			foreach($this->reactions as $reaction)
			{
				echo '<fieldset class="reaction_input_row">
						<input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" value="', $reaction->exportLHSAsText(), '" />
						<select class="reaction_direction" name="reaction_direction[]">
							<option value="left">&larr;</option>
							<option value="both"';
							if($reaction->isReversible()) echo ' selected="selected"';
							echo '>&#x21cc;</option>
							<option value="right"';
							if(!$reaction->isReversible()) echo ' selected="selected"';
							echo '>&rarr;</option>
						</select>
						<input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" value="', $reaction->exportRHSAsText(), '" />
					</fieldset><!-- reaction_input_row -->';
			}
		}
		else echo
		'<fieldset class="reaction_input_row">
						<input type="text" size="32" maxlength="128" class="reaction_left_hand_side" name="reaction_left_hand_side[]" />
						<select class="reaction_direction" name="reaction_direction[]">
							<option value="left">&larr;</option>
							<option value="both" selected="selected">&#x21cc;</option>
							<option value="right">&rarr;</option>
						</select>
						<input type="text" size="32" maxlength="128" class="reaction_right_hand_side" name="reaction_right_hand_side[]" />
					</fieldset><!-- reaction_input_row -->';
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
		for($i=0;$i<$numberOfReactants; ++$i)
		{
			$sourceStoichiometryMatrix[]=array();

			foreach($this->reactions as $reaction)
			{
				$matrixEntry=0;
				foreach($reaction->getLeftHandSide() as $reactant => $stoichiometry)
				{
					if ($reactantList[$i]===$reactant) $matrixEntry=$stoichiometry;
				}
				$sourceStoichiometryMatrix[$i][]=$matrixEntry;
			}
		}
		return $sourceStoichiometryMatrix;
	}

	public function generateTargetStoichiometryMatrix()
	{
		$targetStoichiometryMatrix=array();
		$reactantList=$this->generateReactantList();
		$numberOfReactants=count($reactantList);
		for($i=0;$i<$numberOfReactants; ++$i)
		{
			$targetStoichiometryMatrix[]=array();

			foreach($this->reactions as $reaction)
			{
				$matrixEntry=0;
				foreach($reaction->getRightHandSide() as $reactant => $stoichiometry)
				{
					if($reactantList[$i]===$reactant) $matrixEntry=$stoichiometry;
				}
				$targetStoichiometryMatrix[$i][]=$matrixEntry;
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
		for($i=0;$i<$numberOfReactants;++$i)
		{
			for($j=0;$j<$numberOfReactions;++$j) $stoichiometryMatrix[$i][$j]-=$sourceStoichiometryMatrix[$i][$j];		
		}
		return $stoichiometryMatrix;
	}

	public function parseStoichiometry($matrix)
	{
		$success=true;
		if(gettype($matrix) == 'array' and count($matrix))
		{
			$allReactants = array();
			$reactantPrefix = '';
			$numberOfReactants = count($matrix);
			$numberOfReactions = count($matrix[0]);
			for($i = 0; $i < $numberOfReactants; ++$i)
			{
				if(count($matrix[$i]) !== $numberOfReactions) $success = false;
				if(floor($i/26))$reactantPrefix = chr((floor($i/26)%26)+65);
				$allReactants[] = $reactantPrefix.chr(($i%26)+65);
			}
			for($i=0; $i<$numberOfReactions; ++$i)
			{
				$lhs = array();
				$rhs = array();
				for($j=0; $j<$numberOfReactants; ++$j)
				{
					if(!(is_numeric($matrix[$j][$i]) and (int)$matrix[$j][$i] == $matrix[$j][$i])) $success = false;
					else if($matrix[$j][$i] <0 ) $lhs[$allReactants[$j]] = ($matrix[$j][$i] * -1);
					else if($matrix[$j][$i] >0 ) $rhs[$allReactants[$j]] = $matrix[$j][$i];
				}
				$this->addReaction(new Reaction($lhs, $rhs, false));
			}
		}
		else $success = false;
		return $success;
	}

	public function generateReactionRateJacobianMatrix()
	{
		return;
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
