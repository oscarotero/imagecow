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
            array('x', 500, 1000, 500),
            array('x', '0%', 1000, 0),
            array('x', '0.0%', 1000, 0),
            array('x', '100%', 1000, 1000),
            array('x', '100%', 1000.0, 1000),
            array('x', '75%', 1000, 750),
            array('x', '75.5%', 1000, 755),
            array('x', '755', 1000, 755),
            array('x', '755.0', 1000, 755),
            array('x', '755.5', 1000, 755),
            array('y', 'top', 1000, 0),
            array('y', 'middle', 1000, 500),
            array('y', 'bottom', 1000, 1000),
        );
    }

    /**
     * @dataProvider integerValueDataProvider
     */
    public function testIntegerValue($direction, $value, $relatedValue, $expected)
    {
        $result = Dimmensions::getIntegerValue($direction, $value, $relatedValue, true);

        $this->assertSame($expected, $result);
    }

    public function percentageValueDataProvider()
    {
        return array(
            array('y', 500, 1000, '50%'),
            array('y', 0, 1000, '0%'),
            array('y', 0.0, 1000.0, '0%'),
            array('y', 1000, 1000, '100%'),
            array('y', 750, 1000, '75%'),
            array('y', 755, 1000, '75.5%'),
            array('y', 755.0, 1000, '75.5%'),
            array('y', 'top', 1000, '0%'),
            array('y', 'middle', 1000, '50%'),
            array('y', 'bottom', 1000, '100%'),
        );
    }

    /**
     * @dataProvider percentageValueDataProvider
     */
    public function testPercentageValue($direction, $value, $relatedValue, $expected)
    {
        $result = Dimmensions::getPercentageValue($direction, $value, $relatedValue, true);

        $this->assertSame($expected, $result);
    }

    public function positionValueDataProvider()
    {
        return array(
            array('x', 25, 500, 1000, 25),
            array('x', '50%', 500, 1000, 250),
            array('x', '50.0%', 500, 1000, 250),
            array('x', '0%', 500, 1000, 0),
            array('x', '100%', 500, 1000, 500),
            array('x', '100%', 500, 1000, 500),
            array('x', 750, 500, 1000, 750),
            array('x', 750.0, 500, 1000, 750),
        );
    }

    /**
     * @dataProvider positionValueDataProvider
     */
    public function testPositionValue($direction, $position, $newSize, $oldSize, $expected)
    {
        $result = Dimmensions::getPositionValue($direction, $position, $newSize, $oldSize);

        $this->assertSame($expected, $result);
    }
}
