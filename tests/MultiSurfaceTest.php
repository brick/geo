<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;

/**
 * Unit tests for class MultiSurface.
 */
class MultiSurfaceTest extends AbstractTestCase
{
    /**
     * @dataProvider providerInvalidFromText
     * @expectedException \Brick\Geo\Exception\UnexpectedGeometryException
     *
     * @param string $wkt A valid WKT, for a non-multisurface geometry.
     */
    public function testInvalidFromText($wkt)
    {
        MultiSurface::fromText($wkt);
    }

    /**
     * @return array
     */
    public function providerInvalidFromText()
    {
        return [
            ['POINT EMPTY'],
            ['LINESTRING EMPTY'],
            ['GEOMETRYCOLLECTION EMPTY'],
            ['MULTILINESTRING EMPTY'],
        ];
    }

    /**
     * @dataProvider providerInvalidFromBinary
     * @expectedException \Brick\Geo\Exception\UnexpectedGeometryException
     *
     * @param string $wkb A valid HEX WKB, for a non-multisurface geometry.
     */
    public function testInvalidFromBinary($wkb)
    {
        MultiSurface::fromBinary(hex2bin($wkb));
    }

    /**
     * @return array
     */
    public function providerInvalidFromBinary()
    {
        return [
            ['000000000200000000'],
            ['000000000300000000'],
            ['010f00000000000000'],
            ['010700000000000000'],
            ['01ed03000000000000'],
        ];
    }

    /**
     * @dataProvider providerArea
     *
     * @param string $multiSurface The WKT of the MultiSurface to test.
     * @param float  $area         The expected area.
     */
    public function testArea($multiSurface, $area)
    {
        $this->requiresGeometryEngine();

        $multiSurface = MultiSurface::fromText($multiSurface);
        $this->skipIfUnsupportedGeometry($multiSurface);

        $actualArea = $multiSurface->area();

        $this->assertInternalType('float', $actualArea);
        $this->assertEquals($area, $actualArea, '', 0.001);
    }

    /**
     * @return array
     */
    public function providerArea()
    {
        return [
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1)))', 32],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)))', 30],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4), (2 2, 2 3, 3 3, 3 2, 2 2)))', 29],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)), ((6 5, 6 9, 11 9, 11 5, 6 5)))', 50],
            ['MULTIPOLYGON Z (((1 1 0, 1 3 0, 4 3 0, 4 5 0, 6 5 0, 6 1 0, 1 1 0)), ((2 4 0, 2 6 0, 4 6 0, 2 4 0)))', 16],
        ];
    }

    /**
     * @dataProvider providerCentroid
     *
     * @param string $multiMultiSurface The WKT of the MultiSurface to test.
     * @param string $centroid          The WKT of the expected centroid.
     */
    public function testCentroid($multiMultiSurface, $centroid)
    {
        $this->requiresGeometryEngine();

        $multiSurface = MultiSurface::fromText($multiMultiSurface);
        $this->skipIfUnsupportedGeometry($multiSurface);
        $this->assertWktEquals($multiSurface->centroid(), $centroid);
    }

    /**
     * @return array
     */
    public function providerCentroid()
    {
        return [
            ['MULTIPOLYGON (((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1)))', 'POINT (1.5 1.5)'],
            ['MULTIPOLYGON (((1 1, 1 3, 3 3, 3 1, 1 1)), ((4 1, 4 3, 6 3, 6 1, 4 1)))', 'POINT (3.5 2)'],
            ['MULTIPOLYGON (((1 1, 1 4, 4 4, 4 1, 1 1), (2 2, 2 3, 3 3, 3 2, 2 2)), ((5 1, 5 4, 8 4, 8 1, 5 1), (6 2, 6 3, 7 3, 7 2, 6 2)))', 'POINT (4.5 2.5)'],
        ];
    }

    /**
     * @dataProvider providerPointOnSurface
     *
     * @param string $multiMultiSurface The WKT of the MultiSurface to test.
     */
    public function testPointOnSurface($multiMultiSurface)
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL() || $this->isMariaDB()) {
            // MySQL and MariaDB do not support ST_PointOnSurface()
            $this->expectException(GeometryEngineException::class);
        }

        $multiSurface = MultiSurface::fromText($multiMultiSurface);
        $this->skipIfUnsupportedGeometry($multiSurface);

        $pointOnSurface = $multiSurface->pointOnSurface();

        $this->assertInstanceOf(Point::class, $pointOnSurface);
        $this->assertTrue($multiSurface->contains($pointOnSurface));
    }

    /**
     * @return array
     */
    public function providerPointOnSurface()
    {
        return [
            ['MULTIPOLYGON (((1 1, 1 3, 4 3, 4 6, 6 6, 6 1, 1 1)))'],
            ['MULTIPOLYGON (((0 0, 0 4, 3 4, 3 3, 4 3, 4 0, 0 0)))'],
            ['MULTIPOLYGON (((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1)))'],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)), ((6 5, 6 9, 11 9, 11 5, 6 5)))'],
        ];
    }
}
