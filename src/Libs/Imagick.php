<?php

namespace Imagecow\Libs;

use Imagick as BaseImagick;
use ImagickPixel as BaseImagickPixel;
use Imagecow\ImageException;

/**
 * Imagick library.
 */
class Imagick extends AbstractLib implements LibInterface
{
    protected $image;

    /**
     * {@inheritdoc}
     */
    public static function checkCompatibility()
    {
        return extension_loaded('imagick');
    }

    /**
     * {@inheritdoc}
     *
     * @return Imagick
     */
    public static function createFromFile($filename)
    {
        $imagick = new BaseImagick();

        if ($imagick->readImage($filename) !== true) {
            throw new ImageException("The image file '{$filename}' cannot be loaded");
        }

        return new static($imagick);
    }

    /**
     * {@inheritdoc}
     *
     * @return Imagick
     */
    public static function createFromString($string)
    {
        $imagick = new BaseImagick();

        $imagick->readImageBlob($string);

        return new static($imagick);
    }

    /**
     * Constructor of the class.
     *
     * @param BaseImagick $image The Imagick instance
     */
    public function __construct(BaseImagick $image)
    {
        $this->image = $image;

        //Convert CMYK to RGB
        if ($this->image->getImageColorspace() !== BaseImagick::COLORSPACE_CMYK) {
            return $this;
        }

        $profiles = $this->image->getImageProfiles('*', false);

        if (array_search('icc', $profiles) === false) {
            $this->image->profileImage('icc', file_get_contents(__DIR__.'/icc/us_web_uncoated.icc'));
        }

        $this->image->profileImage('icm', file_get_contents(__DIR__.'/icc/srgb.icm'));
        $this->image->transformImageColorspace(BaseImagick::COLORSPACE_SRGB);
    }

    /**
     * Destroy the image.
     */
    public function __destruct()
    {
        $this->image->destroy();
    }

    /**
     * {@inheritdoc}
     */
    public function flip()
    {
        if ($this->image->flipImage() !== true) {
            throw new ImageException('There was an error on flip the image');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function flop()
    {
        if ($this->image->flopImage() !== true) {
            throw new ImageException('There was an error on flop the image');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function save($filename)
    {
        $image = $this->getCompressed();

        if ($this->animated) {
            if (!($fp = fopen($filename, 'w'))) {
                throw new ImageException("The image file '{$filename}' cannot be saved");
            }

            $image->writeImagesFile($fp);

            fclose($fp);
        } elseif (!$image->writeImage($filename)) {
            throw new ImageException("The image file '{$filename}' cannot be saved");
        }
    }

    /**
     * Gets the original image object.
     *
     * @return object
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
        $image = $this->getCompressed();

        if (!$this->animated) {
            return $image->getImageBlob();
        }

        if (!($fp = fopen($file = tempnam(sys_get_temp_dir(), 'imagick'), 'w'))) {
            throw new ImageException('Cannot create a temp file to generate the string data image');
        }

        $image->writeImagesFile($fp);

        fclose($fp);

        $string = file_get_contents($file);

        unlink($file);

        return $string;
    }

    /**
     * {@inheritdoc}
     */
    public function getMimeType()
    {
        $format = strtolower($this->image->getImageFormat());

        if (in_array($format, ['jpeg', 'jpg', 'gif', 'png'], true)) {
            return "image/$format";
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        if ($this->animated) {
            return $this->image->coalesceImages()->getImageWidth();
        } else {
            return $this->image->getImageWidth();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        if ($this->animated) {
            return $this->image->coalesceImages()->getImageHeight();
        } else {
            return $this->image->getImageHeight();
        }
    }

    /**
     * {@inheritdoc}
     */
    public function format($format)
    {
        if (preg_match('/jpe?g/i', $format)) {
            list($r, $g, $b) = $this->background;

            $this->image->setImageBackgroundColor("rgb($r,$g,$b)");
            $this->image = $this->image->mergeImageLayers(BaseImagick::LAYERMETHOD_FLATTEN);
        }

        if ($this->image->setImageFormat($format) !== true) {
            throw new ImageException("The image format '{$format}' is not valid");
        }
    }

    /**
     * {@inheritdoc}
     */
    public function resize($width, $height)
    {
        if ($this->animated) {
            $this->image = $this->image->coalesceImages();

            foreach ($this->image as $frame) {
                $frame->scaleImage($width, $height);
            }

            $this->image = $this->image->deconstructImages();
        } else {
            if ($this->image->scaleImage($width, $height) !== true) {
                throw new ImageException('There was an error resizing the image');
            }

            $this->image->setImagePage(0, 0, 0, 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getCropOffsets($width, $height, $method)
    {
        $class = 'Imagecow\\Crops\\'.ucfirst(strtolower($method));

        if (!class_exists($class)) {
            throw new ImageException("The crop method '$method' is not available for Imagick");
        }

        return $class::getOffsets($this->image, $width, $height);
    }

    /**
     * {@inheritdoc}
     */
    public function crop($width, $height, $x, $y)
    {
        if ($this->animated) {
            $this->image = $this->image->coalesceImages();

            foreach ($this->image as $frame) {
                $frame->cropImage($width, $height, $x, $y);
                $frame->setImagePage(0, 0, 0, 0);
            }

            $this->image = $this->image->deconstructImages();
        } else {
            if ($this->image->cropImage($width, $height, $x, $y) !== true) {
                throw new ImageException('There was an error cropping the image');
            }

            $this->image->setImagePage(0, 0, 0, 0);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function rotate($angle)
    {
        if ($this->image->rotateImage(new BaseImagickPixel(), $angle) !== true) {
            throw new ImageException('There was an error rotating the image');
        }

        $this->image->setImagePage(0, 0, 0, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function blur($loops)
    {
        $width = $this->getWidth();
        $height = $this->getHeight();

        $this->resize($width / 5, $height / 5);

        for ($i = 0; $i < $loops; $i++) {
            $this->image->blurImage(5, 100);
        }

        $this->resize($width, $height);

        $this->image->blurImage(10, 100);
    }

    /**
     * Returns a copy of the image compressed and ready to save or print.
     *
     * @return BaseImagick The instance of the image
     */
    private function getCompressed()
    {
        $image = $this->image;

        if ($this->animated) {
            $image = $image->coalesceImages();

            foreach ($image as $frame) {
                $frame->stripImage();
                $frame->setImageUnits(1);
                $frame->setImageCompressionQuality($this->quality);
            }

            return $image->deconstructImages();
        }

        $format = strtolower($image->getImageFormat());

        $image->stripImage();
        $image->setImageUnits(1);
        $image->setImageCompressionQuality($this->quality);

        switch ($format) {
            case 'jpeg':
                $image->setInterlaceScheme(BaseImagick::INTERLACE_JPEG);
                $image->setImageCompression(BaseImagick::COMPRESSION_JPEG);
                break;

            case 'gif':
                $image->setInterlaceScheme(BaseImagick::INTERLACE_GIF);
                break;

            case 'png':
                $image->setInterlaceScheme(BaseImagick::INTERLACE_PNG);
                break;
        }

        return $image;
    }

    /**
     * {@inheritdoc}
     */
    public function watermark(LibInterface $image, $x, $y)
    {
        if (!($image instanceof self)) {
            $image = self::createFromString($image->getString());
        }

        $this->image->compositeImage($image->getImage(), BaseImagick::COMPOSITE_DISSOLVE, $x, $y);
    }

    /**
     * {@inheritdoc}
     */
    public function opacity($opacity)
    {
        if ($opacity >= 100 || $opacity < 0) {
            return;
        }

        if ($this->image->getImageAlphaChannel() !== BaseImagick::ALPHACHANNEL_ACTIVATE) {
            $this->image->setImageAlphaChannel(BaseImagick::ALPHACHANNEL_OPAQUE);
        }

        // NOTE: Using setImageOpacity will destroy current alpha channels!
        $this->image->evaluateImage(BaseImagick::EVALUATE_MULTIPLY, $opacity / 100, BaseImagick::CHANNEL_ALPHA);
    }

    /**
     * {@inheritdoc}
     */
    public function setProgressive($progressive)
    {
        if ($progressive) {
            $this->image->setInterlaceScheme(BaseImagick::INTERLACE_PLANE);
        } else {
            $this->image->setInterlaceScheme(BaseImagick::INTERLACE_NO);
        }
    }
}
