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
     */
    public function testRead($wkb, $wkt)
    {
        $reader = new WKBReader();
        $geometry = $reader->read(hex2bin($wkb));
        $this->assertSame($wkt, $geometry->asText());
    }

    /**
     * @return array
     */
    public function providerRead()
    {
        foreach ($this->providerLittleEndianWKB() as list($wkt, $wkb)) {
            yield [$wkb, $wkt];
        }

        foreach ($this->providerBigEndianWKB() as list($wkt, $wkb)) {
            yield [$wkb, $wkt];
        }
    }
}
