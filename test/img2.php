<?php
include('loader.php');

use Fol\Loader;
use Imagecow\Image;

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();


$Image = Image::create();

$Image->load('berto.jpg');

$Image->fixOrientation()->show();
die();

print_r($Image->getExifData());
die();

if ($Error = $Image->getError()) {
	$Error->getImage()->show();
} else {
	$Image->show();
}
?>