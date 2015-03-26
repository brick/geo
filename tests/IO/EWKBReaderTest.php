<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKBReader;
use Brick\Geo\IO\EWKTWriter;

/**
 * Unit tests for class EWKBReader.
 */
class EWKBReaderTest extends EWKBAbstractTest
{
    /**
     * @dataProvider providerRead
     *
     * @param string  $ewkb       The EWKB to read, hex-encoded.
     * @param string  $ewkt       The expected EWKT output.
     * @param boolean $is3D       Whether the geometry has Z coordinates.
     * @param boolean $isMeasured Whether the geometry has M coordinates.
     */
    public function testRead($ewkb, $ewkt, $is3D, $isMeasured)
    {
        $reader = new EWKBReader();
        $writer = new EWKTWriter();

        $geometry = $reader->read(hex2bin($ewkb));
        $this->assertSame($ewkt, $writer->write($geometry));
    }

    /**
     * @return array
     */
    public function providerRead()
    {
        foreach ($this->providerBigEndianEWKB() as list($ewkt, $ewkb, $is3D, $isMeasured)) {
            yield [$ewkb, $ewkt, $is3D, $isMeasured];
        }

        foreach ($this->providerBigEndianEWKB_SRID() as list($ewkt, $ewkb, $is3D, $isMeasured)) {
            yield [$ewkb, $ewkt, $is3D, $isMeasured];
        }

        foreach ($this->providerLittleEndianEWKB() as list ($ewkt, $ewkb, $is3D, $isMeasured)) {
            yield [$ewkb, $ewkt, $is3D, $isMeasured];
        }

        foreach ($this->providerLittleEndianEWKB_SRID() as list ($ewkt, $ewkb, $is3D, $isMeasured)) {
            yield [$ewkb, $ewkt, $is3D, $isMeasured];
        }

        /* WKB being valid EWKB, we test the reader against WKB as well */

        foreach ($this->providerBigEndianWKB() as list($wkt, $wkb, $is3D, $isMeasured)) {
            yield [$wkb, $wkt, $is3D, $isMeasured];
        }

        foreach ($this->providerLittleEndianWKB() as list($wkt, $wkb, $is3D, $isMeasured)) {
            yield [$wkb, $wkt, $is3D, $isMeasured];
        }
    }
}
