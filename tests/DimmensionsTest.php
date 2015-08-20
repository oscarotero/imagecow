<?php
use Imagecow\Utils\Dimmensions;

class DimmensionsTest extends PHPUnit_Framework_TestCase
{
    public function resizeDataProvider()
    {
        return array(
            array(1000, 500, 500, 0, false, 500, 250),
            array(1000, 500, 500.0, 0.0, false, 500, 250),
            array(1000, 500, 0, 250, false, 500, 250),
            array(1000, 500, 500, 250, false, 500, 250),
            array(1000, 500, 500, 500, false, 500, 250),
            array(1000, 500, 1000, 1000, false, 1000, 500),
            array(1000, 500, 900, 900, false, 900, 450),
            array(1000, 500, 2000, 500, false, 1000, 500),
            array(1000, 500, 2000, 2000, false, 2000, 1000),

            array(1000, 500, 500, 0, true, 500, 250),
            array(1000, 500, 0, 250, true, 500, 250),
            array(1000, 500, 500, 250, true, 500, 250),
            array(1000, 500, 500, 500, true, 1000, 500),
            array(1000, 500, 1000, 1000, true, 2000, 1000),
            array(1000, 500, 900, 900, true, 1800, 900),
            array(1000, 500, 2000, 500, true, 2000, 1000),
            array(1000, 500, 2000, 2000, true, 4000, 2000),
        );
    }

    /**
     * @dataProvider resizeDataProvider
     */
    public function testResize($imageWidth, $imageHeight, $newWidth, $newHeight, $cover, $expectedWidth, $expectedHeight)
    {
        list($width, $height) = Dimmensions::getResizeDimmensions($imageWidth, $imageHeight, $newWidth, $newHeight, $cover);

        $this->assertSame($expectedWidth, $width);
        $this->assertSame($expectedHeight, $height);
    }

    public function integerValueDataProvider()
    {
        return array(
            array(500, 1000, 500),
            array('0%', 1000, 0),
            array('0.0%', 1000, 0),
            array('100%', 1000, 1000),
            array('100%', 1000.0, 1000),
            array('75%', 1000, 750),
            array('75.5%', 1000, 755),
            array('755', 1000, 755),
            array('755.0', 1000, 755),
            array('755.5', 1000, 755),
            array('top', 1000, 0),
            array('middle', 1000, 500),
            array('bottom', 1000, 1000),
        );
    }

    /**
     * @dataProvider integerValueDataProvider
     */
    public function testIntegerValue($value, $relatedValue, $expected)
    {
        $result = Dimmensions::getIntegerValue($value, $relatedValue, true);

        $this->assertSame($expected, $result);
    }

    public function percentageValueDataProvider()
    {
        return array(
            array(500, 1000, '50%'),
            array(0, 1000, '0%'),
            array(0.0, 1000.0, '0%'),
            array(1000, 1000, '100%'),
            array(750, 1000, '75%'),
            array(755, 1000, '75.5%'),
            array(755.0, 1000, '75.5%'),
            array('top', 1000, '0%'),
            array('middle', 1000, '50%'),
            array('bottom', 1000, '100%'),
        );
    }

    /**
     * @dataProvider percentageValueDataProvider
     */
    public function testPercentageValue($value, $relatedValue, $expected)
    {
        $result = Dimmensions::getPercentageValue($value, $relatedValue, true);

        $this->assertSame($expected, $result);
    }

    public function positionValueDataProvider()
    {
        return array(
            array(25, 500, 1000, 25),
            array('50%', 500, 1000, 250),
            array('50.0%', 500, 1000, 250),
            array('0%', 500, 1000, 0),
            array('100%', 500, 1000, 500),
            array('100%', 500, 1000, 500),
            array(750, 500, 1000, 750),
            array(750.0, 500, 1000, 750),
        );
    }

    /**
     * @dataProvider positionValueDataProvider
     */
    public function testPositionValue($position, $newSize, $oldSize, $expected)
    {
        $result = Dimmensions::getPositionValue($position, $newSize, $oldSize);

        $this->assertSame($expected, $result);
    }
}
