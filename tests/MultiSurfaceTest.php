<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;

/**
 * Unit tests for class MultiSurface.
 */
class MultiSurfaceTest extends AbstractTestCase
{
    /**
     * @dataProvider providerInvalidFromText
     *
     * @param string $wkt A valid WKT, for a non-multisurface geometry.
     *
     * @return void
     */
    public function testInvalidFromText(string $wkt) : void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiSurface::fromText($wkt);
    }

    /**
     * @return array
     */
    public function providerInvalidFromText() : array
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
     *
     * @param string $wkb A valid HEX WKB, for a non-multisurface geometry.
     *
     * @return void
     */
    public function testInvalidFromBinary(string $wkb) : void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiSurface::fromBinary(hex2bin($wkb));
    }

    /**
     * @return array
     */
    public function providerInvalidFromBinary() : array
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
     *
     * @return void
     */
    public function testArea(string $multiSurface, float $area) : void
    {
        $this->requiresGeometryEngine();

        $multiSurface = MultiSurface::fromText($multiSurface);
        $this->skipIfUnsupportedGeometry($multiSurface);

        $actualArea = $multiSurface->area();

        self::assertIsFloat($actualArea);
        self::assertEqualsWithDelta($area, $actualArea, 0.001);
    }

    /**
     * @return array
     */
    public function providerArea() : array
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
     *
     * @return void
     */
    public function testCentroid(string $multiMultiSurface, string $centroid) : void
    {
        $this->requiresGeometryEngine();

        $multiSurface = MultiSurface::fromText($multiMultiSurface);
        $this->skipIfUnsupportedGeometry($multiSurface);
        $this->assertWktEquals($multiSurface->centroid(), $centroid);
    }

    /**
     * @return array
     */
    public function providerCentroid() : array
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
     *
     * @return void
     */
    public function testPointOnSurface(string $multiMultiSurface) : void
    {
        $this->requiresGeometryEngine();

        if ($this->isMySQL() || $this->isMariaDB('< 10.1.2')) {
            // MySQL and older MariaDB do not support ST_PointOnSurface()
            $this->expectException(GeometryEngineException::class);
        }

        $multiSurface = MultiSurface::fromText($multiMultiSurface);
        $this->skipIfUnsupportedGeometry($multiSurface);

        $pointOnSurface = $multiSurface->pointOnSurface();

        self::assertInstanceOf(Point::class, $pointOnSurface);
        self::assertTrue($multiSurface->contains($pointOnSurface));
    }

    /**
     * @return array
     */
    public function providerPointOnSurface() : array
    {
        return [
            ['MULTIPOLYGON (((1 1, 1 3, 4 3, 4 6, 6 6, 6 1, 1 1)))'],
            ['MULTIPOLYGON (((0 0, 0 4, 3 4, 3 3, 4 3, 4 0, 0 0)))'],
            ['MULTIPOLYGON (((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1)))'],
            ['MULTIPOLYGON (((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4)), ((6 5, 6 9, 11 9, 11 5, 6 5)))'],
        ];
    }
}
