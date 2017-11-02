<?php

require __DIR__.'/bootstrap.php';

use Imagecow\Image;

$image = Image::fromFile(__DIR__.'/my-image.jpg', $library);

$watermark = Image::fromFile(__DIR__.'/logo.png', $library);
$watermark->opacity(50);

$image->watermark($watermark, '100%-50px', 'bottom');

$image->show();
