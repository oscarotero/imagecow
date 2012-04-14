<?php
/**
 * Imagick library for Imagecow (version 0.4)
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

	/**
	 * public function load (string $image)
	 *
	 * Loads an image
	 * Returns this
	 */
	public function load ($image) {
		$this->image = new \Imagick();

		if ($this->image->readImage($image) !== true) {
			$this->setError('The image file "'.$image.'" cannot be loaded', IMAGECOW_ERROR_LOADING);
			$this->image = $this->file = null;
		}

		return $this;
	}



	/**
	 * public function setImage (Imagick $image)
	 *
	 * Sets a new Imagick object
	 * Returns this
	 */
	public function setImage (\Imagick $image) {
		$this->filename = $image;

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
			if ($this->image->writeImage() !== true) {
				$this->setError('The image file cannot be saved', IMAGECOW_ERROR_LOADING);
			}
		} else if ($this->image->writeImage($filename) !== true) {
			$this->setError('The image file "'.$filename.'" cannot be saved', IMAGECOW_ERROR_LOADING);
		}

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
		if (!$this->image) {
			return false;
		}

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
		if (!$this->image) {
			return false;
		}

		return $this->image->getImageWidth();
	}



	/**
	 * public function getHeight (void)
	 *
	 * Gets the image height
	 * Returns integer
	 */
	public function getHeight () {
		if (!$this->image) {
			return false;
		}

		return $this->image->getImageHeight();
	}



	/**
	 * public function convert (string $format)
	 *
	 * Converts an image to another format
	 * Returns this
	 */
	public function convert ($format) {
		if (!$this->image || $this->image->setImageFormat($format) !== true) {
			$this->setError('The image format "'.$format.'" is not valid', IMAGECOW_ERROR_FUNCTION);
			return $this;
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
		if (!$this->image) {
			return $this;
		}

		$imageWidth = $this->getWidth();
		$imageHeight = $this->getHeight();

		$width = $this->getSize($width, $imageWidth);
		$height = $this->getSize($height, $imageHeight);

		if (!$enlarge && $this->enlarge($width, $height, $imageWidth, $imageHeight)) {
			return $this;
		}

		if ($this->image->scaleImage($width, $height, (($width === 0 || $height === 0) ? false : true)) !== true) {
			$this->setError('There was an error resizing the image', IMAGECOW_ERROR_FUNCTION);
		} else {
			$this->image->setImagePage(0, 0, 0, 0);
		}

		return $this;
	}



	/**
	 * public function crop (int $width, int $height, [int $x], [int $y])
	 *
	 * Crops an image
	 * Returns this
	 */
	public function crop ($width, $height, $x = 'center', $y = 'middle') {
		if (!$this->image) {
			return $this;
		}

		$imageWidth = $this->getWidth();
		$imageHeight = $this->getHeight();

		$width = $this->getSize($width, $imageWidth);
		$height = $this->getSize($height, $imageHeight);

		$x = $this->position($x, $width, $imageWidth);
		$y = $this->position($y, $height, $imageHeight);

		if ($this->image->cropImage($width, $height, $x, $y) !== true) {
			$this->setError('There was an error cropping the image', IMAGECOW_ERROR_FUNCTION);
		} else {
			$this->image->setImagePage(0, 0, 0, 0);
		}

		return $this;
	}
}
?>