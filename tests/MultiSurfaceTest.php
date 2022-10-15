<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\MultiSurface;

/**
 * Unit tests for class MultiSurface.
 */
class MultiSurfaceTest extends AbstractTestCase
{
    /**
     * @dataProvider providerInvalidFromText
     *
     * @param string $wkt A valid WKT, for a non-multisurface geometry.
     */
    public function testInvalidFromText(string $wkt) : void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiSurface::fromText($wkt);
    }

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
     */
    public function testInvalidFromBinary(string $wkb) : void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiSurface::fromBinary(hex2bin($wkb));
    }

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
}
