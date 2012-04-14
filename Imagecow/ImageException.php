<?php

namespace Imagecow;

class ImageException extends \Exception {

	/**
	 * public function getImageError ([int $width], [int $height])
	 *
	 * Returns an Image with the error string or null
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