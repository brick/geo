<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\WkbReader;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class WkbReader.
 */
class WkbReaderTest extends WkbAbstractTestCase
{
    /**
     * @param string $wkb The WKB to read, hex-encoded.
     * @param string $wkt The expected WKT output.
     */
    #[DataProvider('providerRead')]
    public function testRead(string $wkb, string $wkt) : void
    {
        $reader = new WkbReader();
        $geometry = $reader->read(hex2bin($wkb), 4326);

        self::assertSame($wkt, $geometry->asText());
        self::assertSame(4326, $geometry->SRID());
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerLittleEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach (self::providerBigEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }
}
