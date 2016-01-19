<?php

namespace Imagecow;

/**
 * Exception class to manage the image errors.
 */
class ImageException extends \Exception
{
    /**
     * Generate an image with the message printed. Use always the Gd library.
     *
     * @param int $width  Width of the image
     * @param int $height Height of the image
     *
     * @return Image
     */
    public function getImage($width = 400, $height = 400)
    {
        $image = imagecreatetruecolor($width, $height);
        $textColor = imagecolorallocate($image, 255, 255, 255);

        foreach (str_split($this->getMessage(), intval($width / 10)) as $line => $text) {
            imagestring($image, 5, 10, (($line + 1) * 18), $text, $textColor);
        }

        return new Image(new Libs\Gd($image));
    }
}
