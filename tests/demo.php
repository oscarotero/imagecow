<?php
include_once dirname(__DIR__).'/src/autoloader.php';

use Imagecow\Image;

$image = Image::create(__DIR__.'/images/image.jpg');

$image
    ->setCenterPoint('center', 'top')
    ->setEnlarge(true)
    ->resizeCrop(300, 400)
    ->resize(400);

//$image->show();
