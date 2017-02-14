<?php

namespace Imagecow\Libs;

use Imagecow\ImageException;

/**
 * Interface implemented by all libraries.
 */
interface LibInterface
{
    /**
     * Check if the library is installed.
     *
     * @return bool
     */
    public static function checkCompatibility();

    /**
     * Create an instance from an image file.
     *
     * @param string $filename Name of the file to load
     *
     * @return self
     */
    public static function createFromFile($filename);

    /**
     * Create an instance from an image string.
     *
     * @param string $string The image content
     *
     * @return self
     */
    public static function createFromString($string);

    /**
     * Saves the image in a file.
     *
     * @param string $filename Name of the file where the image will be saved. If it's not defined, The original file will be overwritten.
     */
    public function save($filename);

    /**
     * Gets the image data in a string.
     *
     * @return string The image data
     */
    public function getString();

    /**
     * Gets the mime type of the image.
     *
     * @return string The mime type
     */
    public function getMimeType();

    /**
     * Gets the width of the image.
     *
     * @return int The width in pixels
     */
    public function getWidth();

    /**
     * Gets the height of the image.
     *
     * @return int The height in pixels
     */
    public function getHeight();

    /**
     * Converts the image to other format.
     *
     * @param string $format The new format: png, jpg, gif
     */
    public function format($format);

    /**
     * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300).
     *
     * @param int $width  The max width of the image
     * @param int $height The max height of the image
     */
    public function resize($width, $height);

    /**
     * Calculates automatically the x,y positions of a crop using a specific method.
     *
     * @param int    $width  The new width of the image
     * @param int    $height The new height of the image
     * @param string $method The method name (for example: "Entropy")
     *
     * @throws ImageException if the method is not available
     *
     * @return array [x, y]
     */
    public function getCropOffsets($width, $height, $method);

    /**
     * Crops the image.
     *
     * @param int $width  The new width of the image
     * @param int $height The new height of the image
     * @param int $x      The "x" position where start to crop
     * @param int $y      The "y" position where start to crop
     */
    public function crop($width, $height, $x, $y);

    /**
     * Rotates the image.
     *
     * @param int $angle Rotation angle in degrees (anticlockwise)
     */
    public function rotate($angle);

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
    public function opacity($opacity);

    /**
     * Applies a watermark image.
     * 
     * @param LibInterface $image
     * @param int          $x     The horizontal position
     * @param int          $y     The vertical position
     */
    public function watermark(LibInterface $image, $x, $y);

    /**
     * Define the image compression quality for jpg images.
     *
     * @param int $quality The quality (from 0 to 100)
     */
    public function setCompressionQuality($quality);

    /**
     * Set a default background color used to fill in some transformation functions.
     *
     * @param array $background The color in rgb, for example: [0, 127, 34]
     */
    public function setBackground(array $background);

    /**
     * Defines the image as an animated image.
     *
     * @param bool $animated
     */
    public function setAnimated($animated);

    /**
     * Defines the image as progressive (if its jpg).
     *
     * @param bool $progressive
     */
    public function setProgressive($progressive);
}
