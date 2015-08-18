<?php
include_once dirname(__DIR__).'/vendor/autoload.php';

use Imagecow\Image;

Image::create(__DIR__.'/images/crop.jpg')
    ->setCropMethod('Balanced')
    ->resizeCrop(100, 200)
    ->show();
