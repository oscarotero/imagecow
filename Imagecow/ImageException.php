<?php
/**
 * Imagecow PHP library
 *
 * Exception class to manage the image errors
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.4 (2012)
 */

namespace Imagecow;

class ImageException extends \Exception {

	/**
	 * Generate an image with the message printed. Use alwais the Gd library.
	 *
	 * @param int  $width   Width of the image. By default 400px
	 * @param int  $height  Height of the image. By default 400px
	 *
	 * @return Imagecow\Libs\Gd  The Imagecow instance with the image
	 */
	public function getImage ($width = 400, $height = 400) {
		$imageError = imagecreate($width, $height);

		$bgColor = imagecolorallocate($imageError, 128, 128, 128);
		$textColor = imagecolorallocate($imageError, 255, 255, 255);

		foreach (str_split($this->getMessage(), intval($width/10)) as $line => $text) {
			imagestring($imageError, 5, 10, (($line + 1) * 18), $text, $textColor);
		}

		return new Libs\Gd($imageError);
	}
}
?>