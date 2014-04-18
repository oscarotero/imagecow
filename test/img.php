<?php
use Imagecow\Image;

include '../Imagecow/autoloader.php';

$image = Image::create(__DIR__.'/pictures/05.jpg');

$image->resize(300)->crop(150, 150);

$image->show();
