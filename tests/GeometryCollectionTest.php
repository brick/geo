<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\Point;

/**
 * Unit tests for class GeometryCollection.
 */
class GeometryCollectionTest extends AbstractTestCase
{
    /**
     * @dataProvider providerNumGeometries
     *
     * @param string $geometry      The WKT of the GeometryCollection to test.
     * @param int    $numGeometries The expected number of geometries.
     *
     * @return void
     */
    public function testNumGeometries(string $geometry, int $numGeometries) : void
    {
        $geometry = GeometryCollection::fromText($geometry);
        self::assertSame($numGeometries, $geometry->numGeometries());
    }

    /**
     * @return array
     */
    public function providerNumGeometries() : array
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY', 0],
            ['GEOMETRYCOLLECTION (POINT EMPTY)', 1],
            ['GEOMETRYCOLLECTION (POINT EMPTY, LINESTRING EMPTY)', 2],
        ];
    }

    /**
     * @dataProvider providerGeometryN
     *
     * @param string      $geometry  The WKT of the GeometryCollection to test.
     * @param int         $n         The number of the geometry to return.
     * @param string|null $geometryN The WKT of the expected result, or NULL if an exception is expected.
     * @param int         $srid      The SRID of the geometries.
     *
     * @return void
     */
    public function testGeometryN(string $geometry, int $n, ?string $geometryN, int $srid) : void
    {
        if ($geometryN === null) {
            $this->expectException(NoSuchGeometryException::class);
        }

        $g = GeometryCollection::fromText($geometry, $srid);
        $this->assertWktEquals($g->geometryN($n), $geometryN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerGeometryN() : \Generator
    {
        $tests = [
            ['GEOMETRYCOLLECTION EMPTY', 0, null],
            ['GEOMETRYCOLLECTION EMPTY', 1, null],
            ['GEOMETRYCOLLECTION (POINT EMPTY)', 0, null],
            ['GEOMETRYCOLLECTION (POINT EMPTY)', 1, 'POINT EMPTY'],
            ['GEOMETRYCOLLECTION (POINT EMPTY)', 2, null],
            ['GEOMETRYCOLLECTION (LINESTRING (1 2, 3 4), POINT (5 6))', 0, null],
            ['GEOMETRYCOLLECTION (LINESTRING (1 2, 3 4), POINT (5 6))', 1, 'LINESTRING (1 2, 3 4)'],
            ['GEOMETRYCOLLECTION (LINESTRING (1 2, 3 4), POINT (5 6))', 2, 'POINT (5 6)'],
            ['GEOMETRYCOLLECTION (LINESTRING (1 2, 3 4), POINT (5 6))', 3, null]
        ];

        foreach ($tests as [$geometryCollection, $n, $geometryN]) {
            foreach ([0, 1] as $srid) {
                yield [$geometryCollection, $n, $geometryN, $srid];
            }
        }
    }

    /**
     * Tests Countable and Traversable interfaces.
     *
     * @return void
     */
    public function testInterfaces() : void
    {
        $point = Point::fromText('POINT (1 2)');
        $lineString = LineString::fromText('LINESTRING (1 2, 3 4)');

        $geometryCollection = GeometryCollection::of($point, $lineString);

        self::assertInstanceOf(\Countable::class, $geometryCollection);
        self::assertCount(2, $geometryCollection);

        self::assertInstanceOf(\Traversable::class, $geometryCollection);
        self::assertSame([$point, $lineString], iterator_to_array($geometryCollection));
    }
}
