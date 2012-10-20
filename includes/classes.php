<?php
/**
 * CoNtRol standard classes
 *
 * Assorted classes used within CoNtRol.
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth 2012
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    01/10/2012
 * @modified   10/10/2012
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
			else if (!is_numeric($reactants[$i][0])) $reactantStoichiometries[$reactants[$i]] = 1;
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
	 * @return  mixed   $reaction       If there is no error, returns a reaction object. Otherwise returns FALSE.
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

	public function exportAsText()
	{
	//	$text = $this->leftHandSide[0];
		$text = '';
	//	$leftHandSideLength = count($this->leftHandSide);
  //	$rightHandSideLength = count($this->rightHandSide);
	//	for($i = 1; $i < $leftHandSideLength; ++$i) $text = $text.' + '.$this->leftHandSide[$i];

		foreach($this->leftHandSide as $reactant => $stoichiometry)
		{
			if (strlen($text)) $text .= ' + ';
			if ($stoichiometry == 1) $text .= $reactant;
			else $text = $text.$stoichiometry.$reactant;		
		}
		if($this->reversible) $text .= ' <> ';
		else $text .= ' > ';
		
		foreach($this->rightHandSide as $reactant => $stoichiometry)
		{
			if (strlen($text)) $text .= ' + ';
			if ($stoichiometry == 1) $text .= $reactant;
			else $text = $text.$stoichiometry.$reactant;		
		}

	/*	if($rightHandSideLength)
		{
			$text .= $this->rightHandSide[0];
			for($i = 1; $i < $rightHandSideLength; ++$i) $text = $text.' + '.$this->rightHandSide[$i];
		}*/

		$text .= CLIENT_LINE_ENDING;

		return $text;
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
		$this->reactions[] = $reaction;
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

	public function generateStoichiometryMatrix()
	{
		return;
	}

	public function generateReactionRateJacobianMatrix()
	{
		return;
	}
}
