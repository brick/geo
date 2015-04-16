<?php

namespace Brick\Geo\Tests;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\CurvePolygon;
use Brick\Geo\Exception\GeometryException;

/**
 * Unit tests for class CurvePolygon.
 */
class CurvePolygonTest extends AbstractTestCase
{
    /**
     * @dataProvider providerEmptyFactoryMethod
     *
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     */
    public function testEmptyFactoryMethod($is3D, $isMeasured, $srid)
    {
        $cs = CoordinateSystem::create($is3D, $isMeasured, $srid);
        $polygon = CurvePolygon::create([], $cs);

        $this->assertTrue($polygon->isEmpty());
        $this->assertSame($is3D, $polygon->is3D());
        $this->assertSame($isMeasured, $polygon->isMeasured());
        $this->assertSame($srid, $polygon->SRID());
    }

    /**
     * @return array
     */
    public function providerEmptyFactoryMethod()
    {
        return [
            [false, false, 0],
            [true ,false, 0],
            [false, true, 0],
            [true, true, 0],
            [false, false, 4326],
            [true ,false, 4326],
            [false, true, 4326],
            [true, true, 4326]
        ];
    }

    /**
     * @dataProvider providerExteriorRing
     *
     * @param string $curvePolygon The WKT of the CurvePolygon to test.
     * @param string $exteriorRing The WKT of the expected exterior ring.
     */
    public function testExteriorRing($curvePolygon, $exteriorRing)
    {
        foreach ([0, 1] as $srid) {
            $ring = CurvePolygon::fromText($curvePolygon, $srid)->exteriorRing();
            $this->assertWktEquals($ring, $exteriorRing, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerExteriorRing()
    {
        return [
            ['CURVEPOLYGON ((0 0, 0 9, 9 9, 0 0), COMPOUNDCURVE ((1 2, 3 4), CIRCULARSTRING (3 4, 5 6, 7 8, 9 0, 1 2)))', 'LINESTRING (0 0, 0 9, 9 9, 0 0)'],
            ['CURVEPOLYGON Z ((0 0 1, 0 9 1, 9 9 1, 0 0 1), CIRCULARSTRING Z (1 1 1, 4 7 1, 6 5 1, 2 3 1, 1 1 1))', 'LINESTRING Z (0 0 1, 0 9 1, 9 9 1, 0 0 1)'],
            ['CURVEPOLYGON M (COMPOUNDCURVE M (CIRCULARSTRING M (0 0 1, 0 9 1, 9 9 1), (9 9 1, 0 0 1)), (1 1 1, 4 7 1, 6 5 1, 1 1 1))', 'COMPOUNDCURVE M (CIRCULARSTRING M (0 0 1, 0 9 1, 9 9 1), (9 9 1, 0 0 1))', false, true],
            ['CURVEPOLYGON ZM (CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6, 4 5 6 7, 1 2 3 4), (3 4 5 6, 4 5 6 7, 9 8 7 6, 3 4 5 6))', 'CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6, 4 5 6 7, 1 2 3 4)', true, true],
        ];
    }

    /**
     * @dataProvider providerExteriorRingOfEmptyCurvePolygon
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string $polygon The WKT of the CurvePolygon to test.
     */
    public function testExteriorRingOfEmptyCurvePolygon($polygon)
    {
        CurvePolygon::fromText($polygon)->exteriorRing();
    }

    /**
     * @return array
     */
    public function providerExteriorRingOfEmptyCurvePolygon()
    {
        return [
            ['CURVEPOLYGON EMPTY'],
            ['CURVEPOLYGON Z EMPTY'],
            ['CURVEPOLYGON M EMPTY'],
            ['CURVEPOLYGON ZM EMPTY'],
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
        $polygon = CurvePolygon::fromText($polygon);
        $this->assertSame($numInteriorRings, $polygon->numInteriorRings());
    }

    /**
     * @return array
     */
    public function providerNumInteriorRings()
    {
        return [
            ['CURVEPOLYGON EMPTY', 0],
            ['CURVEPOLYGON Z EMPTY', 0],
            ['CURVEPOLYGON M EMPTY', 0],
            ['CURVEPOLYGON ZM EMPTY', 0],
            ['CURVEPOLYGON ((0 0, 0 1, 1 1, 0 0))', 0],
            ['CURVEPOLYGON (COMPOUNDCURVE (CIRCULARSTRING (0 0, 0 9, 9 9), (9 9, 0 0)), (1 1, 1 8, 8 8, 1 1))', 1],
            ['CURVEPOLYGON ((0 0, 0 9, 9 9, 0 0), CIRCULARSTRING (1 2, 1 4, 2 4, 2 3, 1 2), (1 5, 2 6, 2 5, 1 5))', 2],
            ['CURVEPOLYGON Z ((0 0 0, 0 1 0, 1 1 0, 0 0 0))', 0],
            ['CURVEPOLYGON Z ((0 0 0, 0 9 0, 9 9 0, 0 0 0), COMPOUNDCURVE Z (CIRCULARSTRING Z (1 1 0, 1 8 0, 8 8 0), (8 8 0, 1 1 0)))', 1],
        ];
    }

    /**
     * @dataProvider providerInteriorRingN
     *
     * @param string      $curvePolygon  The WKT of the CurvePolygon to test.
     * @param integer     $n             The ring number.
     * @param string|null $interiorRingN The WKT of the expected interior ring, or NULL if an exception is expected.
     * @param integer     $srid          The SRID of the geometries.
     */
    public function testInteriorRingN($curvePolygon, $n, $interiorRingN, $srid)
    {
        if ($interiorRingN === null) {
            $this->setExpectedException(GeometryException::class);
        }

        $ring = CurvePolygon::fromText($curvePolygon, $srid)->interiorRingN($n);
        $this->assertWktEquals($ring, $interiorRingN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerInteriorRingN()
    {
        $tests = [
            ['CURVEPOLYGON EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['CURVEPOLYGON Z EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['CURVEPOLYGON M EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['CURVEPOLYGON ZM EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['CURVEPOLYGON ((0 0, 0 1, 1 1, 0 0))', [
                0 => null,
                1 => null,
            ]],
            ['CURVEPOLYGON ((0 0, 0 9, 9 9, 0 0), CIRCULARSTRING (0 0, 0 1, 1 1, 1 0, 0 0))', [
                0 => null,
                1 => 'CIRCULARSTRING (0 0, 0 1, 1 1, 1 0, 0 0)',
                2 => null,
            ]],
            ['CURVEPOLYGON Z ((0 0 1, 0 9 1, 9 9 1, 0 0 1), CIRCULARSTRING Z (1 1 1, 4 7 1, 6 5 1, 2 3 1, 1 1 1), (2 2 2, 2 3 2, 3 2 2, 2 2 2))', [
                0 => null,
                1 => 'CIRCULARSTRING Z (1 1 1, 4 7 1, 6 5 1, 2 3 1, 1 1 1)',
                2 => 'LINESTRING Z (2 2 2, 2 3 2, 3 2 2, 2 2 2)',
                3 => null,
            ]],
            ['CURVEPOLYGON M (COMPOUNDCURVE M (CIRCULARSTRING M (0 0 1, 0 9 1, 9 9 1), (9 9 1, 0 0 1)))', [
                0 => null,
                1 => null,
            ]],
            ['CURVEPOLYGON M (COMPOUNDCURVE M (CIRCULARSTRING M (0 0 1, 0 9 1, 9 9 1), (9 9 1, 0 0 1)), (1 1 1, 4 7 1, 6 5 1, 1 1 1))', [
                0 => null,
                1 => 'LINESTRING M (1 1 1, 4 7 1, 6 5 1, 1 1 1)',
                2 => null,
            ]],
        ];

        foreach ($tests as list ($curvePolygon, $interiorRings)) {
            foreach ($interiorRings as $n => $interiorRingN) {
                foreach ([0, 1] as $srid) {
                    yield [$curvePolygon, $n, $interiorRingN, $srid];
                }
            }
        }
    }
}
