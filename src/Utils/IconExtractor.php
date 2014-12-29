<?php
namespace Imagecow\Utils;

class IconExtractor
{
    protected $image;


    /**
     * Contructor
     *
     * @param string $filename The path of ico file
     */
    public function __construct($filename)
    {
        if (!extension_loaded('imagick')) {
            throw new \Exception('IconExtractor needs imagick extension');
        }

        $image = new \Imagick();
        $image->readImage($filename);

        $this->image = $image;
    }


    /**
     * Get the better quality image found in the icon
     *
     * @return \Imagecow\Image
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

        $better = new \Imagecow\Image(new \Imagecow\Libs\Imagick($this->image));
        $better->format('png');

        return $better;
    }
}
