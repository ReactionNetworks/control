/**
 * CoNtRol MySQL database schema and known CRN isomorphism data
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @created    11/05/2013
 * @modified   09/08/2014
 */

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

CREATE DATABASE IF NOT EXISTS control DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE control;

CREATE TABLE IF NOT EXISTS batch_jobs (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  filename varchar(255) NOT NULL,
  original_filename varchar(255) NOT NULL,
  file_format tinyint(3) unsigned NOT NULL COMMENT '0 = human, 1 = net stoichiometry, 2 = net stoichiometry + V, 3 = source + target + V, 4 = s + v, 5 = SBML, 6 = sauro',
  email varchar(255) NOT NULL,
  label varchar(255) DEFAULT NULL,
  `status` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 = not started, 1 = in progress, 2 = complete, 3 = output file downloaded, 4 = output file removed, 5 = job cancelled',
  detailed_output tinyint(3) unsigned NOT NULL DEFAULT '0',
  mass_action_only tinyint(3) unsigned NOT NULL,
  tests_enabled varchar(2047) NOT NULL,
  filekey varchar(14) NOT NULL,
  remote_ip varchar(40) NOT NULL COMMENT 'Length 40 to allow IPv6',
  remote_user_agent varchar(2047) NOT NULL,
  creation_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  update_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id),
  UNIQUE KEY filename (filename),
  UNIQUE KEY filekey (filekey)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;

DROP TABLE known_crns;
CREATE TABLE IF NOT EXISTS known_crns (
  id int(11) NOT NULL AUTO_INCREMENT,
  submitter varchar(127) DEFAULT NULL,
  number_of_reactions tinyint(4) NOT NULL,
  number_of_species tinyint(4) NOT NULL,
  sauro_string varchar(255) NOT NULL,
  result text NOT NULL,
  remote_ip varchar(40) DEFAULT NULL COMMENT 'Length 40 to allow IPv6',
  remote_user_agent varchar(2047) DEFAULT NULL,
  creation_timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  update_timestamp timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (id),
  UNIQUE KEY sauro_string (sauro_string)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8;
INSERT INTO known_crns (id, submitter, number_of_reactions, number_of_species, sauro_string, result, remote_ip, remote_user_agent, creation_timestamp, update_timestamp) VALUES
(1, 'Pete Donnell', 4, 3, '4 3 4 0 0 5 0 6 6 1 5 1 1 4 5 2 2 6 6 3 3 5', 'Each stoichiometry class of this network contains a unique equilibrium, located in the relative interior, which attracts all initial conditions within the stoichiometry class.\r\n\r\nReference:\r\nM. Banaji and J. Mierczyński, &ldquo;Global convergence in systems of differential equations arising from chemical reaction networks&rdquo;, <em>J. Diff. Eq.</em>, <strong>254</strong> (3) (2013), 1357-1374', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(2, 'Pete Donnell', 4, 6, '4 6 4 0 0 8 8 1 8 1 9 1 1 8 1 8 1 8 5 2 8 2 2 9 2 6 8 3 3 7', 'This CRN is known as the &ldquo;Brusselator&rdquo;, and is a simple model of the Belousov-Zhabotinsky reactions. For some parameter values it undergoes a Hopf bifurcation and exhibits a stable limit cycle.\r\n\r\nReference:\r\n<a href="http://www.bibliotecapleyades.net/archivos_pdf/brusselator.pdf">http://www.bibliotecapleyades.net/archivos_pdf/brusselator.pdf</a>', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(3, 'Pete Donnell', 5, 6, '5 6 5 0 6 0 6 0 0 7 0 7 7 1 7 1 1 6 1 6 1 5 8 2 6 2 2 9 9 3 3 6 3 8 7 4 9 4 4 10 4 6 4 6', 'This CRN is known as the &ldquo;catalytic trigger&rdquo;, the simplest catalytic reaction without autocatalysis that allows multiplicity of steady states.\r\n\r\nReferences:\r\n1. M. G. Slin''ko, V. I. Bykov, G. S. Yablonskii, T. A. Akramov, &ldquo;Multiplicity of the Steady State in Heterogeneous Catalytic Reactions&rdquo;, <em>Dokl. Akad. Nauk SSSR</em> <strong>226</strong> (4) (1976), 876.\r\n2. V. I. Bykov, V. I. Elokhin, G. S. Yablonskii, &ldquo;The simplest catalytic mechanism permitting several steady states of the surface&rdquo;, <em>React. Kinet. Catal. Lett.</em> <strong>4</strong> (2) (1976), 191–198.', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(4, 'Pete Donnell', 7, 7, '7 7 7 0 8 0 8 0 0 9 0 9 9 1 9 1 1 8 1 8 1 7 10 2 8 2 2 11 11 3 3 8 3 10 9 4 11 4 4 12 4 8 4 8 10 5 8 5 5 13 13 6 6 8 6 10', 'This CRN is the &ldquo;catalytic oscillator&rdquo;, the simplest catalytic reaction without autocatalysis that allows nonlinear self-oscillations.\r\n\r\nReference:\r\nV. I. Bykov, G. S. Yablonskii, V. F. Kim, &ldquo;On the simple model of kinetic self-oscillations in catalytic reaction of CO oxidation&rdquo;, <em>Doklady AN USSR</em> (Chemistry) <strong>242</strong> (3) (1978), 637–639.', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00'),
(5, 'Pete Donnell', 6, 3, '6 3 6 0 0 6 0 6 6 1 6 1 1 6 6 2 7 2 2 8 8 3 3 7 3 6 8 4 4 7 7 5 5 8', 'This CRN was analysed by Edelstein, and exhibits multiple steady states with hysteresis under mass action kinetics.\r\n\r\nReference:\r\nB. B. Edelstein (1970) &ldquo;Biochemical model with multiple steady states and hysteresis&rdquo;, <em>J. Theor. Biol.</em> <strong>29</strong> (1970), 57–62. ', '', '', '0000-00-00 00:00:00', '0000-00-00 00:00:00');

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
