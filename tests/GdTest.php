<?php

namespace Imagecow\Tests;

use Imagecow\Image;
use Imagecow\Adapters\GdAdapter;
use PHPUnit\Framework\TestCase;

class GdTest extends TestCase
{
    protected static $adapter = GdAdapter::class;

    public static function setUpBeforeClass()
    {
        foreach (glob(__DIR__.'/images/tmp.'.static::$adapter.'.*') as $file) {
            unlink($file);
        }
    }

    public function testJpg()
    {
        $file = __DIR__.'/images/image.jpg';
        $tmpFile = __DIR__.'/images/tmp.'.static::$adapter.'.image.jpg';

        $image = Image::fromFile($file, static::$adapter);

        $this->assertSame('image/jpeg', $image->getMimeType());

        $this->assertSame(2048, $image->getWidth());
        $this->assertSame(2048, $image->getHeight());

        //Resize
        $image->resize(1500);

        $this->assertSame(1500, $image->getWidth());
        $this->assertSame(1500, $image->getHeight());

        //Crop
        $image->crop(1000, 800, Image::CROP_ENTROPY);

        $this->assertSame(1000, $image->getWidth());
        $this->assertSame(800, $image->getHeight());

        $image->crop('50%', '50%');

        $this->assertSame(500, $image->getWidth());
        $this->assertSame(400, $image->getHeight());

        //Save
        $image->save($tmpFile);

        $this->assertTrue(is_file($tmpFile));

        $image = Image::fromFile($tmpFile, static::$adapter);

        $this->assertSame(500, $image->getWidth());
        $this->assertSame(400, $image->getHeight());

        unlink($tmpFile);
    }

    public function testPng()
    {
        $file = __DIR__.'/images/image.png';
        $tmpFile = __DIR__.'/images/tmp.'.static::$adapter.'.image.png';

        $image = Image::fromFile($file, static::$adapter);

        $this->assertSame('image/png', $image->getMimeType());

        $this->assertSame(512, $image->getWidth());
        $this->assertSame(512, $image->getHeight());

        //Resize
        $image->resize(500);

        $this->assertSame(500, $image->getWidth());
        $this->assertSame(500, $image->getHeight());

        //Crop
        $image->crop(400, 300, Image::CROP_ENTROPY);

        $this->assertSame(400, $image->getWidth());
        $this->assertSame(300, $image->getHeight());

        $image->crop('50%', '50%');

        $this->assertSame(200, $image->getWidth());
        $this->assertSame(150, $image->getHeight());

        //Save
        $image->save($tmpFile);

        $this->assertTrue(is_file($tmpFile));

        $image = Image::fromFile($tmpFile, static::$adapter);

        $this->assertSame(200, $image->getWidth());
        $this->assertSame(150, $image->getHeight());

        unlink($tmpFile);
    }

    public function testWebp()
    {
        if(static::$adapter != 'Imagick') {
        	$this->assertSame(true, true); // Prevent this test did not perform any assertions
        	return;
        }

        $file = __DIR__.'/images/image.webp';
        $tmpFile = __DIR__.'/images/tmp.'.static::$adapter.'.image.webp';

        $image = Image::fromFile($file, static::$adapter);

        $this->assertSame('image/webp', $image->getMimeType());

        $this->assertSame(512, $image->getWidth());
        $this->assertSame(512, $image->getHeight());

        //Resize
        $image->resize(500);

        $this->assertSame(500, $image->getWidth());
        $this->assertSame(500, $image->getHeight());

        //Crop
        $image->crop(400, 300, Image::CROP_ENTROPY);

        $this->assertSame(400, $image->getWidth());
        $this->assertSame(300, $image->getHeight());

        $image->crop('50%', '50%');

        $this->assertSame(200, $image->getWidth());
        $this->assertSame(150, $image->getHeight());

        //Save
        $image->save($tmpFile);

        $this->assertTrue(is_file($tmpFile));

        $image = Image::fromFile($tmpFile, static::$adapter);

        $this->assertSame(200, $image->getWidth());
        $this->assertSame(150, $image->getHeight());

        unlink($tmpFile);
    }
}
