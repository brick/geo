<?php

namespace Brick\Geo\Tests;

use Brick\Geo\CircularString;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Point;

/**
 * Unit tests for class CircularString.
 */
class CircularStringTest extends AbstractTestCase
{
    /**
     * @dataProvider providerCreate
     *
     * @param string[] $pointsWKT        The WKT of the Points that compose the CircularString.
     * @param boolean  $is3D              Whether the points have Z coordinates.
     * @param boolean  $isMeasured        Whether the points have M coordinates.
     * @param string   $circularStringWKT The WKT of the expected CircularString.
     */
    public function testCreate(array $pointsWKT, $is3D, $isMeasured, $circularStringWKT)
    {
        foreach ([0, 1] as $srid) {
            $instantiatePoint = function ($point) use ($srid) {
                return Point::fromText($point, $srid);
            };

            $cs = CoordinateSystem::create($is3D, $isMeasured, $srid);
            $circularString = CircularString::create(array_map($instantiatePoint, $pointsWKT), $cs);
            $this->assertWktEquals($circularString, $circularStringWKT, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerCreate()
    {
        return [
            [['POINT (1 1)', 'POINT (2 2)', 'POINT (3 3)'], false, false, 'CIRCULARSTRING (1 1, 2 2, 3 3)'],
            [['POINT Z (1 2 3)', 'POINT Z (4 5 6)', 'POINT Z (7 8 9)'], true, false, 'CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)'],
            [['POINT M (1 2 3)', 'POINT M (4 5 6)', 'POINT M (7 8 9)'], false, true, 'CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)'],
            [['POINT ZM (1 2 3 4)', 'POINT ZM (2 3 4 5)', 'POINT ZM (3 4 5 6)'], true, true, 'CIRCULARSTRING ZM (1 2 3 4, 2 3 4 5, 3 4 5 6)'],
        ];
    }

    /**
     * @dataProvider providerCreateInvalidCircularString
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string $circularString The WKT of an invalid CircularString.
     */
    public function testCreateInvalidCircularString($circularString)
    {
        CircularString::fromText($circularString);
    }

    /**
     * @return array
     */
    public function providerCreateInvalidCircularString()
    {
        return [
            ['CIRCULARSTRING (1 1)'],
            ['CIRCULARSTRING (1 1, 2 2)'],
            ['CIRCULARSTRING (1 1, 2 2, 3 3, 4 4)'],
            ['CIRCULARSTRING (1 1, 2 2, 3 3, 4 4, 5 5, 6 6'],
        ];
    }

    /**
     * @dataProvider providerStartPointEndPoint
     *
     * @param string $circularString
     * @param string $startPoint
     * @param string $endPoint
     */
    public function testStartPointEndPoint($circularString, $startPoint, $endPoint)
    {
        foreach ([0, 1] as $srid) {
            $cs = CircularString::fromText($circularString, $srid);
            $this->assertWktEquals($cs->startPoint(), $startPoint, $srid);
            $this->assertWktEquals($cs->endPoint(), $endPoint, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerStartPointEndPoint()
    {
        return [
            ['CIRCULARSTRING (1 2, 3 4, 5 6, 7 8, 9 0)', 'POINT (1 2)', 'POINT (9 0)'],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 8 9)', 'POINT Z (1 2 3)', 'POINT Z (7 8 9)'],
            ['CIRCULARSTRING M (1 2 3, 4 5 6, 7 8 9)', 'POINT M (1 2 3)', 'POINT M (7 8 9)'],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5)', 'POINT ZM (1 2 3 4)', 'POINT ZM (2 3 4 5)']
        ];
    }

    /**
     * @dataProvider providerEmptyCircularString
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string $circularString The WKT of an empty CircularString.
     */
    public function testStartPointOfEmptyCircularString($circularString)
    {
        CircularString::fromText($circularString)->startPoint();
    }

    /**
     * @dataProvider providerEmptyCircularString
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string $circularString The WKT of an empty CircularString.
     */
    public function testEndPointOfEmptyCircularString($circularString)
    {
        CircularString::fromText($circularString)->endPoint();
    }

    /**
     * @return array
     */
    public function providerEmptyCircularString()
    {
        return [
            ['CIRCULARSTRING EMPTY'],
            ['CIRCULARSTRING Z EMPTY'],
            ['CIRCULARSTRING M EMPTY'],
            ['CIRCULARSTRING ZM EMPTY'],
        ];
    }

    /**
     * @dataProvider providerNumPoints
     *
     * @param string  $lineString
     * @param integer $numPoints
     */
    public function testNumPoints($lineString, $numPoints)
    {
        $lineString = CircularString::fromText($lineString);
        $this->assertSame($numPoints, $lineString->numPoints());
    }

    /**
     * @return array
     */
    public function providerNumPoints()
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

    /**
     * @dataProvider providerPointN
     *
     * @param string  $lineString
     * @param integer $n
     * @param string  $pointN
     */
    public function testPointN($lineString, $n, $pointN)
    {
        foreach ([0, 1] as $srid) {
            $ls = CircularString::fromText($lineString, $srid);
            $this->assertWktEquals($ls->pointN($n), $pointN, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerPointN()
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

    /**
     * @dataProvider providerInvalidPointNThrowsException
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string  $lineString
     * @param integer $n
     */
    public function testInvalidPointNThrowsException($lineString, $n)
    {
        CircularString::fromText($lineString)->pointN($n);
    }

    /**
     * @return array
     */
    public function providerInvalidPointNThrowsException()
    {
        return [
            ['CIRCULARSTRING (1 2, 3 4, 5 2)', 0],
            ['CIRCULARSTRING (1 2, 3 4, 5 2)', 4],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 2 9)', 0],
            ['CIRCULARSTRING Z (1 2 3, 4 5 6, 7 2 9)', 4],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5, 3 4 5 6)', 0],
            ['CIRCULARSTRING ZM (1 2 3 4, 5 6 7 8, 2 3 4 5, 3 4 5 6)', 5],
        ];
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces()
    {
        $circularString = CircularString::fromText('CIRCULARSTRING (1 2, 3 4, 5 6)');

        $this->assertInstanceOf(\Countable::class, $circularString);
        $this->assertSame(3, count($circularString));

        $this->assertInstanceOf(\Traversable::class, $circularString);
        $this->assertSame([
            $circularString->pointN(1),
            $circularString->pointN(2),
            $circularString->pointN(3)
        ], iterator_to_array($circularString));
    }
}
