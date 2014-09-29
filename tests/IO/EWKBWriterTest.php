<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKBTools;
use Brick\Geo\IO\EWKBWriter;
use Brick\Geo\IO\EWKTReader;

/**
 * Unit tests for class EWKBWriter.
 */
class EWKBWriterTest extends EWKBAbstractTest
{
    /**
     * @dataProvider providerWrite
     *
     * @param string  $ewkt       The EWKT to read.
     * @param string  $ewkb       The expected EWKB output, hex-encoded.
     * @param boolean $is3D       Whether the geometry has Z coordinates.
     * @param boolean $isMeasured Whether the geometry has M coordinates.
     * @param integer $byteOrder  The byte order to use.
     */
    public function testWrite($ewkt, $ewkb, $is3D, $isMeasured, $byteOrder)
    {
        $this->is3D($is3D);
        $this->isMeasured($isMeasured);

        $writer = new EWKBWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new EWKTReader();

        $geometry = $reader->read($ewkt);
        $output = $writer->write($geometry);

        $this->assertSame($ewkb, bin2hex($output));
    }

    /**
     * @return \Generator
     */
    public function providerWrite()
    {
        foreach ($this->providerLittleEndianEWKB() as list($wkt, $ewkb, $is3D, $isMeasured)) {
            yield [$wkt, $ewkb, $is3D, $isMeasured, EWKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerLittleEndianEWKB_SRID() as list($wkt, $ewkb, $is3D, $isMeasured)) {
            yield [$wkt, $ewkb, $is3D, $isMeasured, EWKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerBigEndianEWKB() as list($wkt, $ewkb, $is3D, $isMeasured)) {
            yield [$wkt, $ewkb, $is3D, $isMeasured, EWKBTools::BIG_ENDIAN];
        }

        foreach ($this->providerBigEndianEWKB_SRID() as list($wkt, $ewkb, $is3D, $isMeasured)) {
            yield [$wkt, $ewkb, $is3D, $isMeasured, EWKBTools::BIG_ENDIAN];
        }
    }
}
