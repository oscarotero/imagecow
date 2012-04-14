<?php
/**
 * GD library for Imagecow (version 0.3)
 *
 * 2012. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
 * Original code from phpCan Image class (http://idc.anavallasuiza.com/)
 *
 * Imagecow is released under the GNU Affero GPL version 3.
 * More information at http://www.gnu.org/licenses/agpl-3.0.html
 */

namespace Imagecow\Libs;

use Imagecow\Image;

class Gd extends Image implements InterfaceLibs {
	protected $image;
	protected $type;
	protected $filename;

	private static $image_types = array('gif', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff_ii', 'tiff_mm', 'jpc', 'jp2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm', 'ico');



	/**
	 * public function load (string $image)
	 *
	 * Loads an image
	 * Returns this
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
	 * public function setImage (resource $image, [$type])
	 *
	 * Sets a new GD resource
	 * Returns this
	 */
	public function setImage ($image, $type = null) {
		if (is_resource($image)) {
			$this->image = $image;
			$this->file = null;
			$this->type = isset($type) ? $type : IMAGETYPE_JPEG;

			imagealphablending($this->image, true);
			imagesavealpha($this->image, true);
		} else {
			$this->image = $this->file = $this->type = null;

			$this->setError('The image is not a valid resource', IMAGECOW_ERROR_LOADING);
		}

		return $this;
	}



	/**
	 * public function getImage ()
	 *
	 * Gets the GD resource
	 * Returns resource/null
	 */
	public function getImage () {
		return $this->image;
	}



	/**
	 * public function unload (void)
	 *
	 * Destroys an image
	 * Return this
	 */
	public function unload () {
		if ($this->image) {
			imagedestroy($this->image);
		}

		return $this;
	}



	/**
	 * public function save (string $filename)
	 *
	 * Saves the image into a file
	 * Returns this
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
	 * public function toString (void)
	 *
	 * Gets the image data
	 * Returns string
	 */
	public function toString () {
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
	 * public function getMimeType (void)
	 *
	 * Gets the image mime type
	 * Returns string/false
	 */
	public function getMimeType () {
		if (!$this->image) {
			return false;
		}

		return image_type_to_mime_type($this->type);
	}


	/**
	 * public function getWidth (void)
	 *
	 * Gets the image width
	 * Returns integer/false
	 */
	public function getWidth () {
		if (!$this->image) {
			return false;
		}

		return imagesx($this->image);
	}


	/**
	 * public function getHeight (void)
	 *
	 * Gets the image height
	 * Returns integer/false
	 */
	public function getHeight () {
		if (!$this->image) {
			return false;
		}

		return imagesy($this->image);
	}



	/**
	 * public function getImageError ([int $width], [int $height])
	 *
	 * Returns an Image with the error string or null
	 */
	public function getImageError ($width = 400, $height = 400) {
		if (!$this->Error) {
			return null;
		}

		$imageError = imagecreate($width, $height);

		$bgColor = imagecolorallocate($imageError, 128, 128, 128);
		$textColor = imagecolorallocate($imageError, 255, 255, 255);

		foreach (str_split($this->Error->getMessage(), intval($width/10)) as $line => $text) {
			imagestring($imageError, 5, 10, (($line + 1) * 18), $text, $textColor);
		}

		$image = new static();

		return $image->setImage($imageError);
	}



	/**
	 * public function convert (string $format)
	 *
	 * Converts an image to another format
	 * Returns this
	 */
	public function convert ($format) {
		if (!$this->image || ($type = array_search($format, selft::$image_types)) === false) {
			$this->setError('The image format "'.$format.'" is not valid', IMAGECOW_ERROR_FUNCTION);
			return $this;
		}

		$this->type = $type + 1;

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