<?php
declare(strict_types = 1);

namespace Imagecow\Adapters;

use Imagecow\ImageException;

/**
 * Interface implemented by all libraries.
 */
interface AdapterInterface
{
    /**
     * Check if the library is installed.
     */
    public static function checkCompatibility(): bool;

    /**
     * Create an instance from an image file.
     */
    public static function createFromFile(string $filename): AdapterInterface;

    /**
     * Create an instance from an image string.
     */
    public static function createFromString(string $string): AdapterInterface;

    /**
     * Saves the image in a file.
     */
    public function save(string $filename);

    /**
     * Gets the image data in a string.
     */
    public function getString(): string;

    /**
     * Gets the mime type of the image.
     */
    public function getMimeType(): string;

    /**
     * Gets the width of the image in pixels.
     */
    public function getWidth(): int;

    /**
     * Gets the height of the image.
     */
    public function getHeight(): int;

    /**
     * Converts the image to other format.
     */
    public function format(string $format);

    /**
     * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300).
     */
    public function resize(int $maxWidth, int $maxHeight);

    /**
     * Calculates automatically the x,y positions of a crop using a specific method.
     *
     * @throws ImageException if the method is not available
     *
     * @return array [x, y]
     */
    public function getCropOffsets(int $width, int $height, string $method): array;

    /**
     * Crops the image.
     */
    public function crop(int $width, int $height, int $x, int $y);

    /**
     * Rotates the image in degrees (anticlockwise)
     */
    public function rotate(int $angle);

    /**
     * Inverts the image vertically.
     */
    public function flip();

    /**
     * Inverts the image horizontally.
     */
    public function flop();

    /**
     * Changes the opacity of the image.
     */
    public function opacity(int $opacity);

    /**
     * Applies a watermark image.
     */
    public function watermark(LibInterface $image, int $x, int $y);

    /**
     * Define the image compression quality for jpg images (from 0 to 100).
     */
    public function setCompressionQuality(int $quality);

    /**
     * Set a default background color used to fill in some transformation functions.
     */
    public function setBackground(array $background);

    /**
     * Defines the image as an animated image.
     */
    public function setAnimated(bool $animated);

    /**
     * Defines the image as progressive (if its jpg).
     */
    public function setProgressive(bool $progressive);
}
