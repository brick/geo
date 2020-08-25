<?php

namespace Brick\Geo\Tests;

use Brick\Geo\CompoundCurve;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Curve;
use Brick\Geo\Exception\NoSuchGeometryException;

/**
 * Unit tests for class CompoundCurve.
 */
class CompoundCurveTest extends AbstractTestCase
{
    /**
     * @dataProvider providerCreate
     *
     * @param string[] $curvesWKT        The WKT of the Curves that compose the CompoundCurve.
     * @param bool     $is3D             Whether the curves have Z coordinates.
     * @param bool     $isMeasured       Whether the curves have M coordinates.
     * @param string   $compoundCurveWKT The WKT of the expected CompoundCurve.
     *
     * @return void
     */
    public function testCreate(array $curvesWKT, bool $is3D, bool $isMeasured, string $compoundCurveWKT) : void
    {
        foreach ([0, 1] as $srid) {
            $instantiateCurve = function ($curve) use ($srid) {
                return Curve::fromText($curve, $srid);
            };

            $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
            $compoundCurve = new CompoundCurve($cs, ...array_map($instantiateCurve, $curvesWKT));
            $this->assertWktEquals($compoundCurve, $compoundCurveWKT, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerCreate() : array
    {
        return [
            [['LINESTRING (1 1, 2 2)', 'CIRCULARSTRING (2 2, 3 3, 5 5)'], false, false, 'COMPOUNDCURVE ((1 1, 2 2), CIRCULARSTRING (2 2, 3 3, 5 5))'],
            [['LINESTRING Z (1 2 3, 4 5 6, 7 8 9)'], true, false, 'COMPOUNDCURVE Z ((1 2 3, 4 5 6, 7 8 9))'],
            [['CIRCULARSTRING M (1 2 3, 2 3 4, 3 4 5)', 'LINESTRING M (3 4 5, 4 5 6)'], false, true, 'COMPOUNDCURVE M (CIRCULARSTRING M (1 2 3, 2 3 4, 3 4 5), (3 4 5, 4 5 6))'],
            [['CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6)'], true, true, 'COMPOUNDCURVE ZM (CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6))'],
        ];
    }

    /**
     * @dataProvider providerCreateInvalidCompoundCurve
     * @expectedException \Brick\Geo\Exception\InvalidGeometryException
     *
     * @param string $compoundCurve The WKT of an invalid CompoundCurve.
     *
     * @return void
     */
    public function testCreateInvalidCompoundCurve(string $compoundCurve) : void
    {
        CompoundCurve::fromText($compoundCurve);
    }

    /**
     * @return array
     */
    public function providerCreateInvalidCompoundCurve() : array
    {
        return [
            ['COMPOUNDCURVE ((1 1))'], // contains an invalid LineString
            ['COMPOUNDCURVE (CIRCULARSTRING (1 1, 2 2))'], // contains an invalid CircularString
            ['COMPOUNDCURVE ((1 1, 2 2), CIRCULARSTRING (1 1, 2 2, 4 4))'], // incontinuous compound curve
        ];
    }

    /**
     * @dataProvider providerStartPointEndPoint
     *
     * @param string $compoundCurve
     * @param string $startPoint
     * @param string $endPoint
     *
     * @return void
     */
    public function testStartPointEndPoint(string $compoundCurve, string $startPoint, string $endPoint) : void
    {
        foreach ([0, 1] as $srid) {
            $cc = CompoundCurve::fromText($compoundCurve, $srid);
            $this->assertWktEquals($cc->startPoint(), $startPoint, $srid);
            $this->assertWktEquals($cc->endPoint(), $endPoint, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerStartPointEndPoint() : array
    {
        return [
            ['COMPOUNDCURVE ((1 1, 2 2), CIRCULARSTRING (2 2, 3 3, 5 5))', 'POINT (1 1)', 'POINT (5 5)'],
            ['COMPOUNDCURVE Z ((1 2 3, 4 5 6, 7 8 9))', 'POINT Z (1 2 3)', 'POINT Z (7 8 9)'],
            ['COMPOUNDCURVE M (CIRCULARSTRING M (1 2 3, 2 3 4, 3 4 5), (3 4 5, 4 5 6))', 'POINT M (1 2 3)', 'POINT M (4 5 6)'],
            ['COMPOUNDCURVE ZM (CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6))', 'POINT ZM (1 2 3 4)', 'POINT ZM (3 4 5 6)'],
        ];
    }

    /**
     * @dataProvider providerEmptyCompoundCurve
     * @expectedException \Brick\Geo\Exception\EmptyGeometryException
     *
     * @param string $compoundCurve The WKT of an empty CompoundCurve.
     *
     * @return void
     */
    public function testStartPointOfEmptyCompoundCurve(string $compoundCurve) : void
    {
        CompoundCurve::fromText($compoundCurve)->startPoint();
    }

    /**
     * @dataProvider providerEmptyCompoundCurve
     * @expectedException \Brick\Geo\Exception\EmptyGeometryException
     *
     * @param string $compoundCurve The WKT of an empty CompoundCurve.
     *
     * @return void
     */
    public function testEndPointOfEmptyCompoundCurve(string $compoundCurve) : void
    {
        CompoundCurve::fromText($compoundCurve)->endPoint();
    }

    /**
     * @return array
     */
    public function providerEmptyCompoundCurve() : array
    {
        return [
            ['COMPOUNDCURVE EMPTY'],
            ['COMPOUNDCURVE Z EMPTY'],
            ['COMPOUNDCURVE M EMPTY'],
            ['COMPOUNDCURVE ZM EMPTY'],
        ];
    }

    /**
     * @dataProvider providerNumCurves
     *
     * @param string $compoundCurve The WKT of the CompoundCurve to test.
     * @param int    $numCurves     The expected number of curves.
     *
     * @return void
     */
    public function testNumCurves(string $compoundCurve, int $numCurves) : void
    {
        self::assertSame($numCurves, CompoundCurve::fromText($compoundCurve)->numCurves());
    }

    /**
     * @return array
     */
    public function providerNumCurves() : array
    {
        return [
            ['COMPOUNDCURVE EMPTY', 0],
            ['COMPOUNDCURVE Z EMPTY', 0],
            ['COMPOUNDCURVE M EMPTY', 0],
            ['COMPOUNDCURVE ZM EMPTY', 0],
            ['COMPOUNDCURVE ((1 1, 2 2), CIRCULARSTRING (2 2, 3 3, 5 5))', 2],
            ['COMPOUNDCURVE Z ((1 2 3, 4 5 6, 7 8 9))', 1],
            ['COMPOUNDCURVE M (CIRCULARSTRING M (1 2 3, 2 3 4, 3 4 5), (3 4 5, 4 5 6))', 2],
            ['COMPOUNDCURVE ZM (CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6))', 1],
        ];
    }

    /**
     * @dataProvider providerCurveN
     *
     * @param string      $compoundCurve The WKT of the CompoundCurve to test.
     * @param int         $n             The curve number.
     * @param string|null $curveN        The WKT of the expected curve, or NULL if an exception is expected.
     * @param int         $srid          The SRID of the geometries.
     *
     * @return void
     */
    public function testCurveN(string $compoundCurve, int $n, ?string $curveN, int $srid) : void
    {
        if ($curveN === null) {
            $this->expectException(NoSuchGeometryException::class);
        }

        $curve = CompoundCurve::fromText($compoundCurve, $srid)->curveN($n);
        $this->assertWktEquals($curve, $curveN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerCurveN() : \Generator
    {
        $tests = [
            ['COMPOUNDCURVE EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['COMPOUNDCURVE Z EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['COMPOUNDCURVE M EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['COMPOUNDCURVE ZM EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['COMPOUNDCURVE ((1 1, 2 2), CIRCULARSTRING (2 2, 3 3, 5 5))', [
                0 => null,
                1 => 'LINESTRING (1 1, 2 2)',
                2 => 'CIRCULARSTRING (2 2, 3 3, 5 5)',
                3 => null,
            ]],
            ['COMPOUNDCURVE Z ((1 2 3, 4 5 6, 7 8 9))', [
                0 => null,
                1 => 'LINESTRING Z (1 2 3, 4 5 6, 7 8 9)',
                2 => null,
            ]],
            ['COMPOUNDCURVE M (CIRCULARSTRING M (1 2 3, 2 3 4, 3 4 5), (3 4 5, 4 5 6))', [
                0 => null,
                1 => 'CIRCULARSTRING M (1 2 3, 2 3 4, 3 4 5)',
                2 => 'LINESTRING M (3 4 5, 4 5 6)',
                3 => null,
            ]],
            ['COMPOUNDCURVE ZM ((1 2 3 4, 5 6 7 8), CIRCULARSTRING ZM (5 6 7 8, 6 7 8 9, 7 8 9 0))', [
                0 => null,
                1 => 'LINESTRING ZM (1 2 3 4, 5 6 7 8)',
                2 => 'CIRCULARSTRING ZM (5 6 7 8, 6 7 8 9, 7 8 9 0)',
                3 => null,
            ]],
        ];

        foreach ($tests as [$compoundCurve, $curves]) {
            foreach ($curves as $n => $curveN) {
                foreach ([0, 1] as $srid) {
                    yield [$compoundCurve, $n, $curveN, $srid];
                }
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
        $compoundCurve = CompoundCurve::fromText('COMPOUNDCURVE (CIRCULARSTRING(1 2, 3 4, 5 6), (5 6, 7 8))');

        self::assertInstanceOf(\Countable::class, $compoundCurve);
        self::assertCount(2, $compoundCurve);

        self::assertInstanceOf(\Traversable::class, $compoundCurve);
        self::assertSame([
            $compoundCurve->curveN(1),
            $compoundCurve->curveN(2)
        ], iterator_to_array($compoundCurve));
    }
}
