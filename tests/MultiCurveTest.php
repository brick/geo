<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\MultiCurve;
use PHPUnit\Framework\Attributes\DataProvider;

use function hex2bin;

/**
 * Unit tests for class MultiCurve.
 */
class MultiCurveTest extends AbstractTestCase
{
    /**
     * @param string $wkt A valid WKT, for a non-multicurve geometry.
     */
    #[DataProvider('providerInvalidFromText')]
    public function testInvalidFromText(string $wkt): void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiCurve::fromText($wkt);
    }

    public static function providerInvalidFromText(): array
    {
        return [
            ['POINT EMPTY'],
            ['LINESTRING EMPTY'],
            ['GEOMETRYCOLLECTION EMPTY'],
            ['MULTIPOLYGON EMPTY'],
        ];
    }

    /**
     * @param string $wkb A valid HEX WKB, for a non-multicurve geometry.
     */
    #[DataProvider('providerInvalidFromBinary')]
    public function testInvalidFromBinary(string $wkb): void
    {
        $this->expectException(UnexpectedGeometryException::class);
        MultiCurve::fromBinary(hex2bin($wkb));
    }

    public static function providerInvalidFromBinary(): array
    {
        return [
            ['000000000200000000'],
            ['000000000300000000'],
            ['010f00000000000000'],
            ['010700000000000000'],
            ['01ee03000000000000'],
        ];
    }
}
