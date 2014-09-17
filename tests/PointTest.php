<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Point;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @dataProvider providerFactory
     *
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     */
    public function testFactory(Point $point, $x, $y, $z, $m)
    {
        $this->assertPointEquals($x, $y, $z, $m, $point);
    }

    /**
     * @return array
     */
    public function providerFactory()
    {
        return [
            [Point::factory('1.2', '3.4'), 1.2, 3.4, null, null],
            [Point::factory('1.2', '3.4', '5.6'), 1.2, 3.4, 5.6, null],
            [Point::factory('1.2', '3.4', null, '5.6'), 1.2, 3.4, null, 5.6],
            [Point::factory('1.2', '3.4', '5.6', '7.8'), 1.2, 3.4, 5.6, 7.8],
        ];
    }

    public function testGeometryType()
    {
        $this->assertSame('Point', Point::factory(0, 0)->geometryType());
    }

    public function testDimension()
    {
        $this->assertSame(0, Point::factory(0, 0)->dimension());
    }

    public function testIsEmpty()
    {
        $this->assertFalse(Point::factory(0, 0)->isEmpty());
    }

    /**
     * @dataProvider providerEquals
     *
     * @param Point   $a
     * @param Point   $b
     * @param boolean $isEqual
     */
    public function testEquals(Point $a, Point $b, $isEqual)
    {
        $this->assertSame($isEqual, $a->equals($b));
    }

    /**
     * @return \Generator
     */
    public function providerEquals()
    {
        $points = [
            [1.2, 2.3, null, null],
            [2.3, 3.4,  4.5, null],
            [3.4, 4.5, null,  5.6],
            [4.5, 5.6,  6.7,  7.8]
        ];

        foreach ($points as list($x1, $y1, $z1, $m1)) {
            $p1 = Point::factory($x1, $y1, $z1, $m1);
            foreach ($points as list($x2, $y2, $z2, $m2)) {
                $p2 = Point::factory($x2, $y2, $z2, $m2);

                $isEqual = $x1 === $x2 && $y1 === $y2 && $z1 === $z2 && $m1 === $m2;

                yield [$p1, $p2, $isEqual];
            }
        }
    }
}
