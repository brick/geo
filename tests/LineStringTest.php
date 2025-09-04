<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Countable;
use PHPUnit\Framework\Attributes\DataProvider;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * Unit tests for class LineString.
 */
class LineStringTest extends AbstractTestCase
{
    #[DataProvider('providerNumPoints')]
    public function testNumPoints(string $lineString, int $numPoints): void
    {
        $lineString = LineString::fromText($lineString);
        self::assertSame($numPoints, $lineString->numPoints());
    }

    public static function providerNumPoints(): array
    {
        return [
            ['LINESTRING EMPTY', 0],
            ['LINESTRING Z EMPTY', 0],
            ['LINESTRING M EMPTY', 0],
            ['LINESTRING ZM EMPTY', 0],
            ['LINESTRING (1 2, 3 4, 5 6, 7 8)', 4],
            ['LINESTRING Z (1 2 3, 4 5 6)', 2],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 3],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 2],
        ];
    }

    #[DataProvider('providerPointN')]
    public function testPointN(string $lineString, int $n, string $pointN): void
    {
        foreach ([0, 1] as $srid) {
            $ls = LineString::fromText($lineString, $srid);
            $this->assertWktEquals($ls->pointN($n), $pointN, $srid);
        }
    }

    public static function providerPointN(): array
    {
        return [
            ['LINESTRING (1 2, 3 4, 5 6)', 1, 'POINT (1 2)'],
            ['LINESTRING (1 2, 3 4, 5 6)', 2, 'POINT (3 4)'],
            ['LINESTRING (1 2, 3 4, 5 6)', 3, 'POINT (5 6)'],
            ['LINESTRING Z (1 2 3, 4 5 6)', 1, 'POINT Z (1 2 3)'],
            ['LINESTRING Z (1 2 3, 4 5 6)', 2, 'POINT Z (4 5 6)'],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 1, 'POINT M (1 2 3)'],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 2, 'POINT M (4 5 6)'],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 3, 'POINT M (7 8 9)'],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 1, 'POINT ZM (1 2 3 4)'],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 2, 'POINT ZM (5 6 7 8)'],
        ];
    }

    #[DataProvider('providerInvalidPointNThrowsException')]
    public function testInvalidPointNThrowsException(string $lineString, int $n): void
    {
        $this->expectException(NoSuchGeometryException::class);
        LineString::fromText($lineString)->pointN($n);
    }

    public static function providerInvalidPointNThrowsException(): array
    {
        return [
            ['LINESTRING (1 2, 3 4, 5 6)', 0],
            ['LINESTRING (1 2, 3 4, 5 6)', 4],
            ['LINESTRING Z (1 2 3, 4 5 6)', 0],
            ['LINESTRING Z (1 2 3, 4 5 6)', 3],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 0],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 5],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 0],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 3],
        ];
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces(): void
    {
        $lineString = LineString::fromText('LINESTRING (1 2, 3 4, 5 6)');

        self::assertInstanceOf(Countable::class, $lineString);
        self::assertCount(3, $lineString);

        self::assertInstanceOf(Traversable::class, $lineString);
        self::assertSame([
            $lineString->pointN(1),
            $lineString->pointN(2),
            $lineString->pointN(3),
        ], iterator_to_array($lineString));
    }

    #[DataProvider('providerRectangle')]
    public function testRectangle(string $point1, string $point2, string $expected): void
    {
        $point1 = Point::fromText($point1);
        $point2 = Point::fromText($point2);

        $actual = LineString::rectangle($point1, $point2);

        self::assertSame($expected, $actual->asText());
    }

    public static function providerRectangle(): array
    {
        return [
            ['POINT (1 2)', 'POINT (3 4)', 'LINESTRING (1 2, 3 2, 3 4, 1 4, 1 2)'],
            ['POINT (1 4)', 'POINT (3 2)', 'LINESTRING (1 2, 3 2, 3 4, 1 4, 1 2)'],
            ['POINT (3 2)', 'POINT (1 4)', 'LINESTRING (1 2, 3 2, 3 4, 1 4, 1 2)'],
        ];
    }

    #[DataProvider('providerRectangleWithInvalidPoints')]
    public function testRectangleWithInvalidPoints(string $point1, string $point2, int $srid1 = 0, int $srid2 = 0): void
    {
        $point1 = Point::fromText($point1, $srid1);
        $point2 = Point::fromText($point2, $srid2);

        $this->expectException(CoordinateSystemException::class);
        LineString::rectangle($point1, $point2);
    }

    public static function providerRectangleWithInvalidPoints(): array
    {
        return [
            ['POINT (1 1)', 'POINT Z (2 3 4)'],
            ['POINT M (1 2 3)', 'POINT (3 3)'],
            ['POINT (1 2)', 'POINT ZM (3 4 5 6)'],
            ['POINT (1 2)', 'POINT (3 4)', 1, 2],
        ];
    }

    /**
     * @param string[] $addedPointsWkt
     */
    #[DataProvider('providerWithAddedPoints')]
    public function testWithAddedPoints(string $lineStringWkt, array $addedPointsWkt, string $expectedWkt): void
    {
        $lineString = LineString::fromText($lineStringWkt, 1234);
        $actual = $lineString->withAddedPoints(
            ...array_map(
                fn (string $wkt) => Point::fromText($wkt, 1234),
                $addedPointsWkt,
            ),
        );

        $this->assertWktEquals($lineString, $lineStringWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithAddedPoints(): array
    {
        return [
            ['LINESTRING EMPTY', [], 'LINESTRING EMPTY'],
            ['LINESTRING EMPTY', ['POINT (1 2)', 'POINT (3 4)'], 'LINESTRING (1 2, 3 4)'],
            ['LINESTRING (0 0, 1 1)', [], 'LINESTRING (0 0, 1 1)'],
            ['LINESTRING (0 0, 1 1)', ['POINT (2 2)'], 'LINESTRING (0 0, 1 1, 2 2)'],
            ['LINESTRING (0 0, 1 1)', ['POINT (2 2)', 'POINT (3 3)'], 'LINESTRING (0 0, 1 1, 2 2, 3 3)'],
        ];
    }
}
