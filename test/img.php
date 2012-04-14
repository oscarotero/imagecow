<?php
include('loader.php');

use Fol\Loader;

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();

$Image = Imagecow\Image::create();

$Image->load(isset($_GET['img']) ? $_GET['img'] : 'img.jpg');

$transform = $Image->getResponsiveOperations($_COOKIE['imageCow_detection'], $_GET['transform']);
$Image->transform($transform);

if ($Error = $Image->getError()) {
	$Image->getImageError()->show();
} else {
	$Image->show();
}
?>