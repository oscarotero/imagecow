<?php
include_once dirname(__DIR__).'/Imagecow/autoloader.php';

use Imagecow\Image;

class StylecowTest extends PHPUnit_Framework_TestCase
{
    public function testResponsiveOperations()
    {
        $cookie = '400,300,fast';

        $operations = array(
            array(
                'resize,500,300;max-width=400:resize,400',
                'resize,500,300|resize,400'
            ),
            array(
                'resize,500,300;min-width=400:resize,400',
                'resize,500,300|resize,400'
            ),
            array(
                'resize,500,300;max-width=399:resize,400',
                'resize,500,300'
            ),
            array(
                'resize,500,300;min-width=401:resize,400',
                'resize,500,300'
            ),
            array(
                'resize,500,300;min-width=399,max-width=401:resize,400',
                'resize,500,300|resize,400'
            ),
            array(
                'resize,500,300;min-width=398,max-width=399:resize,400',
                'resize,500,300'
            ),
            array(
                'resize,500,300;min-width=398,max-width=400:resize,400',
                'resize,500,300|resize,400'
            )
        );

        foreach ($operations as $test) {
            $this->assertEquals($test[1], Image::getResponsiveOperations($cookie, $test[0]));
        }
    }
}
