<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\LineString;
use Brick\Geo\Polygon;

/**
 * Unit tests for class Polygon.
 */
class PolygonTest extends AbstractTestCase
{
    /**
     * @dataProvider providerConstructorEmpty
     *
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     */
    public function testConstructorEmpty($is3D, $isMeasured, $srid)
    {
        $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
        $polygon = new Polygon($cs);

        $this->assertTrue($polygon->isEmpty());
        $this->assertSame($is3D, $polygon->is3D());
        $this->assertSame($isMeasured, $polygon->isMeasured());
        $this->assertSame($srid, $polygon->SRID());
    }

    /**
     * @return array
     */
    public function providerConstructorEmpty()
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
     * @dataProvider providerConstructor
     *
     * @param string[] $ringsWKT
     * @param string   $polygonWKT
     * @param boolean  $hasZ
     * @param boolean  $hasM
     * @param integer  $srid
     */
    public function testConstructor(array $ringsWKT, $polygonWKT, $hasZ, $hasM, $srid)
    {
        $rings = [];

        foreach ($ringsWKT as $lineStringWKT) {
            $rings[] = LineString::fromText($lineStringWKT, $srid);
        }

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);
        $polygon = new Polygon($cs, ...$rings);
        $this->assertWktEquals($polygon, $polygonWKT, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerConstructor()
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
     * @dataProvider providerConstructorWithCoordinateSystemMix
     *
     * @param string  $ringWKT  The WKT of the outer ring of the polygon.
     * @param integer $ringSRID The SRID of the outer ring of the polygon.
     * @param boolean $hasZ     Whether the coordinate system has Z coordinates.
     * @param boolean $hasM     Whether the coordinate system has M coordinates.
     * @param boolean $srid     The SRID of the coordinate system.
     * @param string  $message  The expected exception message, optional.
     */
    public function testConstructorWithCoordinateSystemMix($ringWKT, $ringSRID, $hasZ, $hasM, $srid, $message = '')
    {
        $this->expectException(CoordinateSystemException::class);

        if ($message !== '') {
            $this->expectExceptionMessage($message);
        }

        $cs = new CoordinateSystem($hasZ, $hasM, $srid);
        $ring = LineString::fromText($ringWKT, $ringSRID);
        new Polygon($cs, $ring);
    }

    /**
     * @return array
     */
    public function providerConstructorWithCoordinateSystemMix()
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
     * @dataProvider providerOf
     *
     * @param string[] $ringsWKT
     * @param string   $polygonWKT
     * @param integer  $srid
     */
    public function testOf(array $ringsWKT, $polygonWKT, $srid)
    {
        $rings = [];

        foreach ($ringsWKT as $ringWKT) {
            $rings[] = LineString::fromText($ringWKT, $srid);
        }

        $polygon = Polygon::of(...$rings);
        $this->assertWktEquals($polygon, $polygonWKT, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerOf()
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

    /**
     * @dataProvider providerOfWithCoordinateSystemMix
     * @expectedException \Brick\Geo\Exception\CoordinateSystemException
     *
     * @param string  $outerRingWKT
     * @param string  $innerRingWKT
     * @param integer $outerRingSRID
     * @param integer $innerRingSRID
     */
    public function testOfWithCoordinateSystemMix($outerRingWKT, $innerRingWKT, $outerRingSRID, $innerRingSRID)
    {
        $outerRing = LineString::fromText($outerRingWKT, $outerRingSRID);
        $innerRing = LineString::fromText($innerRingWKT, $innerRingSRID);

        Polygon::of($outerRing, $innerRing);
    }

    /**
     * @return array
     */
    public function providerOfWithCoordinateSystemMix()
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
     * @dataProvider providerExteriorRing
     *
     * @param string $polygon      The WKT of the Polygon to test.
     * @param string $exteriorRing The WKT of the expected exterior ring.
     */
    public function testExteriorRing($polygon, $exteriorRing)
    {
        foreach ([0, 1] as $srid) {
            $ring = Polygon::fromText($polygon, $srid)->exteriorRing();
            $this->assertWktEquals($ring, $exteriorRing, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerExteriorRing()
    {
        return [
            ['POLYGON ((1 2, 1 3, 2 2, 1 2))', 'LINESTRING (1 2, 1 3, 2 2, 1 2)'],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (1 1, 1 8, 8 8, 1 1))', 'LINESTRING (0 0, 0 9, 9 9, 0 0)'],
            ['POLYGON Z ((1 2 3, 4 5 6, 7 8 9, 1 2 3))', 'LINESTRING Z (1 2 3, 4 5 6, 7 8 9, 1 2 3)'],
            ['POLYGON M ((1 2 3, 4 5 6, 7 8 9, 1 2 3))', 'LINESTRING M (1 2 3, 4 5 6, 7 8 9, 1 2 3)'],
            ['POLYGON ZM ((1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1))', 'LINESTRING ZM (1 2 0 1, 1 3 0 2, 2 2 0 3, 1 2 0 1)'],
        ];
    }

    /**
     * @dataProvider providerExteriorRingOfEmptyPolygon
     * @expectedException \Brick\Geo\Exception\EmptyGeometryException
     *
     * @param string $polygon The WKT of the polygon to test.
     */
    public function testExteriorRingOfEmptyPolygon($polygon)
    {
        Polygon::fromText($polygon)->exteriorRing();
    }

    /**
     * @return array
     */
    public function providerExteriorRingOfEmptyPolygon()
    {
        return [
            ['POLYGON EMPTY'],
            ['POLYGON Z EMPTY'],
            ['POLYGON M EMPTY'],
            ['POLYGON ZM EMPTY'],
        ];
    }

    /**
     * @dataProvider providerNumInteriorRings
     *
     * @param string  $polygon          The WKT of the Polygon to test.
     * @param integer $numInteriorRings The expected number of interior rings.
     */
    public function testNumInteriorRings($polygon, $numInteriorRings)
    {
        $polygon = Polygon::fromText($polygon);
        $this->assertSame($numInteriorRings, $polygon->numInteriorRings());
    }

    /**
     * @return array
     */
    public function providerNumInteriorRings()
    {
        return [
            ['POLYGON EMPTY', 0],
            ['POLYGON Z EMPTY', 0],
            ['POLYGON M EMPTY', 0],
            ['POLYGON ZM EMPTY', 0],
            ['POLYGON ((0 0, 0 1, 1 1, 0 0))', 0],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (1 1, 1 8, 8 8, 1 1))', 1],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (1 2, 1 4, 2 4, 1 2), (1 5, 2 6, 2 5, 1 5))', 2],
            ['POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 0 0 0))', 0],
            ['POLYGON Z ((0 0 0, 0 9 0, 9 9 0, 0 0 0), (1 1 0, 1 8 0, 8 8 0, 1 1 0))', 1],
        ];
    }

    /**
     * @dataProvider providerInteriorRingN
     *
     * @param string      $polygon       The WKT of the Polygon to test.
     * @param integer     $n             The ring number.
     * @param string|null $interiorRingN The WKT of the expected interior ring, or NULL if an exception is expected.
     * @param integer     $srid          The SRID of the geometries.
     */
    public function testInteriorRingN($polygon, $n, $interiorRingN, $srid)
    {
        if ($interiorRingN === null) {
            $this->expectException(NoSuchGeometryException::class);
        }

        $ring = Polygon::fromText($polygon, $srid)->interiorRingN($n);
        $this->assertWktEquals($ring, $interiorRingN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerInteriorRingN()
    {
        $tests = [
            ['POLYGON EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYGON Z EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYGON M EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYGON ZM EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYGON ((0 0, 0 1, 1 1, 0 0))', [
                0 => null,
                1 => null,
            ]],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (1 1, 1 8, 8 8, 1 1))', [
                0 => null,
                1 => 'LINESTRING (1 1, 1 8, 8 8, 1 1)',
                2 => null,
            ]],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (1 1, 1 8, 8 8, 1 1))', [
                0 => null,
                1 => 'LINESTRING (1 1, 1 8, 8 8, 1 1)',
                2 => null,
            ]],
            ['POLYGON ((0 0, 0 9, 9 9, 0 0), (1 2, 1 4, 2 4, 1 2), (1 5, 2 6, 2 5, 1 5))', [
                0 => null,
                1 => 'LINESTRING (1 2, 1 4, 2 4, 1 2)',
                2 => 'LINESTRING (1 5, 2 6, 2 5, 1 5)',
                3 => null,
            ]],
            ['POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 0 0 0))', [
                0 => null,
                1 => null,
            ]],
            ['POLYGON Z ((0 0 0, 0 9 0, 9 9 0, 0 0 0), (1 1 0, 1 8 0, 8 8 0, 1 1 0))', [
                0 => null,
                1 => 'LINESTRING Z (1 1 0, 1 8 0, 8 8 0, 1 1 0)',
                2 => null,
            ]],
        ];

        foreach ($tests as list ($polygon, $interiorRings)) {
            foreach ($interiorRings as $n => $interiorRingN) {
                foreach ([0, 1] as $srid) {
                    yield [$polygon, $n, $interiorRingN, $srid];
                }
            }
        }
    }
}
