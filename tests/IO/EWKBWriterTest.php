<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKBTools;
use Brick\Geo\IO\EWKBWriter;
use Brick\Geo\IO\EWKTReader;
use Brick\Geo\Point;

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
     * @param integer $byteOrder  The byte order to use.
     */
    public function testWrite($ewkt, $ewkb, $byteOrder)
    {
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
        foreach ($this->providerLittleEndianEWKB() as list($wkt, $ewkb)) {
            yield [$wkt, $ewkb, EWKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerLittleEndianEWKB_SRID() as list($wkt, $ewkb)) {
            yield [$wkt, $ewkb, EWKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerBigEndianEWKB() as list($wkt, $ewkb)) {
            yield [$wkt, $ewkb, EWKBTools::BIG_ENDIAN];
        }

        foreach ($this->providerBigEndianEWKB_SRID() as list($wkt, $ewkb)) {
            yield [$wkt, $ewkb, EWKBTools::BIG_ENDIAN];
        }
    }

    /**
     * @dataProvider providerWriteEmptyPointThrowsException
     * @expectedException \Brick\Geo\Exception\GeometryIOException
     *
     * @param Point $point
     */
    public function testWriteEmptyPointThrowsException(Point $point)
    {
        $writer = new EWKBWriter();
        $writer->write($point);
    }

    /**
     * @return array
     */
    public function providerWriteEmptyPointThrowsException()
    {
        return [
            [Point::xyEmpty()],
            [Point::xyzEmpty()],
            [Point::xymEmpty()],
            [Point::xyzmEmpty()]
        ];
    }
}
