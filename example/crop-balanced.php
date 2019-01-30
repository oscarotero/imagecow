<?php

require __DIR__.'/bootstrap.php';

use Imagecow\Image;

$paddedFilePath = __DIR__.'/square.jpg';

$imageCowPadded = Image::fromFile($paddedFilePath, Image::LIB_IMAGICK);

$imageCowPadded
     ->crop(50, 50, Image::CROP_BALANCED)
     ->format('png')
     ->show();
