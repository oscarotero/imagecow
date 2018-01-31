<?php

namespace Imagecow;

use Imagecow\Utils\Dimmensions;

class Image
{
    const LIB_GD = 'Gd';
    const LIB_IMAGICK = 'Imagick';

    const CROP_ENTROPY = 'Entropy';
    const CROP_BALANCED = 'Balanced';
    const CROP_FACE = 'Face';

    protected $image;
    protected $filename;
    protected $clientHints = [
        'dpr' => null,
        'viewport-width' => null,
        'width' => null,
    ];

    /**
     * Static function to create a new Imagecow instance from an image file.
     *
     * @param string $filename The path of the file
     * @param string $library  The name of the image library to use (Gd or Imagick). If it's not defined, detects automatically the library to use.
     *
     * @return Image
     */
    public static function fromFile($filename, $library = null)
    {
        $class = self::getLibraryClass($library);

        $image = new static($class::createFromFile($filename), $filename);

        if ($image->getMimeType() !== 'image/gif') {
            return $image;
        }

        $stream = fopen($filename, 'rb');

        if (self::isAnimatedGif($stream)) {
            $image->image->setAnimated(true);
        }

        fclose($stream);

        return $image;
    }

    /**
     * Static function to create a new Imagecow instance from a binary string.
     *
     * @param string $string  The string of the image
     * @param string $library The name of the image library to use (Gd or Imagick). If it's not defined, detects automatically the library to use.
     *
     * @return Image
     */
    public static function fromString($string, $library = null)
    {
        $class = self::getLibraryClass($library);

        $image = new static($class::createFromString($string));

        if ($image->getMimeType() !== 'image/gif') {
            return $image;
        }

        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $string);
        rewind($stream);

        if (self::isAnimatedGif($stream)) {
            $image->image->setAnimated(true);
        }

        fclose($stream);

        return $image;
    }

    /**
     * Constructor.
     *
     * @param Libs\LibInterface $image
     * @param string            $filename Original filename (used to overwrite)
     */
    public function __construct(Libs\LibInterface $image, $filename = null)
    {
        $this->image = $image;
        $this->filename = $filename;
    }

    /**
     * Set the available client hints.
     *
     * @param array $clientHints
     *
     * @return self
     */
    public function setClientHints(array $clientHints)
    {
        $normalize = [];

        foreach ($clientHints as $key => $value) {
            $normalize[strtolower($key)] = is_null($value) ? null : (float) $value;
        }

        if (array_diff_key($normalize, $this->clientHints)) {
            throw new \InvalidArgumentException('Invalid client hints');
        }

        $this->clientHints = array_replace($this->clientHints, $normalize);

        return $this;
    }

    /**
     * Set a default background color used to fill in some transformation functions.
     *
     * @param array $background The color in rgb, for example: array(0,127,34)
     *
     * @return self
     */
    public function setBackground(array $background)
    {
        $this->image->setBackground($background);

        return $this;
    }

    /**
     * Define the image compression quality for jpg images.
     *
     * @param int $quality The quality (from 0 to 100)
     *
     * @deprecated Use quality instead
     *
     * @return self
     */
    public function setCompressionQuality($quality)
    {
        error_log('The method `setCompressionQuality()` is deprecated. Use `quality()` instead.');

        return $this->quality($quality);
    }

    /**
     * Get the fixed size according with the client hints.
     *
     * @param int $width
     * @param int $height
     *
     * @return array
     */
    private function calculateClientSize($width, $height)
    {
        if ($this->clientHints['width'] !== null && $this->clientHints['width'] < $width) {
            return Dimmensions::getResizeDimmensions($width, $height, $this->clientHints['width'], null);
        }

        if ($this->clientHints['viewport-width'] !== null && $this->clientHints['viewport-width'] < $width) {
            return Dimmensions::getResizeDimmensions($width, $height, $this->clientHints['viewport-width'], null);
        }

        if ($this->clientHints['dpr'] !== null) {
            $width *= $this->clientHints['dpr'];
            $height *= $this->clientHints['dpr'];
        }

        return [$width, $height];
    }

    /**
     * Inverts the image vertically.
     *
     * @return self
     */
    public function flip()
    {
        $this->image->flip();

        return $this;
    }

    /**
     * Inverts the image horizontally.
     *
     * @return self
     */
    public function flop()
    {
        $this->image->flop();

        return $this;
    }

    /**
     * Saves the image in a file.
     *
     * @param string $filename Name of the file where the image will be saved. If it's not defined, The original file will be overwritten.
     *
     * @return self
     */
    public function save($filename = null)
    {
        $this->image->save($filename ?: $this->filename);

        return $this;
    }

    /**
     * Returns the image instance.
     *
     * @return Libs\LibInterface
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Gets the image data in a string.
     *
     * @return string The image data
     */
    public function getString()
    {
        return $this->image->getString();
    }

    /**
     * Gets the mime type of the image.
     *
     * @return string The mime type
     */
    public function getMimeType()
    {
        return $this->image->getMimeType();
    }

    /**
     * Gets the width of the image.
     *
     * @return int The width in pixels
     */
    public function getWidth()
    {
        return $this->image->getWidth();
    }

    /**
     * Gets the height of the image.
     *
     * @return int The height in pixels
     */
    public function getHeight()
    {
        return $this->image->getHeight();
    }

    /**
     * Converts the image to other format.
     *
     * @param string $format The new format: png, jpg, gif
     *
     * @return self
     */
    public function format($format)
    {
        $this->image->format($format);

        return $this;
    }

    /**
     * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300).
     *
     * @param int|string $width  The max width of the image. It can be a number (pixels) or percentaje
     * @param int|string $height The max height of the image. It can be a number (pixels) or percentaje
     * @param bool       $cover
     *
     * @return self
     */
    public function resize($width, $height = 0, $cover = false)
    {
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        $width = Dimmensions::getIntegerValue('x', $width, $imageWidth);
        $height = Dimmensions::getIntegerValue('y', $height, $imageHeight);

        list($width, $height) = Dimmensions::getResizeDimmensions($imageWidth, $imageHeight, $width, $height, $cover);
        list($width, $height) = $this->calculateClientSize($width, $height);

        if ($width >= $imageWidth && !$cover) {
            return $this;
        }

        $this->image->resize($width, $height);

        return $this;
    }

    /**
     * Crops the image.
     *
     * @param int|string $width  The new width of the image. It can be a number (pixels) or percentaje
     * @param int|string $height The new height of the image. It can be a number (pixels) or percentaje
     * @param int|string $x      The "x" position to crop. It can be number (pixels), percentaje, [left, center, right] or one of the Image::CROP_* constants
     * @param int|string $y      The "y" position to crop. It can be number (pixels), percentaje or [top, middle, bottom]
     *
     * @return self
     */
    public function crop($width, $height, $x = 'center', $y = 'middle')
    {
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        $width = Dimmensions::getIntegerValue('x', $width, $imageWidth);
        $height = Dimmensions::getIntegerValue('y', $height, $imageHeight);

        list($width, $height) = $this->calculateClientSize($width, $height);

        if (in_array($x, [self::CROP_BALANCED, self::CROP_ENTROPY, self::CROP_FACE], true)) {
            list($x, $y) = $this->image->getCropOffsets($width, $height, $x);
        }

        $x = Dimmensions::getPositionValue('x', $x, $width, $imageWidth);
        $y = Dimmensions::getPositionValue('y', $y, $height, $imageHeight);

        $this->image->crop($width, $height, $x, $y);

        return $this;
    }

    /**
     * Adjust the image to the given dimmensions. Resizes and crops the image maintaining the proportions.
     *
     * @param int|string $width  The new width in number (pixels) or percentaje
     * @param int|string $height The new height in number (pixels) or percentaje
     * @param int|string $x      The "x" position to crop. It can be number (pixels), percentaje, [left, center, right] or one of the Image::CROP_* constants
     * @param int|string $y      The "y" position to crop. It can be number (pixels), percentaje or [top, middle, bottom]
     *
     * @return self
     */
    public function resizeCrop($width, $height, $x = 'center', $y = 'middle')
    {
        $this->resize($width, $height, true);
        $this->crop($width, $height, $x, $y);

        return $this;
    }

    /**
     * Rotates the image.
     *
     * @param int $angle Rotation angle in degrees (anticlockwise)
     *
     * @return self
     */
    public function rotate($angle)
    {
        if (($angle = intval($angle)) !== 0) {
            $this->image->rotate($angle);
        }

        return $this;
    }

    /**
     * Apply blur to image
     *
     * @param int $loops Quantity of blur effect loop
     *
     * @return self
     */
    public function blur($loops = 4)
    {
        $this->image->blur($loops);

        return $this;
    }

    /**
     * Define the image compression quality for jpg images.
     *
     * @param int $quality The quality (from 0 to 100)
     *
     * @return self
     */
    public function quality($quality)
    {
        $quality = intval($quality);

        if ($quality < 0) {
            $quality = 0;
        } elseif ($quality > 100) {
            $quality = 100;
        }

        $this->image->setCompressionQuality($quality);

        return $this;
    }

    /**
     * Add a watermark to current image.
     *
     * @param string $file Image to set as watermark
     * @param mixed  $x    Horizontal position
     * @param mixed  $y    Vertical position
     *
     * @return self
     */
    public function watermark(Image $image, $x = 'right', $y = 'bottom')
    {
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        $width = $image->getWidth();
        $height = $image->getHeight();

        $x = Dimmensions::getPositionValue('x', $x, $width, $imageWidth);
        $y = Dimmensions::getPositionValue('y', $y, $height, $imageHeight);

        $this->image->watermark($image->getImage(), $x, $y);

        return $this;
    }

    /**
     * Add opacity to image from 0 (transparent) to 100 (opaque).
     *
     * @param int $opacity Opacity value
     *
     * @return self
     */
    public function opacity($opacity)
    {
        $this->image->opacity($opacity);

        return $this;
    }

    /**
     * Set the image progressive or not
     *
     * @param bool $progressive
     *
     * @return self
     */
    public function progressive($progressive = true)
    {
        $this->image->setProgressive((bool) $progressive);

        return $this;
    }

    /**
     * Reads the EXIF data from a JPEG and returns an associative array
     * (requires the exif PHP extension enabled).
     *
     * @param null|string $key
     *
     * @return null|array
     */
    public function getExifData($key = null)
    {
        if ($this->filename !== null && ($this->getMimeType() === 'image/jpeg')) {
            $exif = exif_read_data($this->filename);

            if ($key !== null) {
                return isset($exif[$key]) ? $exif[$key] : null;
            }

            return $exif;
        }
    }

    /**
     * Transform the image executing various operations of crop, resize, resizeCrop and format.
     *
     * @param string $operations The string with all operations separated by "|".
     *
     * @return self
     */
    public function transform($operations = null)
    {
        //No transform operations, resize to fix the client size
        if (empty($operations)) {
            return $this->resize($this->getWidth(), $this->getHeight());
        }

        $operations = self::parseOperations($operations);

        foreach ($operations as $operation) {
            switch ($operation['function']) {
                case 'crop':
                case 'resizecrop':
                    if (empty($operation['params'][2])) {
                        break;
                    }

                    switch ($operation['params'][2]) {
                        case 'CROP_ENTROPY':
                            $operation['params'][2] = self::CROP_ENTROPY;
                            break;

                        case 'CROP_BALANCED':
                            $operation['params'][2] = self::CROP_BALANCED;
                            break;

                        case 'CROP_FACE':
                            $operation['params'][2] = self::CROP_FACE;
                            break;
                    }

                    break;
            }

            call_user_func_array([$this, $operation['function']], $operation['params']);
        }

        return $this;
    }

    /**
     * Send the HTTP header with the content-type, output the image data and die.
     */
    public function show()
    {
        if (($string = $this->getString()) && ($mimetype = $this->getMimeType())) {
            header('Content-Type: '.$mimetype);
            die($string);
        }
    }

    /**
     * Returns the image as base64 url.
     *
     * @return string|null
     */
    public function base64()
    {
        if (($string = $this->getString()) && ($mimetype = $this->getMimeType())) {
            $string = base64_encode($string);

            return "data:{$mimetype};base64,{$string}";
        }
    }

    /**
     * Auto-rotate the image according with its exif data
     * Taken from: http://php.net/manual/en/function.exif-read-data.php#76964.
     *
     * @return self
     */
    public function autoRotate()
    {
        switch ($this->getExifData('Orientation')) {
            case 2:
                $this->flop();
                break;

            case 3:
                $this->rotate(180);
                break;

            case 4:
                $this->flip();
                break;

            case 5:
                $this->flip()->rotate(90);
                break;

            case 6:
                $this->rotate(90);
                break;

            case 7:
                $this->flop()->rotate(90);
                break;

            case 8:
                $this->rotate(-90);
                break;
        }

        return $this;
    }

    /**
     * Check whether the image is an animated gif.
     * Copied from: https://github.com/Sybio/GifFrameExtractor/blob/master/src/GifFrameExtractor/GifFrameExtractor.php#L181.
     *
     * @param resource A stream pointer opened by fopen()
     *
     * @return bool
     */
    private static function isAnimatedGif($stream)
    {
        $count = 0;

        while (!feof($stream) && $count < 2) {
            $chunk = fread($stream, 1024 * 100); //read 100kb at a time
            $count += preg_match_all('#\x00\x21\xF9\x04.{4}\x00(\x2C|\x21)#s', $chunk, $matches);
        }

        return $count > 1;
    }

    /**
     * Converts a string with operations in an array.
     *
     * @param string $operations The operations string
     *
     * @return array
     */
    private static function parseOperations($operations)
    {
        $valid_operations = ['resize', 'resizecrop', 'crop', 'format', 'quality'];
        $operations = explode('|', str_replace(' ', '', $operations));
        $return = [];

        foreach ($operations as $operations) {
            $params = explode(',', $operations);
            $function = strtolower(trim(array_shift($params)));

            if (!in_array($function, $valid_operations, true)) {
                throw new ImageException("The transform function '{$function}' is not valid");
            }

            $return[] = [
                'function' => $function,
                'params' => $params,
            ];
        }

        return $return;
    }

    /**
     * Checks the library to use and returns its class.
     *
     * @param string $library The library name (Gd, Imagick)
     *
     * @throws ImageException if the image library does not exists.
     *
     * @return string
     */
    private static function getLibraryClass($library)
    {
        if (!$library) {
            $library = Libs\Imagick::checkCompatibility() ? self::LIB_IMAGICK : self::LIB_GD;
        }

        $class = 'Imagecow\\Libs\\'.$library;

        if (!class_exists($class)) {
            throw new ImageException('The image library is not valid');
        }

        if (!$class::checkCompatibility()) {
            throw new ImageException("The image library '$library' is not installed in this computer");
        }

        return $class;
    }
}
