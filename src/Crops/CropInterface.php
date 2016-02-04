<?php

namespace Imagecow\Crops;

use Imagick;

/**
 * Interface used by all crops.
 */
interface CropInterface
{
    /**
     * Returns the x,y values.
     *
     * @param Imagick $original
     * @param int     $targetWidth
     * @param int     $targetHeight
     *
     * @return array
     */
    public static function getOffsets(Imagick $original, $targetWidth, $targetHeight);
}
