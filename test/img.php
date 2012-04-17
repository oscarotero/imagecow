<?php
include('loader.php');

use Fol\Loader;
use Imagecow\Image;

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();


$transform = Image::getResponsiveOperations($_COOKIE['Imagecow_detection'], $_GET['transform']);

$Image = Image::create();

$Image->load($_GET['img'])->transform($transform);

if ($Error = $Image->getError()) {
	$Error->getImage()->show();
} else {
	$Image->show();
}
?>