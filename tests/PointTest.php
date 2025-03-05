<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Point;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @param float[] $coords
     */
    #[DataProvider('providerConstructorWithInvalidCoordinates')]
    public function testConstructorWithInvalidCoordinates(
        bool $hasZ,
        bool $hasM,
        array $coords,
        string $exceptionMessage,
    ): void {
        $this->expectException(InvalidGeometryException::class);
        $this->expectExceptionMessage($exceptionMessage);

        new Point(new CoordinateSystem($hasZ, $hasM), ...$coords);
    }

    public static function providerConstructorWithInvalidCoordinates() : Generator
    {
        yield [false, false, [1], 'Expected 2 coordinates for Point XY, got 1.'];
        yield [false, false, [1, 2, 3], 'Expected 2 coordinates for Point XY, got 3.'];
        yield [true,  false, [1], 'Expected 3 coordinates for Point XYZ, got 1.'];
        yield [true,  false, [1, 2], 'Expected 3 coordinates for Point XYZ, got 2.'];
        yield [true,  false, [1, 2, 3, 4], 'Expected 3 coordinates for Point XYZ, got 4.'];
        yield [false, true,  [1], 'Expected 3 coordinates for Point XYM, got 1.'];
        yield [false, true,  [1, 2], 'Expected 3 coordinates for Point XYM, got 2.'];
        yield [false, true,  [1, 2, 3, 4], 'Expected 3 coordinates for Point XYM, got 4.'];
        yield [true,  true,  [1], 'Expected 4 coordinates for Point XYZM, got 1.'];
        yield [true,  true,  [1, 2], 'Expected 4 coordinates for Point XYZM, got 2.'];
        yield [true,  true,  [1, 2, 3], 'Expected 4 coordinates for Point XYZM, got 3.'];
        yield [true,  true,  [1, 2, 3, 4, 5], 'Expected 4 coordinates for Point XYZM, got 5.'];

        foreach ([
            [NAN, 'NaN'],
            [+INF, '+INF'],
            [-INF, '-INF'],
        ] as [$value, $name]) {
            yield [false, false, [$value, 2], "Coordinate #1 (X) for Point XY is $name, this is not allowed."];
            yield [false, false, [1, $value], "Coordinate #2 (Y) for Point XY is $name, this is not allowed."];
            yield [true, false, [1, 2, $value], "Coordinate #3 (Z) for Point XYZ is $name, this is not allowed."];
            yield [false, true, [1, 2, $value], "Coordinate #3 (M) for Point XYM is $name, this is not allowed."];
            yield [true, true, [1, 2, $value, 4], "Coordinate #3 (Z) for Point XYZM is $name, this is not allowed."];
            yield [true, true, [1, 2, 3, $value], "Coordinate #4 (M) for Point XYZM is $name, this is not allowed."];
        }
    }

    public function testConstructorWithAssociativeArray() : void
    {
        $point = new Point(CoordinateSystem::xy(), ...['x_whatever' => 1, 'y_whatever' => 2]);

        self::assertSame(1.0, $point->x());
        self::assertSame(2.0, $point->y());
    }

    private function assertPointFactoryMethodAndAccessors(Point $point, float $x, float $y, ?float $z, ?float $m, int $srid) : void
    {
        self::assertSame($x, $point->x());
        self::assertSame($y, $point->y());
        self::assertSame($z, $point->z());
        self::assertSame($m, $point->m());
        self::assertSame($srid, $point->SRID());
        self::assertFalse($point->isEmpty());
    }

    public function testXy() : void
    {
        $point = Point::xy(1.2, 3.4);
        $this->assertPointFactoryMethodAndAccessors($point, 1.2, 3.4, null, null, 0);
    }

    public function testXyWithSRID() : void
    {
        $point = Point::xy(1.2, 3.4, 123);
        $this->assertPointFactoryMethodAndAccessors($point, 1.2, 3.4, null, null, 123);
    }

    public function testXyz() : void
    {
        $point = Point::xyz(2.3, 3.4, 4.5);
        $this->assertPointFactoryMethodAndAccessors($point, 2.3, 3.4, 4.5, null, 0);
    }

    public function testXyzWithSRID() : void
    {
        $point = Point::xyz(2.3, 3.4, 4.5, 123);
        $this->assertPointFactoryMethodAndAccessors($point, 2.3, 3.4, 4.5, null, 123);
    }

    public function testXym() : void
    {
        $point = Point::xym(3.4, 4.5, 5.6);
        $this->assertPointFactoryMethodAndAccessors($point, 3.4, 4.5, null, 5.6, 0);
    }

    public function testXymWithSRID() : void
    {
        $point = Point::xym(3.4, 4.5, 5.6, 123);
        $this->assertPointFactoryMethodAndAccessors($point, 3.4, 4.5, null, 5.6, 123);
    }

    public function testXyzm() : void
    {
        $point = Point::xyzm(4.5, 5.6, 6.7, 7.8);
        $this->assertPointFactoryMethodAndAccessors($point, 4.5, 5.6, 6.7, 7.8, 0);
    }

    public function testXyzmWithSRID() : void
    {
        $point = Point::xyzm(4.5, 5.6, 6.7, 7.8, 123);
        $this->assertPointFactoryMethodAndAccessors($point, 4.5, 5.6, 6.7, 7.8, 123);
    }

    private function assertPointEmptyFactoryMethod(Point $point, bool $is3D, bool $isMeasured, int $srid) : void
    {
        self::assertTrue($point->isEmpty());
        self::assertNull($point->x());
        self::assertNull($point->y());
        self::assertNull($point->z());
        self::assertNull($point->m());
        self::assertSame($is3D, $point->is3D());
        self::assertSame($isMeasured, $point->isMeasured());
        self::assertSame($srid, $point->SRID());
    }

    public function testXyEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyEmpty(), false, false, 0);
    }

    public function testXyEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyEmpty(123), false, false, 123);
    }

    public function testXyzEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzEmpty(), true, false, 0);
    }

    public function testXyzEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzEmpty(123), true, false, 123);
    }

    public function testXymEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xymEmpty(), false, true, 0);
    }

    public function testXymEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xymEmpty(123), false, true, 123);
    }

    public function testXyzmEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzmEmpty(), true, true, 0);
    }

    public function testXyzmEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzmEmpty(123), true, true, 123);
    }

    /**
     * @param string $point       The WKT of the point to test.
     * @param array  $coordinates The expected coordinates.
     */
    #[DataProvider('providerToArray')]
    public function testToArray(string $point, array $coordinates) : void
    {
        $point = Point::fromText($point);
        self::assertSame($coordinates, $point->toArray());
    }

    public static function providerToArray() : array
    {
        return [
            ['POINT EMPTY', []],
            ['POINT Z EMPTY', []],
            ['POINT M EMPTY', []],
            ['POINT ZM EMPTY', []],
            ['POINT (1.2 2.3)', [1.2, 2.3]],
            ['POINT Z (2.3 3.4 4.5)', [2.3, 3.4, 4.5]],
            ['POINT M (3.4 4.5 5.6)', [3.4, 4.5, 5.6]],
            ['POINT ZM (4.5 5.6 6.7 7.8)', [4.5, 5.6, 6.7, 7.8]],
        ];
    }
}
