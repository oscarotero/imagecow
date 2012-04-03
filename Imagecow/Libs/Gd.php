<?php
/**
 * GD library for Imagecow (version 0.2)
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

	private $image_types = array('gif', 'jpeg', 'png', 'swf', 'psd', 'bmp', 'tiff_ii', 'tiff_mm', 'jpc', 'jp2', 'jpx', 'jb2', 'swc', 'iff', 'wbmp', 'xbm', 'ico');



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

				$this->info = array(
					'file' => $image,
					'width' => $data[0],
					'height' => $data[1],
					'type' => $data[2],
					'mime' => $data['mime'],
					'format' => $extension,
				);
			}
		} else {
			$this->info = false;
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
	 * public function getInfo (void)
	 *
	 * Gets the image info
	 * Returns array
	 */
	public function getInfo () {
		return $this->info;
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
	 * public function resize (int $width, [int $height], [bool $enlarge])
	 *
	 * Resizes an image
	 * Returns this
	 */
	public function resize ($width, $height = 0, $enlarge = false) {
		if (!$this->info) {
			return false;
		}

		$width = $this->getSize($width, $this->info['width']);
		$height = $this->getSize($height, $this->info['height']);

		if (!$enlarge && $this->enlarge($width, $height, $this->info['width'], $this->info['height'])) {
			return $this;
		}

		if (!$width && !$height) {
			return false;
		}

		if ($width != 0 && ($height == 0 || ($this->info['width']/$width) > ($this->info['height']/$height))) {
			$new_width = $width;
			$new_height = floor(($width/$this->info['width']) * $this->info['height']);
		} else {
			$new_width = floor(($height/$this->info['height']) * $this->info['width']);
			$new_height = $height;
		}

		$tmp_image = imagecreatetruecolor($new_width, $new_height);

		imagecopyresampled($tmp_image, $this->image, 0, 0, 0, 0, $new_width, $new_height, $this->info['width'], $this->info['height']);

		$this->image = $tmp_image;

		$this->info['width'] = $new_width;
		$this->info['height'] = $new_height;

		return $this;
	}



	/**
	 * public function crop (int $width, int $height, [int $x], [int $y])
	 *
	 * Crops an image
	 * Returns this
	 */
	public function crop ($width, $height, $x = 'center', $y = 'middle') {
		if (!$this->info) {
			return false;
		}

		$width = $this->getSize($width, $this->info['width']);
		$height = $this->getSize($height, $this->info['height']);

		$x = $this->position($x, $width, $this->info['width']);
		$y = $this->position($y, $height, $this->info['height']);

		$tmp_image = imagecreatetruecolor($width, $height);

		imagecopyresampled($tmp_image, $this->image, 0, 0, $x, $y, $this->info['width'], $this->info['height'], $this->info['width'], $this->info['height']);

		$this->image = $tmp_image;

		$this->info['width'] = $width;
		$this->info['height'] = $height;

		return $this;
	}



	/**
	 * public function flip (void)
	 *
	 * Inverts an image vertically
	 * Returns this
	 */
	public function flip () {
		if (!$this->info) {
			return false;
		}

		$tmp_image = imagecreatetruecolor($this->info['width'], $this->info['height']);

		imagecopyresampled($tmp_image, $this->image, 0, 0, 0, ($this->info['height'] - 1), $this->info['width'], $this->info['height'], $this->info['width'], -$this->info['height']);

		$this->image = $tmp_image;

		return $this;
	}



	/**
	 * public function flop (void)
	 *
	 * Inverts an image horizontally
	 * Returns this
	 */
	public function flop () {
		if (!$this->info) {
			return false;
		}

		$tmp_image = imagecreatetruecolor($this->info['width'], $this->info['height']);

		imagecopyresampled($tmp_image, $this->image, 0, 0, ($this->info['width'] - 1), 0, $this->info['width'], $this->info['height'], -$this->info['width'], $this->info['height']);

		$this->image = $tmp_image;

		return $this;
	}



	/**
	 * public function zoomCrop (int $width, int $height, [int $x], [int $y])
	 *
	 * Crops an resize an image to specific dimmensions
	 * Returns this
	 */
	public function zoomCrop ($width, $height, $x = 'center', $y = 'middle') {
		$width = $this->getSize($width, $this->info['width']);
		$height = $this->getSize($height, $this->info['height']);

		if (($width == 0) || ($height == 0) || !$this->info) {
			return false;
		}

		$width_resize = ($width / $this->info['width']) * 100;
		$height_resize = ($height / $this->info['height']) * 100;

		if ($width_resize < $height_resize) {
			$this->resize(0, $height);
		} else {
			$this->resize($width, 0);
		}

		$this->crop($width, $height, $x, $y);

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
			return false;
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
	 * public function rotate (int $degrees, [string $background])
	 *
	 * Rotates an image
	 * Returns this
	 */
	public function rotate ($degrees, $background = null) {
		if (!$this->info) {
			return false;
		}

		$background = explode('-', $background);
		$transparent = false;

		if ($background[0] == 'transparent') {
			$transparent = true;
			$bg_color = $background[1] ? $background[1] : 'FFFFFF';

			if ($this->info['format'] != 'gif' || $this->info['format'] != 'png') {
				$this->convert('png');
			}
		} else {
			$bg_color = $background[0];
		}

		$bg_color = hexdec($bg_color);

		$this->image = imagerotate($this->image, $degrees, $bg_color);

		if ($transparent) {
			$background = imagecolorat($this->image, 0, 0);
			imagecolortransparent($this->image, $background);
		}

		$this->info['width'] = imagesx($this->image);
		$this->info['height'] = imagesy($this->image);

		return $this;
	}



	/**
	 * public function merge (string/object $image, [int $x], [int $y])
	 *
	 * Merges two images in one
	 * Returns this
	 */
	public function merge ($image, $x = 'center', $y = 'middle') {
		if (!$this->info) {
			return false;
		}

		if (!is_resource($image)) {
			if ($data = @getImageSize($image)) {
				$extension = image_type_to_extension($data[2], false);
				$function = 'imagecreatefrom'.$extension;

				if (function_exists($function)) {
					$image = $function($image);
				}
			}
		}

		$width = imagesx($image);
		$height = imagesy($image);

		if ($this->info['width'] > $width) {
			$width = $this->info['width'];
		}
		if ($this->info['height'] > $height) {
			$height = $this->info['height'];
		}

		$x = $this->position($x, $width, $this->info['width']);
		$y = $this->position($y, $height, $this->info['height']);

		$tmp_image = imagecreatetruecolor($width, $height);

		imagecopymerge($tmp_image, $this->image, 0, 0, 0, 0, $this->info['width'], $this->info['height'], 100);
		imagecopy($tmp_image, $image, 0, 0, 0, 0, $width, $height);

		$this->image = $tmp_image;

		$this->info['width'] = $width;
		$this->info['height'] = $height;

		return $this;
	}



	/**
	 * public function convert (string $format)
	 *
	 * Converts an image to another format
	 * Returns this
	 */
	public function convert ($format) {
		if (!$this->info) {
			return false;
		}

		$type = array_search($format, $this->image_types);

		if ($type === false) {
			return $this;
		}

		$type++;

		$this->info['type'] = $type;
		$this->info['mime'] = image_type_to_mime_type($type);
		$this->info['format'] = image_type_to_extension($type, false);

		return $this;
	}



	/**
	 * public function alpha (string $file)
	 *
	 * Applies an alpha mask to image
	 * Returns this
	 */
	public function alpha ($file) {
		if (!$this->info) {
			return false;
		}

		$file = pathinfo($file);

		if (!$file['dirname']) {
			$file['dirname'] = pathinfo($this->info['file'], PATHINFO_DIRNAME);
		}

		$mask_file = $file['dirname'].'/'.$file['basename'];

		if (!is_file($mask_file) || !is_readable($mask_file)) {
			//return false;
		}

		$file = fopen($mask_file, 'rb');
		$mask_data = '';

		do {
			$buffer = fread($file, 8192);
			$mask_data .= $buffer;
		} while (strlen($buffer) > 0);

		fclose($fp_mask);

		$mask = ImageCreateFromString($mask_data);

		if ($this->info['format'] != 'gif' || $this->info['format'] != 'png') {
			$this->convert('png');
		}

		return $this;
	}
}
?>