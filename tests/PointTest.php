<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Point;

/**
 * Unit tests for class Point.
 */
class PointTest extends AbstractTestCase
{
    /**
     * @dataProvider providerFactoryMethodsAndAccessors
     *
     * @param Point      $point
     * @param float      $x
     * @param float      $y
     * @param float|null $z
     * @param float|null $m
     * @param int        $srid
     */
    public function testFactoryMethodsAndAccessors(Point $point, $x, $y, $z, $m, $srid)
    {
        $this->assertSame($x, $point->x());
        $this->assertSame($y, $point->y());
        $this->assertSame($z, $point->z());
        $this->assertSame($m, $point->m());
        $this->assertSame($srid, $point->SRID());
        $this->assertFalse($point->isEmpty());
    }

    /**
     * @return array
     */
    public function providerFactoryMethodsAndAccessors()
    {
        return [
            [Point::xy(1.2, 3.4), 1.2, 3.4, null, null, 0],
            [Point::xy('2.3', '4.5', '4326'), 2.3, 4.5, null, null, 4326],
            [Point::xyz(1.2, 3.4, 5.6), 1.2, 3.4, 5.6, null, 0],
            [Point::xyz('2.3', '4.5', '6.7', 4326), 2.3, 4.5, 6.7, null, 4326],
            [Point::xym(1.2, 3.4, 5.6), 1.2, 3.4, null, 5.6, 0],
            [Point::xym('2.3', '4.5', '6.7', 4326), 2.3, 4.5, null, 6.7, 4326],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), 1.2, 3.4, 5.6, 7.8, 0],
            [Point::xyzm('2.3', '4.5', '6.7', '8.9', 4326), 2.3, 4.5, 6.7, 8.9, 4326]
        ];
    }

    /**
     * @dataProvider providerEmptyFactoryMethods
     *
     * @param Point   $point
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     */
    public function testEmptyFactoryMethods(Point $point, $is3D, $isMeasured, $srid)
    {
        $this->assertTrue($point->isEmpty());
        $this->assertNull($point->x());
        $this->assertNull($point->y());
        $this->assertNull($point->z());
        $this->assertNull($point->m());
        $this->assertSame($is3D, $point->is3D());
        $this->assertSame($isMeasured, $point->isMeasured());
        $this->assertSame($srid, $point->SRID());
    }

    /**
     * @return array
     */
    public function providerEmptyFactoryMethods()
    {
        return [
            [Point::xyEmpty(), false, false, 0],
            [Point::xyzEmpty(), true ,false, 0],
            [Point::xymEmpty(), false, true, 0],
            [Point::xyzmEmpty(), true, true, 0],
            [Point::xyEmpty(4326), false, false, 4326],
            [Point::xyzEmpty(4326), true ,false, 4326],
            [Point::xymEmpty(4326), false, true, 4326],
            [Point::xyzmEmpty(4326), true, true, 4326]
        ];
    }
}
