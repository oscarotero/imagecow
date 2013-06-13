<?php
use Imagecow\Image;

//A simple PSR-0 autoload function
function autoload ($className) {
	$className = ltrim($className, '\\');
	$fileName = dirname(__DIR__).'/';
	$namespace = '';
	
	if ($lastNsPos = strripos($className, '\\')) {
		$namespace = substr($className, 0, $lastNsPos);
		$className = substr($className, $lastNsPos + 1);
		$fileName  .= str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
	}

	$fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';

	if (is_file($fileName)) {
		require $fileName;
	}
}

spl_autoload_register('autoload');

$Image = Image::create('Imagick');
$Image->load('conversion.png');
$Image->format('jpg')->resize(420)->save('conversion2.jpg');
exit;

$transform = Image::getResponsiveOperations($_COOKIE['Imagecow_detection'], $_GET['transform']);

$Image = Image::create();

$Image->load($_GET['img'])->transform($transform);

if ($Error = $Image->getError()) {
	$Error->getImage()->show();
} else {
	$Image->show();
}
?>