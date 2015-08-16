<?php
namespace Imagecow\Libs;

/**
 * Base class extended by other libraries with common methods and properties
 */
abstract class BaseLib
{
    protected $quality = 86;
    protected $background = array(255, 255, 255);
    protected $animated = false;

    /**
     * {@inheritDoc}
     */
    public function setCompressionQuality($quality)
    {
        $this->quality = $quality;
    }

    /**
     * {@inheritDoc}
     */
    public function setBackground(array $background)
    {
        $this->background = $background;
    }

    /**
     * {@inheritDoc}
     */
    public function setAnimated($animated)
    {
        $this->animated = (boolean) $animated;
    }
}
