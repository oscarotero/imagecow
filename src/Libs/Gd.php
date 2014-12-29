<?php
/**
 * Imagecow PHP library
 *
 * GD library
 *
 * PHP version 5.3
 */

namespace Imagecow\Libs;

use Imagecow\ImageException;

class Gd extends BaseLib implements LibInterface
{
    protected $image;
    protected $type;


    /**
     * {@inheritDoc}
     */
    public static function createFromFile ($filename)
    {
        $data = @getImageSize($filename);
        if ($data && is_array($data)) {
            $function = 'imagecreatefrom'.image_type_to_extension($data[2], false);

            if (function_exists($function)) {
                return new static($function($filename), $data[2]);
            }
        }

        throw new ImageException("The image file '{$filename}' cannot be loaded");
    }


    /**
     * {@inheritDoc}
     */
    public static function createFromString ($string)
    {
        if (($image = @imagecreatefromstring($string))) {
            return new static($image);
        }

        throw new ImageException('Error creating the image from string');
    }


    /**
     * Constructor of the class
     *
     * @param resource $image The Gd resource.
     */
    public function __construct($image, $type = null)
    {
        $this->image = $image;
        $this->type = isset($type) ? $type : IMAGETYPE_PNG;

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
    }


    /**
     * Destroy the image
     */
    public function __destruct()
    {
        imagedestroy($this->image);
    }


    /**
     * {@inheritDoc}
     */
    public function flip()
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $image = $this->createImage($width, $height, array(0, 0, 0, 127));

        if (imagecopyresampled($image, $this->image, 0, 0, 0, ($height - 1), $width, $height, $width, -$height) === false) {
            throw new ImageException('Error flipping the image');
        }

        $this->image = $image;
    }


    /**
     * {@inheritDoc}
     */
    public function flop()
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $image = $this->createImage($width, $height, array(0, 0, 0, 127));

        if (imagecopyresampled($image, $this->image, 0, 0, ($width - 1), 0, $width, $height, -$width, $height) === false) {
            throw new ImageException('Error flopping the image');
        }

        $this->image = $image;
    }


    /**
     * Creates a new truecolor image
     *
     * @param integer $width
     * @param integer $height
     * @param array   $background
     *
     * @return resource
     */
    private function createImage ($width, $height, array $background = array(0, 0, 0))
    {
        if (($image = imagecreatetruecolor($width, $height)) === false) {
            throw new ImageException('Error creating a image');
        }

        if (imagesavealpha($image, true) === false) {
            throw new ImageException('Error saving the alpha chanel of the image');
        }

        if (isset($background[3])) {
            $background = imagecolorallocatealpha($image, $background[0], $background[1], $background[2], $background[3]);
        } else {
            $background = imagecolorallocate($image, $background[0], $background[1], $background[2]);
        }

        if (imagefill($image, 0, 0, $background) === false) {
            throw new ImageException('Error filling the image');
        }

        return $image;
    }


    /**
     * {@inheritDoc}
     */
    public function save($filename)
    {
        $extension = image_type_to_extension($this->type, false);
        $function = 'image'.$extension;

        if (!function_exists($function) || ($function($this->image, $filename) === false)) {
            throw new ImageException("The image format '{$extension}' cannot be saved to '{$filename}'");
        }
    }


    /**
     * {@inheritDoc}
     */
    public function getString()
    {
        $extension = image_type_to_extension($this->type, false);
        $function = 'image'.$extension;

        if (!function_exists($function)) {
            throw new ImageException("The image format '{$extension}' cannot be exported");
        }

        ob_start();

        if ($extension === 'jpeg') {
            $function($this->image, null, $this->quality);
        } else {
            $function($this->image);
        }

        return ob_get_clean();
    }


    /**
     * {@inheritDoc}
     */
    public function getMimeType()
    {
        return image_type_to_mime_type($this->type);
    }


    /**
     * {@inheritDoc}
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }


    /**
     * {@inheritDoc}
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }


    /**
     * {@inheritDoc}
     */
    public function format($format)
    {
        switch (strtolower($format)) {
            case 'jpg':
            case 'jpeg':
                $width = $this->getWidth();
                $height = $this->getHeight();
                $image = $this->createImage($width, $height, $this->background);

                imagecopy($image, $this->image, 0, 0, 0, 0, $width, $height);

                $this->image = $image;
                $this->type = IMAGETYPE_JPEG;
                break;

            case 'gif':
                $this->type = IMAGETYPE_GIF;
                break;

            case 'png':
                $this->type = IMAGETYPE_PNG;
                break;

            default:
                throw new ImageException("The image format '{$format}' is not valid");
        }
    }


    /**
     * {@inheritDoc}
     */
    public function resize($width, $height)
    {
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        if ($width !== 0 && ($height === 0 || ($imageWidth/$width) > ($imageHeight/$height))) {
            $height = ceil(($width/$imageWidth) * $imageHeight);
        } else {
            $width = ceil(($height/$imageHeight) * $imageWidth);
        }

        if (($imageWidth !== $width) || ($imageHeight !== $height)) {
            $image = $this->createImage($width, $height, array(0, 0, 0, 127));

            if (imagecopyresampled($image, $this->image, 0, 0, 0, 0, $width, $height, $imageWidth, $imageHeight) === false) {
                throw new ImageException('There was an error resizing the image');
            }

            $this->image = $image;
        }
    }


    /**
     * {@inheritDoc}
     */
    public function crop($width, $height, $x, $y)
    {
        $image = $this->createImage($width, $height, ($this->type === IMAGETYPE_JPEG) ? $this->background : array(0, 0, 0, 127));

        if (imagecopyresampled($image, $this->image, 0, 0, $x, $y, $width + $x, $height + $y, $width + $x, $height + $y) === false) {
            throw new ImageException('There was an error cropping the image');
        }

        $this->image = $image;
    }


    /**
     * {@inheritDoc}
     */
    public function rotate($angle)
    {
        $background = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

        if ($background === false || ($image = imagerotate($this->image, $angle, $background)) === false) {
            throw new ImageException('There was an error rotating the image');
        }

        $this->image = $image;
    }
}
