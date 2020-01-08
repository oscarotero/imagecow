<?php

require __DIR__.'/bootstrap.php';

use Imagecow\Image;

$quality = 8;
$image = Image::fromFile(__DIR__.'/my-image.jpg');
$image->autoRotate();

$watermark = Image::fromFile('logo.png');
$image->watermark($watermark, $x = 'right', $y = 'bottom');

$image->quality($quality);
$image->resize(500, 500);
$image->show();