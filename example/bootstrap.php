<?php

require dirname(__DIR__).'/src/autoloader.php';

$library = isset($_GET['library']) && ($_GET['library'] === 'Imagick') ? 'Imagick' : 'Gd';
