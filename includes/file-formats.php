<?php
/**
 * CoNtRol reaction network upload format radio buttons
 *
 * Outputs HTML for different file formats for upload
 *
 * @author     Pete Donnell <pete dot donnell at port dot ac dot uk>
 * @copyright  University of Portsmouth, Kitson Consulting Limited 2012-2013
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html
 * @created    19/08/2013
 * @modified   19/08/2013
 */
 
$human = new FileFormat('Human readable','human','e.g. A + 2B --&gt; C','http://reaction-networks.net/wiki/CoNtRol#Human_Readable');
$stoichiometry = new FileFormat('Net Stoichiometry','stoichiometry','e.g. -1 -2 1','http://reaction-networks.net/wiki/CoNtRol#Net_Stoichiometry');
$sv = new FileFormat('Net Stoichiometry + V Matrix','sv','','http://reaction-networks.net/wiki/CoNtRol#Net_Stoichiometry_.2B_V_Matrix');
$source_target = new FileFormat('Source and Target Stoichiometry','source_target','','http://reaction-networks.net/wiki/CoNtRol#Source_Stoichiometry_.2B_Target_Stoichiometry');
$stv = new FileFormat('Source and Target + V Matrix','stv','','http://reaction-networks.net/wiki/CoNtRol#Source_Stoichiometry_.2B_Target_Stoichiometry_.2B_V_Matrix');
$sauro = new FileFormat('Sauro','sauro','e.g. 1 4 0 3 0 4 5 0 7 0','');
$feinberg1 = new FileFormat('Martin Feinberg\'s CRN Toolbox Version 1.x','feinberg1','','');
$feinberg2 = new FileFormat('Martin Feinberg\'s CRN Toolbox Version 2.x','feinberg2','','');

$format_array = array($human, $stoichiometry, $sv, $source_target, $stv, $sauro, $feinberg1, $feinberg2);