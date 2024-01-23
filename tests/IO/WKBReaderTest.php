<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\WKBReader;

/**
 * Unit tests for class WKBReader.
 */
class WKBReaderTest extends WKBAbstractTestCase
{
    /**
     * @dataProvider providerRead
     *
     * @param string $wkb The WKB to read, hex-encoded.
     * @param string $wkt The expected WKT output.
     */
    public function testRead(string $wkb, string $wkt) : void
    {
        $reader = new WKBReader();
        $geometry = $reader->read(hex2bin($wkb), 4326);

        self::assertSame($wkt, $geometry->asText());
        self::assertSame(4326, $geometry->SRID());
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerLittleEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach (self::providerBigEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }
}
