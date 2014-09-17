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
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     */
    public function testFactory(Point $point, $x, $y, $z, $m)
    {
        $this->assertPointEquals($x, $y, $z, $m, $point);
    }

    /**
     * @return array
     */
    public function providerFactory()
    {
        return [
            [Point::factory('1.2', '3.4'), 1.2, 3.4, null, null],
            [Point::factory('1.2', '3.4', '5.6'), 1.2, 3.4, 5.6, null],
            [Point::factory('1.2', '3.4', null, '5.6'), 1.2, 3.4, null, 5.6],
            [Point::factory('1.2', '3.4', '5.6', '7.8'), 1.2, 3.4, 5.6, 7.8],
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
     * @return \Generator
     */
    public function providerEquals()
    {
        return [
            ['POINT(1 2)', true],
            ['POINT(3 2)', false],
            ['POINT(1 3)', false],
            ['POINT(2 3)', false],
            ['LINESTRING(1 2, 1 2)', true],
            ['LINESTRING(1 2, 1 3)', false],
            ['GEOMETRYCOLLECTION(POINT(1 2))', true],
            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 2))', true],
            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 3))', false]
        ];
    }
}
