<?php
/**
 * Library Interface for Imagecow (version 0.2)
 *
 * 2012. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
 * Original code from phpCan Image class (http://idc.anavallasuiza.com/)
 *
 * Imagecow is released under the GNU Affero GPL version 3.
 * More information at http://www.gnu.org/licenses/agpl-3.0.html
 */

namespace Imagecow\Libs;

interface InterfaceLibs {
	public function load ($image);
	public function unload ();
	public function save ($filename = '');
	public function resize ($width, $height = 0, $enlarge = false);
	public function crop ($width, $height, $x = 'center', $y = 'middle');
	public function flip ();
	public function flop ();
	public function zoomCrop ($width, $height, $x = 'center', $y = 'middle');
	public function toString ();
	public function getMimeType ();
	public function rotate ($degrees, $background = null);
	public function merge ($image, $x = 'center', $y = 'middle');
	public function convert ($format);
}
?>