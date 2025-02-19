<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\GeometryCollection;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class GeometryCollection.
 */
class GeometryCollectionTest extends AbstractTestCase
{
    /**
     * @param string $geometry      The WKT of the GeometryCollection to test.
     * @param int    $numGeometries The expected number of geometries.
     */
    #[DataProvider('providerNumGeometries')]
    public function testNumGeometries(string $geometry, int $numGeometries) : void
    {
        $geometry = GeometryCollection::fromText($geometry);
        self::assertSame($numGeometries, $geometry->numGeometries());
    }

    public static function providerNumGeometries() : array
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY', 0],
            ['GEOMETRYCOLLECTION (POINT EMPTY)', 1],
            ['GEOMETRYCOLLECTION (POINT EMPTY, LINESTRING EMPTY)', 2],
        ];
    }

    /**
     * @param string      $geometry  The WKT of the GeometryCollection to test.
     * @param int         $n         The number of the geometry to return.
     * @param string|null $geometryN The WKT of the expected result, or NULL if an exception is expected.
     * @param int         $srid      The SRID of the geometries.
     */
    #[DataProvider('providerGeometryN')]
    public function testGeometryN(string $geometry, int $n, ?string $geometryN, int $srid) : void
    {
        if ($geometryN === null) {
            $this->expectException(NoSuchGeometryException::class);
        }

        $g = GeometryCollection::fromText($geometry, $srid);
        $this->assertWktEquals($g->geometryN($n), $geometryN, $srid);
    }

    public static function providerGeometryN() : \Generator
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

    /**
     * @param string[] $addedGeometriesWkt
     */
    #[DataProvider('providerWithAddedGeometries')]
    public function testWithAddedGeometries(string $geometryCollectionWkt, array $addedGeometriesWkt, string $expectedWkt): void
    {
        $geometryCollection = GeometryCollection::fromText($geometryCollectionWkt, 1234);
        $actual = $geometryCollection->withAddedGeometries(
            ...array_map(fn (string $wkt) => Geometry::fromText($wkt, 1234),
            $addedGeometriesWkt,
        ));

        $this->assertWktEquals($geometryCollection, $geometryCollectionWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithAddedGeometries(): array
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY', [], 'GEOMETRYCOLLECTION EMPTY'],
            ['GEOMETRYCOLLECTION EMPTY', ['POINT (1 2)'], 'GEOMETRYCOLLECTION (POINT (1 2))'],
            ['GEOMETRYCOLLECTION EMPTY', ['POINT (1 2)', 'LINESTRING (3 4, 5 6)'], 'GEOMETRYCOLLECTION (POINT (1 2), LINESTRING (3 4, 5 6))'],
            ['GEOMETRYCOLLECTION (POINT (9 9), POLYGON ((1 2, 3 4, 3 1, 1 2)))', [], 'GEOMETRYCOLLECTION (POINT (9 9), POLYGON ((1 2, 3 4, 3 1, 1 2)))'],
            ['GEOMETRYCOLLECTION (POINT (9 9), POLYGON ((1 2, 3 4, 3 1, 1 2)))', ['POINT (1 2)'], 'GEOMETRYCOLLECTION (POINT (9 9), POLYGON ((1 2, 3 4, 3 1, 1 2)), POINT (1 2))'],
            ['GEOMETRYCOLLECTION (POINT (9 9), POLYGON ((1 2, 3 4, 3 1, 1 2)))', ['POINT (1 2)', 'LINESTRING (3 4, 5 6)'], 'GEOMETRYCOLLECTION (POINT (9 9), POLYGON ((1 2, 3 4, 3 1, 1 2)), POINT (1 2), LINESTRING (3 4, 5 6))'],
            ['GEOMETRYCOLLECTION Z EMPTY', [], 'GEOMETRYCOLLECTION Z EMPTY'],
            ['GEOMETRYCOLLECTION Z EMPTY', ['POINT Z (1 2 3)'], 'GEOMETRYCOLLECTION Z (POINT Z (1 2 3))'],
            ['GEOMETRYCOLLECTION Z EMPTY', ['POINT Z (1 2 3)', 'LINESTRING Z (3 4 5, 5 6 7)'], 'GEOMETRYCOLLECTION Z (POINT Z (1 2 3), LINESTRING Z (3 4 5, 5 6 7))'],
            ['GEOMETRYCOLLECTION Z (POINT Z (9 9 1), POLYGON Z ((1 2 3, 3 4 5, 3 1 2, 1 2 3)))', [], 'GEOMETRYCOLLECTION Z (POINT Z (9 9 1), POLYGON Z ((1 2 3, 3 4 5, 3 1 2, 1 2 3)))'],
            ['GEOMETRYCOLLECTION Z (POINT Z (9 9 1), POLYGON Z ((1 2 3, 3 4 5, 3 1 2, 1 2 3)))', ['POINT Z (1 2 3)'], 'GEOMETRYCOLLECTION Z (POINT Z (9 9 1), POLYGON Z ((1 2 3, 3 4 5, 3 1 2, 1 2 3)), POINT Z (1 2 3))'],
            ['GEOMETRYCOLLECTION Z (POINT Z (9 9 1), POLYGON Z ((1 2 3, 3 4 5, 3 1 2, 1 2 3)))', ['POINT Z (1 2 3)', 'LINESTRING Z (3 4 5, 5 6 7)'], 'GEOMETRYCOLLECTION Z (POINT Z (9 9 1), POLYGON Z ((1 2 3, 3 4 5, 3 1 2, 1 2 3)), POINT Z (1 2 3), LINESTRING Z (3 4 5, 5 6 7))'],

            ['MULTIPOINT EMPTY', [], 'MULTIPOINT EMPTY'],
            ['MULTIPOINT EMPTY', ['POINT (1 2)'], 'MULTIPOINT (1 2)'],
            ['MULTIPOINT EMPTY', ['POINT (1 2)', 'POINT (3 4)'], 'MULTIPOINT (1 2, 3 4)'],
            ['MULTIPOINT (0 0)', [], 'MULTIPOINT (0 0)'],
            ['MULTIPOINT (0 0)', ['POINT (1 2)'], 'MULTIPOINT (0 0, 1 2)'],
            ['MULTIPOINT (0 0)', ['POINT (1 2)', 'POINT (3 4)'], 'MULTIPOINT (0 0, 1 2, 3 4)'],
            ['MULTIPOINT (0 0, 1 1)', [], 'MULTIPOINT (0 0, 1 1)'],
            ['MULTIPOINT (0 0, 1 1)', ['POINT (2 2)'], 'MULTIPOINT (0 0, 1 1, 2 2)'],
            ['MULTIPOINT (0 0, 1 1)', ['POINT (2 2)', 'POINT (3 3)'], 'MULTIPOINT (0 0, 1 1, 2 2, 3 3)'],
        ];
    }
}
