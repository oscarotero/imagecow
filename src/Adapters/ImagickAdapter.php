<?php
declare(strict_types = 1);

namespace Imagecow\Adapters;

use Imagick;
use ImagickPixel;
use Imagecow\ImageException;

/**
 * Imagick library.
 */
final class ImagickAdapter implements AdapterInterface
{
    use CommonTrait;

    private $image;

    public static function checkCompatibility(): bool
    {
        return extension_loaded('imagick');
    }

    public static function createFromFile(string $filename): AdapterInterface
    {
        $imagick = new Imagick();

        if ($imagick->readImage($filename) !== true) {
            throw new ImageException("The image file '{$filename}' cannot be loaded");
        }

        return new static($imagick);
    }

    public static function createFromString(string $string): AdapterInterface
    {
        $imagick = new Imagick();

        $imagick->readImageBlob($string);

        return new static($imagick);
    }

    public function __construct(Imagick $image)
    {
        $this->image = $image;

        //Convert CMYK to RGB
        if ($this->image->getImageColorspace() !== Imagick::COLORSPACE_CMYK) {
            return $this;
        }

        $profiles = $this->image->getImageProfiles('*', false);

        if (array_search('icc', $profiles) === false) {
            $this->image->profileImage('icc', file_get_contents(__DIR__.'/icc/us_web_uncoated.icc'));
        }

        $this->image->profileImage('icm', file_get_contents(__DIR__.'/icc/srgb.icm'));
        $this->image->transformImageColorspace(Imagick::COLORSPACE_SRGB);
    }

    public function __destruct()
    {
        $this->image->destroy();
    }

    public function flip()
    {
        if ($this->image->flipImage() !== true) {
            throw new ImageException('There was an error on flip the image');
        }
    }

    public function flop()
    {
        if ($this->image->flopImage() !== true) {
            throw new ImageException('There was an error on flop the image');
        }
    }

    public function save(string $filename)
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

    public function getImage(): Imagick
    {
        return $this->image;
    }

    public function getString(): string
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

    public function getMimeType(): string
    {
        $format = strtolower($this->image->getImageFormat());

        if (in_array($format, ['jpeg', 'jpg', 'gif', 'png', 'webp'], true)) {
            return "image/$format";
        }
    }

    public function getWidth(): int
    {
        if ($this->animated) {
            return $this->image->coalesceImages()->getImageWidth();
        } else {
            return $this->image->getImageWidth();
        }
    }

    public function getHeight(): int
    {
        if ($this->animated) {
            return $this->image->coalesceImages()->getImageHeight();
        } else {
            return $this->image->getImageHeight();
        }
    }

    public function format(string $format): string
    {
        if (preg_match('/jpe?g/i', $format)) {
            list($r, $g, $b) = $this->background;

            $this->image->setImageBackgroundColor("rgb($r,$g,$b)");
            $this->image = $this->image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
        }

        if ($this->image->setImageFormat($format) !== true) {
            throw new ImageException("The image format '{$format}' is not valid");
        }
    }

    public function resize(int $maxWidth, int $maxHeight)
    {
        if ($this->animated) {
            $this->image = $this->image->coalesceImages();

            foreach ($this->image as $frame) {
                $frame->scaleImage($maxWidth, $maxHeight);
            }

            $this->image = $this->image->deconstructImages();
        } else {
            if ($this->image->scaleImage($maxWidth, $maxHeight) !== true) {
                throw new ImageException('There was an error resizing the image');
            }

            $this->image->setImagePage(0, 0, 0, 0);
        }
    }

    public function getCropOffsets(int $width, int $height, string $method): array
    {
        $class = 'Imagecow\\Crops\\'.ucfirst(strtolower($method));

        if (!class_exists($class)) {
            throw new ImageException("The crop method '$method' is not available for Imagick");
        }

        return $class::getOffsets($this->image, $width, $height);
    }

    public function crop(int $width, int $height, int $x, int $y)
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

    public function rotate(int $angle)
    {
        if ($this->image->rotateImage(new ImagickPixel('#FFFFFF'), $angle) !== true) {
            throw new ImageException('There was an error rotating the image');
        }

        $this->image->setImagePage(0, 0, 0, 0);
    }

    public function blur(int $loops)
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
     */
    private function getCompressed(): Imagick
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
                $image->setInterlaceScheme(Imagick::INTERLACE_JPEG);
                $image->setImageCompression(Imagick::COMPRESSION_JPEG);
                break;

            case 'gif':
                $image->setInterlaceScheme(Imagick::INTERLACE_GIF);
                break;

            case 'png':
                $image->setInterlaceScheme(Imagick::INTERLACE_PNG);
                break;
        }

        return $image;
    }

    public function watermark(LibInterface $image, $x, $y)
    {
        if (!($image instanceof self)) {
            $image = self::createFromString($image->getString());
        }

        $this->image->compositeImage($image->getImage(), Imagick::COMPOSITE_DISSOLVE, $x, $y);
    }

    public function opacity(int $opacity)
    {
        if ($opacity >= 100 || $opacity < 0) {
            return;
        }

        if ($this->image->getImageAlphaChannel() !== Imagick::ALPHACHANNEL_ACTIVATE) {
            $this->image->setImageAlphaChannel(Imagick::ALPHACHANNEL_OPAQUE);
        }

        // NOTE: Using setImageOpacity will destroy current alpha channels!
        $this->image->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);
    }

    public function setProgressive(bool $progressive)
    {
        if ($progressive) {
            $this->image->setInterlaceScheme(Imagick::INTERLACE_PLANE);
        } else {
            $this->image->setInterlaceScheme(Imagick::INTERLACE_NO);
        }
    }
}
