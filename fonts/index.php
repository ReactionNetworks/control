<?php
/**
 * CoNtRol protected directory redirect
 *
 * @author     Pete Donnell <pete-dot-donnell-at-port-dot-ac-dot-uk>
 * @copyright  2012-2014 University of Portsmouth & Kitson Consulting Limited
 * @license    https://gnu.org/licenses/gpl-3.0-standalone.html GPLv3 or later
 * @see        https://reaction-networks.net/control/documentation/
 * @package    CoNtRol
 * @modified   29/04/2014
 */

/**
 * Standard include
 */
require_once('../includes/config.php');

header('Location: '.SITE_URL);
