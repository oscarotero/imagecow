<?php
set_time_limit(0);
ini_set('memory_limit', -1);

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

foreach (glob(__DIR__.'/pictures/*') as $picture) {
	if (!preg_match('/\/[0-9]+\.[a-z]{2,4}$/', $picture)) {
		continue;
	}

	$Image = Image::create('Imagick');

	$Image->load($picture);
	$Image->resize(250)->save(preg_replace('/\.([a-z]{2,4})$/', '-resize-imagick.$1', $picture));

	$Image->load($picture);
	$Image->crop(200, 220)->save(preg_replace('/\.([a-z]{2,4})$/', '-crop-imagick.$1', $picture));

	$Image->load($picture);
	$Image->resizeCrop(250, 200)->save(preg_replace('/\.([a-z]{2,4})$/', '-resize-crop-imagick.$1', $picture));

	$Image = Image::create('Gd');

	$Image->load($picture);
	$Image->resize(250)->save(preg_replace('/\.([a-z]{2,4})$/', '-resize-gd.$1', $picture));

	$Image->load($picture);
	$Image->crop(200, 220)->save(preg_replace('/\.([a-z]{2,4})$/', '-crop-gd.$1', $picture));

	$Image->load($picture);
	$Image->resizeCrop(250, 200)->save(preg_replace('/\.([a-z]{2,4})$/', '-resize-crop-gd.$1', $picture));
}
