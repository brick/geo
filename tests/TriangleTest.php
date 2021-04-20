<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\LineString;
use Brick\Geo\Triangle;

/**
 * Unit tests for class Triangle.
 */
class TriangleTest extends AbstractTestCase
{
    public function testCreate() : void
    {
        $ring = LineString::fromText('LINESTRING (1 1, 1 2, 2 2, 1 1)');
        $triangle = Triangle::of($ring);
        $this->assertWktEquals($triangle, 'TRIANGLE ((1 1, 1 2, 2 2, 1 1))');
    }

    public function testCreateWithInvalidNumberOfPoints() : void
    {
        $ring = LineString::fromText('LINESTRING (1 1, 1 2, 2 2, 2 1, 1 1)');

        $this->expectException(InvalidGeometryException::class);
        Triangle::of($ring);
    }

    public function testCreateWithInteriorRings() : void
    {
        $exteriorRing = LineString::fromText('LINESTRING (0 0, 0 3, 3 3, 0 0)');
        $interiorRing = LineString::fromText('LINESTRING (1 1, 1 2, 2 2, 1 1)');

        $this->expectException(InvalidGeometryException::class);
        Triangle::of($exteriorRing, $interiorRing);
    }
}
