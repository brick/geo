<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
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

    /**
     * @dataProvider providerWithX
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithX($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withX(9), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withX(9), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithX()
    {
        return [
            ['POINT (1 2)', 'POINT (9 2)'],
            ['POINT Z (1 2 3)', 'POINT Z (9 2 3)'],
            ['POINT M (1 2 3)', 'POINT M (9 2 3)'],
            ['POINT ZM (1 2 3 4)', 'POINT ZM (9 2 3 4)']
        ];
    }

    /**
     * @dataProvider providerWithY
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithY($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withY(9), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withY(9), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithY()
    {
        return [
            ['POINT (1 2)', 'POINT (1 9)'],
            ['POINT Z (1 2 3)', 'POINT Z (1 9 3)'],
            ['POINT M (1 2 3)', 'POINT M (1 9 3)'],
            ['POINT ZM (1 2 3 4)', 'POINT ZM (1 9 3 4)']
        ];
    }

    /**
     * @dataProvider providerWithZ
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithZ($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withZ(9), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withZ(9), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithZ()
    {
        return [
            ['POINT (1 2)', 'POINT Z (1 2 9)'],
            ['POINT Z (1 2 3)', 'POINT Z (1 2 9)'],
            ['POINT M (1 2 3)', 'POINT ZM (1 2 9 3)'],
            ['POINT ZM (1 2 3 4)', 'POINT ZM (1 2 9 4)']
        ];
    }

    /**
     * @dataProvider providerWithM
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithM($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withM(9), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withM(9), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithM()
    {
        return [
            ['POINT (1 2)', 'POINT M (1 2 9)'],
            ['POINT Z (1 2 3)', 'POINT ZM (1 2 3 9)'],
            ['POINT M (1 2 3)', 'POINT M (1 2 9)'],
            ['POINT ZM (1 2 3 4)', 'POINT ZM (1 2 3 9)']
        ];
    }

    /**
     * @dataProvider providerWithoutZ
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithoutZ($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withoutZ(), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withoutZ(), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithoutZ()
    {
        return [
            ['POINT (1 2)', 'POINT (1 2)'],
            ['POINT Z (1 2 3)', 'POINT (1 2)'],
            ['POINT M (1 2 3)', 'POINT M (1 2 3)'],
            ['POINT ZM (1 2 3 4)', 'POINT M (1 2 4)']
        ];
    }

    /**
     * @dataProvider providerWithoutM
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithoutM($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withoutM(), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withoutM(), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithoutM()
    {
        return [
            ['POINT (1 2)', 'POINT (1 2)'],
            ['POINT Z (1 2 3)', 'POINT Z (1 2 3)'],
            ['POINT M (1 2 3)', 'POINT (1 2)'],
            ['POINT ZM (1 2 3 4)', 'POINT Z (1 2 3)']
        ];
    }

    /**
     * @dataProvider providerWithoutZM
     *
     * @param string $point  The WKT of the point to alter.
     * @param string $result The WKT of the expected result.
     */
    public function testWithoutZM($point, $result)
    {
        $this->assertWktEquals(Point::fromText($point)->withoutZM(), $result);
        $this->assertWktEquals(Point::fromText($point, 4326)->withoutZM(), $result, 4326);
    }

    /**
     * @return array
     */
    public function providerWithoutZM()
    {
        return [
            ['POINT (1 2)', 'POINT (1 2)'],
            ['POINT Z (1 2 3)', 'POINT (1 2)'],
            ['POINT M (1 2 3)', 'POINT (1 2)'],
            ['POINT ZM (1 2 3 4)', 'POINT (1 2)']
        ];
    }

    /**
     * @dataProvider providerWithSRID
     *
     * @param string $point  The WKT of the point to alter.
     */
    public function testWithSRID($point)
    {
        $this->assertWktEquals(Point::fromText($point, 4326)->withSRID(4327), $point, 4327);
    }

    /**
     * @return array
     */
    public function providerWithSRID()
    {
        return [
            ['POINT (1 2)'],
            ['POINT Z (1 2 3)'],
            ['POINT M (1 2 3)'],
            ['POINT ZM (1 2 3 4)']
        ];
    }
}
