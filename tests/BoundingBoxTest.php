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
        $bbox = new BoundingBox();

        $this->expectException(EmptyGeometryException::class);
        $bbox->getSouthWest();
    }

    public function testGetNorthEastOnEmptyBoundingBox(): void
    {
        $bbox = new BoundingBox();

        $this->expectException(EmptyGeometryException::class);
        $bbox->getNorthEast();
    }

    public function testSRIDMix(): void
    {
        $bbox = new BoundingBox();
        $bbox->extendedWithPoint(Point::xy(0, 0));

        $this->expectException(CoordinateSystemException::class);
        $bbox->extendedWithPoint(Point::xy(0, 0, 4326));
    }

    public function testExtendedWithPoint(): void
    {
        $bbox = new BoundingBox();

        $bbox = $bbox->extendedWithPoint(Point::xy(1, 2, 4326));
        $this->assertPointXYEquals(1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(1, 2, 4326, $bbox->getNorthEast());

        // -x
        $bbox = $bbox->extendedWithPoint(Point::xy(-1, 3, 4326));
        $this->assertPointXYEquals(-1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(1, 3, 4326, $bbox->getNorthEast());

        // noop
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 2, 4326));
        $this->assertPointXYEquals(-1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(1, 3, 4326, $bbox->getNorthEast());

        // +x
        $bbox = $bbox->extendedWithPoint(Point::xy(3, 2, 4326));
        $this->assertPointXYEquals(-1, 2, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(3, 3, 4326, $bbox->getNorthEast());

        // -y
        $bbox = $bbox->extendedWithPoint(Point::xy(3, -1, 4326));
        $this->assertPointXYEquals(-1, -1, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(3, 3, 4326, $bbox->getNorthEast());

        // noop
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 0, 4326));
        $this->assertPointXYEquals(-1, -1, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(3, 3, 4326, $bbox->getNorthEast());

        // +y
        $bbox = $bbox->extendedWithPoint(Point::xy(0, 7, 4326));
        $this->assertPointXYEquals(-1, -1, 4326, $bbox->getSouthWest());
        $this->assertPointXYEquals(3, 7, 4326, $bbox->getNorthEast());
    }
}
