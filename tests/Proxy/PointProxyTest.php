<?php

namespace Brick\Geo\Tests\Proxy;

use Brick\Geo\Point;
use Brick\Geo\Proxy\PointProxy;
use Brick\Geo\Tests\AbstractTestCase;

/**
 * Unit tests for class PointProxy.
 */
class PointProxyTest extends AbstractTestCase
{
    /**
     * @dataProvider providerProxy
     *
     * @param string $data
     * @param bool   $isBinary
     * @param bool   $is3D
     * @param bool   $isMeasured
     * @param array  $coords
     *
     * @return void
     */
    public function testProxy(string $data, bool $isBinary, bool $is3D, bool $isMeasured, array $coords) : void
    {
        if ($isBinary) {
            $data = hex2bin($data);
        }

        $this->castToFloat($coords);

        $x = $coords ? $coords[0] : null;
        $y = $coords ? $coords[1] : null;
        $z = $coords && $is3D ? $coords[2] : null;
        $m = $coords && $isMeasured ? $coords[$is3D ? 3 : 2] : null;

        $spatialDimension = $is3D ? 3 : 2;
        $coordinateDimension = $spatialDimension + ($isMeasured ? 1 : 0);

        foreach ([0, 1] as $srid) {
            $pointProxy = new PointProxy($data, $isBinary, $srid);

            $this->assertInstanceOf(Point::class, $pointProxy);

            $this->assertSame($is3D, $pointProxy->is3D());
            $this->assertSame($isMeasured, $pointProxy->is3D());
            $this->assertSame(! $coords, $pointProxy->isEmpty());

            $this->assertSame($x, $pointProxy->x());
            $this->assertSame($y, $pointProxy->y());
            $this->assertSame($z, $pointProxy->z());
            $this->assertSame($m, $pointProxy->m());

            $this->assertSame('Point', $pointProxy->geometryType());
            $this->assertSame($coords, $pointProxy->toArray());
            $this->assertSame($srid, $pointProxy->SRID());

            $this->assertSame(0, $pointProxy->dimension());
            $this->assertSame($spatialDimension, $pointProxy->spatialDimension());
            $this->assertSame($coordinateDimension, $pointProxy->coordinateDimension());

            $asText = $isBinary ? Point::fromBinary($data)->asText() : $data;

            $this->assertSame($asText, (string) $pointProxy);
            $this->assertSame($asText, $pointProxy->asText());

            if ($coords) {
                $asBinary = $isBinary ? $data : Point::fromText($data)->asBinary();
                $this->assertSame($asBinary, $pointProxy->asBinary());
            }
        }
    }

    /**
     * @return array
     */
    public function providerProxy() : array
    {
        return [
            ['POINT EMPTY', false, false, false, []],
            ['POINT (1 2)', false, false, false, [1, 2]],
            ['00000000013ff00000000000004000000000000000', true, false, false, [1, 2]],
            ['0101000000000000000000f03f0000000000000040', true, false, false, [1, 2]],
        ];
    }

    /**
     * @return void
     */
    public function testLoading() : void
    {
        $pointProxy = new PointProxy('POINT(1 2)', false);

        $this->assertInstanceOf(Point::class, $pointProxy);
        $this->assertFalse($pointProxy->isLoaded());

        $this->assertPointEquals([1, 2], false, false, 0, $pointProxy);
        $this->assertTrue($pointProxy->isLoaded());

        $this->assertInstanceOf(Point::class, $pointProxy->getGeometry());
    }
}
