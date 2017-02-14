<?php

namespace Imagecow\Libs;

/**
 * Base class extended by other libraries with common methods and properties.
 */
abstract class AbstractLib
{
    protected $quality = 86;
    protected $background = [255, 255, 255];
    protected $animated = false;
    protected $progressive = false;

    /**
     * {@inheritdoc}
     */
    public function setCompressionQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * {@inheritdoc}
     */
    public function setBackground(array $background)
    {
        $this->background = $background;
    }

    /**
     * {@inheritdoc}
     */
    public function setAnimated($animated)
    {
        $this->animated = (boolean) $animated;
    }
}
