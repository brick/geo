<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\BoundingBox;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\EmptyGeometryException;
use Brick\Geo\Point;

/**
 * Unit tests for class BoundingBox.
 */
class BoundingBoxTest extends AbstractTestCase
{
    public function testGetSouthWestOnEmptyBoundingBox(): void
    {
        $bbox = BoundingBox::new();

        $this->expectException(EmptyGeometryException::class);
        $bbox->getSouthWest();
    }

    public function testGetNorthEastOnEmptyBoundingBox(): void
    {
        $bbox = BoundingBox::new();

        $this->expectException(EmptyGeometryException::class);
        $bbox->getNorthEast();
    }

    public function testSridMix(): void
    {
        $bbox = BoundingBox::new();
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 0));

        $this->expectException(CoordinateSystemException::class);
        $bbox->extendedWithPoint(Point::xy(0, 0, 4326));
    }

    public function testDimensionalityMix(): void
    {
        $bbox = BoundingBox::new();
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 0));

        $this->expectException(CoordinateSystemException::class);
        $bbox->extendedWithPoint(Point::xyz(0, 0, 0));
    }

    public function testExtendedWithPointXy(): void
    {
        $bbox = BoundingBox::new();

        $bbox = $bbox->extendedWithPoint(Point::xy(1, 2, 4326));
        $this->assertPointXyEquals(1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(1, 2, 4326, $bbox->getNorthEast());

        // -x
        $bbox = $bbox->extendedWithPoint(Point::xy(-1, 3, 4326));
        $this->assertPointXyEquals(-1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(1, 3, 4326, $bbox->getNorthEast());

        // noop
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 2, 4326));
        $this->assertPointXyEquals(-1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(1, 3, 4326, $bbox->getNorthEast());

        // +x
        $bbox = $bbox->extendedWithPoint(Point::xy(3, 2, 4326));
        $this->assertPointXyEquals(-1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(3, 3, 4326, $bbox->getNorthEast());

        // -y
        $bbox = $bbox->extendedWithPoint(Point::xy(3, -1, 4326));
        $this->assertPointXyEquals(-1, -1, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(3, 3, 4326, $bbox->getNorthEast());

        // noop
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 0, 4326));
        $this->assertPointXyEquals(-1, -1, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(3, 3, 4326, $bbox->getNorthEast());

        // +y
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 7, 4326));
        $this->assertPointXyEquals(-1, -1, 4326, $bbox->getSouthWest());
        $this->assertPointXyEquals(3, 7, 4326, $bbox->getNorthEast());
    }

    public function testExtendedWithPointXyz(): void
    {
        $bbox = BoundingBox::new();

        $bbox = $bbox->extendedWithPoint(Point::xyz(1, 2, 3, 4326));
        $this->assertPointXyzEquals(1, 2, 3, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(1, 2, 3, 4326, $bbox->getNorthEast());

        // -x, +y
        $bbox = $bbox->extendedWithPoint(Point::xyz(-1, 3, 3, 4326));
        $this->assertPointXyzEquals(-1, 2, 3, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(1, 3, 3, 4326, $bbox->getNorthEast());

        // -z
        $bbox = $bbox->extendedWithPoint(Point::xyz(0, 2, 2, 4326));
        $this->assertPointXyzEquals(-1, 2, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(1, 3, 3, 4326, $bbox->getNorthEast());

        // +x
        $bbox = $bbox->extendedWithPoint(Point::xyz(3, 2, 2.5, 4326));
        $this->assertPointXyzEquals(-1, 2, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(3, 3, 3, 4326, $bbox->getNorthEast());

        // -y
        $bbox = $bbox->extendedWithPoint(Point::xyz(3, -1, 2, 4326));
        $this->assertPointXyzEquals(-1, -1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(3, 3, 3, 4326, $bbox->getNorthEast());

        // +z
        $bbox = $bbox->extendedWithPoint(Point::xyz(0, 0, 4, 4326));
        $this->assertPointXyzEquals(-1, -1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(3, 3, 4, 4326, $bbox->getNorthEast());

        // +y, -z
        $bbox = $bbox->extendedWithPoint(Point::xyz(0, 7, -5, 4326));
        $this->assertPointXyzEquals(-1, -1, -5, 4326, $bbox->getSouthWest());
        $this->assertPointXyzEquals(3, 7, 4, 4326, $bbox->getNorthEast());
    }
}
