<?php

namespace Imagecow\Utils;

use Imagecow\ImageException;

/**
 * Usefull dimmensions calculations.
 */
class Dimmensions
{
    protected static $positionsKeywords = array(
        'top' => '0%',
        'left' => '0%',
        'middle' => '50%',
        'center' => '50%',
        'right' => '100%',
        'bottom' => '100%',
    );

    /**
     * Calculate the dimensions of a resize.
     *
     * @param int  $oldWidth
     * @param int  $oldHeight
     * @param int  $newWidth
     * @param int  $newHeight
     * @param bool $cover
     *
     * @return array [width, height]
     */
    public static function getResizeDimmensions($oldWidth, $oldHeight, $newWidth, $newHeight, $cover = false)
    {
        if (empty($newHeight)) {
            return array((int) $newWidth, (int) ceil(($newWidth / $oldWidth) * $oldHeight));
        }

        if (empty($newWidth)) {
            return array((int) ceil(($newHeight / $oldHeight) * $oldWidth), (int) $newHeight);
        }

        $scaleWidth = $newWidth / $oldWidth;
        $scaleHeight = $newHeight / $oldHeight;

        if ($cover) {
            if ($scaleWidth > $scaleHeight) {
                return array((int) $newWidth, (int) ceil($scaleWidth * $oldHeight));
            }

            if ($scaleWidth < $scaleHeight) {
                return array((int) ceil($scaleHeight * $oldWidth), (int) $newHeight);
            }
        } else {
            if ($scaleWidth < $scaleHeight) {
                return array((int) $newWidth, (int) ceil($scaleWidth * $oldHeight));
            }

            if ($scaleWidth > $scaleHeight) {
                return array((int) ceil($scaleHeight * $oldWidth), (int) $newHeight);
            }
        }

        if ($scaleWidth < $scaleHeight || ($cover && $scaleWidth > $scaleHeight)) {
            return array((int) $newWidth, (int) ceil($scaleWidth * $oldHeight));
        }

        if ($scaleWidth > $scaleHeight || ($cover && $scaleWidth < $scaleHeight)) {
            return array((int) ceil($scaleHeight * $oldWidth), (int) $newHeight);
        }

        return array((int) $newWidth, (int) $newHeight);
    }

    /**
     * Calculate a dimension value.
     *
     * @param int|string $value
     * @param int        $relatedValue
     * @param bool       $position
     *
     * @return int
     */
    public static function getIntegerValue($value, $relatedValue, $position = false)
    {
        if ($position && isset(static::$positionsKeywords[$value])) {
            $value = static::$positionsKeywords[$value];
        }

        if (substr($value, -1) === '%') {
            return intval(($relatedValue / 100) * floatval(substr($value, 0, -1)));
        }

        return intval($value);
    }

    /**
     * Calculate a dimension value.
     *
     * @param int|string $value
     * @param int        $relatedValue
     * @param bool       $position
     *
     * @return string
     */
    public static function getPercentageValue($value, $relatedValue, $position = false)
    {
        if ($position && isset(static::$positionsKeywords[$value])) {
            return static::$positionsKeywords[$value];
        }

        if (substr($value, -1) === '%') {
            return $value;
        }

        if (is_numeric($value)) {
            return empty($value) ? '0%' : (($value / $relatedValue) * 100).'%';
        }

        throw new ImageException("Invalid position: {$value}");
    }

    /**
     * Calculates the x/y position.
     *
     * @param string|int|null $position
     * @param int             $newValue
     * @param int             $oldValue
     *
     * @return int
     */
    public static function getPositionValue($position, $newValue, $oldValue)
    {
        if (is_numeric($position)) {
            return intval($position);
        }

        $newCenter = static::getIntegerValue($position, $newValue, true);
        $oldCenter = static::getIntegerValue($position, $oldValue, true);

        return $oldCenter - $newCenter;
    }
}
