<?php
namespace Imagecow\Utils;

class SvgExtractor
{
    protected $image;


    /**
     * Contructor
     *
     * @param string $filename The path of the svg file
     */
    public function __construct($filename)
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception('SvgExtractor needs imagick extension');
        }

        $image = new \Imagick();
        $image->setBackgroundColor(new \ImagickPixel('transparent'));

        if (!is_file($filename)) {
            throw new \Exception("'$filename' is not a readable file");
        }

        $image->readImage($filename);

        $this->image = $image;
    }


    /**
     * Get the svg as an image
     *
     * @return \Imagecow\Image
     */
    public function get($width = 0, $height = 0)
    {
        $imageWidth = $this->image->getImageWidth();
        $imageHeight = $this->image->getImageHeight();

        if ($width !== 0 && ($height === 0 || ($imageWidth/$width) > ($imageHeight/$height))) {
            $height = ceil(($width/$imageWidth) * $imageHeight);
        } elseif ($height !== 0) {
            $width = ceil(($height/$imageHeight) * $imageWidth);
        } else {
            $width = $imageWidth;
            $height = $imageHeight;
        }

        $image = new \Imagick();
        $image->setBackgroundColor(new \ImagickPixel('transparent'));
        $image->setResolution($width, $height);

        $blob = $this->image->getImageBlob();

        $blob = preg_replace('/<svg([^>]*) width="([^"]*)"/si', '<svg$1 width="'.$width.'px"', $blob);
        $blob = preg_replace('/<svg([^>]*) height="([^"]*)"/si', '<svg$1 height="'.$height.'px"', $blob);

        $image->readImageBlob($blob);
        $image->setImageFormat("png");

        return new \Imagecow\Image(new \Imagecow\Libs\Imagick($image));
    }
}
