<?php
declare(strict_types = 1);

namespace Imagecow\Adapters;

/**
 * Base class extended by other libraries with common methods and properties.
 */
trait CommonTrait
{
    private $quality = 86;
    private $background = [255, 255, 255];
    private $animated = false;
    private $progressive = false;

    public function setCompressionQuality(int $quality)
    {
        $this->quality = $quality;
    }

    public function setBackground(array $background)
    {
        $this->background = $background;
    }

    public function setAnimated(bool $animated)
    {
        $this->animated = $animated;
    }
}
