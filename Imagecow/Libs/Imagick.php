<?php
/**
 * Imagick library for Imagecow (version 0.3)
 *
 * 2012. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
 * Original code from phpCan Image class (http://idc.anavallasuiza.com/)
 *
 * Imagecow is released under the GNU Affero GPL version 3.
 * More information at http://www.gnu.org/licenses/agpl-3.0.html
 */

namespace Imagecow\Libs;

use Imagecow\Image;

class Imagick extends Image implements InterfaceLibs {
	protected $image;
	protected $info;


	/**
	 * public function load (string $image)
	 *
	 * Loads an image
	 * Returns this
	 */
	public function load ($image) {
		$this->image = new Imagick();

		$this->image->readImage($image);

		$this->info = array(
			'file' => $image,
		);

		return $this;
	}



	/**
	 * public function unload (void)
	 *
	 * Destroys an image
	 * Returns this
	 */
	public function unload () {
		$this->image->destroy();

		return $this;
	}



	/**
	 * public function save (string $filename)
	 *
	 * Saves the image into a file
	 * Returns this
	 */
	public function save ($filename = '') {
		if (!$filename) {
			$this->image->writeImage();
		} else {
			$this->image->writeImage($filename);
		}

		return $this;
	}



	/**
	 * public function resize (int $width, [int $height], [bool $enlarge])
	 *
	 * Resizes an image
	 * Returns this
	 */
	public function resize ($width, $height = 0, $enlarge = false) {
		$width = intval($width);
		$height = intval($height);

		if (!$enlarge && $this->enlarge($width, $height, $this->image->getImageWidth(), $this->image->getImageHeight())) {
			return $this;
		}

		$fit = ($width === 0 || $height === 0) ? false : true;

		$this->image->scaleImage($width, $height, $fit);

		return $this;
	}



	/**
	 * public function crop (int $width, int $height, [int $x], [int $y])
	 *
	 * Crops an image
	 * Returns this
	 */
	public function crop ($width, $height, $x = 'center', $y = 'middle') {
		$x = $this->position($x, $width, $this->image->getImageWidth());
		$y = $this->position($y, $height, $this->image->getImageHeight());

		$this->image->cropImage($width, $height, $x, $y);

		return $this;
	}



	/**
	 * public function flip (void)
	 *
	 * Inverts an image vertically
	 * Returns this
	 */
	public function flip () {
		$this->image->flipImage();

		return $this;
	}



	/**
	 * public function flop (void)
	 *
	 * Inverts an image horizontally
	 * Returns this
	 */
	public function flop () {
		$this->image->flopImage();

		return $this;
	}



	/**
	 * public function toString (void)
	 *
	 * Gets the image data
	 * Returns string
	 */
	public function toString () {
		return $this->image->getImageBlob();
	}



	/**
	 * public function getMimeType (void)
	 *
	 * Gets the image mime type
	 * Returns string
	 */
	public function getMimeType () {
		$format = strtolower($this->image->getImageFormat());

		switch ($format) {
			case 'jpeg':
			case 'jpg':
			case 'gif':
			case 'png':
				return "image/$format";
		}
	}



	/**
	 * public function getWidth (void)
	 *
	 * Gets the image width
	 * Returns integer
	 */
	public function getWidth () {
		return $this->image->getImageWidth();
	}



	/**
	 * public function getHeight (void)
	 *
	 * Gets the image height
	 * Returns integer
	 */
	public function getHeight () {
		return $this->image->getImageHeight();
	}



	/**
	 * public function rotate (int $degrees, [int $background])
	 *
	 * Rotates the image
	 * Returns this
	 */
	public function rotate ($degrees, $background = null) {
		if (is_null($background)) {
			$background = 'white';
		}

		if (preg_match('/^#?[0-9abcdef]{6}$/i', $background) && !strstr($background, '#')) {
			$background = '#'.$background;
		}

		$this->image->rotateImage($background, $degrees);

		return $this;
	}



	/**
	 * public function merge (string/object $image, [int $x], [int $y])
	 *
	 * Merges two images in one
	 * Returns this
	 */
	public function merge ($image, $x = 'center', $y = 'middle') {
		if (is_object($image)) {
			$object_image = $image;
		} else {
			global $Config;

			$object_image = new Imagick();
			$object_image->readImage($Config->paths['web'].$image);
		}

		$x = $this->position($x, $object_image->getImageWidth(), $this->image->getImageWidth());
		$y = $this->position($y, $object_image->getImageHeight(), $this->image->getImageHeight());

		$this->image->compositeImage($object_image, $object_image->getImageCompose(), $x, $y);
		$this->image->flattenImages();

		return $this;
	}



	/**
	 * public function convert (string $format)
	 *
	 * Converts an image to another format
	 * Returns this
	 */
	public function convert ($format) {
		$this->image->setImageFormat($format);

		return $this;
	}
}
?>