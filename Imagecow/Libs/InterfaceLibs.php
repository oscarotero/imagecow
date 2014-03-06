<?php
/**
 * Imagecow PHP library
 *
 * Interface for the available libraries
 *
 * PHP version 5.3
 */

namespace Imagecow\Libs;

interface InterfaceLibs
{

    /**
     * Load an image file
     *
     * @param string $image Name of the file to load
     *
     * @return $this
     */
    public function load ($image);


    /**
     * Destroy the image loaded
     *
     * @return $this
     */
    public function unload ();


    /**
     * Save the image in a file
     *
     * @param string $filename Name of the file where the image will be saved. If it's not defined, The original file will be overwritten.
     *
     * @return $this
     */
    public function save ($filename = null);


    /**
     * Gets the image data in a string
     *
     * @return string The image data
     */
    public function getString ();


    /**
     * Gets the mime-type of the image
     *
     * @return string The mime-type
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
     *
     * @return $this
     */
    public function format ($format);


    /**
     * Resizes the image maintaining the proportion (A 800x600 image resized to 400x400 becomes to 400x300)
     *
     * @param int/string $width   The max width of the image. It can be a number (pixels) or percentaje
     * @param int/string $height  The max height of the image. It can be a number (pixels) or percentaje
     * @param boolean    $enlarge True if the new image can be bigger (false by default)
     *
     * @return $this
     */
    public function resize ($width, $height = 0, $enlarge = false);


    /**
     * Crops the image
     *
     * @param int/string $width  The new width of the image. It can be a number (pixels) or percentaje
     * @param int/string $height The new height of the image. It can be a number (pixels) or percentaje
     * @param int/string $x      The "x" position where start to crop. It can be number (pixels), percentaje or one of the available keywords (left,center,right)
     * @param int/string $y      The "y" position where start to crop. It can be number (pixels), percentaje or one of the available keywords (top,middle,bottom)
     *
     * @return $this
     */
    public function crop ($width, $height, $x = 'center', $y = 'middle');
}
