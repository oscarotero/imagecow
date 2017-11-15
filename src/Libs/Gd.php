<?php

namespace Imagecow\Libs;

use Imagecow\ImageException;

/**
 * GD library.
 */
class Gd extends AbstractLib implements LibInterface
{
    public static $fallbackCropMethods = [
        'Entropy' => ['center', 'middle'],
        'Balanced' => ['center', 'middle'],
    ];

    protected $image;
    protected $type;

    /**
     * {@inheritdoc}
     */
    public static function checkCompatibility()
    {
        return extension_loaded('gd');
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromFile($filename)
    {
        $data = getImageSize($filename);

        if (empty($data) || !is_array($data)) {
            throw new ImageException("The image file '{$filename}' cannot be loaded");
        }

        $function = 'imagecreatefrom'.image_type_to_extension($data[2], false);

        if (function_exists($function)) {
            return new static($function($filename), $data[2]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public static function createFromString($string)
    {
        if (($image = imagecreatefromstring($string))) {
            return new static($image);
        }

        throw new ImageException('Error creating the image from string');
    }

    /**
     * Constructor of the class.
     *
     * @param resource $image The Gd resource.
     */
    public function __construct($image, $type = null)
    {
        $this->image = $image;
        $this->type = isset($type) ? $type : IMAGETYPE_PNG;

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
        imagesetinterpolation($this->image, IMG_BICUBIC);
    }

    /**
     * Destroy the image.
     */
    public function __destruct()
    {
        imagedestroy($this->image);
    }

    /**
     * {@inheritdoc}
     */
    public function flip()
    {
        imageflip($this->image, IMG_FLIP_VERTICAL);
    }

    /**
     * {@inheritdoc}
     */
    public function flop()
    {
        imageflip($this->image, IMG_FLIP_HORIZONTAL);
    }

    /**
     * {@inheritdoc}
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
     * Gets the original image object.
     *
     * @return resource
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        return image_type_to_mime_type($this->type);
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return imagesx($this->image);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return imagesy($this->image);
    }

    /**
     * {@inheritdoc}
     */
    public function format($format)
    {
        switch (strtolower($format)) {
            case 'jpg':
            case 'jpeg':
                $width = $this->getWidth();
                $height = $this->getHeight();

                if (($image = imagecreatetruecolor($width, $height)) === false) {
                    throw new ImageException('Error creating a image');
                }

                if (imagesavealpha($image, true) === false) {
                    throw new ImageException('Error saving the alpha chanel of the image');
                }

                if (isset($this->background[3])) {
                    $background = imagecolorallocatealpha($image, $this->background[0], $this->background[1], $this->background[2], $this->background[3]);
                } else {
                    $background = imagecolorallocate($image, $this->background[0], $this->background[1], $this->background[2]);
                }

                if (imagefill($image, 0, 0, $background) === false) {
                    throw new ImageException('Error filling the image');
                }

                imagecopy($image, $this->image, 0, 0, 0, 0, $width, $height);

                imagedestroy($this->image);
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
     * {@inheritdoc}
     */
    public function resize($width, $height)
    {
        $mode = ($this->getWidth() < $width) ? IMG_BILINEAR_FIXED : IMG_BICUBIC;

        if (($image = imagescale($this->image, $width, $height, $mode)) === false) {
            throw new ImageException('Error resizing the image');
        }

        imagedestroy($this->image);
        $this->image = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function getCropOffsets($width, $height, $method)
    {
        if (empty(static::$fallbackCropMethods[$method])) {
            throw new ImageException("The crop method '$method' is not available for Gd");
        }

        return static::$fallbackCropMethods[$method];
    }

    /**
     * {@inheritdoc}
     */
    public function crop($width, $height, $x, $y)
    {
        $crop = [
            'width' => $width,
            'height' => $height,
            'x' => $x,
            'y' => $y,
        ];

        if (($image = imagecrop($this->image, $crop)) === false) {
            throw new ImageException('Error cropping the image');
        }

        imagedestroy($this->image);
        $this->image = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function rotate($angle)
    {
        $background = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

        if ($background === false || ($image = imagerotate($this->image, -$angle, $background)) === false) {
            throw new ImageException('Error rotating the image');
        }

        imagedestroy($this->image);
        $this->image = $image;
    }

    /**
     * {@inheritdoc}
     */
    public function blur($loops)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $loops *= 10;

        $this->resize($width / 5, $height / 5);

        for ($x = 0; $x < $loops; $x++) {
            if (($x % 4) === 0) {
                imagefilter($this->image, IMG_FILTER_SMOOTH, -4);
                imagefilter($this->image, IMG_FILTER_BRIGHTNESS, 2);
            }

            imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);
        }

        $this->resize($width, $height);
    }

    /**
     * {@inheritdoc}
     */
    public function watermark(LibInterface $image, $x, $y)
    {
        if (!($image instanceof self)) {
            $image = self::createFromString($image->getString());
        }

        imagecopy($this->image, $image->getImage(), $x, $y, 0, 0, $image->getWidth(), $image->getHeight());
    }

    /**
     * {@inheritdoc}
     */
    public function opacity($opacity)
    {
        if ($opacity >= 100 || $opacity < 0) {
            return;
        }

        $this->format('png');

        $opacity = $opacity / 100;

        $width = $this->getWidth();
        $height = $this->getHeight();

        imagealphablending($this->image, false);

        for ($x = 0; $x < $width; ++$x) {
            for ($y = 0; $y < $height; ++$y) {
                $color = imagecolorat($this->image, $x, $y);
                $alpha = 127 - (($color >> 24) & 0xFF);

                if ($alpha <= 0) {
                    continue;
                }

                $color = ($color & 0xFFFFFF) | ((int) round(127 - $alpha * $opacity) << 24);

                imagesetpixel($this->image, $x, $y, $color);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setProgressive($progressive)
    {
        imageinterlace($this->image, $progressive);
    }
}
