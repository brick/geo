<?php

namespace Brick\Geo\Tests;

use Brick\Geo\LineString;

/**
 * Unit tests for class LineString.
 */
class LineStringTest extends AbstractTestCase
{
    /**
     * @dataProvider providerNumPoints
     *
     * @param string  $lineString
     * @param integer $numPoints
     */
    public function testNumPoints($lineString, $numPoints)
    {
        $lineString = LineString::fromText($lineString);
        $this->assertSame($numPoints, $lineString->numPoints());
    }

    /**
     * @return array
     */
    public function providerNumPoints()
    {
        return [
            ['LINESTRING EMPTY', 0],
            ['LINESTRING Z EMPTY', 0],
            ['LINESTRING M EMPTY', 0],
            ['LINESTRING ZM EMPTY', 0],
            ['LINESTRING (1 2, 3 4, 5 6, 7 8)', 4],
            ['LINESTRING Z (1 2 3, 4 5 6)', 2],
            ['LINESTRING M (1 2 3, 4 5 6, 7 8 9)', 3],
            ['LINESTRING ZM (1 2 3 4, 5 6 7 8)', 2]
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
            $ls = LineString::fromText($lineString, $srid);
            $this->assertWktEquals($ls->pointN($n), $pointN, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerPointN()
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

    /**
     * @dataProvider providerInvalidPointNThrowsException
     * @expectedException \Brick\Geo\Exception\NoSuchGeometryException
     *
     * @param string  $lineString
     * @param integer $n
     */
    public function testInvalidPointNThrowsException($lineString, $n)
    {
        LineString::fromText($lineString)->pointN($n);
    }

    /**
     * @return array
     */
    public function providerInvalidPointNThrowsException()
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
    public function testInterfaces()
    {
        $lineString = LineString::fromText('LINESTRING (1 2, 3 4, 5 6)');

        $this->assertInstanceOf(\Countable::class, $lineString);
        $this->assertSame(3, count($lineString));

        $this->assertInstanceOf(\Traversable::class, $lineString);
        $this->assertSame([
            $lineString->pointN(1),
            $lineString->pointN(2),
            $lineString->pointN(3)
        ], iterator_to_array($lineString));
    }
}
