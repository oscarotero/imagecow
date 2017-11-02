<?php

require dirname(__DIR__).'/src/autoloader.php';

use Imagecow\Image;

$image = Image::fromFile(__DIR__.'/my-image.jpg', 'Gd');
$image->blur();

$image->show();
