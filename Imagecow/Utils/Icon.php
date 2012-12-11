<?php
/**
 * Imagecow PHP library
 *
 * Ico extractor library
 * Original code from Diogo Resende (http://www.phpclasses.org/package/2369-PHP-Extract-graphics-from-ico-files-into-PNG-images.html)
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.0.1 (2012)
 */

namespace Imagecow\Utils;

class Icon {
	public $formats = array();


	/**
	 * Allocate a color on $im resource
	 * 
 	 * @param               resource    $im       Image resource
	 * @param               integer     $red      Red component
	 * @param               integer     $green    Green component
	 * @param               integer     $blue     Blue component
	 * @param   optional    integer     $alphpa   Alpha channel
	 * 
	 * @return              integer               Color index
	 */
	private static function allocateColor (&$im, $red, $green, $blue, $alpha = 0) {
		$c = imagecolorexactalpha($im, $red, $green, $blue, $alpha);

		if ($c >= 0) {
			return $c;
		}

		return imagecolorallocatealpha($im, $red, $green, $blue, $alpha);
	}


	/**
	 * The constructor loads an ICO file and save all formats in $this->formats property
	 * 
	 * @param string $file The path of ico file
	 */
	public function __construct ($file) {
		if (!($data = file_get_contents($file))) {
			return false;
		}

		//Icon header
		$iconHeader = unpack("SReserved/SType/SCount", $data);
		$data = substr($data, 6);

		//Extract each icon header
		for ($i = 0; $i < $iconHeader['Count']; $i++) {
			$icodata = unpack("CWidth/CHeight/CColorCount/CReserved/SPlanes/SBitCount/LSizeInBytes/LFileOffset", $data);

			$icodata['FileOffset'] -= ($iconHeader['Count'] * 16) + 6;

			if ($icodata['ColorCount'] === 0) {
				$icodata['ColorCount'] = 256;
			}

			$this->formats[] = $icodata;

			$data = substr($data, 16);
		}

		//Extract aditional headers for each extracted icon header
		for ($i = 0, $total = count($this->formats); $i < $total; $i++) {
			$icodata = unpack("LSize/LWidth/LHeight/SPlanes/SBitCount/LCompression/LImageSize/LXpixelsPerM/LYpixelsPerM/LColorsUsed/LColorsImportant", substr($data, $this->formats[$i]['FileOffset']));

			$this->formats[$i]['header'] = $icodata;
			$this->formats[$i]['colors'] = array();

			$this->formats[$i]['BitCount'] = $this->formats[$i]['header']['BitCount'];

			switch ($this->formats[$i]['BitCount']) {
				case 32:
				case 24:
					$length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] * ($this->formats[$i]['BitCount'] / 8);
					$this->formats[$i]['data'] = substr($data, $this->formats[$i]['FileOffset'] + $this->formats[$i]['header']['Size'], $length);
					break;

				case 8:
				case 4:
					$icodata = substr($data, $this->formats[$i]['FileOffset'] + $icodata['Size'], $this->formats[$i]['ColorCount'] * 4);
					$offset = 0;

					for ($j = 0; $j < $this->formats[$i]['ColorCount']; $j++) {
						$this->formats[$i]['colors'][] = array(
							'red' => ord($icodata[$offset]),
							'green' => ord($icodata[$offset + 1]),
							'blue' => ord($icodata[$offset + 2]),
							'reserved' => ord($icodata[$offset + 3])
						);
						$offset += 4;
					}

					$length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] * (1 + $this->formats[$i]['BitCount']) / $this->formats[$i]['BitCount'];
					$this->formats[$i]['data'] = substr($data, $this->formats[$i]['FileOffset'] + ($this->formats[$i]['ColorCount'] * 4) + $this->formats[$i]['header']['Size'], $length);
					break;

				case 1:
					$icodata = substr($data, $this->formats[$i]['FileOffset'] + $icodata['Size'], $this->formats[$i]['ColorCount'] * 4);

					$this->formats[$i]['colors'][] = array(
							'blue' => ord($icodata[0]),
							'green' => ord($icodata[1]),
							'red' => ord($icodata[2]),
							'reserved' => ord($icodata[3])
					);

					$this->formats[$i]['colors'][] = array(
							'blue' => ord($icodata[4]),
							'green' => ord($icodata[5]),
							'red' => ord($icodata[6]),
							'reserved' => ord($icodata[7])
					);

					$length = $this->formats[$i]['header']['Width'] * $this->formats[$i]['header']['Height'] / 8;
					$this->formats[$i]['data'] = substr($data, $this->formats[$i]['FileOffset'] + $this->formats[$i]['header']['Size'] + 8, $length);
					break;
			}

			$this->formats[$i]['data_length'] = strlen($this->formats[$i]['data']);
		}

		return true;
	}

	
	/**
	 * Return an image resource with the icon stored on the $index position of the ICO file
	 *
	 * @param integer $index Position of the icon inside ICO
	 * 
	 * @return resource Image resource
	 */
	public function get ($index, $background = array(255, 255, 255), $transparent = false) {
		if (!isset($this->formats[$index])) {
			return false;
		}

		$im = imagecreatetruecolor($this->formats[$index]['Width'], $this->formats[$index]['Height']);

		$background = self::allocateColor($im, $background[0], $background[1], $background[2]);
		imagefilledrectangle($im, 0 , 0, $this->formats[$index]['Width'], $this->formats[$index]['Height'], $background);

		if ($transparent) {
			imagecolortransparent($im, $background);
		}

		if (in_array($this->formats[$index]['BitCount'], array(1, 4, 8, 24))) {
			if ($this->formats[$index]['BitCount'] != 24) {
				$c = array();

				for ($i = 0; $i < $this->formats[$index]['ColorCount']; $i++) {
					$c[$i] = self::allocateColor($im, $this->formats[$index]['colors'][$i]['red'], $this->formats[$index]['colors'][$i]['green'], $this->formats[$index]['colors'][$i]['blue'], round($this->formats[$index]['colors'][$i]['reserved'] / 255 * 127));
				}
			}

			$width = $this->formats[$index]['Width'];

			if (($width % 32) > 0) {
				$width += (32 - ($this->formats[$index]['Width'] % 32));
			}

			$offset = $this->formats[$index]['Width'] * $this->formats[$index]['Height'] * $this->formats[$index]['BitCount'] / 8;
			$total_bytes = ($width * $this->formats[$index]['Height']) / 8;
			$bits = '';
			$bytes = 0;
			$bytes_per_line = ($this->formats[$index]['Width'] / 8);
			$bytes_to_remove = (($width - $this->formats[$index]['Width']) / 8);

			for ($i = 0; $i < $total_bytes; $i++) {
				$bits .= str_pad(decbin(ord($this->formats[$index]['data'][$offset + $i])), 8, '0', STR_PAD_LEFT);
				$bytes++;

				if ($bytes === $bytes_per_line) {
					$i += $bytes_to_remove;
					$bytes = 0;
				}
			}
		}

		switch ($this->formats[$index]['BitCount']) {
			case 32:
				$offset = 0;

				for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; $i--) {
					for ($j = 0; $j < $this->formats[$index]['Width']; $j++) {
						$color = substr($this->formats[$index]['data'], $offset, 4);

						if (ord($color[3]) > 0) {
							$c = self::allocateColor($im, ord($color[2]), ord($color[1]), ord($color[0]), 127 - round(ord($color[3]) / 255 * 127));
							imagesetpixel($im, $j, $i, $c);
						}

						$offset += 4;
					}
				}
				break;

			case 24:
				$offset = 0;
				$bitoffset = 0;

				for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; $i--) {
					for ($j = 0; $j < $this->formats[$index]['Width']; $j++) {
						if ($bits[$bitoffset] == 0) {
							$color = substr($this->formats[$index]['data'], $offset, 3);
							$c = self::allocateColor($im, ord($color[2]), ord($color[1]), ord($color[0]));
							imagesetpixel($im, $j, $i, $c);
						}

						$offset += 3;
						$bitoffset++;
					}
				}
				break;

			case 8:
				$offset = 0;

				for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; $i--) {
					for ($j = 0; $j < $this->formats[$index]['Width']; $j++) {
						if ($bits[$offset] == 0) {
							$color = ord(substr($this->formats[$index]['data'], $offset, 1));
							imagesetpixel($im, $j, $i, $c[$color]);
						}

						$offset++;
					}
				}
				break;

			case 4:
				$offset = 0;
				$maskoffset = 0;
				$leftbits = true;

				for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; $i--) {
					for ($j = 0; $j < $this->formats[$index]['Width']; $j++) {
						if ($leftbits) {
							$color = substr($this->formats[$index]['data'], $offset, 1);
							$color = array(
								'High' => bindec(substr(decbin(ord($color)), 0, 4)),
								'Low' => bindec(substr(decbin(ord($color)), 4))
							);

							if ($bits[$maskoffset++] == 0) {
								imagesetpixel($im, $j, $i, $c[$color['High']]);
							}

							$leftbits = false;
						} else {
							if ($bits[$maskoffset++] == 0) {
								imagesetpixel($im, $j, $i, $c[$color['Low']]);
							}

							$offset++;
							$leftbits = true;
						}
					}
				}
				break;

			case 1:
				$colorbits = '';
				$total = strlen($this->formats[$index]['data']);

				for ($i = 0; $i < $total; $i++) {
					$colorbits .= str_pad(decbin(ord($this->formats[$index]['data'][$i])), 8, '0', STR_PAD_LEFT);
				}
				
				$total = strlen($colorbits);
				$offset = 0;

				for ($i = $this->formats[$index]['Height'] - 1; $i >= 0; $i--) {
					for ($j = 0; $j < $this->formats[$index]['Width']; $j++) {
						if ($bits[$offset] == 0) {
							imagesetpixel($im, $j, $i, $c[$colorbits[$offset]]);
						}

						$offset++;
					}
				}
				break;
		}

		return $im;
	}
}
?>
