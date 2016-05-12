<?php

require dirname(__DIR__).'/src/autoloader.php';

use Imagecow\Image;

$image = Image::fromFile(__DIR__.'/my-image.jpg', 'Gd');
$watermark = Image::fromFile(__DIR__.'/logo.png', 'Gd');
$watermark->opacity(50);

$image->watermark($watermark, '100%-50px', 'bottom');

$image->show();
