<?php
/**
 * Imagecow PHP library (version 0.2)
 *
 * 2012. Created by Oscar Otero (http://oscarotero.com / http://anavallasuiza.com)
 * Original code from phpCan Image class (http://idc.anavallasuiza.com/)
 *
 * Imagecow is released under the GNU Affero GPL version 3.
 * More information at http://www.gnu.org/licenses/agpl-3.0.html
 */

namespace Imagecow;

abstract class Image {

	static function create ($library = null) {
		if (!$library) {
			$library = extension_loaded('imagick') ? 'Imagick' : 'Gd';
		}

		$class = 'Imagecow\\Libs\\'.$library;

		if (class_exists($class)) {
			return new $class;
		}
	}


	/**
	 * public function transform ([string $operations])
	 *
	 * Executes a list of operations
	 * Returns this
	 */
	public function transform ($operations = '') {
		if (!$operations) {
			return $this;
		}

		$array_operations = $this->getOperations($operations);

		foreach ($array_operations as $operation) {
			call_user_func_array(array($this, $operation['function']), $operation['params']);
		}

		return $this;
	}



	/**
	 * private function getOperations (array $operations)
	 *
	 * Splits string operations and convert it to array
	 * Returns array
	 */
	private function getOperations ($operations) {
		$return = array();
		$array = explode('|', $operations);

		foreach ($array as $each) {
			$params = explode(',', $each);

			while (empty($params[0]) && (count($params) > 0)) {
				array_shift($params);
			}

			$return[] = array(
				'function' => array_shift($params),
				'params' => $params
			);
		}

		return $return;
	}



	/**
	 * public function get ([string $image])
	 *
	 * Gets the image object
	 * Returns this
	 */
	public function get ($image = '') {
		if ($image) {
			if (!$this->load($image)) {
				return false;
			}
		}

		return $this->image;
	}



	/**
	 * public function set ([object $image])
	 *
	 * Sets the image object
	 * Returns this
	 */
	public function set ($image) {
		$this->image = $image;

		return $this;
	}



	/**
	 * public function show ([bool $header])
	 *
	 * Shows the image and die
	 */
	public function show ($header = true) {

		//Show header mime-type
		if ($header && ($type = $this->getMimeType())) {
			header('Content-Type: '.$type);
		}

		echo $this->toString();

		die();
	}



	/**
	 * protected function position (int/string $position, int $size, int $canvas)
	 *
	 * Calculates the x/y position of the image
	 * Returns integer
	 */
	protected function position ($position, $size, $canvas) {
		if (is_int($position)) {
			return $position;
		}

		switch ($position) {
			case 'top':
			case 'left':
				$position = 0;
				break;

			case 'middle':
			case 'center':
				$position = ($canvas/2) - ($size/2);
				break;

			case 'right':
			case 'bottom':
				$position = $canvas - $size;
				break;

			default:
				$position = $this->getSize($position, $canvas);
		}

		return $position;
	}


	/**
	 * protected function getSize (string $value, int $total_size)
	 *
	 * Calculates a dimmension size
	 * Returns integer
	 */
	protected function getSize ($value, $total_size) {
		if (substr($value, -1) === '%') {
			return ($total_size/100) * intval(substr($value, 0, -1));
		}

		return intval($value);
	}



	/**
	 * protected function enlarge ($width, $height, $image_width, $image_height)
	 *
	 * Calculate if the image must be enlarge or not
	 * Returns boolean
	 */
	protected function enlarge ($width, $height, $image_width, $image_height) {
		if ($width && $width > $image_width) {
			return true;
		}

		if ($height && $height > $image_height) {
			return true;
		}

		return false;
	}
}
?>