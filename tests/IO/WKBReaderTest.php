<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\WKBReader;

/**
 * Unit tests for class WKBReader.
 */
class WKBReaderTest extends WKBAbstractTest
{
    /**
     * @dataProvider providerRead
     *
     * @param string $wkb The WKB to read, hex-encoded.
     * @param string $wkt The expected WKT output.
     *
     * @return void
     */
    public function testRead(string $wkb, string $wkt) : void
    {
        $reader = new WKBReader();
        $geometry = $reader->read(hex2bin($wkb), 4326);

        self::assertSame($wkt, $geometry->asText());
        self::assertSame(4326, $geometry->SRID());
    }

    /**
     * @return \Generator
     */
    public function providerRead() : \Generator
    {
        foreach ($this->providerLittleEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach ($this->providerBigEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }
}
