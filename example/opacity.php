<?php
require dirname(__DIR__).'/src/autoloader.php';

use Imagecow\Image;

$image = Image::fromFile(__DIR__.'/logo.png');
$image->opacity(50);

$image->show();
