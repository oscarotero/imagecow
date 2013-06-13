<?php
set_time_limit(0);
ini_set('memory_limit', -1);

use Imagecow\Image;

include('../Imagecow/autoloader.php');

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
