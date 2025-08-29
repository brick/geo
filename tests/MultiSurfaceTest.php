<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\MultiSurface;
use PHPUnit\Framework\Attributes\DataProvider;

use function hex2bin;

/**
 * Unit tests for class MultiSurface.
 */
class MultiSurfaceTest extends AbstractTestCase
{
    /**
     * @param string $wkt A valid WKT, for a non-multisurface geometry.
     */
    #[DataProvider('providerInvalidFromText')]
    public function testInvalidFromText(string $wkt): void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiSurface::fromText($wkt);
    }

    public static function providerInvalidFromText(): array
    {
        return [
            ['POINT EMPTY'],
            ['LINESTRING EMPTY'],
            ['GEOMETRYCOLLECTION EMPTY'],
            ['MULTILINESTRING EMPTY'],
        ];
    }

    /**
     * @param string $wkb A valid HEX WKB, for a non-multisurface geometry.
     */
    #[DataProvider('providerInvalidFromBinary')]
    public function testInvalidFromBinary(string $wkb): void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiSurface::fromBinary(hex2bin($wkb));
    }

    public static function providerInvalidFromBinary(): array
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
