<?php

namespace Brick\Geo\Tests;

use Brick\Geo\LineString;
use Brick\Geo\Polygon;

/**
 * Unit tests for class Polygon.
 */
class PolygonTest extends AbstractTestCase
{
    /**
     * @dataProvider providerEmptyFactoryMethod
     *
     * @param boolean $is3D
     * @param boolean $isMeasured
     * @param integer $srid
     */
    public function testEmptyFactoryMethod($is3D, $isMeasured, $srid)
    {
        $polygon = Polygon::polygonEmpty($is3D, $isMeasured, $srid);

        $this->assertTrue($polygon->isEmpty());
        $this->assertSame($is3D, $polygon->is3D());
        $this->assertSame($isMeasured, $polygon->isMeasured());
        $this->assertSame($srid, $polygon->SRID());

        $expectedExteriorRing = LineString::lineStringEmpty($is3D, $isMeasured, $srid);
        $this->assertWktEquals($polygon->exteriorRing(), $expectedExteriorRing->asText(), $srid);
    }

    /**
     * @return array
     */
    public function providerEmptyFactoryMethod()
    {
        return [
            [false, false, 0],
            [true ,false, 0],
            [false, true, 0],
            [true, true, 0],
            [false, false, 4326],
            [true ,false, 4326],
            [false, true, 4326],
            [true, true, 4326]
        ];
    }
}
