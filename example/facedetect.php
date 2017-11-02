<?php

require __DIR__.'/bootstrap.php';

use Imagecow\Image;

Image::fromFile(__DIR__.'/my-image.jpg', $library)->crop(200, 200, Image::CROP_FACE)->show();
