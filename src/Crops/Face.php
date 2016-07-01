<?php

namespace Imagecow\Crops;

use Imagick;
use Imagecow\ImageException;

/**
 * This class is adapted from Stig Lindqvist's great Crop library:
 * https://github.com/stojg/crop
 * Copyright (c) 2013, Stig Lindqvist.
 *
 * CropFace
 *
 * This class will try to find the most interesting point in the image by trying to find a face and
 * center the crop on that
 *
 * @todo implement
 * @see https://github.com/mauricesvay/php-facedetection/blob/master/FaceDetector.php
 */
class Face extends Entropy
{
    const CLASSIFIER_FACE = '/classifier/haarcascade_frontalface_default.xml';
    const CLASSIFIER_PROFILE = '/classifier/haarcascade_profileface.xml';

    /**
     * safeZoneList
     *
     * @var array
     * @access protected
     */
    protected static $safeZoneList = array();

    /**
     * originalImage
     *
     * @var object
     * @access protected
     */
    protected static $originalImage;

    /**
     * baseDimension
     *
     * @var array
     * @access protected
     */
    protected static $baseDimension = array();

    /**
     * Returns the x,y values.
     *
     * @param Imagick $original
     * @param int     $targetWidth
     * @param int     $targetHeight
     *
     * @return array
     */
    public static function getOffsets(Imagick $original, $targetWidth, $targetHeight)
    {
        static::$originalImage = $original;
        static::$baseDimension = [
            'width' => $original->getImageWidth(),
            'height' => $original->getImageWidth(),
        ];

        return parent::getOffsets($original, $targetWidth, $targetHeight);
    }

    /**
     * getBaseDimension
     *
     * @param string $key width|height
     *
     * @access protected
     *
     * @return int
     */
    protected static function getBaseDimension($key)
    {
        if (isset(static::$baseDimension)) {
            return static::$baseDimension[$key];
        } elseif ($key === 'width') {
            return static::$originalImage->getImageWidth();
        }

        return static::$originalImage->getImageHeight();
    }

    /**
     * getFaceList get faces positions and sizes
     *
     * @access protected
     *
     * @return array
     */
    protected static function getFaceList()
    {
        if (!function_exists('face_detect')) {
            throw new ImageException('PHP Facedetect extension must be installed. See http://www.xarg.org/project/php-facedetect/ for more details');
        }

        $faceList = static::getFaceListFromClassifier(self::CLASSIFIER_FACE);

        if (!is_array($faceList)) {
            $faceList = [];
        }

        $profileList = static::getFaceListFromClassifier(self::CLASSIFIER_PROFILE);

        if (!is_array($profileList)) {
            $profileList = [];
        }

        return array_merge($faceList, $profileList);
    }

    /**
     * getFaceListFromClassifier
     *
     * @param string $classifier
     *
     * @access protected
     *
     * @return array
     */
    protected static function getFaceListFromClassifier($classifier)
    {
        $file = tmpfile();

        static::$originalImage->writeImageFile($file);

        $faceList = face_detect(stream_get_meta_data($file)['uri'], __DIR__.$classifier);

        fclose($file);

        return $faceList;
    }

    /**
     * getSafeZoneList
     *
     * @access protected
     *
     * @return array
     */
    protected static function getSafeZoneList()
    {
        // the local key is the current image width-height
        $key = static::$originalImage->getImageWidth().'-'.static::$originalImage->getImageHeight();

        if (isset(static::$safeZoneList[$key])) {
            return static::$safeZoneList[$key];
        }

        $faceList = static::getFaceList();

        // getFaceList works on the main image, so we use a ratio between main/current image
        $xRatio = static::getBaseDimension('width') / static::$originalImage->getImageWidth();
        $yRatio = static::getBaseDimension('height') / static::$originalImage->getImageHeight();

        $safeZoneList = array();

        foreach ($faceList as $face) {
            $hw = ceil($face['w'] / 2);
            $hh = ceil($face['h'] / 2);

            $safeZone = array(
                'left' => $face['x'] - $hw,
                'right' => $face['x'] + $face['w'] + $hw,
                'top' => $face['y'] - $hh,
                'bottom' => $face['y'] + $face['h'] + $hh
            );

            $safeZoneList[] = array(
                'left' => round($safeZone['left'] / $xRatio),
                'right' => round($safeZone['right'] / $xRatio),
                'top' => round($safeZone['top'] / $yRatio),
                'bottom' => round($safeZone['bottom'] / $yRatio),
            );
        }

        static::$safeZoneList[$key] = $safeZoneList;

        return static::$safeZoneList[$key];
    }
}
