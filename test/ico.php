<?php
use Imagecow\Utils\Icon;

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

$Icon = new Icon('http://google.com/favicon.ico');

foreach ($Icon->formats as $index => $data) {
	if ($data['Width'] >= 16) {
		$Image = $Icon->get($index);

		header('Content-Type: image/png');
		imagepng($Image);
		die();
	}
}
?>