<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryException;
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
     * @param string  $geometry      The WKT of the GeometryCollection to test.
     * @param integer $numGeometries The expected number of geometries.
     */
    public function testNumGeometries($geometry, $numGeometries)
    {
        $geometry = GeometryCollection::fromText($geometry);
        $this->assertSame($numGeometries, $geometry->numGeometries());
    }

    /**
     * @return array
     */
    public function providerNumGeometries()
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
     * @param integer     $n         The number of the geometry to return.
     * @param string|null $geometryN The WKT of the expected result, or NULL if an exception is expected.
     * @param integer     $srid      The SRID of the geometries.
     */
    public function testGeometryN($geometry, $n, $geometryN, $srid)
    {
        if ($geometryN === null) {
            $this->setExpectedException(GeometryException::class);
        }

        $g = GeometryCollection::fromText($geometry, $srid);
        $this->assertWktEquals($g->geometryN($n), $geometryN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerGeometryN()
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

        foreach ($tests as list ($geometryCollection, $n, $geometryN)) {
            foreach ([0, 1] as $srid) {
                yield [$geometryCollection, $n, $geometryN, $srid];
            }
        }
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces()
    {
        $point = Point::fromText('POINT (1 2)');
        $lineString = LineString::fromText('LINESTRING (1 2, 3 4)');

        $geometryCollection = GeometryCollection::create([$point, $lineString]);

        $this->assertInstanceOf(\Countable::class, $geometryCollection);
        $this->assertSame(2, count($geometryCollection));

        $this->assertInstanceOf(\Traversable::class, $geometryCollection);
        $this->assertSame([$point, $lineString], iterator_to_array($geometryCollection));
    }
}
