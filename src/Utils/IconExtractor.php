<?php

namespace Imagecow\Utils;

use Imagick;
use Imagecow\Libs\Imagick as ImagickLib;
use Imagecow\Image;

/**
 * Simple class to extracts all images from .ico files
 * Requires Imagick.
 */
class IconExtractor
{
    protected $image;

    /**
     * Contructor.
     *
     * @param string $filename The path of ico file
     */
    public function __construct($filename)
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception('IconExtractor needs imagick extension');
        }

        $image = new Imagick();
        $image->readImage($filename);

        $this->image = $image;
    }

    /**
     * Get the better quality image found in the icon.
     *
     * @return Image
     */
    public function getBetterQuality()
    {
        $quality = 0;
        $better = 0;

        foreach ($this->image as $index => $image) {
            $q = $image->getImageDepth() + ($image->getImageWidth() * $image->getImageHeight());

            if ($q > $quality) {
                $quality = $q;
                $better = $index;
            }
        }

        $this->image->setIteratorIndex($better);

        $better = new Image(new ImagickLib($this->image));
        $better->format('png');

        return $better;
    }
}
