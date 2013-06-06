<?php
/**
 * Imagecow PHP library
 *
 * Imagick library
 * Original code from phpCan Image class (http://idc.anavallasuiza.com/)
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.4.2 (2012)
 */

namespace Imagecow\Libs;

use Imagecow\Image;

class Imagick extends Image implements InterfaceLibs {


	/**
	 * Constructor of the class
	 *
	 * @param string/Imagick  $image  The string with the filename to load or the Imagick instance
	 */
	public function __construct ($image = null) {
		if (isset($image)) {
			if (is_object($image)) {
				$this->setImage($image);
			} else if (is_string($image)) {
				$this->load($image);
			}
		}
	}


	/**
	 * public function load (string $image)
	 *
	 * Loads an image
	 * Returns this
	 */
	public function load ($image) {
		$imagick = new \Imagick();

		if ($imagick->readImage($image) !== true) {
			$this->setError('The image file "'.$image.'" cannot be loaded', IMAGECOW_ERROR_LOADING);
			$this->image = null;
		} else {
			$this->setImage($imagick);
		}

		return $this;
	}



	/**
	 * Destroy the image loaded
	 *
	 * @return $this
	 */
	public function unload () {
		$this->image->destroy();

		return $this;
	}



	/**
	 * Returns the filename associated with this image
	 *
	 * @return string The filename. Returns null if no filename is associated (no image loaded or loaded from a string)
	 */
	public function getFilename () {
		if (!$this->image) {
			return null;
		}

		return $this->image->getImageFilename();
	}



	/**
	 * Inverts the image vertically
	 *
	 * @return $this
	 */
	public function flip () {
		if (!$this->image) {
			return $this;
		}

		if ($this->image->flipImage() !== true) {
			$this->setError('There was an error on flip the image', IMAGECOW_ERROR_FUNCTION);
		}

		return $this;
	}



	/**
	 * Inverts the image horizontally
	 *
	 * @return $this
	 */
	public function flop () {
		if (!$this->image) {
			return $this;
		}

		if ($this->image->flipImage() !== true) {
			$this->setError('There was an error on flop the image', IMAGECOW_ERROR_FUNCTION);
		}

		return $this;
	}



	/**
	 * Sets a new Imagick instance
	 *
	 * @param Imagick  $image  The new Imagick instance
	 *
	 * @return $this
	 */
	public function setImage (\Imagick $image) {
		//Convert to RGB
		if (method_exists($image, 'getImageProfiles') && ($image->getImageColorspace() === \Imagick::COLORSPACE_CMYK)) {
			$profiles = $image->getImageProfiles('*', false);

			if (array_search('icc', $profiles) === false) {
				$image->profileImage('icc', file_get_contents(__DIR__.'/icc/us_web_uncoated.icc'));
			}

			$image->profileImage('icm', file_get_contents(__DIR__.'/icc/srgb.icm'));
		}

		$this->image = $image;

		return $this;
	}



	/**
	 * Save the image in a file
	 *
	 * @param string  $filename  Name of the file where the image will be saved. If it's not defined, The original file will be overwritten.
	 *
	 * @return $this
	 */
	public function save ($filename = null) {
		$filename = $filename ? $filename : $this->image->getImageFilename();

		if (!($fp = fopen($filename, 'w'))) {
			$this->setError('The image file "'.$filename.'" cannot be saved', IMAGECOW_ERROR_LOADING);
			return $this;
		}

		$this->image->writeImagesFile($fp);

		fclose($fp);

		return $this;
	}


	/**
	 * Gets the image data in a string
	 *
	 * @return string  The image data
	 */
	public function getString () {
		if (!$this->image) {
			return '';
		}

		if (strtolower($this->image->getImageFormat()) === 'jpeg') {
			$this->image->setImageCompression(\Imagick::COMPRESSION_JPEG);
			$this->image->setImageCompressionQuality($this->quality);

			return $this->image->getImageBlob();
		}

		if (strtolower($this->image->getImageFormat()) === 'gif') {
			if ($fp = fopen($file = tempnam(sys_get_temp_dir(), 'imagick'), 'w')) {
				$this->image->writeImagesFile($fp);

				fclose($fp);

				$string = file_get_contents($file);

				unlink($file);

				return $string;
			}
		}

		return $this->image->getImageBlob();
	}



	/**
	 * Gets the mime-type of the image
	 *
	 * @return string  The mime-type
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
	 * Gets the width of the image
	 *
	 * @return integer  The width in pixels
	 */
	public function getWidth () {
		if (!$this->image) {
			return false;
		}

		return $this->image->getImageWidth();
	}



	/**
	 * Gets the height of the image
	 *
	 * @return integer  The height in pixels
	 */
	public function getHeight () {
		if (!$this->image) {
			return false;
		}

		return $this->image->getImageHeight();
	}



	/**
	 * Converts the image to other format
	 *
	 * @param string  $format  The new format: png, jpg, gif
	 *
	 * @return $this
	 */
	public function format ($format) {
		if (!$this->image || $this->image->setImageFormat($format) !== true) {
			$this->setError('The image format "'.$format.'" is not valid', IMAGECOW_ERROR_FUNCTION);
			return $this;
		}

		return $this;
	}



	/**
	 * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300)
	 *
	 * @param int/string  $width    The max width of the image. It can be a number (pixels) or percentaje
	 * @param int/string  $height   The max height of the image. It can be a number (pixels) or percentaje
	 * @param boolean     $enlarge  True if the new image can be bigger (false by default)
	 *
	 * @return $this
	 */
	public function resize ($width, $height = 0, $enlarge = false) {
		if (!$this->image) {
			return $this;
		}

		$imageWidth = $this->getWidth();
		$imageHeight = $this->getHeight();

		$width = $this->getSize($width, $imageWidth);
		$height = $this->getSize($height, $imageHeight);

		if (!$enlarge && $this->enlarge($width, $imageWidth) && $this->enlarge($height, $imageHeight)) {
			return $this;
		}

		if (strtolower($this->image->getImageFormat()) === 'gif') {
			$this->image = $this->image->coalesceImages();

			foreach ($this->image as $frame) {
				$frame->scaleImage($width, $height, (($width === 0 || $height === 0) ? false : true));
			}

			$this->image = $this->image->deconstructImages();
		} else {
			if ($this->image->scaleImage($width, $height, (($width === 0 || $height === 0) ? false : true)) !== true) {
				$this->setError('There was an error resizing the image', IMAGECOW_ERROR_FUNCTION);
			} else {
				$this->image->setImagePage(0, 0, 0, 0);
			}
		}

		return $this;
	}



	/**
	 * Crops the image
	 *
	 * @param int/string  $width   The new width of the image. It can be a number (pixels) or percentaje
	 * @param int/string  $height  The new height of the image. It can be a number (pixels) or percentaje
	 * @param int/string  $x       The "x" position where start to crop. It can be number (pixels), percentaje or one of the available keywords (left,center,right)
	 * @param int/string  $y       The "y" position where start to crop. It can be number (pixels), percentaje or one of the available keywords (top,middle,bottom)
	 *
	 * @return $this
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

		if (strtolower($this->image->getImageFormat()) === 'gif') {
			$this->image = $this->image->coalesceImages();

			foreach ($this->image as $frame) {
				$frame->cropImage($width, $height, $x, $y);
			}

			$this->image = $this->image->deconstructImages();
		} else {
			if ($this->image->cropImage($width, $height, $x, $y) !== true) {
				$this->setError('There was an error cropping the image', IMAGECOW_ERROR_FUNCTION);
			} else {
				$this->image->setImagePage(0, 0, 0, 0);
			}
		}

		return $this;
	}


	/**
	 * Rotates the image
	 *
	 * @param int  $angle   Rotation angle in degrees (anticlockwise)
	 *
	 * @return $this
	 */
	public function rotate ($angle) {
		$angle = intval($angle);

		if (!$this->image || $angle === 0) {
			return $this;
		}

		if ($this->image->rotateImage(new ImagickPixel(), $angle) !== true) {
			$this->setError('There was an error rotating the image', IMAGECOW_ERROR_FUNCTION);
		} else {
			$this->image->setImagePage(0, 0, 0, 0);
		}

		return $this;
	}
}
?>