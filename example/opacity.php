<?php

require __DIR__.'/bootstrap.php';

use Imagecow\Image;

$image = Image::fromFile(__DIR__.'/logo.png', $library);
$image->opacity(50);

$image->show();
