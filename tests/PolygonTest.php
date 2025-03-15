<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class Polygon.
 */
class PolygonTest extends AbstractTestCase
{
    #[DataProvider('providerConstructorEmpty')]
    public function testConstructorEmpty(bool $is3D, bool $isMeasured, int $srid) : void
    {
        $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
        $polygon = new Polygon($cs);

        self::assertTrue($polygon->isEmpty());
        self::assertSame($is3D, $polygon->is3D());
        self::assertSame($isMeasured, $polygon->isMeasured());
        self::assertSame($srid, $polygon->srid());
    }

    public static function providerConstructorEmpty() : array
    {
        return [
            [false, false, 0],
            [true ,false, 0],
            [false, true, 0],
            [true, true, 0],
            [false, false, 4326],
            [true ,false, 4326],
            [false, true, 4326],
            [true, true, 4326],
        ];
    }

    /**
     * @param string[] $ringsWKT
     */
    #[DataProvider('providerConstructor')]
    public function testConstructor(array $ringsWKT, string $polygonWKT, bool $hasZ, bool $hasM, int $srid) : void
    {
        $rings = [];

        foreach ($ringsWKT as $lineStringWKT) {
            $rings[] = LineString::fromText($lineStringWKT, $srid);
        }

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);
        $polygon = new Polygon($cs, ...$rings);
        $this->assertWktEquals($polygon, $polygonWKT, $srid);
    }

    public static function providerConstructor() : \Generator
    {
        $tests = [
            [['LINESTRING (0 0, 0 3, 3 3, 3 0)', 'LINESTRING (1 1, 1 2, 2 2, 2 1, 1 1)'], 'POLYGON ((0 0, 0 3, 3 3, 3 0), (1 1, 1 2, 2 2, 2 1, 1 1))', false, false],
            [['LINESTRING Z (0 0 1, 0 3 1, 3 3 1, 3 0 1, 0 0 1)'], 'POLYGON Z ((0 0 1, 0 3 1, 3 3 1, 3 0 1, 0 0 1))', true, false],
            [['LINESTRING M (0 0 1, 0 3 2, 3 3 3, 3 0 4, 0 0 1)'], 'POLYGON M ((0 0 1, 0 3 2, 3 3 3, 3 0 4, 0 0 1))', false, true],
            [['LINESTRING ZM (0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1)'], 'POLYGON ZM ((0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1))', true, true],
        ];

        foreach ($tests as $test) {
            foreach ([0, 1] as $srid) {
                yield array_merge($test, [$srid]);
            }
        }
    }

    /**
     * @param string  $ringWKT  The WKT of the outer ring of the polygon.
     * @param int     $ringSRID The SRID of the outer ring of the polygon.
     * @param bool    $hasZ     Whether the coordinate system has Z coordinates.
     * @param bool    $hasM     Whether the coordinate system has M coordinates.
     * @param int     $srid     The SRID of the coordinate system.
     * @param string  $message  The expected exception message, optional.
     */
    #[DataProvider('providerConstructorWithCoordinateSystemMix')]
    public function testConstructorWithCoordinateSystemMix(string $ringWKT, int $ringSRID, bool $hasZ, bool $hasM, int $srid, string $message = '') : void
    {
        $this->expectException(CoordinateSystemException::class);

        if ($message !== '') {
            $this->expectExceptionMessage($message);
        }

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);
        $ring = LineString::fromText($ringWKT, $ringSRID);
        new Polygon($cs, $ring);
    }

    public static function providerConstructorWithCoordinateSystemMix() : array
    {
        return [
            ['LINESTRING (0 0, 0 1, 1 1, 0 0)', 0, false, false, 1, 'SRID mix: Polygon with SRID 1 cannot contain LineString with SRID 0.'],
            ['LINESTRING (0 0, 0 1, 1 1, 0 0)', 0, true, false, 0, 'Dimensionality mix: Polygon XYZ cannot contain LineString XY.'],
            ['LINESTRING (0 0, 0 1, 1 1, 0 0)', 0, false, true, 0],
            ['LINESTRING (0 0, 0 1, 1 1, 0 0)', 0, true, true, 0],

            ['LINESTRING Z (0 0 1, 0 1 1, 1 1 1, 0 0 1)', 1, true, false, 0],
            ['LINESTRING Z (0 0 1, 0 1 1, 1 1 1, 0 0 1)', 1, false, false, 1],
            ['LINESTRING Z (0 0 1, 0 1 1, 1 1 1, 0 0 1)', 1, false, true, 1],
            ['LINESTRING Z (0 0 1, 0 1 1, 1 1 1, 0 0 1)', 1, true, true, 1],

            ['LINESTRING M (0 0 1, 0 1 2, 1 1 3, 0 0 1)', 2, false, true, 3],
            ['LINESTRING M (0 0 1, 0 1 2, 1 1 3, 0 0 1)', 2, false, false, 2],
            ['LINESTRING M (0 0 1, 0 1 2, 1 1 3, 0 0 1)', 2, true, false, 2],
            ['LINESTRING M (0 0 1, 0 1 2, 1 1 3, 0 0 1)', 2, true, true, 2],

            ['LINESTRING ZM (0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1)', 3, true, true, 2],
            ['LINESTRING ZM (0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1)', 3, false, false, 3],
            ['LINESTRING ZM (0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1)', 3, false, true, 3],
            ['LINESTRING ZM (0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1)', 3, true, false, 3],
        ];
    }

    /**
     * @param string[] $ringsWKT
     */
    #[DataProvider('providerOf')]
    public function testOf(array $ringsWKT, string $polygonWKT, int $srid) : void
    {
        $rings = [];

        foreach ($ringsWKT as $ringWKT) {
            $rings[] = LineString::fromText($ringWKT, $srid);
        }

        $polygon = Polygon::of(...$rings);
        $this->assertWktEquals($polygon, $polygonWKT, $srid);
    }

    public static function providerOf() : \Generator
    {
        $tests = [
            [['LINESTRING (0 0, 0 3, 3 3, 3 0)', 'LINESTRING (1 1, 1 2, 2 2, 2 1, 1 1)'], 'POLYGON ((0 0, 0 3, 3 3, 3 0), (1 1, 1 2, 2 2, 2 1, 1 1))'],
            [['LINESTRING Z (0 0 1, 0 3 1, 3 3 1, 3 0 1, 0 0 1)'], 'POLYGON Z ((0 0 1, 0 3 1, 3 3 1, 3 0 1, 0 0 1))'],
            [['LINESTRING M (0 0 1, 0 3 2, 3 3 3, 3 0 4, 0 0 1)'], 'POLYGON M ((0 0 1, 0 3 2, 3 3 3, 3 0 4, 0 0 1))'],
            [['LINESTRING ZM (0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1)'], 'POLYGON ZM ((0 0 1 1, 0 1 1 2, 1 1 1 3, 0 0 1 1))'],
        ];

        foreach ($tests as $test) {
            foreach ([0, 1] as $srid) {
                yield array_merge($test, [$srid]);
            }
        }
    }

    #[DataProvider('providerOfWithCoordinateSystemMix')]
    public function testOfWithCoordinateSystemMix(string $outerRingWKT, string $innerRingWKT, int $outerRingSRID, int $innerRingSRID) : void
    {
        $outerRing = LineString::fromText($outerRingWKT, $outerRingSRID);
        $innerRing = LineString::fromText($innerRingWKT, $innerRingSRID);

        $this->expectException(CoordinateSystemException::class);
        Polygon::of($outerRing, $innerRing);
    }

    public static function providerOfWithCoordinateSystemMix() : array
    {
        return [
            ['LINESTRING (0 0, 0 3, 3 3, 0 0)', 'LINESTRING (1 1, 1 2, 2 2, 1 1)', 0, 1],
            ['LINESTRING (0 0, 0 3, 3 3, 0 0)', 'LINESTRING (1 1, 1 2, 2 2, 1 1)', 1, 0],
            ['LINESTRING (0 0, 0 3, 3 3, 0 0)', 'LINESTRING Z (1 1 0, 1 2 0, 2 2 0, 1 1 0)', 0, 0],
            ['LINESTRING Z (0 0 0, 0 3 0, 3 3 0, 0 0 0)', 'LINESTRING (1 1, 1 2, 2 2, 1 1)', 1, 1],
            ['LINESTRING (0 0, 0 3, 3 3, 0 0)', 'LINESTRING M (1 1 1, 1 2 2, 2 2 3, 1 1 1)', 0, 0],
            ['LINESTRING M (0 0 1, 0 3 2, 3 3 3, 0 0 1)', 'LINESTRING (1 1, 1 2, 2 2, 1 1)', 1, 1],
            ['LINESTRING (0 0, 0 3, 3 3, 0 0)', 'LINESTRING ZM (0 0 0 1, 0 3 0 2, 3 3 0 3, 0 0 0 1)', 0, 0],
            ['LINESTRING ZM (0 0 0 1, 0 3 0 2, 3 3 0 3, 0 0 0 1)', 'LINESTRING M (0 0 1, 0 3 2, 3 3 3, 0 0 1)', 1, 1],
        ];
    }

    /**
     * @param string      $polygonWKT       The WKT of the Polygon to test.
     * @param string|null $exteriorRingWKT  The WKT of the exterior ring, or null if the Polygon is empty.
     * @param string[]    $interiorRingWKTs The WKT of the interior rings.
     * @param int         $srid             The SRID of the geometries.
     */
    #[DataProvider('providerRings')]
    public function testRings(string $polygonWKT, ?string $exteriorRingWKT, array $interiorRingWKTs, int $srid) : void
    {
        $polygon = Polygon::fromText($polygonWKT, $srid);

        $ringWKTs = array_merge($exteriorRingWKT === null ? [] : [$exteriorRingWKT], $interiorRingWKTs);

        self::assertWktEqualsMultiple($polygon->rings(), $ringWKTs, $srid);

        if ($exteriorRingWKT !== null) {
            $this->assertWktEquals($polygon->exteriorRing(), $exteriorRingWKT, $srid);
        } else {
            $this->expectExceptionIn(function () use ($polygon) {
                $polygon->exteriorRing();
            }, EmptyGeometryException::class);
        }

        self::assertWktEqualsMultiple($polygon->interiorRings(), $interiorRingWKTs, $srid);
        self::assertSame(count($interiorRingWKTs), $polygon->numInteriorRings());

        $this->expectExceptionIn(function () use ($polygon) {
            $polygon->interiorRingN(0);
        }, NoSuchGeometryException::class);

        $index = 1;
        foreach ($interiorRingWKTs as $interiorRingWKT) {
            $this->assertWktEquals($polygon->interiorRingN($index), $interiorRingWKT, $srid);
            $index++;
        }

        $this->expectExceptionIn(function () use ($polygon, $index) {
            $polygon->interiorRingN($index);
        }, NoSuchGeometryException::class);
    }

    public static function providerRings() : \Generator
    {
        $tests = [
            // XY
            [
                'polygon' => 'POLYGON EMPTY',
                'exteriorRing' => null,
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON ((0 0, 0 1, 1 1, 0 0))',
                'exteriorRing' => 'LINESTRING (0 0, 0 1, 1 1, 0 0)',
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON ((0 0, 0 9, 9 9, 0 0), (1 1, 1 8, 8 8, 1 1))',
                'exteriorRing' => 'LINESTRING (0 0, 0 9, 9 9, 0 0)',
                'interiorRings' => [
                    'LINESTRING (1 1, 1 8, 8 8, 1 1)'
                ]
            ],
            [
                'polygon' => 'POLYGON ((0 0, 0 9, 9 9, 0 0), (1 2, 1 4, 2 4, 1 2), (1 5, 2 6, 2 5, 1 5))',
                'exteriorRing' => 'LINESTRING (0 0, 0 9, 9 9, 0 0)',
                'interiorRings' => [
                    'LINESTRING (1 2, 1 4, 2 4, 1 2)',
                    'LINESTRING (1 5, 2 6, 2 5, 1 5)',
                ]
            ],
            // XYZ
            [
                'polygon' => 'POLYGON Z EMPTY',
                'exteriorRing' => null,
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 0 0 0))',
                'exteriorRing' => 'LINESTRING Z (0 0 0, 0 1 0, 1 1 0, 0 0 0)',
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON Z ((0 0 0, 0 9 0, 9 9 0, 0 0 0), (1 1 0, 1 8 0, 8 8 0, 1 1 0))',
                'exteriorRing' => 'LINESTRING Z (0 0 0, 0 9 0, 9 9 0, 0 0 0)',
                'interiorRings' => [
                    'LINESTRING Z (1 1 0, 1 8 0, 8 8 0, 1 1 0)'
                ]
            ],
            [
                'polygon' => 'POLYGON Z ((0 0 0, 0 9 0, 9 9 0, 0 0 0), (1 1 0, 1 8 0, 8 8 0, 1 1 0), (1 2 3, 4 5 6, 7 8 9, 1 2 3))',
                'exteriorRing' => 'LINESTRING Z (0 0 0, 0 9 0, 9 9 0, 0 0 0)',
                'interiorRings' => [
                    'LINESTRING Z (1 1 0, 1 8 0, 8 8 0, 1 1 0)',
                    'LINESTRING Z (1 2 3, 4 5 6, 7 8 9, 1 2 3)'
                ]
            ],
            // XYM
            [
                'polygon' => 'POLYGON M EMPTY',
                'exteriorRing' => null,
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON M ((0 0 0, 0 1 0, 1 1 0, 0 0 0))',
                'exteriorRing' => 'LINESTRING M (0 0 0, 0 1 0, 1 1 0, 0 0 0)',
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON M ((0 0 0, 0 9 0, 9 9 0, 0 0 0), (1 2 3, 4 5 6, 7 8 9, 1 2 3))',
                'exteriorRing' => 'LINESTRING M (0 0 0, 0 9 0, 9 9 0, 0 0 0)',
                'interiorRings' => [
                    'LINESTRING M (1 2 3, 4 5 6, 7 8 9, 1 2 3)'
                ]
            ],
            [
                'polygon' => 'POLYGON M ((0 0 0, 0 9 0, 9 9 0, 0 0 0), (1 1 0, 1 8 0, 8 8 0, 1 1 0), (1 2 3, 4 5 6, 7 8 9, 1 2 3))',
                'exteriorRing' => 'LINESTRING M (0 0 0, 0 9 0, 9 9 0, 0 0 0)',
                'interiorRings' => [
                    'LINESTRING M (1 1 0, 1 8 0, 8 8 0, 1 1 0)',
                    'LINESTRING M (1 2 3, 4 5 6, 7 8 9, 1 2 3)'
                ]
            ],
            // XYZM
            [
                'polygon' => 'POLYGON ZM EMPTY',
                'exteriorRing' => null,
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON ZM ((1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1))',
                'exteriorRing' => 'LINESTRING ZM (1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1)',
                'interiorRings' => []
            ],
            [
                'polygon' => 'POLYGON ZM ((1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1), (1 2 1 1, 1 3 1 2, 2 2 1 3, 1 2 1 1))',
                'exteriorRing' => 'LINESTRING ZM (1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1)',
                'interiorRings' => [
                    'LINESTRING ZM (1 2 1 1, 1 3 1 2, 2 2 1 3, 1 2 1 1)'
                ]
            ],
            [
                'polygon' => 'POLYGON ZM ((1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1), (1 2 1 1, 1 3 1 2, 2 2 1 3, 1 2 1 1), (1 2 2 1, 1 3 2 2, 2 2 2 3, 1 2 2 1))',
                'exteriorRing' => 'LINESTRING ZM (1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1)',
                'interiorRings' => [
                    'LINESTRING ZM (1 2 1 1, 1 3 1 2, 2 2 1 3, 1 2 1 1)',
                    'LINESTRING ZM (1 2 2 1, 1 3 2 2, 2 2 2 3, 1 2 2 1)'
                ]
            ]
        ];

        foreach ($tests as [
            'polygon' => $polygon,
            'exteriorRing' => $exteriorRing,
            'interiorRings' => $interiorRings,
        ]) {
            foreach ([0, 1] as $srid) {
                yield [$polygon, $exteriorRing, $interiorRings, $srid];
            }
        }
    }

    #[DataProvider('providerWithExteriorRing')]
    public function testWithExteriorRing(string $polygonWkt, string $exteriorRingWkt, string $expectedWkt): void
    {
        $polygon = Polygon::fromText($polygonWkt, 1234);
        $actual = $polygon->withExteriorRing(LineString::fromText($exteriorRingWkt, 1234));

        $this->assertWktEquals($polygon, $polygonWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithExteriorRing(): array
    {
        return [
            ['POLYGON EMPTY', 'LINESTRING (0 0, 0 9, 9 9, 0 0)', 'POLYGON ((0 0, 0 9, 9 9, 0 0))'],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (0 0, 1 1, 2 2, 0 0))', 'LINESTRING (0 0, 1 2, 3 1, 3 0, 0 0)', 'POLYGON ((0 0, 1 2, 3 1, 3 0, 0 0), (0 0, 1 1, 2 2, 0 0))'],
            ['POLYGON Z EMPTY', 'LINESTRING Z (0 0 1, 1 1 2, 2 2 3, 3 3 4, 0 0 1)', 'POLYGON Z ((0 0 1, 1 1 2, 2 2 3, 3 3 4, 0 0 1))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1), (0 0 1, 1 1 2, 2 2 3, 0 0 1))', 'LINESTRING Z (0 0 1, 1 2 2, 3 1 3, 3 0 3, 0 0 3)', 'POLYGON Z ((0 0 1, 1 2 2, 3 1 3, 3 0 3, 0 0 3), (0 0 1, 1 1 2, 2 2 3, 0 0 1))'],
        ];
    }

    /**
     * @param string[] $interiorRingsWkt
     */
    #[DataProvider('providerWithInteriorRings')]
    public function testWithInteriorRings(string $polygonWkt, array $interiorRingsWkt, string $expectedWkt): void
    {
        $polygon = Polygon::fromText($polygonWkt, 1234);
        $actual = $polygon->withInteriorRings(...array_map(
            fn (string $wkt) => LineString::fromText($wkt, 1234),
            $interiorRingsWkt,
        ));

        $this->assertWktEquals($polygon, $polygonWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithInteriorRings(): array
    {
        return [
            ['POLYGON ((0 0, 0 9, 9 9, 0 0))', [], 'POLYGON ((0 0, 0 9, 9 9, 0 0))'],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0))', ['LINESTRING (0 0, 1 1, 2 1, 0 0)'], 'POLYGON ((0 0, 0 9, 9 9, 0 0), (0 0, 1 1, 2 1, 0 0))'],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0))', ['LINESTRING (0 0, 1 1, 2 1, 0 0)', 'LINESTRING (2 1, 2 3, 3 1, 2 1)'], 'POLYGON ((0 0, 0 9, 9 9, 0 0), (0 0, 1 1, 2 1, 0 0), (2 1, 2 3, 3 1, 2 1))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))', [], 'POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))', ['LINESTRING Z (0 0 1, 1 1 2, 2 1 3, 0 0 1)'], 'POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1), (0 0 1, 1 1 2, 2 1 3, 0 0 1))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))', ['LINESTRING Z (0 0 1, 1 1 2, 2 1 3, 0 0 1)', 'LINESTRING Z (2 1 3, 2 3 4, 3 4 2, 2 1 3)'], 'POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1), (0 0 1, 1 1 2, 2 1 3, 0 0 1), (2 1 3, 2 3 4, 3 4 2, 2 1 3))'],
        ];
    }

    /**
     * @param string[] $addedInteriorRingsWkt
     */
    #[DataProvider('providerWithAddedInteriorRings')]
    public function testWithAddedInteriorRings(string $polygonWkt, array $addedInteriorRingsWkt, string $expectedWkt): void
    {
        $polygon = Polygon::fromText($polygonWkt, 1234);
        $actual = $polygon->withAddedInteriorRings(...array_map(
            fn (string $wkt) => LineString::fromText($wkt, 1234),
            $addedInteriorRingsWkt,
        ));

        $this->assertWktEquals($polygon, $polygonWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithAddedInteriorRings(): array
    {
        return [
            ['POLYGON ((0 0, 0 9, 9 9, 0 0))', [], 'POLYGON ((0 0, 0 9, 9 9, 0 0))'],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0))', ['LINESTRING (0 0, 1 1, 2 2, 3 0, 0 0)'], 'POLYGON ((0 0, 0 9, 9 9, 0 0), (0 0, 1 1, 2 2, 3 0, 0 0))'],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0))', ['LINESTRING (0 0, 1 1, 2 2, 3 0, 0 0)', 'LINESTRING (1 2, 2 3, 3 1, 1 2)'], 'POLYGON ((0 0, 0 9, 9 9, 0 0), (0 0, 1 1, 2 2, 3 0, 0 0), (1 2, 2 3, 3 1, 1 2))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))', [], 'POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))', ['LINESTRING Z (0 0 1, 1 1 2, 2 2 3, 3 0 4, 0 0 1)'], 'POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1), (0 0 1, 1 1 2, 2 2 3, 3 0 4, 0 0 1))'],
            ['POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1))', ['LINESTRING Z (0 0 1, 1 1 2, 2 2 3, 3 0 4, 0 0 1)', 'LINESTRING Z (1 2 1, 2 3 2, 3 1 3, 1 2 4)'], 'POLYGON Z ((0 0 1, 0 9 2, 9 9 3, 0 0 1), (0 0 1, 1 1 2, 2 2 3, 3 0 4, 0 0 1), (1 2 1, 2 3 2, 3 1 3, 1 2 4))'],
        ];
    }
}
