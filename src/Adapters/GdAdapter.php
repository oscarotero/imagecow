<?php
declare(strict_types = 1);

namespace Imagecow\Adapters;

use Imagecow\ImageException;

/**
 * GD library.
 */
final class GdAdapter implements AdapterInterface
{
    use CommonTrait;

    public static $fallbackCropMethods = [
        'Entropy' => ['center', 'middle'],
        'Balanced' => ['center', 'middle'],
    ];

    private $image;
    private $type;

    public static function checkCompatibility(): bool
    {
        return extension_loaded('gd');
    }

    public static function createFromFile(string $filename): AdapterInterface
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

    public static function createFromString(string $string): AdapterInterface
    {
        if (($image = imagecreatefromstring($string))) {
            return new static($image);
        }

        throw new ImageException('Error creating the image from string');
    }

    /**
     * @param resource $image The Gd resource.
     */
    public function __construct($image, int $type = null)
    {
        $this->image = $image;
        $this->type = isset($type) ? $type : IMAGETYPE_PNG;

        imagealphablending($this->image, true);
        imagesavealpha($this->image, true);
    }

    public function __destruct()
    {
        imagedestroy($this->image);
    }

    public function flip()
    {
        imageflip($this->image, IMG_FLIP_VERTICAL);
    }

    public function flop()
    {
        imageflip($this->image, IMG_FLIP_HORIZONTAL);
    }

    public function save(string $filename)
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

    public function getString(): string
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

    public function getMimeType(): string
    {
        return image_type_to_mime_type($this->type);
    }

    public function getWidth(): int
    {
        return imagesx($this->image);
    }

    public function getHeight(): int
    {
        return imagesy($this->image);
    }

    public function format(string $format)
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

            case 'webp':
                $this->type = IMAGETYPE_WEBP;
                break;

            default:
                throw new ImageException("The image format '{$format}' is not valid");
        }
    }

    /**
     * imagescale() is not used due a weird black border:
     * https://bugs.php.net/bug.php?id=73281
     */
    public function resize(int $maxWidth, int $maxHeight)
    {
        $image = $this->createImage($maxWidth, $maxHeight, array(0, 0, 0, 127));

        if (imagecopyresampled($image, $this->image, 0, 0, 0, 0, $maxWidth, $maxHeight, $this->getWidth(), $this->getHeight()) === false) {
            throw new ImageException('Error resizing the image');
        }

        imagedestroy($this->image);
        $this->image = $image;
    }

    public function getCropOffsets(int $width, int $height, string $method): array
    {
        if (empty(static::$fallbackCropMethods[$method])) {
            throw new ImageException("The crop method '$method' is not available for Gd");
        }

        return static::$fallbackCropMethods[$method];
    }

    public function crop(int $width, int $height, int $x, int $y)
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

    public function rotate(int $angle)
    {
        $background = imagecolorallocatealpha($this->image, 0, 0, 0, 127);

        if ($background === false || ($image = imagerotate($this->image, -$angle, $background)) === false) {
            throw new ImageException('Error rotating the image');
        }

        imagecolortransparent($image, imagecolorallocatealpha($image, 0, 0, 0, 127));

        imagedestroy($this->image);
        $this->image = $image;
    }

    public function blur(int $loops)
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

    public function watermark(LibInterface $image, $x, $y)
    {
        if (!($image instanceof self)) {
            $image = self::createFromString($image->getString());
        }

        imagecopy($this->image, $image->getImage(), $x, $y, 0, 0, $image->getWidth(), $image->getHeight());
    }

    public function opacity(int $opacity)
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

    public function setProgressive(bool $progressive)
    {
        imageinterlace($this->image, $progressive);
    }

    /**
     * Creates a new truecolor image
     *
     * @return resource
     */
    private function createImage(int $width, int $height, array $background = [0, 0, 0])
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
}
