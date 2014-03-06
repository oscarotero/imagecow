<?php
/**
 * Imagecow PHP library
 *
 * IconExtractor class
 * This class reads an icon file and returns an image resource for each icon image
 *
 * PHP version 5.3
 *
 * @author Oscar Otero <http://oscarotero.com> <oom@oscarotero.com>
 * @license GNU Affero GPL version 3. http://www.gnu.org/licenses/agpl-3.0.html
 * @version 0.0.1 (2013)
 *
 *
 * Original code by Joshua Hatfield:
 * http://www.phpclasses.org/package/3906-PHP-Read-and-write-images-from-ICO-files.html
 *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  Original floIcon copyright (C) 2007 by Joshua Hatfield.                *
 *                                                                         *
 *  In order to use any part of this floIcon Class, you must comply with   *
 *  the license in 'license.doc'.  In particular, you may not remove this  *
 *  copyright notice.                                                      *
 *                                                                         *
 *  Much time and thought has gone into this software and you are          *
 *  benefitting.  We hope that you share your changes too.  What goes      *
 *  around, comes around.                                                  *
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*/

namespace Imagecow\Utils;

class IconExtractor
{
    public $images = array();

    /**
     * The constructor loads an ICO file and save all images in $this->images property
     *
     * @param string $file The path of ico file
     */
    public function __construct($file)
    {
        if (($filePointer = fopen($file, 'r'))) {
            fseek($filePointer, 0);

            $header = unpack('SReserved/SType/SCount', fread($filePointer, 6));

            for ($t = 0; $t < $header['Count']; $t++) {
                $this->images[] = new IconImage($filePointer, 6 + ($t * 16));
            }

            fclose($filePointer);
        }
    }

    /**
     * Return all icon indexes sorted by quality (best quality first)
     *
     * @return array The icon indexes
     */
    public function getSortedIndexes()
    {
        $indexes = array();

        foreach ($this->images as $index => $image) {
            $data = $image->getEntry();

            if (empty($data['BitCount'])) {
                $header = $image->getHeader();
                $data['BitCount'] = $header['BitCount'];
            }

            $indexes[$index] = $data['Width']+$data['BitCount'];
        }

        arsort($indexes);

        return array_keys($indexes);
    }

    /**
     * Return an image resource with the icon stored on the $index position of the ICO file
     *
     * @param integer $index Position of the icon inside ICO
     *
     * @return resource Image resource
     */
    public function get($index)
    {
        return isset($this->images[$index]) ? $this->images[$index]->getImageResource() : null;
    }
}

/**
 * Class to manage each icon image
 */
class IconImage
{
    private $entry = '';
    private $header = '';
    private $headerIconFormat = '';
    private $imageIconFormat = '';

    /**
     * Read a image from an ico file
     *
     * @param resource $filePointer The file pointer of the ico file
     * @param int      $entryOffset The offset where to start to read
     */
    public function __construct($filePointer, $entryOffset)
    {
        $tmpPosition = ftell($filePointer);

        fseek($filePointer, $entryOffset);

        $entryIconFormat = fread($filePointer, 16);
        $this->entry = unpack('CWidth/CHeight/CColorCount/CReserved/SPlanes/SBitCount/LSizeInBytes/LFileOffset', $entryIconFormat);

        fseek($filePointer, $this->entry['FileOffset']);

        $this->headerIconFormat = fread($filePointer, 40);
        $this->header = unpack('LSize/LWidth/LHeight/SPlanes/SBitCount/LCompression/LImageSize/LXpixelsPerM/LYpixelsPerM/LColorsUsed/LColorsImportant', $this->headerIconFormat);

        $this->imageIconFormat = @fread($filePointer, $this->entry['SizeInBytes'] - strlen($this->headerIconFormat));
        fseek($filePointer, $tmpPosition);

        if ($newImage = @imagecreatefromstring($this->headerIconFormat.$this->imageIconFormat)) {
            $this->header = array (
                'Size' => 0,
                'Width' => imagesx($newImage),
                'Height' => imagesy($newImage) * 2,
                'Planes' => 0,
                'BitCount' => 32,
                'Compression' => 0,
                'ImageSize' => strlen($this->imageIconFormat),
                'XpixelsPerM' => 0,
                'YpixelsPerM' => 0,
                'ColorsUsed' => 0,
                'ColorsImportant' => 0,
            );
            imagedestroy($newImage);
        }

        if ($this->entry['Width'] == 0) {
            $this->entry['Width'] = $this->header['Width'];
        }
        if ($this->entry['Height'] == 0) {
            $this->entry['Height'] = $this->header['Height']/2;
        }
    }

    /**
     * Returns the headers of the icon image
     *
     * @return array
     */
    public function getHeader()
    {
        return $this->header;
    }

    /**
     * Returns the entries of the icon image
     *
     * @return array
     */
    public function getEntry()
    {
        return $this->entry;
    }

    /**
     * Return an image resource with the icon image
     *
     * @return resource Image resource
     */
    public function getImageResource()
    {
        if ($newImage = @imagecreatefromstring($this->headerIconFormat.$this->imageIconFormat)) {
            $this->headerIconFormat = '';
            $this->imageIconFormat = $this->headerIconFormat.$this->imageIconFormat;
            imagesavealpha($newImage, true);
            imagealphablending($newImage, false);

            return $newImage;
        }

        if ($this->entry['Height'] <= 1024 && $this->entry['Width'] <= 1024) {
            $newImage = imagecreatetruecolor($this->entry['Width'], $this->entry['Height']);
            imagesavealpha($newImage, true);
            imagealphablending($newImage, false);
            $readPosition = 0;
            $palette = array();

            if ($this->header['BitCount'] < 24) {
                $colorsInPalette = $this->header['ColorsUsed']?$this->header['ColorsUsed']:$this->entry['ColorCount'];

                for ($t = 0; $t < pow(2, $this->header['BitCount']); $t++) {
                    $blue = ord($this->imageIconFormat[$readPosition++]);
                    $green = ord($this->imageIconFormat[$readPosition++]);
                    $red = ord($this->imageIconFormat[$readPosition++]);
                    $readPosition++;
                    $existingPaletteEntry = imagecolorexactalpha($newImage, $red, $green, $blue, 0);

                    if ($existingPaletteEntry >= 0) {
                        $palette[] = $existingPaletteEntry;
                    } else {
                        $palette[] = imagecolorallocatealpha($newImage, $red, $green, $blue, 0);
                    }
                }

                for ($y = 0; $y < $this->entry['Height']; $y++) {
                    $colors = array();

                    for ($x = 0; $x < $this->entry['Width']; $x++) {
                        if ($this->header['BitCount'] < 8) {
                            $color = array_shift($colors);
                            if (is_null($color)) {
                                $byte = ord($this->imageIconFormat[$readPosition++]);
                                $tmp_color = 0;
                                for ($t = 7; $t >= 0; $t--) {
                                    $bit_value = pow(2, $t);
                                    $bit = floor($byte / $bit_value);
                                    $byte = $byte - ($bit * $bit_value);
                                    $tmp_color += $bit * pow(2, $t%$this->header['BitCount']);

                                    if ($t%$this->header['BitCount'] == 0) {
                                        array_push($colors, $tmp_color);
                                        $tmp_color = 0;
                                    }
                                }

                                $color = array_shift($colors);
                            }
                        } else {
                            $color = ord($this->imageIconFormat[$readPosition++]);
                        }

                        imagesetpixel($newImage, $x, $this->entry['Height']-$y-1, $palette[$color]) or die('can\'t set pixel');
                    }

                    if ($readPosition%4) {
                        $readPosition += 4-($readPosition%4);
                    }
                }
            } else {
                $markPosition = $readPosition;
                $retry = true;
                $ignoreAlpha = false;

                while ($retry) {
                    $alphas = array();
                    $retry = false;

                    for ($y = 0; $y < $this->entry['Height'] and !$retry; $y++) {
                        for ($x = 0; $x < $this->entry['Width'] and !$retry; $x++) {
                            $blue = ord($this->imageIconFormat[$readPosition++]);
                            $green = ord($this->imageIconFormat[$readPosition++]);
                            $red = ord($this->imageIconFormat[$readPosition++]);

                            if ($this->header['BitCount'] < 32) {
                                $alpha = 0;
                            } elseif ($ignoreAlpha) {
                                $alpha = 0;
                                $readPosition++;
                            } else {
                                $alpha = ord($this->imageIconFormat[$readPosition++]);
                                $alphas[$alpha] = $alpha;
                                $alpha = 127-round($alpha/255*127);
                            }

                            $paletteEntry = imagecolorexactalpha($newImage, $red, $green, $blue, $alpha);

                            if ($paletteEntry < 0) {
                                $paletteEntry = imagecolorallocatealpha($newImage, $red, $green, $blue, $alpha);
                            }

                            imagesetpixel($newImage, $x, $this->entry['Height']-$y-1, $paletteEntry) or die('can\'t set pixel');
                        }

                        if ($readPosition%4) {
                            $readPosition += 4-($readPosition%4);
                        }
                    }

                    if ($this->header['BitCount'] == 32 && isset($alphas[0]) && count($alphas) == 1) {
                        $retry = true;
                        $readPosition = $markPosition;
                        $ignoreAlpha = true;
                    }
                }

            }

            if ($this->header['BitCount'] < 32 || $ignoreAlpha) {
                $palette[-1] = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
                imagecolortransparent($newImage, $palette[-1]);

                for ($y = 0; $y < $this->entry['Height']; $y++) {
                    $colors = array();

                    for ($x = 0; $x < $this->entry['Width']; $x++) {
                        $color = array_shift($colors);

                        if (is_null($color)) {
                            $byte = ord($this->imageIconFormat[$readPosition++]);
                            $tmp_color = 0;

                            for ($t = 7; $t >= 0; $t--) {
                                $bit_value = pow(2, $t);
                                $bit = floor($byte / $bit_value);
                                $byte = $byte - ($bit * $bit_value);
                                array_push($colors, $bit);
                            }

                            $color = array_shift($colors);
                        }

                        if ($color) {
                            imagesetpixel($newImage, $x, $this->entry['Height']-$y-1, $palette[-1]) or die('can\'t set pixel');
                        }
                    }

                    if ($readPosition%4) {
                        $readPosition += 4-($readPosition%4);
                    }
                }
            }

            if ($this->header['BitCount'] < 24) {
                imagetruecolortopalette($newImage, true, pow(2, $this->header['BitCount']));
            }
        }

        return $newImage;
    }
}
