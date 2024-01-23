<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\IO\EWKBWriter;
use Brick\Geo\IO\EWKTReader;
use Brick\Geo\IO\WKBByteOrder;
use Brick\Geo\Point;

/**
 * Unit tests for class EWKBWriter.
 */
class EWKBWriterTest extends EWKBAbstractTestCase
{
    /**
     * @dataProvider providerWrite
     *
     * @param string       $ewkt      The EWKT to read.
     * @param string       $ewkb      The expected EWKB output, hex-encoded.
     * @param WKBByteOrder $byteOrder The byte order to use.
     */
    public function testWrite(string $ewkt, string $ewkb, WKBByteOrder $byteOrder) : void
    {
        $writer = new EWKBWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new EWKTReader();

        $geometry = $reader->read($ewkt);
        $output = $writer->write($geometry);

        self::assertSame($ewkb, bin2hex($output));
    }

    public static function providerWrite() : \Generator
    {
        foreach (self::providerLittleEndianEWKB() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WKBByteOrder::LITTLE_ENDIAN];
        }

        foreach (self::providerLittleEndianEWKB_SRID() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WKBByteOrder::LITTLE_ENDIAN];
        }

        foreach (self::providerBigEndianEWKB() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WKBByteOrder::BIG_ENDIAN];
        }

        foreach (self::providerBigEndianEWKB_SRID() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WKBByteOrder::BIG_ENDIAN];
        }
    }

    /**
     * @dataProvider providerWriteEmptyPointThrowsException
     */
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new EWKBWriter();

        $this->expectException(GeometryIOException::class);
        $writer->write($point);
    }

    public static function providerWriteEmptyPointThrowsException() : array
    {
        return [
            [Point::xyEmpty()],
            [Point::xyzEmpty()],
            [Point::xymEmpty()],
            [Point::xyzmEmpty()]
        ];
    }
}
