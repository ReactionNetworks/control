<?php
/**
 * CoNtRol reaction network upload format radio buttons
 *
 * Outputs HTML for different file formats for upload
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    19/08/2013
 * @modified   29/04/2014
 */

$human = new FileFormat( 'Human readable', 'human', 'e.g. A + 2B --&gt; C', 'http://reaction-networks.net/wiki/CoNtRol#Human_Readable' );
$stoichiometry = new FileFormat( 'Net Stoichiometry', 'stoichiometry', 'e.g. -1 -2 1', 'http://reaction-networks.net/wiki/CoNtRol#Net_Stoichiometry' );
//$sv = new FileFormat( 'Net Stoichiometry + V Matrix', 'sv', '', 'http://reaction-networks.net/wiki/CoNtRol#Net_Stoichiometry_.2B_V_Matrix' );
$source_target = new FileFormat( 'Source and Target Stoichiometry', 'source_target', '', 'http://reaction-networks.net/wiki/CoNtRol#Source_Stoichiometry_.2B_Target_Stoichiometry' );
//$stv = new FileFormat( 'Source and Target + V Matrix', 'stv', '', 'http://reaction-networks.net/wiki/CoNtRol#Source_Stoichiometry_.2B_Target_Stoichiometry_.2B_V_Matrix' );
$sauro = new FileFormat( 'Sauro', 'sauro', 'e.g. 1 4 0 1 0 2 3 0 4 0', '' );
//$feinberg1 = new FileFormat( "Martin Feinberg's CRN Toolbox Version 1.x", 'feinberg1', '', '' );
//$feinberg2 = new FileFormat( "Martin Feinberg's CRN Toolbox Version 2.x", 'feinberg2', '', '' );
$sbml = new FileFormat( 'Systems Biology Markup Language (SBML) Levels 1, 2, 3','sbml', '', '' );

$format_array = array( $human, $stoichiometry, $source_target, $sauro, $sbml );
