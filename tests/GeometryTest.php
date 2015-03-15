<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Geometry;
use Brick\Geo\Point;
use Brick\Geo\LineString;
use Brick\Geo\LinearRing;
use Brick\Geo\Line;
use Brick\Geo\Polygon;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiLineString;
use Brick\Geo\MultiPolygon;
use Brick\Geo\GeometryCollection;

/**
 * Unit tests for geometries.
 *
 * @todo needs more tests
 */
class GeometryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Returns a clone of the given Geometry, exported as text then imported back.
     *
     * @param  \Brick\Geo\Geometry $geometry
     * @return \Brick\Geo\Geometry
     */
    private function cloneByText(Geometry $geometry)
    {
        return Geometry::fromText($geometry->asText());
    }

    /**
     * Returns a clone of the given Geometry, exported as binary then imported back.
     *
     * @param  \Brick\Geo\Geometry $geometry
     * @return \Brick\Geo\Geometry
     */
    private function cloneByBinary(Geometry $geometry)
    {
        return Geometry::fromBinary($geometry->asBinary());
    }

    /**
     * Tests that a Geometry, cloned by text, is equal to the original Geometry,
     * and that their text representation are the exact same.
     *
     * @param \Brick\Geo\Geometry $geometry
     */
    private function checkTextCloneIsEqual(Geometry $geometry)
    {
        $clone = $this->cloneByText($geometry);
        $this->assertTrue($clone->equals($geometry));
        $this->assertSame($geometry->asText(), $clone->asText());
    }

    /**
     * Tests that a Geometry, cloned by binary, is equal to the original Geometry,
     * and that their binary representation are the exact same.
     *
     * @param \Brick\Geo\Geometry $geometry
     */
    private function checkBinaryCloneIsEqual(Geometry $geometry)
    {
        $clone = $this->cloneByBinary($geometry);
        $this->assertTrue($clone->equals($geometry));
        $this->assertSame($geometry->asBinary(), $clone->asBinary());
    }

    /**
     * @param string $type
     *
     * @param Geometry $geometry
     */
    private function assertGeometryType($type, Geometry $geometry)
    {
        $this->assertSame($type, $geometry->geometryType());
    }

    /**
     * Tests that a Geometry, cloned by text and by binary, is equal to the original Geometry,
     * and that their text and binary representations are the exact same.
     *
     * @param \Brick\Geo\Geometry $geometry
     */
    private function checkCloneIsEqual(Geometry $geometry)
    {
        $this->checkTextCloneIsEqual($geometry);
        $this->checkBinaryCloneIsEqual($geometry);
    }

    public function testGeometry()
    {
        // Point
        $p1 = Point::xy(0, 0);
        $p2 = Point::xy(1, 0);
        $p3 = Point::xy(1, 1);
        $p4 = Point::xy(0, 1);

        foreach ([$p1, $p2, $p3, $p4] as $point) {
            /** @var Point $point */
            $this->assertGeometryType('Point', $point);
            $this->checkCloneIsEqual($point);
        }

        // LineString
        $lineString = LineString::factory([$p1, $p2, $p3]);

        $this->assertGeometryType('LineString', $lineString);
        $this->checkCloneIsEqual($lineString);

        // LinearRing
        $linearRing = LinearRing::factory([$p1, $p2, $p3, $p4, $p1]);

        // $this->assertGeometryType('LinearRing', $linearRing);
        $this->checkCloneIsEqual($linearRing);

        // Line
        $line = Line::create($p1, $p2);

        // $this->assertGeometryType('Line', $line);
        $this->checkCloneIsEqual($line);

        // Polygon
        $polygon = Polygon::factory([$linearRing]);

        $this->assertGeometryType('Polygon', $polygon);
        $this->checkCloneIsEqual($polygon);

        // MultiPoint
        $multiPoint = MultiPoint::factory([$p2, $p3, $p1]);

        $this->assertGeometryType('MultiPoint', $multiPoint);
        $this->checkCloneIsEqual($multiPoint);

        // MultiLineString
        $multiLineString = MultiLineString::factory([$lineString, $linearRing, $line]);

        $this->assertGeometryType('MultiLineString', $multiLineString);
        $this->checkCloneIsEqual($multiLineString);

        // MultiPolygon
        $multiPolygon = MultiPolygon::factory([$polygon]);

        $this->assertGeometryType('MultiPolygon', $multiPolygon);
        $this->checkCloneIsEqual($multiPolygon);

        // GeometryCollection
        $collection = GeometryCollection::factory([
            $p1,
            $p2,
            $p3,
            $p4,
            $multiPoint,
            $lineString,
            $linearRing,
            $line,
            $multiLineString,
            $polygon,
            $multiPolygon
        ]);

        $this->assertGeometryType('GeometryCollection', $collection);

        // PostGIS does not support ST_Equals() on GEOMETRYCOLLECTION yet.
        // Testing only the binary equality for now.
        // $this->checkCloneIsEqual($collection);
        $this->assertSame($collection->asBinary(), $this->cloneByText($collection)->asBinary());
        $this->assertSame($collection->asBinary(), $this->cloneByBinary($collection)->asBinary());
    }
}
