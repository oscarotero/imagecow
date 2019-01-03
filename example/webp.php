<?php

require __DIR__.'/bootstrap.php';

use Imagecow\Image;

$image = Image::fromFile(__DIR__.'/my-image.jpg', $library);
$image->format('webp');

$image->show();
