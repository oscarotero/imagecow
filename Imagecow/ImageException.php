<?php
/**
 * Imagecow PHP library
 *
 * Exception class to manage the image errors
 *
 * PHP version 5.3
 */

namespace Imagecow;

class ImageException extends \Exception
{
    /**
     * Generate an image with the message printed. Use alwais the Gd library.
     *
     * @param int $width  Width of the image. By default 400px
     * @param int $height Height of the image. By default 400px
     *
     * @return Libs\Gd The Imagecow instance with the image
     */
    public function getImage($width = 400, $height = 400)
    {
        $imageError = imagecreate($width, $height);
        $textColor = imagecolorallocate($imageError, 255, 255, 255);

        foreach (str_split($this->getMessage(), intval($width/10)) as $line => $text) {
            imagestring($imageError, 5, 10, (($line + 1) * 18), $text, $textColor);
        }

        return new Libs\Gd($imageError);
    }
}
