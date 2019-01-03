<?php

namespace Imagecow;

use Imagecow\Utils\Dimmensions;
use Imagecow\Adapters\AdapterInterface;
use Imagecow\Adapters\GdAdapter;
use Imagecow\Adapters\ImagickAdapter;

final class Image
{
    const CROP_ENTROPY = 'Entropy';
    const CROP_BALANCED = 'Balanced';
    const CROP_FACE = 'Face';

    private $adapter;
    private $filename;
    private $clientHints = [
        'dpr' => null,
        'viewport-width' => null,
        'width' => null,
    ];

    /**
     * Create a new Imagecow instance from an image file.
     */
    public static function fromFile(string $filename, string $adapter = null): Image
    {
        $adapter = self::getAdapterClass($adapter);
        $image = new static($adapter::createFromFile($filename), $filename);

        if ($image->getMimeType() !== 'image/gif') {
            return $image;
        }

        $stream = fopen($filename, 'rb');

        if (self::isAnimatedGif($stream)) {
            $image->adapter->setAnimated(true);
        }

        fclose($stream);

        return $image;
    }

    /**
     * Create a new Imagecow instance from a string.
     */
    public static function fromString(string $string, string $adapter = null): Image
    {
        $adapter = self::getAdapterClass($adapter);
        $image = new static($adapter::createFromString($string));

        if ($image->getMimeType() !== 'image/gif') {
            return $image;
        }

        $stream = fopen('php://temp', 'r+');

        fwrite($stream, $string);
        rewind($stream);

        if (self::isAnimatedGif($stream)) {
            $image->adapter->setAnimated(true);
        }

        fclose($stream);

        return $image;
    }

    /**
     * Constructor.
     */
    public function __construct(AdapterInterface $adapter, string $filename = null)
    {
        $this->adapter = $adapter;
        $this->filename = $filename;
    }

    /**
     * Set the available client hints.
     */
    public function setClientHints(array $clientHints): self
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
     * Set a default background color used to fill in some transformation functions
     * in rgb format, for example: array(0,127,34)
     */
    public function setBackground(array $background): self
    {
        $this->adapter->setBackground($background);

        return $this;
    }

    /**
     * Get the fixed size according with the client hints.
     */
    private function calculateClientSize(int $width, int $height): array
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
     */
    public function flip(): self
    {
        $this->adapter->flip();

        return $this;
    }

    /**
     * Inverts the image horizontally.
     */
    public function flop(): self
    {
        $this->adapter->flop();

        return $this;
    }

    /**
     * Saves the image in a file (or override the previous opened file).
     */
    public function save(string $filename = null): self
    {
        $this->adapter->save($filename ?: $this->filename);

        return $this;
    }

    public function getAdapter(): AdapterInterface
    {
        return $this->adapter;
    }

    /**
     * Gets the image data in a string.
     */
    public function getString(): string
    {
        return $this->adapter->getString();
    }

    /**
     * Gets the mime type of the image.
     */
    public function getMimeType(): string
    {
        return $this->adapter->getMimeType();
    }

    /**
     * Gets the width of the image in pixels.
     */
    public function getWidth(): int
    {
        return $this->adapter->getWidth();
    }

    /**
     * Gets the height of the image in pixels.
     */
    public function getHeight(): int
    {
        return $this->adapter->getHeight();
    }

    /**
     * Converts the image to other format (png, jpg, gif or webp).
     */
    public function format($format): self
    {
        $this->adapter->format($format);

        return $this;
    }

    /**
     * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300).
     *
     * @param int|string $width  The max width of the image. It can be a number (pixels) or percentaje
     * @param int|string $height The max height of the image. It can be a number (pixels) or percentaje
     */
    public function resize($width, $height = 0, bool $cover = false): self
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

        $this->adapter->resize($width, $height);

        return $this;
    }

    /**
     * Crops the image.
     *
     * @param int|string $width  The new width of the image. It can be a number (pixels) or percentaje
     * @param int|string $height The new height of the image. It can be a number (pixels) or percentaje
     * @param int|string $x      The "x" position to crop. It can be number (pixels), percentaje, [left, center, right] or one of the Image::CROP_* constants
     * @param int|string $y      The "y" position to crop. It can be number (pixels), percentaje or [top, middle, bottom]
     */
    public function crop($width, $height, $x = 'center', $y = 'middle'): self
    {
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        $width = Dimmensions::getIntegerValue('x', $width, $imageWidth);
        $height = Dimmensions::getIntegerValue('y', $height, $imageHeight);

        list($width, $height) = $this->calculateClientSize($width, $height);

        if (in_array($x, [self::CROP_BALANCED, self::CROP_ENTROPY, self::CROP_FACE], true)) {
            list($x, $y) = $this->adapter->getCropOffsets($width, $height, $x);
        }

        $x = Dimmensions::getPositionValue('x', $x, $width, $imageWidth);
        $y = Dimmensions::getPositionValue('y', $y, $height, $imageHeight);

        $this->adapter->crop($width, $height, $x, $y);

        return $this;
    }

    /**
     * Adjust the image to the given dimmensions. Resizes and crops the image maintaining the proportions.
     *
     * @param int|string $width  The new width in number (pixels) or percentaje
     * @param int|string $height The new height in number (pixels) or percentaje
     * @param int|string $x      The "x" position to crop. It can be number (pixels), percentaje, [left, center, right] or one of the Image::CROP_* constants
     * @param int|string $y      The "y" position to crop. It can be number (pixels), percentaje or [top, middle, bottom]
     */
    public function resizeCrop($width, $height, $x = 'center', $y = 'middle'): self
    {
        $this->resize($width, $height, true);
        $this->crop($width, $height, $x, $y);

        return $this;
    }

    /**
     * Rotates the image (in degrees, anticlockwise).
     */
    public function rotate(int $angle): self
    {
        if (($angle = intval($angle)) !== 0) {
            $this->adapter->rotate($angle);
        }

        return $this;
    }

    /**
     * Apply blur to image
     */
    public function blur(int $loops = 4): self
    {
        $this->adapter->blur($loops);

        return $this;
    }

    /**
     * Define the image compression quality for jpg images (from 0 to 100).
     */
    public function quality(int $quality): self
    {
        $quality = intval($quality);

        if ($quality < 0) {
            $quality = 0;
        } elseif ($quality > 100) {
            $quality = 100;
        }

        $this->adapter->setCompressionQuality($quality);

        return $this;
    }

    /**
     * Add a watermark to current image.
     *
     * @param mixed  $x    Horizontal position
     * @param mixed  $y    Vertical position
     */
    public function watermark(Image $image, $x = 'right', $y = 'bottom'): self
    {
        $imageWidth = $this->getWidth();
        $imageHeight = $this->getHeight();

        $width = $image->getWidth();
        $height = $image->getHeight();

        $x = Dimmensions::getPositionValue('x', $x, $width, $imageWidth);
        $y = Dimmensions::getPositionValue('y', $y, $height, $imageHeight);

        $this->adapter->watermark($image->getImage(), $x, $y);

        return $this;
    }

    /**
     * Add opacity to image from 0 (transparent) to 100 (opaque).
     */
    public function opacity(int $opacity): self
    {
        $this->adapter->opacity($opacity);

        return $this;
    }

    /**
     * Set the image progressive or not
     */
    public function progressive(bool $progressive = true): self
    {
        $this->adapter->setProgressive((bool) $progressive);

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
     */
    public function transform(string $operations = null): self
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
     */
    public function autoRotate(): self
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
     */
    private static function isAnimatedGif($stream): bool
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
     */
    private static function parseOperations(string $operations): array
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
     * @throws ImageException if the image library does not exists.
     */
    private static function getAdapterClass(string $adapter = null): string
    {
        if (!$adapter) {
            return ImagickAdapter::checkCompatibility() ? ImagickAdapter::class : GdAdapter::class;
        }

        if (!class_exists($adapter)) {
            throw new ImageException(sprintf('The class %s does not exists', $adapter));
        }

        if (!$adapter::checkCompatibility()) {
            throw new ImageException(sprintf('The class %s cannot be used in this computer', $adapter));
        }

        return $adapter;
    }
}
