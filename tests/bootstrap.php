<?php

error_reporting(E_ALL);

include_once dirname(__DIR__).'/vendor/autoload.php';
include_once dirname(__DIR__).'/src/autoloader.php';

// backward compatibility with PHP 5.5, PHPUnit 4.8 and PHPUnit 6.2
if (!class_exists('PHPUnit_Framework_TestCase') && class_exists('\PHPUnit\Framework\TestCase')) {
    class_alias('\PHPUnit\Framework\TestCase', 'PHPUnit_Framework_TestCase');
}

if (!class_exists('PHPUnit_Framework_Error_Notice') && class_exists('\PHPUnit\Framework\Error\Notice')) {
    class_alias('\PHPUnit\Framework\Error\Notice', 'PHPUnit_Framework_Error_Notice');
}

include_once __DIR__.'/ImageTest_.php';

PHPUnit_Framework_Error_Notice::$enabled = true;
