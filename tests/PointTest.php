<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Geometry;
use Brick\Geo\Point;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @dataProvider providerFactory
     *
     * @param array   $arguments  The arguments to pass to the factory method.
     * @param array   $coords     The expected coordinates of the resulting point.
     * @param boolean $is3D       Whether the resulting point is expected to have a Z coordinate.
     * @param boolean $isMeasured Whether the resulting point is expected to have a M coordinate.
     */
    public function testFactory(array $arguments, array $coords, $is3D, $isMeasured)
    {
        $this->is3D($is3D);
        $this->isMeasured($isMeasured);

        $point = call_user_func_array([Point::class, 'factory'], $arguments);
        $this->assertPointEquals($coords, $is3D, $isMeasured, 0, $point);
    }

    /**
     * @return array
     */
    public function providerFactory()
    {
        return [
            [['1.2', '3.4'], [1.2, 3.4], false, false],
            [['1.2', '3.4', 5.6], [1.2, 3.4, 5.6], true, false],
            [['1.2', '3.4', null, '5.6'], [1.2, 3.4, 5.6], false, true],
            [['1.2', '3.4', '5.6', '7.8'], [1.2, 3.4, 5.6, 7.8], true, true],
        ];
    }

    public function testGeometryType()
    {
        $this->assertSame('Point', Point::factory(0, 0)->geometryType());
    }

    public function testDimension()
    {
        $this->assertSame(0, Point::factory(0, 0)->dimension());
    }

    public function testIsEmpty()
    {
        $this->assertFalse(Point::factory(0, 0)->isEmpty());
    }

    /**
     * @dataProvider providerIs3D
     *
     * @param array   $coordinates The point coordinates.
     * @param boolean $is3D        Whether the point is 3D.
     */
    public function testIs3D(array $coordinates, $is3D)
    {
        $this->is3D($is3D);

        /** @var Point $point */
        $point = call_user_func_array([Point::class, 'factory'], $coordinates);
        $this->assertSame($is3D, $point->is3D());
    }

    /**
     * @return array
     */
    public function providerIs3D()
    {
        return [
            [[1.2, 3.4], false],
            [[1.2, 3.4, 5.6], true],
            [[1.2, 3.4, 5.6, 7.8], true],
            [[1.2, 3.4, null, 7.8], false]
        ];
    }

    /**
     * @dataProvider providerIsMeasured
     *
     * @param array   $coordinates The point coordinates.
     * @param boolean $isMeasured  Whether the point is measured.
     */
    public function testIsMeasured(array $coordinates, $isMeasured)
    {
        $this->isMeasured($isMeasured);

        /** @var Point $point */
        $point = call_user_func_array([Point::class, 'factory'], $coordinates);
        $this->assertSame($isMeasured, $point->isMeasured());
    }

    /**
     * @return array
     */
    public function providerIsMeasured()
    {
        return [
            [[1.2, 3.4], false],
            [[1.2, 3.4, 5.6], false],
            [[1.2, 3.4, 5.6, 7.8], true],
            [[1.2, 3.4, null, 7.8], true]
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
        $point = Point::factory(1, 2);
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

// unsupported in PostGIS
//            ['GEOMETRYCOLLECTION(POINT(1 2))', true],
//            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 2))', true],
//            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 3))', false]
        ];
    }
}
