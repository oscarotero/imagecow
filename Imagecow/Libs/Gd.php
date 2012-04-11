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
	protected $info;

	private static $image_types = array('gif', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff_ii', 'tiff_mm', 'jpc', 'jp2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm', 'ico');



	/**
	 * public function load (string $image)
	 *
	 * Loads an image
	 * Returns this
	 */
	public function load ($image) {
		if ($data = @getImageSize($image)) {
			$extension = image_type_to_extension($data[2], false);
			$function = 'imagecreatefrom'.$extension;

			if (function_exists($function)) {
				$this->image = $function($image);

				imagealphablending($this->image, true);
				imagesavealpha($this->image, true);

				$this->info = array(
					'file' => $image,
					'type' => $data[2],
					'mime' => $data['mime'],
					'format' => $extension
				);
			}
		} else {
			$this->info = null;
		}

		return $this;
	}



	/**
	 * public function unload (void)
	 *
	 * Destroys an image
	 * Return this
	 */
	public function unload () {
		imagedestroy($this->image);

		return $this;
	}



	/**
	 * public function save (string $filename)
	 *
	 * Saves the image into a file
	 * Returns this
	 */
	public function save ($filename = '') {
		$function = 'image'.$this->info['format'];

		if (function_exists($function)) {
			$filename = $filename ? $filename : $this->info['file'];

			$function($this->image, $filename);
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
		if (!$this->info) {
			return '';
		}

		$function = 'image'.$this->info['format'];

		if (function_exists($function)) {
			ob_start();
			$function($this->image);
			return ob_get_clean();
		}
	}


	/**
	 * public function getMimeType (void)
	 *
	 * Gets the image mime type
	 * Returns string
	 */
	public function getMimeType () {
		return $this->info['mime'];
	}


	/**
	 * public function getWidth (void)
	 *
	 * Gets the image width
	 * Returns integer
	 */
	public function getWidth () {
		if (!$this->image) {
			return 0;
		}

		return imagesx($this->image);
	}


	/**
	 * public function getHeight (void)
	 *
	 * Gets the image height
	 * Returns integer
	 */
	public function getHeight () {
		if (!$this->image) {
			return 0;
		}

		return imagesy($this->image);
	}



	/**
	 * public function convert (string $format)
	 *
	 * Converts an image to another format
	 * Returns this
	 */
	public function convert ($format) {
		if (!$this->info || ($type = array_search($format, selft::$image_types)) === false) {
			return $this;
		}

		$type++;

		$this->info['type'] = $type;
		$this->info['mime'] = image_type_to_mime_type($type);
		$this->info['format'] = image_type_to_extension($type, false);

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

		imagesavealpha($tmp_image, true);
		imagefill($tmp_image, 0, 0, imagecolorallocatealpha($tmp_image, 0, 0, 0, 127));
		imagecopyresampled($tmp_image, $this->image, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight);

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

		imagesavealpha($tmp_image, true);
		imagefill($tmp_image, 0, 0, $background);
		imagecopyresampled($tmp_image, $this->image, 0, 0, $x, $y, $width + $x, $height + $y, $width + $x, $height + $y);
		imagefill($tmp_image, 0, 0, $background);

		$this->image = $tmp_image;

		return $this;
	}
}
?>