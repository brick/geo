<?php

namespace Brick\Geo\Tests;

use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;

/**
 * Base class for Geometry tests.
 */
class AbstractTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     * @param Point      $point
     */
    final protected function assertPointEquals($x, $y, $z, $m, Point $point)
    {
        $this->assertSame($x, $point->x());
        $this->assertSame($y, $point->y());
        $this->assertSame($z, $point->z());
        $this->assertSame($m, $point->m());
    }

    /**
     * @param array $coords
     *
     * @return Point
     */
    final protected function createPoint(array $coords)
    {
        return call_user_func_array([Point::class, 'factory'], $coords);
    }

    /**
     * @param array $coords
     *
     * @return LineString
     */
    final protected function createLineString(array $coords)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = self::createPoint($point);
        }

        return LineString::factory($points);
    }

    /**
     * @param array $coords
     *
     * @return Polygon
     */
    final protected function createPolygon(array $coords)
    {
        $rings = [];

        foreach ($coords as $point) {
            $rings[] = self::createLineString($point);
        }

        return Polygon::factory($rings);
    }

    /**
     * @param array $coords
     *
     * @return MultiPoint
     */
    final protected function createMultiPoint(array $coords)
    {
        $points = [];

        foreach ($coords as $point) {
            $points[] = self::createPoint($point);
        }

        return MultiPoint::factory($points);
    }

    /**
     * @param array $coords
     *
     * @return MultiLineString
     */
    final protected function createMultiLineString(array $coords)
    {
        $lineStrings = [];

        foreach ($coords as $lineString) {
            $lineStrings[] = self::createLineString($lineString);
        }

        return MultiLineString::factory($lineStrings);
    }

    /**
     * @param array $coords
     *
     * @return MultiPolygon
     */
    final protected function createMultiPolygon(array $coords)
    {
        $polygons = [];

        foreach ($coords as $polygon) {
            $polygons[] = self::createPolygon($polygon);
        }

        return MultiPolygon::factory($polygons);
    }
}
