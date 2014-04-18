<?php
/**
 * Imagecow PHP library
 *
 * Interface for the available libraries
 *
 * PHP version 5.3
 */

namespace Imagecow\Libs;

interface LibInterface
{
    /**
     * Create an instance from an image file
     *
     * @param string $filename Name of the file to load
     *
     * @return $this
     */
    public static function createFromFile ($filename);


    /**
     * Create an instance from an image string
     *
     * @param string $string The image content
     *
     * @return $this
     */
    public static function createFromString ($string);


    /**
     * Saves the image in a file
     *
     * @param string $filename Name of the file where the image will be saved. If it's not defined, The original file will be overwritten.
     *
     * @return $this
     */
    public function save ($filename);


    /**
     * Gets the image data in a string
     *
     * @return string The image data
     */
    public function getString ();


    /**
     * Gets the mime type of the image
     *
     * @return string The mime type
     */
    public function getMimeType ();


    /**
     * Gets the width of the image
     *
     * @return integer The width in pixels
     */
    public function getWidth ();


    /**
     * Gets the height of the image
     *
     * @return integer The height in pixels
     */
    public function getHeight ();


    /**
     * Converts the image to other format
     *
     * @param string $format The new format: png, jpg, gif
     */
    public function format ($format);


    /**
     * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300)
     *
     * @param integer $width   The max width of the image
     * @param integer $height  The max height of the image
     */
    public function resize ($width, $height);


    /**
     * Crops the image
     *
     * @param integer $width  The new width of the image
     * @param integer $height The new height of the image
     * @param integer $x      The "x" position where start to crop
     * @param integer $y      The "y" position where start to crop
     */
    public function crop ($width, $height, $x, $y);


    /**
     * Rotates the image
     *
     * @param integer $angle Rotation angle in degrees (anticlockwise)
     */
    public function rotate($angle);


    /**
     * Inverts the image vertically
     */
    public function flip();


    /**
     * Inverts the image horizontally
     */
    public function flop();


    /**
     * Define the image compression quality for jpg images
     *
     * @param integer $quality The quality (from 0 to 100)
     */
    public function setCompressionQuality($quality);


    /**
     * Set a default background color used to fill in some transformation functions
     *
     * @param array $background The color in rgb, for example: array(0, 127, 34)
     */
    public function setBackground(array $background);


    /**
     * Defines the image as an animated image
     *
     * @param boolean $animated
     */
    public function setAnimated($animated);
}
