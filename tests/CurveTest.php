<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Curve;
use Brick\Geo\Exception\EmptyGeometryException;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class Curve.
 */
class CurveTest extends AbstractTestCase
{
    /**
     * @param string $lineString The WKT of the Curve to test.
     * @param string $startPoint The WKT of the expected start point.
     * @param string $endPoint   The WKT of the expected end point.
     */
    #[DataProvider('providerStartPointEndPoint')]
    public function testStartPointEndPoint(string $lineString, string $startPoint, string $endPoint): void
    {
        foreach ([0, 1] as $srid) {
            $ls = Curve::fromText($lineString, $srid);

            $this->assertWktEquals($ls->startPoint(), $startPoint, $srid);
            $this->assertWktEquals($ls->endPoint(), $endPoint, $srid);
        }
    }

    public static function providerStartPointEndPoint(): array
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

    #[DataProvider('providerEmptyCurve')]
    public function testStartPointOfEmptyCurveThrowsException(string $lineString): void
    {
        $this->expectException(EmptyGeometryException::class);
        Curve::fromText($lineString)->startPoint();
    }

    #[DataProvider('providerEmptyCurve')]
    public function testEndPointOfEmptyCurveThrowsException(string $lineString): void
    {
        $this->expectException(EmptyGeometryException::class);
        Curve::fromText($lineString)->endPoint();
    }

    public static function providerEmptyCurve(): array
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
}
