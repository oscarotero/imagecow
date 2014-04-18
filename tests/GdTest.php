<?php
include_once dirname(__DIR__).'/Imagecow/autoloader.php';
include_once __DIR__.'/ImageTest_.php';

use Imagecow\Image;

class GdTest extends ImageTest_
{
    protected static $library = 'Gd';
}
