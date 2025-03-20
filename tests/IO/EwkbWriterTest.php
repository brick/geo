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
        $writer = new EwkbWriter(byteOrder: $byteOrder);
        $reader = new EwktReader();

        $geometry = $reader->read($ewkt);
        $output = $writer->write($geometry);

        self::assertSame($ewkb, bin2hex($output));
    }

    public static function providerWrite() : \Generator
    {
        foreach (self::providerLittleEndianEwkb() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::LittleEndian];
        }

        foreach (self::providerLittleEndianEwkbWithSrid() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::LittleEndian];
        }

        foreach (self::providerBigEndianEwkb() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::BigEndian];
        }

        foreach (self::providerBigEndianEwkbWithSrid() as [$wkt, $ewkb]) {
            yield [$wkt, $ewkb, WkbByteOrder::BigEndian];
        }
    }

    #[DataProvider('providerWriteEmptyPointWithoutNanSupportThrowsException')]
    public function testWriteEmptyPointWithoutNanSupportThrowsException(Point $point) : void
    {
        $writer = new EwkbWriter(supportEmptyPointWithNan: false);

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage(
            'Empty points have no WKB representation. If you want to output empty points with NaN coordinates ' .
            '(PostGIS-style), enable the $supportEmptyPointWithNan option.',
        );

        $writer->write($point);
    }

    public static function providerWriteEmptyPointWithoutNanSupportThrowsException() : array
    {
        return [
            [Point::xyEmpty()],
            [Point::xyzEmpty()],
            [Point::xymEmpty()],
            [Point::xyzmEmpty()]
        ];
    }

    #[DataProvider('providerWriteEmptyPointWithNanSupport')]
    public function testWriteEmptyPointWithNanSupport(Point $point, WkbByteOrder $byteOrder, string $expectedHex) : void
    {
        $writer = new EwkbWriter(byteOrder: $byteOrder);

        $actualHex = bin2hex($writer->write($point));
        self::assertSame($expectedHex, $actualHex);
    }

    public static function providerWriteEmptyPointWithNanSupport() : array
    {
        return [
            [Point::xyEmpty(), WkbByteOrder::BigEndian, '00000000017ff80000000000007ff8000000000000'],
            [Point::xyEmpty(), WkbByteOrder::LittleEndian, '0101000000000000000000f87f000000000000f87f'],
            [Point::xyzEmpty(), WkbByteOrder::BigEndian, '00800000017ff80000000000007ff80000000000007ff8000000000000'],
            [Point::xyzEmpty(), WkbByteOrder::LittleEndian, '0101000080000000000000f87f000000000000f87f000000000000f87f'],
            [Point::xymEmpty(), WkbByteOrder::BigEndian, '00400000017ff80000000000007ff80000000000007ff8000000000000'],
            [Point::xymEmpty(), WkbByteOrder::LittleEndian, '0101000040000000000000f87f000000000000f87f000000000000f87f'],
            [Point::xyzmEmpty(), WkbByteOrder::BigEndian, '00c00000017ff80000000000007ff80000000000007ff80000000000007ff8000000000000'],
            [Point::xyzmEmpty(), WkbByteOrder::LittleEndian, '01010000c0000000000000f87f000000000000f87f000000000000f87f000000000000f87f'],
        ];
    }
}
