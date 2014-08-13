<?php
/**
 * CoNtRol standard classes
 *
 * Assorted classes used within CoNtRol.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    01/10/2012
 * @modified   13/08/2014
 */

/**
 * Reaction class
 *
 * Describes an individual reaction.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 */

class Reaction
{
	private $leftHandSide = array();
	private $rightHandSide = array();
	private $reversible = true;

	/**
	 * Constructor
	 *
	 * @param  mixed  $leftHandSide   The left hand side of the reaction, either a string to parse, or an array of pre-parsed strings
	 * @param  mixed  $rightHandSide  The right hand side of the reaction, either a string to parse, or an array of pre-parsed strings
	 * @param  bool   $reversible     TRUE if the reaction is reversible, false otherwise
	 */
	function __construct( $leftHandSide, $rightHandSide, $reversible )
	{
		switch( gettype( $leftHandSide ) )
		{
			case 'array':
				$this->leftHandSide = $leftHandSide;
				break;
			case 'string':
				$this->leftHandSide = Reaction::parseReactants( $leftHandSide );
				break;
			default:
				// Throw an exception?
				break;
		}

		switch( gettype( $rightHandSide ) )
		{
			case 'array':
				$this->rightHandSide = $rightHandSide;
				break;
			case 'string':
				$this->rightHandSide = Reaction::parseReactants( $rightHandSide );
				break;
			default:
				// Throw an exception?
				break;
		}

		$this->reversible = $reversible;
	}



	/**
	 * Parse a string describing one side of a reaction
	 *
	 * @param   string  $reactionString  The string describing the reaction.
	 * @return  mixed   $reactants       If there is no error, returns an array of strings, each of which is a reactant. Otherwise returns FALSE.
	 */
	private static function parseReactants( $reactantString )
	{
		// Remove preceding/trailing whitespace
		$reactantString = trim( $reactantString );

		// Check there are no invalid characters
		if( ( strpos( $reactantString, '>' ) !== false ) or ( strpos( $reactantString, '-' ) !== false ) or
		   ( strpos( $reactantString, '<' ) !== false ) or ( strpos( $reactantString, '=' ) !== false ) ) return false;
		else
		{
			$temp = '';
			$reactantStringLength = strlen( $reactantString );
			// Remove whitespace
			for( $i = 0; $i < $reactantStringLength; ++$i )
			{
				if( $reactantString{$i} !== ' ' ) $temp .= $reactantString{$i};
			}
			$reactants = explode('+', $temp);
		}
		$numberOfReactants = count( $reactants );
		$reactantStoichiometries = array();
		for( $i = 0; $i < $numberOfReactants; ++$i )
		{
			if( is_numeric( $reactants[$i] ) ) return false;
			else if( $reactants[$i] and !is_numeric( $reactants[$i][0] ) )
			{
				$reactant_found = false;
				foreach( $reactantStoichiometries as $reactant => $stoichiometry )
				{
					if( $reactants[$i] == $reactant ) $reactant_found = true;
				}
				if( $reactant_found ) $reactantStoichiometries[$reactants[$i]] += 1;
				else $reactantStoichiometries[$reactants[$i]] = 1;
			}
			else
			{
				$reactantLength = strlen( $reactants[$i] );
				$characterPos = 0;
				for( $j = 0; $j < $reactantLength; ++$j )
				{
					if( !is_numeric( $reactants[$i][$j] ) ) $characterPos = $j;
					if( $characterPos ) break;
				}
				$reactant_found = false;
				foreach( $reactantStoichiometries as $reactant => $stoichiometry )
				{
					if( substr( $reactants[$i], $characterPos ) == $reactant ) $reactant_found = true;
				}
				if( $reactant_found ) $reactantStoichiometries[substr( $reactants[$i], $characterPos )] += substr( $reactants[$i], 0, $characterPos );
				else $reactantStoichiometries[substr( $reactants[$i], $characterPos )] = substr( $reactants[$i], 0, $characterPos );
			}
		}
		return $reactantStoichiometries;
	}


	/**
	 * Parse a string describing both sides of a reaction
	 *
	 * @param   string  $reactionString  The string describing the reaction.
	 * @return  mixed   $reaction        If there is no error, returns a reaction object. Otherwise returns FALSE.
	 */
	public static function parseReaction( $reactionString )
	{
		$temp = '';
		$reversible = true;
		$reactionStringLength = strlen( $reactionString );
		// Remove whitespace
		for( $i = 0; $i < $reactionStringLength; ++$i )
		{
			if( $reactionString{$i} !== ' ' and $reactionString{$i} !== '-' and $reactionString{$i} !== '=' ) $temp .= $reactionString{$i};
		}

		$leftArrowPos = strpos( $temp, '<' );
		$rightArrowPos = strpos( $temp, '>' );

		if( $leftArrowPos === false and $rightArrowPos === false ) return false;
		else
		{
			if( $leftArrowPos !== false and $rightArrowPos !== false )
			{
				if( $leftArrowPos === $rightArrowPos - 1 )
				{
					$lhs = Reaction::parseReactants( substr( $temp, 0, $leftArrowPos ) );
					$rhs = Reaction::parseReactants( substr( $temp, $rightArrowPos + 1 ) );
				}
				else return false;
			}
			else if( $leftArrowPos !== false )
			{
				$rhs = Reaction::parseReactants( substr( $temp, 0, $leftArrowPos ) );
				$lhs = Reaction::parseReactants( substr( $temp, $leftArrowPos + 1 ) );
				$reversible = false;
			}
			else
			{
				$lhs = Reaction::parseReactants( substr( $temp, 0, $rightArrowPos ) );
				$rhs = Reaction::parseReactants( substr( $temp, $rightArrowPos + 1 ) );
				$reversible = false;
			}
		 }
	 	return new Reaction( $lhs, $rhs, $reversible );
	}

	/**
	 * Export Reaction as HTML
	 *
	 * @return  string  $text  HTML markup describing the reaction.
	 */
	public function exportAsHTML()
	{
		$text = '';
		$text .= $this->exportLHSAsText();
		if( $this->reversible ) $text .= ' &#x21cc; ';
		else $text .= ' &rarr; ';
		$text .= $this->exportRHSAsText();
		$text .= '<br />' . CLIENT_LINE_ENDING;
		return $text;
	}

	/**
	 * Export Reaction as plain text
	 *
	 * @param   string  $line_ending  For compatibility between OSes, specify the plain text line ending to use. Defaults to the same as the server.
	 * @return  string  $text         Text describing the reaction.
	 */
	public function exportAsText( $line_ending = PHP_EOL )
	{
		$text = '';
		$text .= $this->exportLHSAsText();
		if( $this->reversible ) $text .= ' <--> ';
		else $text .= ' --> ';
		$text .= $this->exportRHSAsText();
		$text .= $line_ending;
		$text = str_replace( '&empty;', '0', $text );
		return $text;
	}

	/**
	 * Export the left hand side of the reaction as plain text
	 *
	 * @return  string  $text  Text describing the reaction's LHS.
	 */
	public function exportLHSAsText()
	{
		$text = '';

		if( count( $this->leftHandSide ) )
		{
			foreach( $this->leftHandSide as $reactant => $stoichiometry )
			{
				if( $text ) $text .= ' + ';
				if( $stoichiometry == 1 ) $text .= $reactant;
				else if( $stoichiometry ) $text = $text . $stoichiometry . $reactant;
			}
		}
		if( !$text ) $text = '&empty;';

		return $text;
	}

	/**
	 * Export the right hand side of the reaction as plain text
	 *
	 * @return  string  $text  Text describing the reaction's RHS.
	 */
	public function exportRHSAsText()
	{
		$text = '';

		if( count( $this->rightHandSide ) )
		{
			foreach( $this->rightHandSide as $reactant => $stoichiometry )
			{
				if( $text ) $text .= ' + ';
				if( $stoichiometry == 1 ) $text .= $reactant;
				else if( $stoichiometry ) $text = $text . $stoichiometry . $reactant;
			}
		}
		if( !$text ) $text = '&empty;';

		return $text;
	}

	/**
	 * Check whether the Reaction is reversible
	 *
	 * @return  bool  TRUE if the reaction is reversible, FALSE otherwise.
	 */
	public function isReversible()
	{
		return $this->reversible;
	}

	/**
	 * Get the reactants as an array
	 *
	 * TO DO: this function isn't correct for reactions where a reactant appears on both sides
	 *
	 * @return  array  $reactants  An associative array with each reactant name/label
	 *                             as a key, and its stoichiometry as the value.
	 *
	 */
	public function getReactants()
	{
		$reactants = false;

		if( $this->leftHandSide )
		{
			$reactants = array();
			foreach( $this->leftHandSide as $reactant => $stoichiometry )
			{
				if( $reactant !== 0 ) $reactants[] = $reactant;
			}
		}

		if( $this->rightHandSide )
		{
			if( !$reactants ) $reactants = array();
			foreach( $this->rightHandSide as $reactant => $stoichiometry )
			{
				if( $reactant !== 0 ) $reactants[] = $reactant;
			}
		}
		return $reactants;
	}


	/**
	 * Assorted get methods
	 */
	public function getLeftHandSide()
	{
		return $this->leftHandSide;
	}

	public function getRightHandSide()
	{
		return $this->rightHandSide;
	}
}
// End of class Reaction





/**
 * ReactionNetwork class
 *
 * Describes a network of reactions.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 */

class ReactionNetwork
{
	private $reactions = array();

	/**
	 * Constructor
	 *
	 * @param  array  $reactions   An array of Reactions
	 */
	function __construct( $reactions = array() )
	{
		$this->reactions = $reactions;
	}

	/**
	 * Add a reaction
	 *
	 * @param  Reaction  $reaction   The Reaction to add
	 */
	public function addReaction( $reaction )
	{
		if( $reaction )
		{
			$this->reactions[] = $reaction;
			return true;
		}
		else return false;
	}

	/**
	 * Export function for reaction network descriptor
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical equations
	 */
	public function exportReactionNetworkEquations( $line_ending = PHP_EOL, $LaTeX = false )
	{
		$equations = '';
		foreach( $this->reactions as $reaction ) $equations .= $reaction->exportAsText( $line_ending );
		if( !$equations ) $equations = 'No reactions found.';
		return $equations;
	}

	/**
	 * Export function for reaction network net stoichiometry and V matrix descriptor
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical matrices
	 */
	public function exportStoichiometryAndVMatrix( $LaTeX = false )
	{
		$equations = 'S MATRIX' . PHP_EOL;
		$equations .= $this->exportStoichiometryMatrix();
		$equations .= PHP_EOL . 'V MATRIX' . PHP_EOL;
		$equations .= $this->exportVMatrix();
		return $equations;
	}

	/**
	 * Export function for reaction network source and target stoichiometry and V matrix descriptor
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network chemical matrices
	 */
	public function exportSourceAndTargetStoichiometryAndVMatrix( $LaTeX = false )
	{
		$equations = 'S MATRIX'.PHP_EOL;
		$equations .= $this->exportSourceStoichiometryMatrix();
		$equations .= PHP_EOL . PHP_EOL . 'T MATRIX' . PHP_EOL;
		$equations .= $this->exportTargetStoichiometryMatrix();
		$equations .= PHP_EOL . PHP_EOL . 'V MATRIX' . PHP_EOL;
		$equations .= $this->exportVMatrix();
		/* TO DO: add REVERSIBLE section to output
		$equations .= PHP_EOL.PHP_EOL.'REVERSIBLE'.PHP_EOL;
		foreach($this->reactions)*/
		return $equations;
	}

	/**
	 * Export function for GLPK CRN data description
	 *
	 * @return  string  $glpk  GLPK version of CRN
	 */
	public function exportGLPKData()
	{
		$glpk = 'set REACTIONS:=';
		$numberOfReactionsIrreversible = 0;
		foreach( $this->reactions as $reaction )
		{
			++$numberOfReactionsIrreversible;
			if( $reaction->isReversible() ) ++$numberOfReactionsIrreversible;
		}
		for( $i = 1; $i <= $numberOfReactionsIrreversible; ++$i ) $glpk .= " $i";
		$glpk .= ';' . PHP_EOL . 'set REACTANTS:=';
		$numberOfReactants = count( $this->generateReactantList() );
		for( $i = 1; $i <= $numberOfReactants; ++$i ) $glpk .= " $i";
		$glpk .= ';' . PHP_EOL . PHP_EOL . 'param Ys:';
		for( $i = 1; $i <= $numberOfReactionsIrreversible; ++$i ) $glpk .= "\t$i";
		$glpk .= ':=';
		$sourceMatrix = $this->generateIrreversibleSourceStoichiometryMatrix();
		for( $i = 0; $i < count( $sourceMatrix ); ++$i )
		{
			$glpk .= PHP_EOL;
			$glpk .= ($i + 1) . "\t";
			foreach( $sourceMatrix[$i] as $element ) $glpk .= "\t$element";
		}
		$glpk .= ';' . PHP_EOL . PHP_EOL . 'param Gamma:';
		for( $i = 1; $i <= $numberOfReactionsIrreversible; ++$i ) $glpk .= "\t$i";
		$glpk .= ':=';
		$netMatrix = $this->generateIrreversibleStoichiometryMatrix();
		for( $i = 0; $i < count( $netMatrix ); ++$i )
		{
			$glpk .= PHP_EOL;
			$glpk .= ($i + 1) . "\t";
			foreach( $netMatrix[$i] as $element ) $glpk .= "\t$element";
		}
		$glpk .= ';' . PHP_EOL . PHP_EOL;
		return $glpk;
	}

	/**
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

	/**
	 * Export function for reaction network multiedges in Sauro format
	 *
	 * @return  string  $sauro  Sauro version of reaction network multiedges
	 */
	public function exportSauro()
	{
		$irreversibleStoichiometryMatrix = $this->generateIrreversibleSourceStoichiometryMatrix();
		$numberOfReactions = count( $this->reactions );
		$numberOfReactionsIrreversible = count( $irreversibleStoichiometryMatrix[0] );
		$numberOfReversibleReactionsSeen = 0;
		$reactants = $this->generateReactantList();
		$numberOfReactants = count( $reactants );
		$sauro = (string) count( $irreversibleStoichiometryMatrix );
		$sauro .= ' ';
		$sauro .= (string) $numberOfReactionsIrreversible;
		$sauro .= ' ';
		for( $i = 0; $i < $numberOfReactions; ++$i )
		{
			foreach( $this->reactions[$i]->getLeftHandSide() as $reactant => $stoichiometry )
			{
				for( $j = 0; $j < count( $reactants ); ++$j )
				{
					if( $reactants[$j] === $reactant )
					{
						for( $k = 0; $k < $stoichiometry; ++$k )
						{
							$sauro .= (string) $j + $numberOfReactionsIrreversible;
							$sauro .= ' ';
							$sauro .= $i;
							$sauro .= ' ';
						}
					}
				}
			}
			foreach( $this->reactions[$i]->getRightHandSide() as $reactant => $stoichiometry )
			{
				for( $j = 0; $j < count( $reactants ); ++$j )
				{
					if( $reactants[$j] === $reactant )
					{
						for( $k = 0; $k < $stoichiometry; ++$k )
						{
							$sauro .= $i;
							$sauro .= ' ';
							$sauro .= (string) $j + $numberOfReactionsIrreversible;
							$sauro .= ' ';
						}
					}
				}
			}
			if( $this->reactions[$i]->isReversible() )
			{
				foreach( $this->reactions[$i]->getRightHandSide() as $reactant => $stoichiometry )
				{
					for( $j = 0; $j < count( $reactants ); ++$j )
					{
						if( $reactants[$j] === $reactant )
						{
							for( $k = 0; $k < $stoichiometry; ++$k )
							{
								$sauro .= (string) $j + $numberOfReactionsIrreversible;
								$sauro .= ' ';
								$sauro .= $numberOfReactions + $numberOfReversibleReactionsSeen;
								$sauro .= ' ';
							}
						}
					}
				}
				foreach( $this->reactions[$i]->getLeftHandSide() as $reactant => $stoichiometry )
				{
					for( $j = 0; $j < count( $reactants ); ++$j )
					{
						if( $reactants[$j] === $reactant )
						{
							for( $k = 0; $k < $stoichiometry; ++$k )
							{
								$sauro .= $numberOfReactions + $numberOfReversibleReactionsSeen;
								$sauro .= ' ';
								$sauro .= (string) $j + $numberOfReactionsIrreversible;
								$sauro .= ' ';
							}
						}
					}
				}
				++$numberOfReversibleReactionsSeen;
			}
		}
		return trim( $sauro );
	}

	/**
	 * Export function for reaction network multiedges in Sage format
	 *
	 * @return  string  $edges  Sage version of reaction network multiedges
	 */
	public function exportSauroEdgesAsSage()
	{
		$edges = '';
		$numberOfReactions = count( $this->reactions );
		$first_edge = true;
		for( $i = 0; $i < $numberOfReactions; ++$i )
		{
			foreach( $this->reactions[$i]->getLeftHandSide() as $reactant => $stoichiometry )
			{
				for( $j = 0; $j < $stoichiometry; ++$j )
				{
					if( !$first_edge ) $edges .= ',';
					$edges .= '(';
					$edges .= "'" . str_replace( "'", '', $reactant ) . "'";
					$edges .= ',';
					$edges .= $i;
					$edges .= ')';
					$first_edge = false;
				}
			}
			foreach( $this->reactions[$i]->getRightHandSide() as $reactant => $stoichiometry )
			{
				for( $j = 0; $j < $stoichiometry; ++$j )
				{
					if( !$first_edge ) $edges .= ',';
					$edges .= '(';
					$edges .= $i;
					$edges .= ',';
					$edges .= "'" . str_replace( "'", '', $reactant ) . "'";
					$edges .= ')';
					$first_edge = false;
				}
			}
			if( $this->reactions[$i]->isReversible() )
			{
				foreach( $this->reactions[$i]->getRightHandSide() as $reactant => $stoichiometry )
				{
					for( $j = 0; $j < $stoichiometry; ++$j )
					{
						if( !$first_edge ) $edges .= ',';
						$edges .= '(';
						$edges .= "'" . str_replace( "'", '', $reactant ) . "'";
						$edges .= ',';
						$edges .= ( $i + $numberOfReactions );
						$edges .= ')';
						$first_edge = false;
					}
				}
				foreach( $this->reactions[$i]->getleftHandSide() as $reactant => $stoichiometry )
				{
					for( $j = 0; $j < $stoichiometry; ++$j )
					{
						if( !$first_edge ) $edges .= ',';
						$edges .= '(';
						$edges .= ( $i + $numberOfReactions );
						$edges .= ',';
						$edges .= "'" . str_replace( "'", '', $reactant ) . "'";
						$edges .= ')';
						$first_edge = false;
					}
				}
			}
		}
		return $edges;
	}

	/**
	 * Export function for reaction network net stoichiometry
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction network net stoichiometry matrix
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

	/**
	 * Export function for reaction network input stoichiometry
	 *
	 * @return  string  $equations  Text version of reaction network input stoichiometry matrix
	 */
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

	/**
	 * Export function for reaction network output stoichiometry
	 *
	 * @return  string  $equations  Text version of reaction network output stoichiometry matrix
	 */
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

	/**
	 * Export function for reaction rate Jacobian matrix
	 *
	 * @param   bool    $LaTeX      If TRUE, exports LaTeX markup. If FALSE, exports plain text
	 * @return  string  $equations  Text version of reaction rate Jacobian matrix
	 */
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

	/**
	 * Export function for reaction network description
	 *
	 * @param   string  $line_ending  The line ending to use (CRLF, CR, LF, etc). Defaults to PHP_EOL
	 * @return  null
	 */
	public function exportTextFile($line_ending = PHP_EOL)
	{
		// Send headers for download
		header('Content-Type: text/plain');
		header('Content-Disposition: Attachment; filename=crn.txt');
		header('Pragma: no-cache');
		echo $this->exportReactionNetworkEquations($line_ending);
	}

	/**
	 * HTML export function for reaction network
	 *
	 * Generates HTML describing the reaction network for use in an HTML form
	 */
	public function generateFieldsetHTML()
	{
		if(count($this->reactions))
		{
			for($i = 0; $i < count($this->reactions); ++$i)
			{
				echo '						<fieldset class="reaction_input_row">
							'.($i + 1).'. <input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" value="', str_replace('&empty;', '', $this->reactions[$i]->exportLHSAsText()), '" spellcheck="false" placeholder="&empty;" />
							<select class="reaction_direction" name="reaction_direction[]">
								<option value="left">&larr;</option>
								<option value="both"';
							if($this->reactions[$i]->isReversible()) echo ' selected="selected"';
							echo '>&#x21cc;</option>
								<option value="right"';
							if(!$this->reactions[$i]->isReversible()) echo ' selected="selected"';
							echo '>&rarr;</option>
							</select>
							<input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" value="', str_replace('&empty;', '', $this->reactions[$i]->exportRHSAsText()), '" spellcheck="false" placeholder="&empty;" />
						</fieldset><!-- reaction_input_row -->', PHP_EOL;
			}
		}
		else
		{
			echo '						<fieldset class="reaction_input_row">
							1. <input type="text" size="10" maxlength="64" class="reaction_left_hand_side" name="reaction_left_hand_side[]" value="" spellcheck="false" placeholder="&empty;" />
							<select class="reaction_direction" name="reaction_direction[]">
								<option value="left">&larr;</option>
								<option value="both" selected="selected">&#x21cc;</option>
								<option value="right">&rarr;</option>
							</select>
							<input type="text" size="10" maxlength="64" class="reaction_right_hand_side" name="reaction_right_hand_side[]" value="" spellcheck="false" placeholder="&empty;" />
						</fieldset><!-- reaction_input_row -->', PHP_EOL;
		}
	}

	/**
	 * Generate list of distinct reactants
	 *
	 * @return  array  $reactantList  Array of strings, where each element is a reactant. No reactant appears twice.
	 */

	private function generateReactantList()
	{
		$reactantList = array();
		foreach($this->reactions as $reaction)
		{
			$reactants = $reaction->getReactants();
			if($reactants) foreach($reactants as $reactant) if (!in_array($reactant,$reactantList)) $reactantList[] = $reactant;
		}
		return $reactantList;
	}

	/**
	 * Calculate reaction network input stoichiometry matrix
	 *
	 * @return  array  $sourceStoichiometryMatrix  2D array describing reaction network input stoichiometry matrix
	 */
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

	/**
	 * Calculate irreversible reaction network input stoichiometry matrix
	 *
	 * @return  array  $sourceStoichiometryMatrix  2D array describing reaction network input stoichiometry matrix
	 */
	public function generateIrreversibleSourceStoichiometryMatrix()
	{
		$sourceStoichiometryMatrix=array();
		$reactantList=$this->generateReactantList();
		$numberOfReactants=count( $reactantList );
		for( $i = 0; $i < $numberOfReactants; ++$i )
		{
			$sourceStoichiometryMatrix[]=array();

			foreach( $this->reactions as $reaction )
			{
				$matrixEntry = 0;
				foreach( $reaction->getLeftHandSide() as $reactant => $stoichiometry )
				{
					if( $reactantList[$i] === $reactant ) $matrixEntry = $stoichiometry;
				}
				$sourceStoichiometryMatrix[$i][] = $matrixEntry;
				if( $reaction->isReversible() )
				{
					$matrixEntry = 0;
					foreach( $reaction->getRightHandSide() as $reactant => $stoichiometry )
					{
						if( $reactantList[$i] === $reactant ) $matrixEntry = $stoichiometry;
					}
					$sourceStoichiometryMatrix[$i][] = $matrixEntry;
				}
			}
		}
		return $sourceStoichiometryMatrix;
	}

	/**
	 * Calculate reaction network output stoichiometry matrix
	 *
	 * @return  array  $targetStoichiometryMatrix  2D array describing reaction network output stoichiometry matrix
	 */
	public function generateTargetStoichiometryMatrix()
	{
		$targetStoichiometryMatrix = array();
		$reactantList = $this->generateReactantList();
		$numberOfReactants = count( $reactantList );
		for( $i = 0; $i < $numberOfReactants; ++$i )
		{
			$targetStoichiometryMatrix[] = array();

			foreach($this->reactions as $reaction)
			{
				$matrixEntry = 0;
				foreach( $reaction->getRightHandSide() as $reactant => $stoichiometry )
				{
					if( $reactantList[$i] === $reactant ) $matrixEntry = $stoichiometry;
				}
				$targetStoichiometryMatrix[$i][] = $matrixEntry;
			}
		}
		return $targetStoichiometryMatrix;
	}

	/**
	 * Calculate irreversible reaction network output stoichiometry matrix
	 *
	 * @return  array  $targetStoichiometryMatrix  2D array describing reaction network output stoichiometry matrix
	 */
	public function generateIrreversibleTargetStoichiometryMatrix()
	{
		$targetStoichiometryMatrix = array();
		$reactantList = $this->generateReactantList();
		$numberOfReactants = count( $reactantList );
		for( $i = 0; $i < $numberOfReactants; ++$i )
		{
			$targetStoichiometryMatrix[] = array();

			foreach( $this->reactions as $reaction )
			{
				$matrixEntry = 0;
				foreach( $reaction->getRightHandSide() as $reactant => $stoichiometry )
				{
					if( $reactantList[$i] === $reactant ) $matrixEntry = $stoichiometry;
				}
				$targetStoichiometryMatrix[$i][] = $matrixEntry;
				if( $reaction->isReversible() )
				{
					$matrixEntry = 0;
					foreach( $reaction->getLeftHandSide() as $reactant => $stoichiometry )
					{
						if( $reactantList[$i] === $reactant ) $matrixEntry = $stoichiometry;
					}
					$targetStoichiometryMatrix[$i][] = $matrixEntry;
				}
			}
		}
		return $targetStoichiometryMatrix;
	}

	/**
	 * Calculate reaction network net stoichiometry matrix
	 *
	 * @return  array  $stoichiometryMatrix  2D array describing reaction network net stoichiometry matrix
	 */
	public function generateStoichiometryMatrix()
	{
		$stoichiometryMatrix = $this->generateTargetStoichiometryMatrix();
		$sourceStoichiometryMatrix = $this->generateSourceStoichiometryMatrix();
		$numberOfReactants = count( $stoichiometryMatrix );
		if( isset( $stoichiometryMatrix[0] ) ) $numberOfReactions = count( $stoichiometryMatrix[0] );
		else $numberOfReactions = 0;
		for( $i = 0; $i < $numberOfReactants; ++$i )
		{
			for( $j = 0; $j < $numberOfReactions; ++$j ) $stoichiometryMatrix[$i][$j] -= $sourceStoichiometryMatrix[$i][$j];
		}
		return $stoichiometryMatrix;
	}

	/**
	 * Calculate irreversible reaction network net stoichiometry matrix
	 *
	 * @return  array  $stoichiometryMatrix  2D array describing reaction network net stoichiometry matrix
	 */
	public function generateIrreversibleStoichiometryMatrix()
	{
		$stoichiometryMatrix = $this->generateIrreversibleTargetStoichiometryMatrix();
		$sourceStoichiometryMatrix = $this->generateIrreversibleSourceStoichiometryMatrix();
		$numberOfReactants = count( $stoichiometryMatrix );
		if( isset( $stoichiometryMatrix[0] ) ) $numberOfReactions = count( $stoichiometryMatrix[0] );
		else $numberOfReactions = 0;
		for( $i = 0; $i < $numberOfReactants; ++$i )
		{
			for( $j = 0; $j < $numberOfReactions; ++$j ) $stoichiometryMatrix[$i][$j] -= $sourceStoichiometryMatrix[$i][$j];
		}
		return $stoichiometryMatrix;
	}

	/**
	 * Reaction network net stoichiometry matrix parser
	 *
	 * @param   array  $matrix   2D array describing reaction network net stoichiometry matrix
	 * @return  bool   $success  Returns TRUE if stoichiometry matrix successfully parsed, and FALSE otherwise
	 */
	public function parseStoichiometry( $matrix )
	{
		$success = true;
		if( gettype( $matrix ) == 'array' and count( $matrix ) )
		{
			$allReactants = array();
			$reactantPrefix = '';
			$numberOfReactants = count( $matrix );
			$numberOfReactions = count( $matrix[0] );
			for( $i = 0; $i < $numberOfReactants; ++$i )
			{
				if( count( $matrix[$i] ) !== $numberOfReactions ) $success = false;
				if( floor( $i / 26 ) ) $reactantPrefix = chr( ( floor( $i / 26 ) % 26 ) + 65 );
				$allReactants[] = $reactantPrefix . chr( ( $i % 26 ) + 65 );
			}
			for( $i = 0; $i < $numberOfReactions; ++$i )
			{
				$lhs = array();
				$rhs = array();
				for( $j = 0; $j < $numberOfReactants; ++$j )
				{
					if( !( is_numeric( $matrix[$j][$i] ) and (int) $matrix[$j][$i] == $matrix[$j][$i] ) )
					{
						$success = false;
					}
					elseif( $matrix[$j][$i] < 0 ) $lhs[$allReactants[$j]] = ( $matrix[$j][$i] * -1 );
					elseif( $matrix[$j][$i] > 0 ) $rhs[$allReactants[$j]] = $matrix[$j][$i];
				}
				$this->addReaction( new Reaction( $lhs, $rhs, false ) );
			}
		}
		else $success = false;
		return $success;
	}

	/**
	 * Reaction network Sauro input parser
	 *
	 * @param   string  $row      String in Sauro format representing a CRN
	 * @return  bool    $success  Returns TRUE if Sauro input successfully parsed, and FALSE otherwise
	 */
	public function parseSauro( $row )
	{
		$success = true;
		$row = trim( $row );
		if( gettype( $row ) == 'string' and $row )
		{
			$entries = explode( ' ', $row );
			if( count( $entries ) % 2 ) $success = false;
			$allReactants = array();
			$reactantPrefix = '';
			$numberOfReactants = (int) $entries[1];
			$numberOfReactions = (int) $entries[0];
			for( $i = 0; $i < $numberOfReactants; ++$i )
			{
				if( floor( $i / 26 ) ) $reactantPrefix = chr( ( floor( $i / 26 ) % 26 ) + 65 );
				$allReactants[] = $reactantPrefix . chr( ( $i % 26 ) + 65 );
			}
			for( $i = 0; $i < $numberOfReactions; ++$i )
			{
				$lhs = array();
				$rhs = array();
				for( $j = 2; $j < count($entries); ++$j )
				{
					if( $i == ( $entries[$j] ) )
					{
						if( ( $j % 2 ) == 0 ) // Reaction appears as LHS of pair, ie. reactant is on RHS of reaction
						{
							$reactantLabel = $allReactants[( $entries[$j + 1] - $numberOfReactions )];
							if( array_key_exists( $reactantLabel, $rhs ) ) ++$rhs[$reactantLabel]; // Reactant already on RHS, so increment its stoichiometry
							else $rhs[$reactantLabel] = 1; // Reactant not yet on RHS, so create an entry
						}
						else // Reaction appears as RHS of pair, ie. reactant is on LHS of reaction
						{
							$reactantLabel = $allReactants[( $entries[$j - 1] - $numberOfReactions )];
							if( array_key_exists( $reactantLabel, $lhs ) ) ++$lhs[$reactantLabel]; // Reactant already on LHS, so increment its stoichiometry
							else $lhs[$reactantLabel] = 1; // Reactant not yet on LHS, so create an entry
						}
					}
				}
				$this->addReaction( new Reaction( $lhs, $rhs, false ) );
			}
		}
		else $success = false;
		return $success;
	}

	/**
	 * Reaction network SBML parser
	 *
	 * @param   string  $file_name  Name of SBML file
	 * @return  mixed               Returns TRUE if SBML file successfully parsed, string containing error otherwise
	 */
	public function parseSBML( $file_name )
	{
		$error = false;
		$sbml_file = new DOMDocument();
		if( !$sbml_file->load( $file_name, LIBXML_DTDLOAD | LIBXML_DTDVALID ) )
		{
			$error = true;
			$_SESSION['errors'][] = 'Invalid SBML file.';
			return "Invalid SBML file.\r\n";
		}
		else
		{
			$models = $sbml_file->getElementsByTagName( 'model' );
			if( $models->length !== 1 )
			{
				$error = true;
				$_SESSION['errors'][] = 'File does not contain one model.';
				return "File does not contain one model.\r\n";
			}
			else
			{
				$model_child_nodes = $models->item( 0 )->childNodes;
				$reactions_found = false;
				for( $i = 0; $i < $model_child_nodes->length; ++$i )
				{
					if( $model_child_nodes->item( $i )->nodeName === 'listOfReactions' )
					{
						$reactions_found = true;
						$model_reactions = $model_child_nodes->item( $i )->childNodes;
					}
				}
				for( $i = 0; $i < $model_child_nodes->length; ++$i )
				{
					if( $model_child_nodes->item( $i )->nodeName === 'listOfCompartments' )
					{
						if( count( $model_child_nodes->item( $i )->childNodes ) > 1 )
						{
							$error = true;
							$_SESSION['errors'][] = 'This model contains more than one compartment, which is not currently supported.';
							return "This model contains more than one compartment, which is not currently supported.\r\n";
						}
					}
				}
				if( !$reactions_found )
				{
					$error = true;
					$_SESSION['errors'][] = 'No reactions found.';
					return "No reactions found.\r\n";
				}
				else
				{
					for( $i = 0; $i < $model_reactions->length; ++$i )
					{
						if( $model_reactions->item( $i )->nodeName === 'reaction' )
						{
							$lhs = array();
							$rhs = array();
							$reaction_attributes = $model_reactions->item( $i )->attributes;
							if( $reaction_attributes->getNamedItem( 'reversible' ) and $reaction_attributes->getNamedItem( 'reversible' )->nodeValue === 'false' ) $reversible = false;
							else $reversible = true; // If not explicitly stated, reversibility assumed by SBML specification Level 3
							$reaction_nodes = $model_reactions->item( $i )->childNodes;
							for( $j = 0; $j < $reaction_nodes->length; ++$j )
							{
								if( $reaction_nodes->item( $j )->nodeName === 'listOfReactants' )
								{
									$list_of_reactants = $reaction_nodes->item( $j )->childNodes;
									for( $k = 0; $k < $list_of_reactants->length; ++$k )
									{
										if( $list_of_reactants->item( $k )->hasAttributes() )
										{
											$reactant_attributes = $list_of_reactants->item( $k )->attributes;
											if( $reactant_attributes->getNamedItem( 'species' )->nodeValue != 'EmptySet' )
											{
												if( $reactant_attributes->getNamedItem( 'stoichiometry' ) ) $lhs[$reactant_attributes->getNamedItem( 'species' )->nodeValue] = $reactant_attributes->getNamedItem( 'stoichiometry' )->nodeValue;
												else $lhs[$reactant_attributes->getNamedItem( 'species' )->nodeValue] = 1;
											}
										}
									}
								}
								elseif( $reaction_nodes->item( $j )->nodeName === 'listOfProducts' )
								{
									$list_of_products = $reaction_nodes->item( $j )->childNodes;
									for( $k = 0; $k < $list_of_products->length; ++$k )
									{
										if( $list_of_products->item( $k )->hasAttributes() )
										{
											$product_attributes = $list_of_products->item( $k )->attributes;
											if( $product_attributes->getNamedItem( 'species' )->nodeValue != 'EmptySet' )
											{
												if( $product_attributes->getNamedItem( 'stoichiometry' ) ) $rhs[$product_attributes->getNamedItem( 'species' )->nodeValue] = $product_attributes->getNamedItem( 'stoichiometry' )->nodeValue;
												else $rhs[$product_attributes->getNamedItem( 'species' )->nodeValue] = 1;
											}
										}
									}
								}
								elseif( $reaction_nodes->item( $j )->nodeName === 'listOfModifiers' )
								{
									$list_of_modifiers = $reaction_nodes->item( $j )->childNodes;
									if( CRNDEBUG ) $_SESSION['errors'][] = 'There are ' . $list_of_modifiers->length . ' modifiers';
									for( $k = 0; $k < $list_of_modifiers->length; ++$k )
									{
										if( CRNDEBUG ) $_SESSION['errors'][] = $list_of_modifiers->item( $k )->nodeName;
										if( $list_of_modifiers->item( $k )->attributes )
										{
											if( CRNDEBUG ) $_SESSION['errors'][] = 'Looking for modifier ' . print_r( $list_of_modifiers->item( $k ), true );
											$modifier_found = false;
											$reaction_child_nodes = $model_reactions->item( $i )->childNodes;
											foreach( $reaction_child_nodes as $reaction_child_node )
											{
												if( CRNDEBUG ) $_SESSION['errors'][] = $reaction_child_node->nodeValue;
												if( $reaction_child_node->nodeName === 'kineticLaw' )
												{
													if( CRNDEBUG ) $_SESSION['errors'][] = 'kineticLaw node found';
													$kinetic_law_child_nodes = $reaction_child_node->childNodes;
													foreach( $kinetic_law_child_nodes as $kinetic_law_child_node )
													{
														if( $kinetic_law_child_node->nodeName === 'math' )
														{
															if( CRNDEBUG ) $_SESSION['errors'][] = 'math node found';
															$math_child_nodes = $kinetic_law_child_node->childNodes;
															foreach( $math_child_nodes as $math_child_node )
															{
																if( $math_child_node->nodeName === 'apply' )
																{
																	if( CRNDEBUG ) $_SESSION['errors'][] = 'apply node found';
																	$times_found = false;
																	foreach( $math_child_node->childNodes as $math_node )
																	{
																		if( $math_node->nodeName === 'times' ) $times_found = true;
																		if( $times_found and $math_node->nodeName === 'ci' and trim( $math_node->nodeValue ) === $list_of_modifiers->item( $k )->attributes->getNamedItem( 'species' )->nodeValue )
																		{
																			$modifier_found = true;
																			$lhs[$list_of_modifiers->item( $k )->attributes->getNamedItem( 'species' )->nodeValue] = 1;
																			$rhs[$list_of_modifiers->item( $k )->attributes->getNamedItem( 'species' )->nodeValue] = 1;
																		}
																	} // foreach( $math_child_node->childNodes as $math_node )
																} // if( $math_child_nodes->nodeName === 'apply' )
															} // foreach( $math_child_nodes as $math_child_node )
														} // if( $kinetic_law_child_node->nodeName === 'math' )
													} // foreach( $kinetic_law_child_nodes as $kinetic_law_child_node )
												} // if( $reaction_child_node->nodeName === 'kineticLaw' )
											} // foreach( $reaction_child_nodes as $reaction_child_node )
											if( !$error and !$modifier_found )
											{
												$error = true;
												$_SESSION['errors'][] = 'This model includes one or more reactions with unrecognised modifiers, which are not currently supported.';
												return "This model includes one or more reactions with unrecognised modifiers, which are not currently supported.\r\n";
											} // if( !$error and !$modifier_found )
										} // if( $list_of_modifiers->item( $k )->nodeValue )
									} // for( $k = 0; $k < $list_of_modifiers->length; ++$k )
								} // elseif( $reaction_nodes->item( $j )->nodeName === 'listOfModifiers' )
							}
							$this->addReaction( new Reaction( $lhs, $rhs, $reversible ) );
						}
					}
				}
			}
		}
		return !$error;
	}

	/**
	 * Reaction network input/output stoichiometry matrix parser
	 *
	 * @param   array  $sourceMatrix   2D array describing reaction network input stoichiometry matrix
	 * @param   array  $targetMatrix   2D array describing reaction network output stoichiometry matrix
	 * @return  bool   $success  Returns TRUE if stoichiometry matrices successfully parsed, and FALSE otherwise
	 */
	public function parseSourceTargetStoichiometry($sourceMatrix, $targetMatrix)
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
				if(count($sourceMatrix[$i]) !== $numberOfReactions) $success = false;
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
						$success = false;
					}
					elseif(!(is_numeric($targetMatrix[$j][$i]) and (int)$targetMatrix[$j][$i] == $targetMatrix[$j][$i] and $targetMatrix[$j][$i]>=0))
					{
						$success = false;
					}
					else
					{
						if($sourceMatrix[$j][$i] > 0) $lhs[$allReactants[$j]] = $sourceMatrix[$j][$i];
						if($targetMatrix[$j][$i] > 0) $rhs[$allReactants[$j]] = $targetMatrix[$j][$i];
					}
				}
				$this->addReaction(new Reaction($lhs, $rhs, false));
			}
		}
		else $success = false;
		return $success;
	}


	/**
	 * Generate V^T
	 *
	 * @return  array  $V  The transpose of reaction rate Jacobian matrix (V matrix) as a 2D array
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

	/**
	 * Get the number of reactants
	 *
	 * @return  int  $numberOfReactants  The number of reactants in the network
	 */
	public function getNumberOfReactants()
	{
		return count( $this->generateReactantList() );
	}

	/**
	 * Get the number of reactions
	 *
	 * @return  int  $numberOfReactions  The number of reactions in the network
	 */
	public function getNumberOfReactions()
	{
		return count( $this->reactions );
	}

	/**
	 * Get the number of reactions, counting reversible reactions as two irreversible reactions
	 *
	 * @return  int  $numberOfReactions  The number of reactions in the network
	 */
	public function getNumberOfReactionsIrreversible()
	{
		$numberOfReactions = $this->getNumberOfReactions();
		foreach( $this->reactions as $reaction )
		{
			if( $reaction->isReversible() ) ++$numberOfReactions;
		}
		return $numberOfReactions;
	}

	/**
	 * Isomorphism test
	 *
	 * Tests whether the current network is isomorphic to another network, using Sage.
	 *
	 * @param   ReactionNetwork  $reaction_network  The network to compare
	 * @return  bool             $is_isomorphic     True if the network is isomorphic, false otherwise
	 */
	public function isIsomorphic( $reaction_network )
	{
		$is_isomorphic = false;

		// Construct Sage code to check isomorphism
		$sage_string = 'import sage.all' . PHP_EOL;
		$sage_string .= 'original_crn = sage.all.DiGraph( multiedges = True )' . PHP_EOL;
		$sage_string .= 'new_crn = sage.all.DiGraph( multiedges = True )' . PHP_EOL;
		$sage_string .= 'original_crn.add_edges( [';
		$sage_string .= $this->exportSauroEdgesAsSage();
		$sage_string .= '] )' . PHP_EOL;
		$sage_string .= 'new_crn.add_edges( [';
		$sage_string .= $reaction_network->exportSauroEdgesAsSage();
		$sage_string .= '] )' . PHP_EOL;
		$sage_string .= 'print( original_crn.is_isomorphic( new_crn ) )' . PHP_EOL;
		$sage_filename = tempnam( TEMP_FILE_DIR, 'crnsage.' );

		// Write Sage code to temporary file
		if( !$handle = fopen( $sage_filename, 'w' ) )
		{
			die( "ERROR: Cannot open file ($sage_filename)" );
		}
		if( fwrite( $handle, $sage_string ) === false )
		{
			die( "ERROR: Cannot write to file ($sage_filename)" );
		}
		fclose($handle);

		// Run Sage code and capture output
		// Note: Sage requires $HOME to be set, and also by default looks for a .sage directory in $HOME
		// Web server user usually has neither. Work round this with `export HOME` and `--nodotsage`.
		$sage_exec_string = 'export HOME=' . TEMP_FILE_DIR . ' && ' . NICENESS . "sage --nodotsage -q $sage_filename";
		if( CRNDEBUG ) $sage_exec_string .= ' 2>&1';
		else $sage_exec_string .= ' 2> /dev/null';
		$output = array();
		$returnValue = 0;
		exec( $sage_exec_string, $output, $returnValue );
		$result = end( $output );
		if( $result === 'True' ) $is_isomorphic = true;
		if( CRNDEBUG )
		{
			$stderr = fopen( 'php://stderr', 'w' );
			fwrite( $stderr, $sage_string . PHP_EOL );
			fwrite( $stderr, $sage_exec_string . PHP_EOL );
			foreach( $output as $line ) fwrite( $stderr, $line . PHP_EOL );
			fwrite( $stderr, $result . PHP_EOL );
			fclose( $stderr );
		}
		else unlink( $sage_filename );

		return $is_isomorphic;
	}
}
// End of class ReactionNetwork






/**
 * NetworkTest class
 *
 * Describes a test to run on a reaction network.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 */

class NetworkTest
{
	private $shortName = '';
	private $longName = '';
	private $description = '';
	private $executableName = '';
	private $inputFileFormats = array( 'human', 'stoichiometry' );
	private $supportsIniFile = false;
	private $isEnabled = true;

	/**
	 * Constructor
	 */
	function __construct( $shortName, $longName, $description, $executableName, $inputFileFormats, $supportsIniFile = false )
	{
		$this->shortName = $shortName;
		$this->longName = $longName;
		$this->description = $description;
		$this->executableName = $executableName;
		$this->inputFileFormats = $inputFileFormats;
		$this->supportsIniFile = $supportsIniFile;
	}

	/**
	 * Assorted get methods
	 */
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
		$this->isEnabled = true;
	}
	public function disableTest()
	{
		$this->isEnabled = false;
	}

	public function getIsEnabled()
	{
		return $this->isEnabled;
	}

	public function getInputFileFormats()
	{
		return $this->inputFileFormats;
	}

	public function supportsIniFile()
	{
		return $this->supportsIniFile;
	}
}
// End of class NetworkTest






/**
 * FileFormat class
 *
 * Describes a file format describing a reaction network, for upload purposes.
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 */

class FileFormat
{
	private $longName;
	private $shortName;
	private $example;
	private $link;

	public function __construct($long_name, $short_name, $eg, $href)
	{
		$this->longName = sanitise($long_name);
		$this->shortName = $short_name;
		$this->example = $eg;
		$this->link = $href;
	}

	/**
	 * Generate HTML for radio button selector for single file upload in this format
	 */
	public function getNetworkRadioButton()
	{
		echo '<input type="radio" name="upload_network_file_format" value="'.$this->shortName.'"';
		if(isset($_SESSION['upload_file_format'])) // User has chosen a file format previously
		{
			if($this->shortName === $_SESSION['upload_file_format']) echo ' checked="checked"';
		}
		else // User has not chosen a file format previously, so use the default human readable
		{
			if($this->shortName === 'human') echo ' checked="checked"';
		}
		echo ' id="upload_network_file_format_'.$this->shortName.'" /><label for="upload_network_file_format_'.$this->shortName.'">'.$this->longName;
		if ($this->link !== '') echo ' <a href="'.$this->link.'">(details)</a>';
		echo ' '.$this->example.'</label><br />'.PHP_EOL;
	}

	/**
	 * Generate HTML for radio button selector for batch file upload in this format
	 */
	public function getBatchRadioButton()
	{
		echo '<input type="radio" name="upload_batch_file_format" value="'.$this->shortName.'"';
		if(isset($_SESSION['upload_file_format']))
		{
			if($this->shortName === $_SESSION['upload_file_format']) echo ' checked="checked"';
		}
		else
		{
			if($this->shortName === 'human') echo ' checked="checked"';
		}
		echo ' id="upload_batch_file_format_'.$this->shortName.'" /><label for="upload_batch_file_format_'.$this->shortName.'">'.$this->longName;
		if ($this->link !== '') echo ' <a href="'.$this->link.'">(details)</a>';
		echo ' '.$this->example.'</label><br />'.PHP_EOL;
	}
}
// End of class FileFormat
