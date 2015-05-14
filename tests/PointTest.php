<?php

namespace Brick\Geo\Tests;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @expectedException \Brick\Geo\Exception\InvalidGeometryException
     * @dataProvider providerConstructorWithInvalidCoordinates
     *
     * @param boolean $z
     * @param boolean $m
     * @param float   ...$coords
     */
    public function testConstructorWithInvalidCoordinates($z, $m, ...$coords)
    {
        new Point(new CoordinateSystem($z, $m), ...$coords);
    }

    /**
     * @return array
     */
    public function providerConstructorWithInvalidCoordinates()
    {
        return [
            [false, false, 1],
            [false, false, 1, 2, 3],
            [true,  false, 1],
            [true,  false, 1, 2],
            [true,  false, 1, 2, 3, 4],
            [false, true,  1],
            [false, true,  1, 2],
            [false, true,  1, 2, 3, 4],
            [true,  true,  1],
            [true,  true,  1, 2],
            [true,  true,  1, 2, 3],
            [true,  true,  1, 2, 3, 4, 5],
        ];
    }

    /**
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     * @param int        $srid
     */
    private function assertPointFactoryMethodAndAccessors(Point $point, $x, $y, $z, $m, $srid)
    {
        $this->assertSame($x, $point->x());
        $this->assertSame($y, $point->y());
        $this->assertSame($z, $point->z());
        $this->assertSame($m, $point->m());
        $this->assertSame($srid, $point->SRID());
        $this->assertFalse($point->isEmpty());
    }

    public function testXy()
    {
        $point = Point::xy('1.2', '3.4');
        $this->assertPointFactoryMethodAndAccessors($point, 1.2, 3.4, null, null, 0);
    }

    public function testXyWithSRID()
    {
        $point = Point::xy('1.2', '3.4', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 1.2, 3.4, null, null, 123);
    }

    public function testXyz()
    {
        $point = Point::xyz('2.3', '3.4', '4.5');
        $this->assertPointFactoryMethodAndAccessors($point, 2.3, 3.4, 4.5, null, 0);
    }

    public function testXyzWithSRID()
    {
        $point = Point::xyz('2.3', '3.4', '4.5', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 2.3, 3.4, 4.5, null, 123);
    }

    public function testXym()
    {
        $point = Point::xym('3.4', '4.5', '5.6');
        $this->assertPointFactoryMethodAndAccessors($point, 3.4, 4.5, null, 5.6, 0);
    }

    public function testXymWithSRID()
    {
        $point = Point::xym('3.4', '4.5', '5.6', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 3.4, 4.5, null, 5.6, 123);
    }

    public function testXyzm()
    {
        $point = Point::xyzm('4.5', '5.6', '6.7', '7.8');
        $this->assertPointFactoryMethodAndAccessors($point, 4.5, 5.6, 6.7, 7.8, 0);
    }

    public function testXyzmWithSRID()
    {
        $point = Point::xyzm('4.5', '5.6', '6.7', '7.8', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 4.5, 5.6, 6.7, 7.8, 123);
    }

    /**
     * @param Point   $point
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     */
    private function assertPointEmptyFactoryMethod(Point $point, $is3D, $isMeasured, $srid)
    {
        $this->assertTrue($point->isEmpty());
        $this->assertNull($point->x());
        $this->assertNull($point->y());
        $this->assertNull($point->z());
        $this->assertNull($point->m());
        $this->assertSame($is3D, $point->is3D());
        $this->assertSame($isMeasured, $point->isMeasured());
        $this->assertSame($srid, $point->SRID());
    }

    public function testXyEmpty()
    {
        $this->assertPointEmptyFactoryMethod(Point::xyEmpty(), false, false, 0);
    }

    public function testXyEmptyWithSRID()
    {
        $this->assertPointEmptyFactoryMethod(Point::xyEmpty(123), false, false, 123);
    }

    public function testXyzEmpty()
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzEmpty(), true, false, 0);
    }

    public function testXyzEmptyWithSRID()
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzEmpty(123), true, false, 123);
    }

    public function testXymEmpty()
    {
        $this->assertPointEmptyFactoryMethod(Point::xymEmpty(), false, true, 0);
    }

    public function testXymEmptyWithSRID()
    {
        $this->assertPointEmptyFactoryMethod(Point::xymEmpty(123), false, true, 123);
    }

    public function testXyzmEmpty()
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzmEmpty(), true, true, 0);
    }

    public function testXyzmEmptyWithSRID()
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzmEmpty(123), true, true, 123);
    }
}
