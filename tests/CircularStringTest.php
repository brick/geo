<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\CircularString;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Point;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class CircularString.
 */
class CircularStringTest extends AbstractTestCase
{
    /**
     * @param string[] $pointsWKT        The WKT of the Points that compose the CircularString.
     * @param bool     $is3D              Whether the points have Z coordinates.
     * @param bool     $isMeasured        Whether the points have M coordinates.
     * @param string   $circularStringWKT The WKT of the expected CircularString.
     */
    #[DataProvider('providerCreate')]
    public function testCreate(array $pointsWKT, bool $is3D, bool $isMeasured, string $circularStringWKT) : void
    {
        foreach ([0, 1] as $srid) {
            $instantiatePoint = fn(string $point) => Point::fromText($point, $srid);

            $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
            $circularString = new CircularString($cs, ...array_map($instantiatePoint, $pointsWKT));
            $this->assertWktEquals($circularString, $circularStringWKT, $srid);
        }
    }

    public static function providerCreate() : array
    {
        return [
            [['POINT (1 1)', 'POINT (2 2)', 'POINT (3 3)'], false, false, 'CIRCULARSTRING (1 1, 2 2, 3 3)'],
            [['POINT Z (1 2 3)', 'POINT Z (4 5 6)', 'POINT Z (7 8 9)'], true, false, 'CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)'],
            [['POINT M (1 2 3)', 'POINT M (4 5 6)', 'POINT M (7 8 9)'], false, true, 'CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)'],
            [['POINT ZM (1 2 3 4)', 'POINT ZM (2 3 4 5)', 'POINT ZM (3 4 5 6)'], true, true, 'CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6)'],
        ];
    }

    /**
     * @param string $circularString The WKT of an invalid CircularString.
     */
    #[DataProvider('providerCreateInvalidCircularString')]
    public function testCreateInvalidCircularString(string $circularString) : void
    {
        $this->expectException(InvalidGeometryException::class);
        CircularString::fromText($circularString);
    }

    public static function providerCreateInvalidCircularString() : array
    {
        return [
            ['CIRCULARSTRING (1 1)'],
            ['CIRCULARSTRING (1 1, 2 2)'],
            ['CIRCULARSTRING (1 1, 2 2, 3 3, 4 4)'],
            ['CIRCULARSTRING (1 1, 2 2, 3 3, 4 4, 5 5, 6 6)'],
        ];
    }

    #[DataProvider('providerStartPointEndPoint')]
    public function testStartPointEndPoint(string $circularString, string $startPoint, string $endPoint) : void
    {
        foreach ([0, 1] as $srid) {
            $cs = CircularString::fromText($circularString, $srid);
            $this->assertWktEquals($cs->startPoint(), $startPoint, $srid);
            $this->assertWktEquals($cs->endPoint(), $endPoint, $srid);
        }
    }

    public static function providerStartPointEndPoint() : array
    {
        return [
            ['CIRCULARSTRING (1 2, 3 4, 5 6, 7 8, 9 0)', 'POINT (1 2)', 'POINT (9 0)'],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 'POINT Z (1 2 3)', 'POINT Z (7 8 9)'],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 'POINT M (1 2 3)', 'POINT M (7 8 9)'],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5)', 'POINT ZM (1 2 3 4)', 'POINT ZM (2 3 4 5)']
        ];
    }

    /**
     * @param string $circularString The WKT of an empty CircularString.
     */
    #[DataProvider('providerEmptyCircularString')]
    public function testStartPointOfEmptyCircularString(string $circularString) : void
    {
        $this->expectException(EmptyGeometryException::class);
        CircularString::fromText($circularString)->startPoint();
    }

    /**
     * @param string $circularString The WKT of an empty CircularString.
     */
    #[DataProvider('providerEmptyCircularString')]
    public function testEndPointOfEmptyCircularString(string $circularString) : void
    {
        $this->expectException(EmptyGeometryException::class);
        CircularString::fromText($circularString)->endPoint();
    }

    public static function providerEmptyCircularString() : array
    {
        return [
            ['CIRCULARSTRING EMPTY'],
            ['CIRCULARSTRING Z EMPTY'],
            ['CIRCULARSTRING M EMPTY'],
            ['CIRCULARSTRING ZM EMPTY'],
        ];
    }

    #[DataProvider('providerNumPoints')]
    public function testNumPoints(string $circularString, int $numPoints) : void
    {
        $circularString = CircularString::fromText($circularString);
        self::assertSame($numPoints, $circularString->numPoints());
    }

    public static function providerNumPoints() : array
    {
        return [
            ['CIRCULARSTRING EMPTY', 0],
            ['CIRCULARSTRING Z EMPTY', 0],
            ['CIRCULARSTRING M EMPTY', 0],
            ['CIRCULARSTRING ZM EMPTY', 0],
            ['CIRCULARSTRING (1 2, 3 4, 5 6, 7 8, 9 0)', 5],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 3],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 3],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5)', 3]
        ];
    }

    #[DataProvider('providerPointN')]
    public function testPointN(string $lineString, int $n, string $pointN) : void
    {
        foreach ([0, 1] as $srid) {
            $ls = CircularString::fromText($lineString, $srid);
            $this->assertWktEquals($ls->pointN($n), $pointN, $srid);
        }
    }

    public static function providerPointN() : array
    {
        return [
            ['CIRCULARSTRING (1 2, 3 4, 5 6)', 1, 'POINT (1 2)'],
            ['CIRCULARSTRING (1 2, 3 4, 5 6)', 2, 'POINT (3 4)'],
            ['CIRCULARSTRING (1 2, 3 4, 5 6)', 3, 'POINT (5 6)'],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 1, 'POINT Z (1 2 3)'],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 2, 'POINT Z (4 5 6)'],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 3, 'POINT Z (7 8 9)'],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 1, 'POINT M (1 2 3)'],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 2, 'POINT M (4 5 6)'],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 3, 'POINT M (7 8 9)'],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5)', 1, 'POINT ZM (1 2 3 4)'],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5)', 2, 'POINT ZM (5 6 7 8)'],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5)', 3, 'POINT ZM (2 3 4 5)'],
        ];
    }

    #[DataProvider('providerInvalidPointNThrowsException')]
    public function testInvalidPointNThrowsException(string $lineString, int $n) : void
    {
        $this->expectException(NoSuchGeometryException::class);
        CircularString::fromText($lineString)->pointN($n);
    }

    public static function providerInvalidPointNThrowsException() : array
    {
        return [
            ['CIRCULARSTRING (1 2, 3 4, 5 2)', 0],
            ['CIRCULARSTRING (1 2, 3 4, 5 2)', 4],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 2 9)', 0],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 2 9)', 4],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5, 3 4 5 6, 4 5 6 7)', 0],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5, 3 4 5 6, 4 5 6 7)', 6]
        ];
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces() : void
    {
        $circularString = CircularString::fromText('CIRCULARSTRING (1 2, 3 4, 5 6)');

        self::assertInstanceOf(\Countable::class, $circularString);
        self::assertCount(3, $circularString);

        self::assertInstanceOf(\Traversable::class, $circularString);
        self::assertSame([
            $circularString->pointN(1),
            $circularString->pointN(2),
            $circularString->pointN(3)
        ], iterator_to_array($circularString));
    }

    /**
     * @param string[] $addedPointsWkt
     */
    #[DataProvider('providerWithAddedPoints')]
    public function testWithAddedPoints(string $circularStringWkt, array $addedPointsWkt, string $expectedWkt): void
    {
        $circularString = CircularString::fromText($circularStringWkt, 1234);
        $actual = $circularString->withAddedPoints(...array_map(
            fn (string $wkt) => Point::fromText($wkt, 1234),
            $addedPointsWkt,
        ));

        $this->assertWktEquals($circularString, $circularStringWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithAddedPoints(): array
    {
        return [
            ['CIRCULARSTRING EMPTY', ['POINT (1 2)', 'POINT (3 4)', 'POINT (5 6)'], 'CIRCULARSTRING (1 2, 3 4, 5 6)'],
            ['CIRCULARSTRING (1 2, 3 4, 5 6)', [], 'CIRCULARSTRING (1 2, 3 4, 5 6)'],
            ['CIRCULARSTRING (1 2, 3 4, 5 6)', ['POINT (7 8)', 'POINT (9 0)'], 'CIRCULARSTRING (1 2, 3 4, 5 6, 7 8, 9 0)'],
            ['CIRCULARSTRING Z EMPTY', ['POINT Z (1 2 3)', 'POINT Z (3 4 5)', 'POINT Z (5 6 7)'], 'CIRCULARSTRING Z (1 2 3, 3 4 5, 5 6 7)'],
            ['CIRCULARSTRING Z (1 2 1, 3 4 2, 5 6 3)', [], 'CIRCULARSTRING Z (1 2 1, 3 4 2, 5 6 3)'],
            ['CIRCULARSTRING Z (1 2 1, 3 4 2, 5 6 3)', ['POINT Z (7 8 4)', 'POINT Z (9 0 5)'], 'CIRCULARSTRING Z (1 2 1, 3 4 2, 5 6 3, 7 8 4, 9 0 5)'],
        ];
    }
}
