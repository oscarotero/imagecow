<?php
/**
 * Library Interface for Imagecow (version 0.3)
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
	public function toString ();

	public function getMimeType ();
	public function getWidth ();
	public function getHeight ();

	public function convert ($format);
	public function resize ($width, $height = 0, $enlarge = false);
	public function crop ($width, $height, $x = 'center', $y = 'middle');
}
?>