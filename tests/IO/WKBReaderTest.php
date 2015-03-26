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
     * @param string  $wkb        The WKB to read, hex-encoded.
     * @param string  $wkt        The expected WKT output.
     * @param boolean $is3D       Whether the geometry has Z coordinates.
     * @param boolean $isMeasured Whether the geometry has M coordinates.
     */
    public function testRead($wkb, $wkt, $is3D, $isMeasured)
    {
        $reader = new WKBReader();
        $geometry = $reader->read(hex2bin($wkb), 4326);
        $this->assertSame($wkt, $geometry->asText());
        $this->assertSame(4326, $geometry->SRID());
    }

    /**
     * @return array
     */
    public function providerRead()
    {
        foreach ($this->providerLittleEndianWKB() as list($wkt, $wkb, $is3D, $isMeasured)) {
            yield [$wkb, $wkt, $is3D, $isMeasured];
        }

        foreach ($this->providerBigEndianWKB() as list($wkt, $wkb, $is3D, $isMeasured)) {
            yield [$wkb, $wkt, $is3D, $isMeasured];
        }
    }
}
