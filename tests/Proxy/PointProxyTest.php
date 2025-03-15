<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Proxy;

use Brick\Geo\Point;
use Brick\Geo\Proxy\PointProxy;
use Brick\Geo\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class PointProxy.
 */
class PointProxyTest extends AbstractTestCase
{
    #[DataProvider('providerProxy')]
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

            self::assertInstanceOf(Point::class, $pointProxy);

            self::assertSame($is3D, $pointProxy->is3D());
            self::assertSame($isMeasured, $pointProxy->is3D());
            self::assertSame(! $coords, $pointProxy->isEmpty());

            self::assertSame($x, $pointProxy->x());
            self::assertSame($y, $pointProxy->y());
            self::assertSame($z, $pointProxy->z());
            self::assertSame($m, $pointProxy->m());

            self::assertSame('Point', $pointProxy->geometryType());
            self::assertSame($coords, $pointProxy->toArray());
            self::assertSame($srid, $pointProxy->srid());

            self::assertSame(0, $pointProxy->dimension());
            self::assertSame($spatialDimension, $pointProxy->spatialDimension());
            self::assertSame($coordinateDimension, $pointProxy->coordinateDimension());

            $asText = $isBinary ? Point::fromBinary($data)->asText() : $data;

            self::assertSame($asText, (string) $pointProxy);
            self::assertSame($asText, $pointProxy->asText());

            if ($coords) {
                $asBinary = $isBinary ? $data : Point::fromText($data)->asBinary();
                self::assertSame($asBinary, $pointProxy->asBinary());
            }
        }
    }

    public static function providerProxy() : array
    {
        return [
            ['POINT EMPTY', false, false, false, []],
            ['POINT (1 2)', false, false, false, [1, 2]],
            ['00000000013ff00000000000004000000000000000', true, false, false, [1, 2]],
            ['0101000000000000000000f03f0000000000000040', true, false, false, [1, 2]],
        ];
    }

    public function testLoading() : void
    {
        $pointProxy = new PointProxy('POINT(1 2)', false);

        self::assertInstanceOf(Point::class, $pointProxy);
        self::assertFalse($pointProxy->isLoaded());

        $this->assertPointEquals([1, 2], false, false, 0, $pointProxy);
        self::assertTrue($pointProxy->isLoaded());

        self::assertInstanceOf(Point::class, $pointProxy->getGeometry());
    }
}
