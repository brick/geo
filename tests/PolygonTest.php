<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\Polygon;

/**
 * Unit tests for class Polygon.
 */
class PolygonTest extends AbstractTestCase
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
        $polygon = Polygon::create([], $is3D, $isMeasured, $srid);

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
     * @expectedException \Brick\Geo\Exception\GeometryException
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
            $this->setExpectedException(GeometryException::class);
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
