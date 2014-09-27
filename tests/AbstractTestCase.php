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
     * @param array   $coords     The expected coordinates of the Point as returned by toArray().
     * @param boolean $is3D       Whether the Point is expected to contain a Z coordinate.
     * @param boolean $isMeasured Whether the Point is expected to contain a M coordinate.
     * @param integer $srid       The expected SRID.
     * @param Point   $point      The Point to test.
     */
    final protected function assertPointEquals(array $coords, $is3D, $isMeasured, $srid, Point $point)
    {
        $this->castToFloat($coords);
        $this->assertSame($coords, $point->toArray());
        $this->assertSame($is3D, $point->is3D());
        $this->assertSame($isMeasured, $point->isMeasured());
        $this->assertSame($srid, $point->SRID());
    }

    /**
     * @param array      $coords     The expected coordinates of the LineString as returned by toArray().
     * @param boolean    $is3D       Whether the LineString is expected to contain Z coordinates.
     * @param boolean    $isMeasured Whether the LineString is expected to contain M coordinates.
     * @param LineString $lineString The LineString to test.
     */
    final protected function assertLineStringEquals(array $coords, $is3D, $isMeasured, LineString $lineString)
    {
        $this->castToFloat($coords);
        $this->assertSame($coords, $lineString->toArray());
        $this->assertSame($is3D, $lineString->is3D());
        $this->assertSame($isMeasured, $lineString->isMeasured());
    }

    /**
     * @param array   $coords     The expected coordinates of the Polygon as returned by toArray().
     * @param boolean $is3D       Whether the Polygon is expected to contain Z coordinates.
     * @param boolean $isMeasured Whether the Polygon is expected to contain M coordinates.
     * @param Polygon $polygon    The Polygon to test.
     */
    final protected function assertPolygonEquals(array $coords, $is3D, $isMeasured, Polygon $polygon)
    {
        $this->castToFloat($coords);
        $this->assertSame($coords, $polygon->toArray());
        $this->assertSame($is3D, $polygon->is3D());
        $this->assertSame($isMeasured, $polygon->isMeasured());
    }

    /**
     * @param array      $coords     The expected coordinates of the MultiPoint as returned by toArray().
     * @param boolean    $is3D       Whether the MultiPoint is expected to contain Z coordinates.
     * @param boolean    $isMeasured Whether the MultiPoint is expected to contain M coordinates.
     * @param MultiPoint $multiPoint The MultiPoint to test.
     */
    final protected function assertMultiPointEquals(array $coords, $is3D, $isMeasured, MultiPoint $multiPoint)
    {
        $this->castToFloat($coords);
        $this->assertSame($coords, $multiPoint->toArray());
        $this->assertSame($is3D, $multiPoint->is3D());
        $this->assertSame($isMeasured, $multiPoint->isMeasured());
    }

    /**
     * @param array           $coords          The expected coordinates of the MultiLineString as returned by toArray().
     * @param boolean         $is3D            Whether the MultiLineString is expected to contain Z coordinates.
     * @param boolean         $isMeasured      Whether the MultiLineString is expected to contain M coordinates.
     * @param MultiLineString $multiLineString The MultiLineString to test.
     */
    final protected function assertMultiLineStringEquals(array $coords, $is3D, $isMeasured, MultiLineString $multiLineString)
    {
        $this->castToFloat($coords);
        $this->assertSame($coords, $multiLineString->toArray());
        $this->assertSame($is3D, $multiLineString->is3D());
        $this->assertSame($isMeasured, $multiLineString->isMeasured());
    }

    /**
     * @param array        $coords       The expected coordinates of the MultiPolygon as returned by toArray().
     * @param boolean      $is3D         Whether the MultiPolygon is expected to contain Z coordinates.
     * @param boolean      $isMeasured   Whether the MultiPolygon is expected to contain M coordinates.
     * @param MultiPolygon $multiPolygon The MultiPolygon to test.
     */
    final protected function assertMultiPolygonEquals(array $coords, $is3D, $isMeasured, MultiPolygon $multiPolygon)
    {
        $this->castToFloat($coords);
        $this->assertSame($coords, $multiPolygon->toArray());
        $this->assertSame($is3D, $multiPolygon->is3D());
        $this->assertSame($isMeasured, $multiPolygon->isMeasured());
    }

    /**
     * Casts all values in the array to floats.
     *
     * This allows to write more concise data providers such as [1 2] instead of [1.0, 2.0]
     * while still strictly enforcing that the toArray() methods of the geometries return float values.
     *
     * @param array $coords
     *
     * @return void
     */
    private function castToFloat(array & $coords)
    {
        array_walk_recursive($coords, function (& $value) {
            $value = (float) $value;
        });
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
