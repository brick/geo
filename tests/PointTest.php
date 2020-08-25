<?php

namespace Brick\Geo\Tests;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Point;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @dataProvider providerConstructorWithInvalidCoordinates
     *
     * @param bool    $z
     * @param bool    $m
     * @param float[] $coords
     *
     * @return void
     */
    public function testConstructorWithInvalidCoordinates(bool $z, bool $m, float ...$coords) : void
    {
        $this->expectException(InvalidGeometryException::class);
        new Point(new CoordinateSystem($z, $m), ...$coords);
    }

    /**
     * @return array
     */
    public function providerConstructorWithInvalidCoordinates() : array
    {
        return [
            [false, false, 1],
            [false, false, 1, 2, 3],
            [true,  false, 1],
            [true,  false, 1, 2],
            [true,  false, 1, 2, 3, 4],
            [false, true,  1],
            [false, true,  1, 2],
            [false, true,  1, 2, 3, 4],
            [true,  true,  1],
            [true,  true,  1, 2],
            [true,  true,  1, 2, 3],
            [true,  true,  1, 2, 3, 4, 5],
        ];
    }

    /**
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     * @param int        $srid
     *
     * @return void
     */
    private function assertPointFactoryMethodAndAccessors(Point $point, float $x, float $y, ?float $z, ?float $m, int $srid) : void
    {
        self::assertSame($x, $point->x());
        self::assertSame($y, $point->y());
        self::assertSame($z, $point->z());
        self::assertSame($m, $point->m());
        self::assertSame($srid, $point->SRID());
        self::assertFalse($point->isEmpty());
    }

    /**
     * @return void
     */
    public function testXy() : void
    {
        $point = Point::xy('1.2', '3.4');
        $this->assertPointFactoryMethodAndAccessors($point, 1.2, 3.4, null, null, 0);
    }

    /**
     * @return void
     */
    public function testXyWithSRID() : void
    {
        $point = Point::xy('1.2', '3.4', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 1.2, 3.4, null, null, 123);
    }

    /**
     * @return void
     */
    public function testXyz() : void
    {
        $point = Point::xyz('2.3', '3.4', '4.5');
        $this->assertPointFactoryMethodAndAccessors($point, 2.3, 3.4, 4.5, null, 0);
    }

    /**
     * @return void
     */
    public function testXyzWithSRID() : void
    {
        $point = Point::xyz('2.3', '3.4', '4.5', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 2.3, 3.4, 4.5, null, 123);
    }

    /**
     * @return void
     */
    public function testXym() : void
    {
        $point = Point::xym('3.4', '4.5', '5.6');
        $this->assertPointFactoryMethodAndAccessors($point, 3.4, 4.5, null, 5.6, 0);
    }

    /**
     * @return void
     */
    public function testXymWithSRID() : void
    {
        $point = Point::xym('3.4', '4.5', '5.6', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 3.4, 4.5, null, 5.6, 123);
    }

    /**
     * @return void
     */
    public function testXyzm() : void
    {
        $point = Point::xyzm('4.5', '5.6', '6.7', '7.8');
        $this->assertPointFactoryMethodAndAccessors($point, 4.5, 5.6, 6.7, 7.8, 0);
    }

    /**
     * @return void
     */
    public function testXyzmWithSRID() : void
    {
        $point = Point::xyzm('4.5', '5.6', '6.7', '7.8', 123);
        $this->assertPointFactoryMethodAndAccessors($point, 4.5, 5.6, 6.7, 7.8, 123);
    }

    /**
     * @param Point $point
     * @param bool  $is3D
     * @param bool  $isMeasured
     * @param int   $srid
     *
     * @return void
     */
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

    /**
     * @return void
     */
    public function testXyEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyEmpty(), false, false, 0);
    }

    /**
     * @return void
     */
    public function testXyEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyEmpty(123), false, false, 123);
    }

    /**
     * @return void
     */
    public function testXyzEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzEmpty(), true, false, 0);
    }

    /**
     * @return void
     */
    public function testXyzEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzEmpty(123), true, false, 123);
    }

    /**
     * @return void
     */
    public function testXymEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xymEmpty(), false, true, 0);
    }

    /**
     * @return void
     */
    public function testXymEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xymEmpty(123), false, true, 123);
    }

    /**
     * @return void
     */
    public function testXyzmEmpty() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzmEmpty(), true, true, 0);
    }

    /**
     * @return void
     */
    public function testXyzmEmptyWithSRID() : void
    {
        $this->assertPointEmptyFactoryMethod(Point::xyzmEmpty(123), true, true, 123);
    }

    /**
     * @dataProvider providerToArrayAndInterfaces
     *
     * @param string $point       The WKT of the point to test.
     * @param array  $coordinates The expected coordinates.
     *
     * @return void
     */
    public function testToArrayAndInterfaces(string $point, array $coordinates) : void
    {
        $point = Point::fromText($point);
        self::assertSame($coordinates, $point->toArray());
        self::assertSame($coordinates, iterator_to_array($point));
        self::assertSame(count($coordinates), count($point));
    }

    /**
     * @return array
     */
    public function providerToArrayAndInterfaces() : array
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
