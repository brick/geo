<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Point;
use Brick\Geo\Surface;
use Brick\Geo\Exception\GeometryEngineException;

/**
 * Unit tests for class Surface.
 */
class SurfaceTest extends AbstractTestCase
{
    /**
     * @dataProvider providerArea
     *
     * @param string $surface The WKT of the Surface to test.
     * @param float  $area    The expected area.
     */
    public function testArea(string $surface, float $area) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $surface = Surface::fromText($surface);
        $this->skipIfUnsupportedGeometry($surface);

        $actualArea = $geometryEngine->area($surface);

        self::assertIsFloat($actualArea);
        self::assertEqualsWithDelta($area, $actualArea, 0.001);
    }

    public function providerArea() : array
    {
        return [
            ['POLYGON ((1 1, 1 9, 9 1, 1 1))', 32],
            ['POLYGON ((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4))', 30],
            ['POLYGON ((1 1, 1 9, 9 1, 1 1), (2 4, 2 5, 4 5, 4 4, 2 4), (2 2, 2 3, 3 3, 3 2, 2 2))', 29],

            ['POLYGON ((1 3, 3 5, 4 7, 7 3, 1 3))', 11],
            ['CURVEPOLYGON ((1 3, 3 5, 4 7, 7 3, 1 3))', 11],
            ['CURVEPOLYGON (CIRCULARSTRING (1 3, 3 5, 4 7, 7 3, 1 3))', 24.951],
        ];
    }

    /**
     * @dataProvider providerCentroid
     *
     * @param string $surface  The WKT of the Surface to test.
     * @param string $centroid The WKT of the expected centroid.
     */
    public function testCentroid(string $surface, string $centroid) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        $surface = Surface::fromText($surface);
        $this->skipIfUnsupportedGeometry($surface);
        $this->assertWktEquals($geometryEngine->centroid($surface), $centroid);
    }

    public function providerCentroid() : array
    {
        return [
            ['POLYGON ((1 2, 1 3, 2 3, 2 4, 3 4, 3 3, 4 3, 4 2, 3 2, 3 1, 2 1, 2 2, 1 2))', 'POINT (2.5 2.5)'],
            ['POLYGON ((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))', 'POINT (1.5 1.5)'],

            // Note: centroid() on CurvePolygon is not currently supported by any geometry engine.
        ];
    }

    /**
     * @dataProvider providerPointOnSurface
     *
     * @param string $surface The WKT of the Surface to test.
     */
    public function testPointOnSurface(string $surface) : void
    {
        $geometryEngine = $this->getGeometryEngine();

        if ($this->isMySQL() || $this->isMariaDB('< 10.1.2')) {
            // MySQL and older MariaDB do not support ST_PointOnSurface()
            $this->expectException(GeometryEngineException::class);
        }

        $surface = Surface::fromText($surface);
        $this->skipIfUnsupportedGeometry($surface);

        $pointOnSurface = $geometryEngine->pointOnSurface($surface);

        self::assertInstanceOf(Point::class, $pointOnSurface);
        self::assertTrue($geometryEngine->contains($surface, $pointOnSurface));
    }

    public function providerPointOnSurface() : array
    {
        return [
            ['POLYGON ((1 1, 1 3, 4 3, 4 6, 6 6, 6 1, 1 1))'],
            ['POLYGON ((0 0, 0 4, 3 4, 3 3, 4 3, 4 0, 0 0))'],
            ['POLYGON ((0 0, 0 3, 3 3, 3 0, 0 0), (1 1, 1 2, 2 2, 2 1, 1 1))'],

            // Note: pointOnSurface() on CurvePolygon is not currently supported by any geometry engine.
        ];
    }
}
