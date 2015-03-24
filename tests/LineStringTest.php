<?php

namespace Brick\Geo\Tests;

use Brick\Geo\LineString;

/**
 * Unit tests for class LineString.
 */
class LineStringTest extends AbstractTestCase
{
    public function testStartPoint()
    {
        $lineString = LineString::fromText('LINESTRING Z (1 2 3, 4 5 6, 7 8 9)', 4326);
        $this->assertWktEquals($lineString->startPoint(), 'POINT Z (1 2 3)', 4326);
    }

    public function testStartPointEmpty()
    {
        $lineString = LineString::fromText('LINESTRING EMPTY');
        $this->assertNull($lineString->startPoint());
    }

    public function testEndPoint()
    {
        $lineString = LineString::fromText('LINESTRING Z (1 2 3, 4 5 6, 7 8 9)', 4326);
        $this->assertWktEquals($lineString->endPoint(), 'POINT Z (7 8 9)', 4326);
    }

    public function testEndPointEmpty()
    {
        $lineString = LineString::fromText('LINESTRING EMPTY');
        $this->assertNull($lineString->endPoint());
    }
}
