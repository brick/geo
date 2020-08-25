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
     * @param string $ewkt      The EWKT to read.
     * @param string $ewkb      The expected EWKB output, hex-encoded.
     * @param int    $byteOrder The byte order to use.
     *
     * @return void
     */
    public function testWrite(string $ewkt, string $ewkb, int $byteOrder) : void
    {
        $writer = new EWKBWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new EWKTReader();

        $geometry = $reader->read($ewkt);
        $output = $writer->write($geometry);

        self::assertSame($ewkb, bin2hex($output));
    }

    /**
     * @return \Generator
     */
    public function providerWrite() : \Generator
    {
        foreach ($this->providerLittleEndianEWKB() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, EWKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerLittleEndianEWKB_SRID() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, EWKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerBigEndianEWKB() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, EWKBTools::BIG_ENDIAN];
        }

        foreach ($this->providerBigEndianEWKB_SRID() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, EWKBTools::BIG_ENDIAN];
        }
    }

    /**
     * @dataProvider providerWriteEmptyPointThrowsException
     * @expectedException \Brick\Geo\Exception\GeometryIOException
     *
     * @param Point $point
     *
     * @return void
     */
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new EWKBWriter();
        $writer->write($point);
    }

    /**
     * @return array
     */
    public function providerWriteEmptyPointThrowsException() : array
    {
        return [
            [Point::xyEmpty()],
            [Point::xyzEmpty()],
            [Point::xymEmpty()],
            [Point::xyzmEmpty()]
        ];
    }
}
