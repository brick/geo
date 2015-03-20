<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Geometry;
use Brick\Geo\LinearRing;
use Brick\Geo\Point;
use Brick\Geo\Polygon;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @dataProvider providerFactoryMethodsAndAccessors
     *
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     * @param int        $srid
     */
    public function testFactoryMethodsAndAccessors(Point $point, $x, $y, $z, $m, $srid)
    {
        $this->assertSame($x, $point->x());
        $this->assertSame($y, $point->y());
        $this->assertSame($z, $point->z());
        $this->assertSame($m, $point->m());
        $this->assertSame($srid, $point->SRID());
    }

    /**
     * @return array
     */
    public function providerFactoryMethodsAndAccessors()
    {
        return [
            [Point::xy(1.2, 3.4), 1.2, 3.4, null, null, 0],
            [Point::xy('2.3', '4.5', '4326'), 2.3, 4.5, null, null, 4326],
            [Point::xyz(1.2, 3.4, 5.6), 1.2, 3.4, 5.6, null, 0],
            [Point::xyz('2.3', '4.5', '6.7', 4326), 2.3, 4.5, 6.7, null, 4326],
            [Point::xym(1.2, 3.4, 5.6), 1.2, 3.4, null, 5.6, 0],
            [Point::xym('2.3', '4.5', '6.7', 4326), 2.3, 4.5, null, 6.7, 4326],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), 1.2, 3.4, 5.6, 7.8, 0],
            [Point::xyzm('2.3', '4.5', '6.7', '8.9', 4326), 2.3, 4.5, 6.7, 8.9, 4326]
        ];
    }

    public function testGeometryType()
    {
        $this->assertSame('Point', Point::xy(0, 0)->geometryType());
    }

    public function testDimension()
    {
        $this->assertSame(0, Point::xy(0, 0)->dimension());
    }

    /**
     * @dataProvider providerCoordinateDimension
     *
     * @param Point $point
     * @param int   $coordinateDimension
     */
    public function testCoordinateDimension(Point $point, $coordinateDimension)
    {
        $this->assertSame($coordinateDimension, $point->coordinateDimension());
    }

    /**
     * @return array
     */
    public function providerCoordinateDimension()
    {
        return [
            [Point::xy(1.2, 3.4), 2],
            [Point::xyz(1.2, 3.4, 5.6), 3],
            [Point::xym(1.2, 3.4, 7.8), 3],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), 4],
        ];
    }

    public function testIsEmpty()
    {
        $this->assertFalse(Point::xy(0, 0)->isEmpty());
    }

    /**
     * @dataProvider providerIs3D
     *
     * @param Point   $point The point to test.
     * @param boolean $is3D  Whether the point is 3D.
     */
    public function testIs3D(Point $point, $is3D)
    {
        $this->assertSame($is3D, $point->is3D());
    }

    /**
     * @return array
     */
    public function providerIs3D()
    {
        return [
            [Point::xy(1.2, 3.4), false],
            [Point::xy(1.2, 3.4, 4326), false],
            [Point::xyz(1.2, 3.4, 5.6), true],
            [Point::xyz(1.2, 3.4, 5.6, 4326), true],
            [Point::xym(1.2, 3.4, 7.8), false],
            [Point::xym(1.2, 3.4, 7.8, 4326), false],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), true],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8, 4326), true]
        ];
    }

    /**
     * @dataProvider providerIsMeasured
     *
     * @param Point   $point      The point to test.
     * @param boolean $isMeasured Whether the point is measured.
     */
    public function testIsMeasured(Point $point, $isMeasured)
    {
        $this->assertSame($isMeasured, $point->isMeasured());
    }

    /**
     * @return array
     */
    public function providerIsMeasured()
    {
        return [
            [Point::xy(1.2, 3.4), false],
            [Point::xy(1.2, 3.4, 4326), false],
            [Point::xyz(1.2, 3.4, 5.6), false],
            [Point::xyz(1.2, 3.4, 5.6, 4326), false],
            [Point::xym(1.2, 3.4, 7.8), true],
            [Point::xym(1.2, 3.4, 7.8, 4326), true],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), true],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8, 4326), true]
        ];
    }

    /**
     * @dataProvider providerToArray
     *
     * @param Point $point
     * @param array $array
     */
    public function testToArray(Point $point, array $array)
    {
        $this->assertSame($array, $point->toArray());
    }

    /**
     * @return array
     */
    public function providerToArray()
    {
        return [
            [Point::xy('1.2', '3.4'), [1.2, 3.4]],
            [Point::xy('1.2', '3.4', 4326), [1.2, 3.4]],
            [Point::xyz('1.2', '3.4', '5.6'), [1.2, 3.4, 5.6]],
            [Point::xyz('1.2', '3.4', '5.6', 4326), [1.2, 3.4, 5.6]],
            [Point::xym('1.2', '3.4', '5.6'), [1.2, 3.4, 5.6]],
            [Point::xym('1.2', '3.4', '5.6', 4326), [1.2, 3.4, 5.6]],
            [Point::xyzm('1.2', '3.4', '5.6', '7.8'), [1.2, 3.4, 5.6, 7.8]],
            [Point::xyzm('1.2', '3.4', '5.6', '7.8', 4326), [1.2, 3.4, 5.6, 7.8]]
        ];
    }

    /**
     * @dataProvider providerEquals
     *
     * @param string  $geometry The WKT representation of the Geometry to compare to.
     * @param boolean $isEqual  Whether the geometries are equal.
     */
    public function testEquals($geometry, $isEqual)
    {
        if (preg_match('/^GEOMETRYCOLLECTION/', $geometry)) {
            $this->skipPostgreSQL('This comparison is not available on PostGIS');
        }

        $point = Point::xy(1, 2);
        $geometry = Geometry::fromText($geometry);

        $this->assertSame($isEqual, $point->equals($geometry));
        $this->assertSame($isEqual, $geometry->equals($point));
    }

    /**
     * @return array
     */
    public function providerEquals()
    {
        return [
            ['POINT(1 2)', true],
            ['POINT(3 2)', false],
            ['POINT(1 3)', false],
            ['POINT(2 3)', false],
            ['LINESTRING(1 2, 1 3)', false],
            ['GEOMETRYCOLLECTION(POINT(1 2))', true],
            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 2))', true],
            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 3))', false]
        ];
    }

    /**
     * @dataProvider providerEnvelope
     *
     * @param Point $point
     */
    public function testEnvelope(Point $point)
    {
        $this->assertTrue($point->envelope()->equals($point));
    }

    /**
     * @return array
     */
    public function providerEnvelope()
    {
        return [
            [Point::xy(1, 2)],
            [Point::xyz(2, 3, 4)],
            [Point::xym(3, 4, 5)],
            [Point::xyzm(4, 5, 6, 7)]
        ];
    }
}
