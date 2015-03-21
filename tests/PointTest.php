<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Geometry;
use Brick\Geo\LinearRing;
use Brick\Geo\Point;
use Brick\Geo\Polygon;

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

    public function testGeometryType()
    {
        $this->assertSame('Point', Point::xy(0, 0)->geometryType());
    }

    public function testDimension()
    {
        $this->assertSame(0, Point::xy(0, 0)->dimension());
    }

    /**
     * @dataProvider providerCoordinateDimension
     *
     * @param Point $point
     * @param int   $coordinateDimension
     */
    public function testCoordinateDimension(Point $point, $coordinateDimension)
    {
        $this->assertSame($coordinateDimension, $point->coordinateDimension());
    }

    /**
     * @return array
     */
    public function providerCoordinateDimension()
    {
        return [
            [Point::xy(1.2, 3.4), 2],
            [Point::xyz(1.2, 3.4, 5.6), 3],
            [Point::xym(1.2, 3.4, 7.8), 3],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), 4],
        ];
    }

    public function testIsEmpty()
    {
        $this->assertFalse(Point::xy(0, 0)->isEmpty());
    }

    /**
     * @dataProvider providerIs3D
     *
     * @param Point   $point The point to test.
     * @param boolean $is3D  Whether the point is 3D.
     */
    public function testIs3D(Point $point, $is3D)
    {
        $this->assertSame($is3D, $point->is3D());
    }

    /**
     * @return array
     */
    public function providerIs3D()
    {
        return [
            [Point::xy(1.2, 3.4), false],
            [Point::xy(1.2, 3.4, 4326), false],
            [Point::xyz(1.2, 3.4, 5.6), true],
            [Point::xyz(1.2, 3.4, 5.6, 4326), true],
            [Point::xym(1.2, 3.4, 7.8), false],
            [Point::xym(1.2, 3.4, 7.8, 4326), false],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), true],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8, 4326), true]
        ];
    }

    /**
     * @dataProvider providerIsMeasured
     *
     * @param Point   $point      The point to test.
     * @param boolean $isMeasured Whether the point is measured.
     */
    public function testIsMeasured(Point $point, $isMeasured)
    {
        $this->assertSame($isMeasured, $point->isMeasured());
    }

    /**
     * @return array
     */
    public function providerIsMeasured()
    {
        return [
            [Point::xy(1.2, 3.4), false],
            [Point::xy(1.2, 3.4, 4326), false],
            [Point::xyz(1.2, 3.4, 5.6), false],
            [Point::xyz(1.2, 3.4, 5.6, 4326), false],
            [Point::xym(1.2, 3.4, 7.8), true],
            [Point::xym(1.2, 3.4, 7.8, 4326), true],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8), true],
            [Point::xyzm(1.2, 3.4, 5.6, 7.8, 4326), true]
        ];
    }

    /**
     * @dataProvider providerToArray
     *
     * @param Point $point
     * @param array $array
     */
    public function testToArray(Point $point, array $array)
    {
        $this->assertSame($array, $point->toArray());
    }

    /**
     * @return array
     */
    public function providerToArray()
    {
        return [
            [Point::xy('1.2', '3.4'), [1.2, 3.4]],
            [Point::xy('1.2', '3.4', 4326), [1.2, 3.4]],
            [Point::xyz('1.2', '3.4', '5.6'), [1.2, 3.4, 5.6]],
            [Point::xyz('1.2', '3.4', '5.6', 4326), [1.2, 3.4, 5.6]],
            [Point::xym('1.2', '3.4', '5.6'), [1.2, 3.4, 5.6]],
            [Point::xym('1.2', '3.4', '5.6', 4326), [1.2, 3.4, 5.6]],
            [Point::xyzm('1.2', '3.4', '5.6', '7.8'), [1.2, 3.4, 5.6, 7.8]],
            [Point::xyzm('1.2', '3.4', '5.6', '7.8', 4326), [1.2, 3.4, 5.6, 7.8]]
        ];
    }

    /**
     * @dataProvider providerEquals
     *
     * @param string  $geometry The WKT representation of the Geometry to compare to.
     * @param boolean $isEqual  Whether the geometries are equal.
     */
    public function testEquals($geometry, $isEqual)
    {
        if (preg_match('/^GEOMETRYCOLLECTION/', $geometry)) {
            $this->skipPostgreSQL('This comparison is not available on PostGIS');
        }

        $point = Point::xy(1, 2);
        $geometry = Geometry::fromText($geometry);

        $this->assertSame($isEqual, $point->equals($geometry));
        $this->assertSame($isEqual, $geometry->equals($point));
    }

    /**
     * @return array
     */
    public function providerEquals()
    {
        return [
            ['POINT(1 2)', true],
            ['POINT(3 2)', false],
            ['POINT(1 3)', false],
            ['POINT(2 3)', false],
            ['LINESTRING(1 2, 1 3)', false],
            ['GEOMETRYCOLLECTION(POINT(1 2))', true],
            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 2))', true],
            ['GEOMETRYCOLLECTION(POINT(1 2), POINT(1 3))', false]
        ];
    }

    /**
     * @dataProvider providerEnvelope
     *
     * @param string $point
     * @param string $envelope
     */
    public function testEnvelope($point, $envelope)
    {
        $point = Point::fromText($point);

        $this->assertWktEquals($point->envelope(), $envelope);
        $this->assertWktEquals($point->withSRID(4326)->envelope(), $envelope, 4326);
    }

    /**
     * @return array
     */
    public function providerEnvelope()
    {
        return [
            ['POINT (1 2)', 'POINT (1 2)'],
            ['POINT Z (2 3 4)', 'POINT (2 3)'],
            ['POINT M (3 4 5)', 'POINT (3 4)'],
            ['POINT ZM (4 5 6 7)', 'POINT (4 5)']
        ];
    }

    /**
     * @dataProvider providerIsSimple
     *
     * @param string $point
     */
    public function testIsSimple($point)
    {
        $point = Point::fromText($point);

        $this->assertTrue($point->isSimple());
        $this->assertTrue($point->withSRID(4326)->isSimple());
    }

    /**
     * @return array
     */
    public function providerIsSimple()
    {
        return [
            ['POINT (1 2)'],
            ['POINT Z (2 3 4)'],
            ['POINT M (3 4 5)'],
            ['POINT ZM (4 5 6 7)']
        ];
    }

    /**
     * @dataProvider providerBoundary
     *
     * @param string $point
     */
    public function testBoundary($point)
    {
        $point = Point::fromText($point);

        $this->assertWktEquals($point->boundary(), 'GEOMETRYCOLLECTION EMPTY');
        $this->assertWktEquals($point->withSRID(4326)->boundary(), 'GEOMETRYCOLLECTION EMPTY', 4326);
    }

    /**
     * @return array
     */
    public function providerBoundary()
    {
        return [
            ['POINT (1 2)'],
            ['POINT Z (2 3 4)'],
            ['POINT M (3 4 5)'],
            ['POINT ZM (4 5 6 7)']
        ];
    }
}
