<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Io\EwkbWriter;
use Brick\Geo\Io\EwktReader;
use Brick\Geo\Io\Internal\WkbByteOrder;
use Brick\Geo\Point;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class EwkbWriter.
 */
class EwkbWriterTest extends EwkbAbstractTestCase
{
    /**
     * @param string       $ewkt      The EWKT to read.
     * @param string       $ewkb      The expected EWKB output, hex-encoded.
     * @param WkbByteOrder $byteOrder The byte order to use.
     */
    #[DataProvider('providerWrite')]
    public function testWrite(string $ewkt, string $ewkb, WkbByteOrder $byteOrder) : void
    {
        $writer = new EwkbWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new EwktReader();

        $geometry = $reader->read($ewkt);
        $output = $writer->write($geometry);

        self::assertSame($ewkb, bin2hex($output));
    }

    public static function providerWrite() : \Generator
    {
        foreach (self::providerLittleEndianEwkb() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::LITTLE_ENDIAN];
        }

        foreach (self::providerLittleEndianEwkbWithSrid() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::LITTLE_ENDIAN];
        }

        foreach (self::providerBigEndianEwkb() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::BIG_ENDIAN];
        }

        foreach (self::providerBigEndianEwkbWithSrid() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::BIG_ENDIAN];
        }
    }

    #[DataProvider('providerWriteEmptyPointThrowsException')]
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new EwkbWriter();

        $this->expectException(GeometryIoException::class);
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
