<?php
/**
 * Imagecow PHP library
 *
 * Imagick library
 *
 * PHP version 5.3
 */

namespace Imagecow\Libs;

use Imagecow\ImageException;

class Imagick extends BaseLib implements LibInterface
{
    protected $image;


    /**
     * {@inheritDoc}
     *
     * @return \Imagick
     */
    public static function createFromFile ($filename)
    {
        $imagick = new \Imagick();

        if ($imagick->readImage($filename) !== true) {
            throw new ImageException("The image file '{$filename}' cannot be loaded");
        }

        return new static($imagick);
    }


    /**
     * {@inheritDoc}
     *
     * @return \Imagick
     */
    public static function createFromString ($string)
    {
        $imagick = new \Imagick();

        $imagick->readImageBlob($string);

        return new static($imagick);
    }


    /**
     * Constructor of the class
     *
     * @param \Imagick $image The Imagick instance
     */
    public function __construct(\Imagick $image)
    {
        //Convert CMYK to RGB
        if (method_exists($image, 'getImageProfiles') && ($image->getImageColorspace() === \Imagick::COLORSPACE_CMYK)) {
            $profiles = $image->getImageProfiles('*', false);

            if (array_search('icc', $profiles) === false) {
                $image->profileImage('icc', file_get_contents(__DIR__.'/icc/us_web_uncoated.icc'));
            }

            $image->profileImage('icm', file_get_contents(__DIR__.'/icc/srgb.icm'));
        }

        $this->image = $image;
    }


    /**
     * Destroy the image
     */
    public function __destruct()
    {
        $this->image->destroy();
    }


    /**
     * {@inheritDoc}
     */
    public function flip()
    {
        if ($this->image->flipImage() !== true) {
            throw new ImageException('There was an error on flip the image');
        }
    }


    /**
     * {@inheritDoc}
     */
    public function flop()
    {
        if ($this->image->flopImage() !== true) {
            throw new ImageException('There was an error on flop the image');
        }
    }


    /**
     * {@inheritDoc}
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
        } else {
            if (!$image->writeImage($filename)) {
                throw new ImageException("The image file '{$filename}' cannot be saved");
            }
        }
    }


    /**
     * {@inheritDoc}
     */
    public function getString()
    {
        $image = $this->getCompressed();

        if ($this->animated) {
            if (!($fp = fopen($file = tempnam(sys_get_temp_dir(), 'imagick'), 'w'))) {
                throw new ImageException('Cannot create a temp file to generate the string data image');
            }

            $image->writeImagesFile($fp);

            fclose($fp);

            $string = file_get_contents($file);

            unlink($file);

            return $string;
        }

        return $image->getImageBlob();
    }


    /**
     * {@inheritDoc}
     */
    public function getMimeType()
    {
        $format = strtolower($this->image->getImageFormat());

        if (in_array($format, array('jpeg', 'jpg', 'gif', 'png'), true)) {
            return "image/$format";
        }
    }


    /**
     * {@inheritDoc}
     */
    public function getWidth()
    {
        return $this->image->getImageWidth();
    }


    /**
     * {@inheritDoc}
     */
    public function getHeight()
    {
        return $this->image->getImageHeight();
    }


    /**
     * {@inheritDoc}
     */
    public function format($format)
    {
        if (preg_match('/jpe?g/i', $format)) {
            list($r, $g, $b) = $this->background;

            $this->image->setImageBackgroundColor("rgb($r,$g,$b)");
            $this->image = $this->image->flattenImages();
        }

        if ($this->image->setImageFormat($format) !== true) {
            throw new ImageException("The image format '{$format}' is not valid");
        }
    }


    /**
     * {@inheritDoc}
     */
    public function resize($width, $height)
    {
        if ($this->animated) {
            $this->image = $this->image->coalesceImages();

            foreach ($this->image as $frame) {
                $frame->scaleImage($width, $height, (($width === 0 || $height === 0) ? false : true));
            }

            $this->image = $this->image->deconstructImages();
        } else {
            if ($this->image->scaleImage($width, $height, (($width === 0 || $height === 0) ? false : true)) !== true) {
                throw new ImageException('There was an error resizing the image');
            }

            $this->image->setImagePage(0, 0, 0, 0);
        }
    }


    /**
     * {@inheritDoc}
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
     * {@inheritDoc}
     */
    public function rotate($angle)
    {
        if ($this->image->rotateImage(new \ImagickPixel, $angle) !== true) {
            throw new ImageException('There was an error rotating the image');
        }

        $this->image->setImagePage(0, 0, 0, 0);
    }


    /**
     * Returns a copy of the image compressed and ready to save or print
     *
     * @return \Imagick The instance of the image
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

            $image = $image->deconstructImages();
        } else {
            $format = strtolower($image->getImageFormat());

            $image->stripImage();
            $image->setImageUnits(1);
            $image->setImageCompressionQuality($this->quality);

            switch ($format) {
                case 'jpeg':
                    $image->setInterlaceScheme(\Imagick::INTERLACE_JPEG);
                    $image->setImageCompression(\Imagick::COMPRESSION_JPEG);
                    break;

                case 'gif':
                    $image->setInterlaceScheme(\Imagick::INTERLACE_GIF);
                    break;

                case 'png':
                    $image->setInterlaceScheme(\Imagick::INTERLACE_PNG);
                    break;
            }
        }

        return $image;
    }
}
