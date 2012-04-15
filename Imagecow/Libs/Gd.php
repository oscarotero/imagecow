<?php
/**
 * Imagecow PHP library
 *
 * GD library
 * Original code from phpCan Image class (http://idc.anavallasuiza.com/)
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.4 (2012)
 */

namespace Imagecow\Libs;

use Imagecow\Image;

class Gd extends Image implements InterfaceLibs {
	protected $type;
	protected $filename;


	/**
	 * Constructor of the class
	 *
	 * @param string/resource  $image  The string with the filename to load or the Gd resource.
	 */
	public function __construct ($image = null) {
		if (isset($image)) {
			if (is_resource($image)) {
				$this->setImage($image);
			} else if (is_string($image)) {
				$this->load($image);
			}
		}
	}



	/**
	 * Load an image file
	 *
	 * @param string  $image  Name of the file to load
	 *
	 * @return $this
	 */
	public function load ($image) {
		$this->image = $this->file = $this->type = null;

		if (is_file($image) && ($data = @getImageSize($image))) {
			$function = 'imagecreatefrom'.image_type_to_extension($data[2], false);

			if (function_exists($function)) {
				return $this->setImage($function($image), $data[2]);
			}
		}
		
		$this->setError('The image file "'.$image.'" cannot be loaded', IMAGECOW_ERROR_LOADING);

		return $this;
	}



	/**
	 * Destroy the image loaded
	 *
	 * @return $this
	 */
	public function unload () {
		if ($this->image) {
			imagedestroy($this->image);
		}

		return $this;
	}



	/**
	 * Sets a new GD resource
	 *
	 * @param resource  $image  The GD resource
	 * @param int       $type   The image type. By default is IMAGETYPE_PNG
	 *
	 * @return $this
	 */
	public function setImage ($image, $type = null) {
		if (is_resource($image)) {
			$this->image = $image;
			$this->file = null;
			$this->type = isset($type) ? $type : IMAGETYPE_PNG;

			imagealphablending($this->image, true);
			imagesavealpha($this->image, true);
		} else {
			$this->image = $this->file = $this->type = null;

			$this->setError('The image is not a valid resource', IMAGECOW_ERROR_LOADING);
		}

		return $this;
	}



	/**
	 * Save the image in a file
	 *
	 * @param string  $filename  Name of the file where the image will be saved. If it's not defined, The original file will be overwritten.
	 *
	 * @return $this
	 */
	public function save ($filename = '') {
		if (!$this->image) {
			return $this;
		}

		$extension = image_type_to_extension($this->type, false);

		$function = 'image'.$extension;

		if (function_exists($function)) {
			$filename = $filename ? $filename : $this->file;

			if (strpos($filename, '.') === false) {
				$filename .= '.'.$extension;
			}

			if ($function($this->image, $filename) === false) {
				$this->setError('The image file "'.$filename.'" cannot be saved', IMAGECOW_ERROR_LOADING);
			}
		} else {
			$this->setError('The image format "'.$extension.'" cannot be exported', IMAGECOW_ERROR_LOADING);
		}

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

		$extension = image_type_to_extension($this->type, false);

		$function = 'image'.$extension;

		if (!function_exists($function)) {
			$this->setError('The image format "'.$extension.'" cannot be exported', IMAGECOW_ERROR_FUNCTION);
			return '';
		}

		ob_start();
		$function($this->image);
		return ob_get_clean();
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

		return image_type_to_mime_type($this->type);
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

		return imagesx($this->image);
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

		return imagesy($this->image);
	}



	/**
	 * Converts the image to other format
	 *
	 * @param string  $format  The new format: png, jpg, gif
	 *
	 * @return $this
	 */
	public function format ($format) {
		switch (strtolower($format)) {
			case 'jpg':
			case 'jpeg':
				$this->type = IMAGETYPE_JPEG;
				break;

			case 'gif':
				$this->type = IMAGETYPE_GIF;
				break;

			case 'png':
				$this->type = IMAGETYPE_PNG;
				break;

			default:
				$this->setError('The image format "'.$format.'" is not valid', IMAGECOW_ERROR_FUNCTION);
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

		if (!$enlarge && $this->enlarge($width, $height, $imageWidth, $imageHeight)) {
			return $this;
		}

		if (!$width && !$height) {
			return $this;
		}

		if ($width != 0 && ($height == 0 || ($imageWidth/$width) > ($imageHeight/$height))) {
			$height = floor(($width/$imageWidth) * $imageHeight);
		} else {
			$width = floor(($height/$imageHeight) * $imageWidth);
		}

		if ($imageWidth === $width && $imageHeight === $height) {
			return $this;
		}

		$tmp_image = imagecreatetruecolor($width, $height);

		if ($tmp_image === false ||
			imagesavealpha($tmp_image, true) === false ||
			imagefill($tmp_image, 0, 0, imagecolorallocatealpha($tmp_image, 0, 0, 0, 127)) === false || 
			imagecopyresampled($tmp_image, $this->image, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight) === false)
		{
			$this->setError('There was an error resizing the image', IMAGECOW_ERROR_FUNCTION);

			return $this;
		}

		$this->image = $tmp_image;

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

		$tmp_image = imagecreatetruecolor($width, $height);
		$background = imagecolorallocatealpha($tmp_image, 0, 0, 0, 127);

		if ($tmp_image === false ||
			$background === false ||
			imagesavealpha($tmp_image, true) === false ||
			imagefill($tmp_image, 0, 0, $background) === false ||
			imagecopyresampled($tmp_image, $this->image, 0, 0, $x, $y, $width + $x, $height + $y, $width + $x, $height + $y) === false ||
			imagefill($tmp_image, 0, 0, $background) === false)
		{
			$this->setError('There was an error cropping the image', IMAGECOW_ERROR_FUNCTION);

			return $this;
		}

		$this->image = $tmp_image;

		return $this;
	}
}
?>