<?php

namespace Imagecow\Utils;

/**
 * Usefull dimmensions calculations.
 */
class Dimmensions
{
    protected static $positionsKeywordsX = [
        'left' => '0%',
        'center' => '50%',
        'right' => '100%',
    ];

    protected static $positionsKeywordsY = [
        'top' => '0%',
        'middle' => '50%',
        'bottom' => '100%',
    ];

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
            return [(int) $newWidth, (int) ceil(($newWidth / $oldWidth) * $oldHeight)];
        }

        if (empty($newWidth)) {
            return [(int) ceil(($newHeight / $oldHeight) * $oldWidth), (int) $newHeight];
        }

        $scaleWidth = $newWidth / $oldWidth;
        $scaleHeight = $newHeight / $oldHeight;

        if ($cover) {
            if ($scaleWidth > $scaleHeight) {
                return [(int) $newWidth, (int) ceil($scaleWidth * $oldHeight)];
            }

            if ($scaleWidth < $scaleHeight) {
                return [(int) ceil($scaleHeight * $oldWidth), (int) $newHeight];
            }
        } else {
            if ($scaleWidth < $scaleHeight) {
                return [(int) $newWidth, (int) ceil($scaleWidth * $oldHeight)];
            }

            if ($scaleWidth > $scaleHeight) {
                return [(int) ceil($scaleHeight * $oldWidth), (int) $newHeight];
            }
        }

        if ($scaleWidth < $scaleHeight || ($cover && $scaleWidth > $scaleHeight)) {
            return [(int) $newWidth, (int) ceil($scaleWidth * $oldHeight)];
        }

        if ($scaleWidth > $scaleHeight || ($cover && $scaleWidth < $scaleHeight)) {
            return [(int) ceil($scaleHeight * $oldWidth), (int) $newHeight];
        }

        return [(int) $newWidth, (int) $newHeight];
    }

    /**
     * Calculate a dimension value.
     *
     * @param string     $direction
     * @param int|string $value
     * @param int        $relatedValue
     * @param bool       $position
     *
     * @return int
     */
    public static function getIntegerValue($direction, $value, $relatedValue, $position = false)
    {
        $keywords = ($direction === 'y') ? static::$positionsKeywordsY : static::$positionsKeywordsX;

        if ($position && isset($keywords[$value])) {
            $value = $keywords[$value];
        }

        if (substr($value, -1) === '%') {
            return intval(($relatedValue / 100) * floatval(substr($value, 0, -1)));
        }

        return intval($value);
    }

    /**
     * Calculates the x/y position.
     *
     * @param string          $direction (y or x)
     * @param string|int|null $position
     * @param int             $newValue
     * @param int             $oldValue
     *
     * @return int
     */
    public static function getPositionValue($direction, $position, $newValue, $oldValue)
    {
        $split = preg_split('/([\+\-])/', str_replace(' ', '', $position), 2, PREG_SPLIT_DELIM_CAPTURE);

        //Base value
        $value = $split[0];

        if (is_numeric($value)) {
            $value = (int) $value;
        } else {
            $newCenter = static::getIntegerValue($direction, $value, $newValue, true);
            $oldCenter = static::getIntegerValue($direction, $value, $oldValue, true);

            $value = $oldCenter - $newCenter;
        }

        //Offset
        $offset = isset($split[2]) ? $split[1].$split[2] : 0;

        if (is_numeric($offset)) {
            $offset = (int) $offset;
        } else {
            $offset = static::getIntegerValue($direction, $offset, $oldValue, true);
        }

        return $value + $offset;
    }
}
