<?php
use Imagecow\Image;

include '../Imagecow/autoloader.php';

$transform = isset($_COOKIE['Imagecow_detection']) ? Image::getResponsiveOperations($_COOKIE['Imagecow_detection'], $_GET['transform']) : $_GET['transform'];

$Image = Image::create();

$Image->load(__DIR__.'/pictures/'.$_GET['img'])->autoRotate()->transform($transform);

if ($Error = $Image->getError()) {
    $Error->getImage()->show();
} else {
    $Image->show();
}
