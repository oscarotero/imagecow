<?php

namespace Imagecow\Tests;

use Imagecow\Adapters\ImagickAdapter;

class ImagickTest extends GdTest
{
    protected static $library = ImagickAdapter::class;
}
