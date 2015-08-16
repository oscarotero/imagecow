<?php
include_once dirname(__DIR__).'/src/autoloader.php';
include_once __DIR__.'/ImageTest_.php';

class GdTest extends ImageTest_
{
    protected static $library = 'Gd';
}
