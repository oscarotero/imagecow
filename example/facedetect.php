<?php

require dirname(__DIR__).'/src/autoloader.php';

use Imagecow\Image;

Image::fromFile(__DIR__.'/my-image.jpg')->crop(200, 200, Image::CROP_FACE)->show();
