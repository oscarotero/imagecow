<?php
include('loader.php');

use Fol\Loader;

Loader::setLibrariesPath(dirname(__DIR__));
Loader::register();


$Image = Imagecow\Image::create();

$Image->load('imaxe.jpg');

$Image->crop(200, 200, 'center', 'bottom');
$Image->transform(isset($_GET['transform']) ? $_GET['transform'] : 'resize,30%');
$Image->show();
?>