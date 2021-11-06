<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Curve;
use Brick\Geo\Exception\EmptyGeometryException;

/**
 * Unit tests for class Curve.
 */
class CurveTest extends AbstractTestCase
{
    /**
     * @dataProvider providerLength
     *
     * @param string $curve  The WKT of the curve to test.
     * @param float  $length The expected length.
     */
    public function testLength(string $curve, float $length) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $curve = Curve::fromText($curve);
        $this->skipIfUnsupportedGeometry($curve);

        $actualLength = $curve->length($geometryEngine);

        self::assertEqualsWithDelta($length, $actualLength, 0.002);
    }

    public function providerLength() : array
    {
        return [
            ['LINESTRING EMPTY', 0],
            ['LINESTRING (1 1, 2 1)', 1],
            ['LINESTRING (1 1, 1 2)', 1],
            ['LINESTRING (1 1, 2 2)', 1.414],
            ['LINESTRING (1 1, 2 2, 3 2, 3 3)', 3.414],

            ['CIRCULARSTRING (0 0, 1 1, 2 2)', 2.828],
            ['CIRCULARSTRING (0 0, 1 1, 2 1)', 2.483],
            ['CIRCULARSTRING (0 0, 1 1, 3 1, 4 3, 5 3)', 7.013],

            ['COMPOUNDCURVE ((1 1, 2 1), CIRCULARSTRING (2 1, 1 1, 2 2))', 4.331],
            ['COMPOUNDCURVE (CIRCULARSTRING (0 0, 1 1, 2 1), (2 1, 2 2, 3 2, 3 3))', 5.483],
        ];
    }

    /**
     * @dataProvider providerStartPointEndPoint
     *
     * @param string $lineString The WKT of the Curve to test.
     * @param string $startPoint The WKT of the expected start point.
     * @param string $endPoint   The WKT of the expected end point.
     */
    public function testStartPointEndPoint(string $lineString, string $startPoint, string $endPoint) : void
    {
        foreach ([0, 1] as $srid) {
            $ls = Curve::fromText($lineString, $srid);

            $this->assertWktEquals($ls->startPoint(), $startPoint, $srid);
            $this->assertWktEquals($ls->endPoint(), $endPoint, $srid);
        }
    }

    public function providerStartPointEndPoint() : array
    {
        return [
            ['LINESTRING (1 2, 3 4, 5 6)', 'POINT (1 2)', 'POINT (5 6)'],
            ['LINESTRING Z (1 2 3, 4 5 6)', 'POINT Z (1 2 3)', 'POINT Z (4 5 6)'],
            ['LINESTRING M (2 3 4, 5 6 7)', 'POINT M (2 3 4)', 'POINT M (5 6 7)'],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 'POINT ZM (1 2 3 4)', 'POINT ZM (5 6 7 8)'],

            ['CIRCULARSTRING (1 2, 3 4, 5 6)', 'POINT (1 2)', 'POINT (5 6)'],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 'POINT Z (1 2 3)', 'POINT Z (7 8 9)'],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 'POINT M (1 2 3)', 'POINT M (7 8 9)'],
            ['CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6)', 'POINT ZM (1 2 3 4)', 'POINT ZM (3 4 5 6)'],

            ['COMPOUNDCURVE ((1 2, 3 4), CIRCULARSTRING (3 4, 5 6, 7 8))', 'POINT (1 2)', 'POINT (7 8)'],
            ['COMPOUNDCURVE Z ((1 2 3, 4 5 6), CIRCULARSTRING Z (4 5 6, 5 6 7, 6 7 8))', 'POINT Z (1 2 3)', 'POINT Z (6 7 8)'],
            ['COMPOUNDCURVE M ((1 2 3, 2 3 4), CIRCULARSTRING M (2 3 4, 5 6 7, 8 9 0))', 'POINT M (1 2 3)', 'POINT M (8 9 0)'],
            ['COMPOUNDCURVE ZM (CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6), (3 4 5 6, 7 8 9 0))', 'POINT ZM (1 2 3 4)', 'POINT ZM (7 8 9 0)'],
        ];
    }

    /**
     * @dataProvider providerEmptyCurve
     */
    public function testStartPointOfEmptyCurveThrowsException(string $lineString) : void
    {
        $this->expectException(EmptyGeometryException::class);
        Curve::fromText($lineString)->startPoint();
    }

    /**
     * @dataProvider providerEmptyCurve
     */
    public function testEndPointOfEmptyCurveThrowsException(string $lineString) : void
    {
        $this->expectException(EmptyGeometryException::class);
        Curve::fromText($lineString)->endPoint();
    }

    public function providerEmptyCurve() : array
    {
        return [
            ['LINESTRING EMPTY'],
            ['LINESTRING Z EMPTY'],
            ['LINESTRING M EMPTY'],
            ['LINESTRING ZM EMPTY'],

            ['CIRCULARSTRING EMPTY'],
            ['CIRCULARSTRING Z EMPTY'],
            ['CIRCULARSTRING M EMPTY'],
            ['CIRCULARSTRING ZM EMPTY'],

            ['COMPOUNDCURVE EMPTY'],
            ['COMPOUNDCURVE Z EMPTY'],
            ['COMPOUNDCURVE M EMPTY'],
            ['COMPOUNDCURVE ZM EMPTY'],
        ];
    }

    /**
     * @dataProvider providerIsClosed
     *
     * @param string $curve    The WKT of the Curve to test.
     * @param bool   $isClosed Whether the Curve is closed.
     */
    public function testIsClosed(string $curve, bool $isClosed) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $curve = Curve::fromText($curve);
        $this->skipIfUnsupportedGeometry($curve);

        self::assertSame($isClosed, $curve->isClosed($geometryEngine));
    }

    public function providerIsClosed() : array
    {
        return [
            ['LINESTRING (1 1, 2 2)', false],
            ['LINESTRING (1 1, 2 2, 3 3)', false],
            ['LINESTRING (1 1, 2 2, 3 3, 1 1)', true],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0)', false],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0, 1 1 0)', true],
            ['LINESTRING EMPTY', false],
            ['LINESTRING Z EMPTY', false],

            ['CIRCULARSTRING EMPTY', false],
            ['CIRCULARSTRING Z EMPTY', false],
            ['CIRCULARSTRING (1 1, 1 2, 2 2, 3 1, 1 1)', true],
            ['CIRCULARSTRING (1 1, 1 2, 2 2, 3 1, 1 0)', false],

            ['COMPOUNDCURVE EMPTY', false],
            ['COMPOUNDCURVE Z EMPTY', false],
            ['COMPOUNDCURVE ((1 2, 3 4), CIRCULARSTRING (3 4, 5 6, 7 8))', false],
            ['COMPOUNDCURVE ((1 2, 3 4), CIRCULARSTRING (3 4, 5 6, 1 2))', true],
        ];
    }

    /**
     * @dataProvider providerIsRing
     *
     * @param string $curve  The WKT of the Curve to test.
     * @param bool   $isRing Whether the Curve is a ring.
     */
    public function testIsRing(string $curve, bool $isRing) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $curve = Curve::fromText($curve);
        $this->skipIfUnsupportedGeometry($curve);

        if ($curve->isClosed($geometryEngine) && $this->isMariaDB('< 10.1.4')) {
            // @see https://mariadb.atlassian.net/browse/MDEV-7510
            self::markTestSkipped('A bug in MariaDB returns the wrong result.');
        }

        self::assertSame($isRing, $curve->isRing($geometryEngine));
    }

    public function providerIsRing() : array
    {
        return [
            ['LINESTRING (1 1, 2 2)', false],
            ['LINESTRING (1 1, 1 2, 3 3)', false],
            ['LINESTRING (1 1, 1 2, 3 3, 1 1)', true],
            ['LINESTRING (0 0, 0 1, 1 1, 1 0, 0 0)', true],
            ['LINESTRING (0 0, 0 1, 1 0, 1 1, 0 0)', false],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0)', false],
            ['LINESTRING Z (1 1 0, 1 2 0, 2 2 0, 1 1 0)', true],
            ['LINESTRING Z (0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0)', true],
            ['LINESTRING Z (0 0 0, 0 1 0, 1 0 0, 1 1 0, 0 0 0)', false],
            ['LINESTRING M (0 0 1, 0 1 2, 1 1 3, 1 0 4, 0 0 1)', true],
            ['LINESTRING M (0 0 1, 0 1 2, 1 0 3, 1 1 4, 0 0 1)', false],
            ['LINESTRING ZM (0 0 0 1, 0 1 0 2, 1 1 0 3, 1 0 0 4, 0 0 0 1)', true],
            ['LINESTRING ZM (0 0 0 1, 0 1 0 2, 1 0 0 3, 1 1 0 4, 0 0 0 1)', false],
            ['LINESTRING EMPTY', false],
            ['LINESTRING Z EMPTY', false],
            ['LINESTRING M EMPTY', false],
            ['LINESTRING ZM EMPTY', false],

            // Note: there is currently no engine support for isSimple() on non-empty circular strings.

            ['CIRCULARSTRING EMPTY', false],
            ['CIRCULARSTRING Z EMPTY', false],
            ['CIRCULARSTRING M EMPTY', false],
            ['CIRCULARSTRING ZM EMPTY', false],

            // As a consequence, there is no support for isSimple() on non-empty compound curves.

            ['COMPOUNDCURVE EMPTY', false],
            ['COMPOUNDCURVE Z EMPTY', false],
            ['COMPOUNDCURVE M EMPTY', false],
            ['COMPOUNDCURVE ZM EMPTY', false],
        ];
    }
}
