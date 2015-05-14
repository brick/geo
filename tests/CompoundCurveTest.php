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
     * @param boolean  $is3D             Whether the curves have Z coordinates.
     * @param boolean  $isMeasured       Whether the curves have M coordinates.
     * @param string   $compoundCurveWKT The WKT of the expected CompoundCurve.
     */
    public function testCreate(array $curvesWKT, $is3D, $isMeasured, $compoundCurveWKT)
    {
        foreach ([0, 1] as $srid) {
            $instantiateCurve = function ($curve) use ($srid) {
                return Curve::fromText($curve, $srid, false);
            };

            $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
            $compoundCurve = new CompoundCurve($cs, ...array_map($instantiateCurve, $curvesWKT));
            $this->assertWktEquals($compoundCurve, $compoundCurveWKT, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerCreate()
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
     */
    public function testCreateInvalidCompoundCurve($compoundCurve)
    {
        CompoundCurve::fromText($compoundCurve, 0, false);
    }

    /**
     * @return array
     */
    public function providerCreateInvalidCompoundCurve()
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
     */
    public function testStartPointEndPoint($compoundCurve, $startPoint, $endPoint)
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
    public function providerStartPointEndPoint()
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
     */
    public function testStartPointOfEmptyCompoundCurve($compoundCurve)
    {
        CompoundCurve::fromText($compoundCurve)->startPoint();
    }

    /**
     * @dataProvider providerEmptyCompoundCurve
     * @expectedException \Brick\Geo\Exception\EmptyGeometryException
     *
     * @param string $compoundCurve The WKT of an empty CompoundCurve.
     */
    public function testEndPointOfEmptyCompoundCurve($compoundCurve)
    {
        CompoundCurve::fromText($compoundCurve)->endPoint();
    }

    /**
     * @return array
     */
    public function providerEmptyCompoundCurve()
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
     * @param string  $compoundCurve The WKT of the CompoundCurve to test.
     * @param integer $numCurves     The expected number of curves.
     */
    public function testNumCurves($compoundCurve, $numCurves)
    {
        $this->assertSame($numCurves, CompoundCurve::fromText($compoundCurve)->numCurves());
    }

    /**
     * @return array
     */
    public function providerNumCurves()
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
     * @param integer     $n             The curve number.
     * @param string|null $curveN        The WKT of the expected curve, or NULL if an exception is expected.
     * @param integer     $srid          The SRID of the geometries.
     */
    public function testCurveN($compoundCurve, $n, $curveN, $srid)
    {
        if ($curveN === null) {
            $this->setExpectedException(NoSuchGeometryException::class);
        }

        $curve = CompoundCurve::fromText($compoundCurve, $srid)->curveN($n);
        $this->assertWktEquals($curve, $curveN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerCurveN()
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

        foreach ($tests as list ($compoundCurve, $curves)) {
            foreach ($curves as $n => $curveN) {
                foreach ([0, 1] as $srid) {
                    yield [$compoundCurve, $n, $curveN, $srid];
                }
            }
        }
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces()
    {
        $compoundCurve = CompoundCurve::fromText('COMPOUNDCURVE (CIRCULARSTRING(1 2, 3 4, 5 6), (5 6, 7 8))');

        $this->assertInstanceOf(\Countable::class, $compoundCurve);
        $this->assertSame(2, count($compoundCurve));

        $this->assertInstanceOf(\Traversable::class, $compoundCurve);
        $this->assertSame([
            $compoundCurve->curveN(1),
            $compoundCurve->curveN(2)
        ], iterator_to_array($compoundCurve));
    }
}
