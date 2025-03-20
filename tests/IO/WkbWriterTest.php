<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Io\Internal\WkbByteOrder;
use Brick\Geo\Io\WkbWriter;
use Brick\Geo\Io\WktReader;
use Brick\Geo\Point;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class WkbWriter.
 */
class WkbWriterTest extends WkbAbstractTestCase
{
    /**
     * @param string       $wkt       The WKT to read.
     * @param string       $wkb       The expected WKB output, hex-encoded.
     * @param WkbByteOrder $byteOrder The byte order to use.
     */
    #[DataProvider('providerWrite')]
    public function testWrite(string $wkt, string $wkb, WkbByteOrder $byteOrder) : void
    {
        $writer = new WkbWriter(byteOrder: $byteOrder);
        $reader = new WktReader();

        $geometry = $reader->read($wkt);
        $output = $writer->write($geometry);

        self::assertSame($wkb, bin2hex($output));
    }

    public static function providerWrite() : \Generator
    {
        foreach (self::providerLittleEndianWkb() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WkbByteOrder::LittleEndian];
        }

        foreach (self::providerBigEndianWkb() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WkbByteOrder::BigEndian];
        }
    }

    #[DataProvider('providerWriteEmptyPointWithoutNanSupportThrowsException')]
    public function testWriteEmptyPointWithoutNanSupportThrowsException(Point $point) : void
    {
        $writer = new WkbWriter();

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
        $writer = new WkbWriter(
            byteOrder: $byteOrder,
            supportEmptyPointWithNan: true,
        );

        $actualHex = bin2hex($writer->write($point));
        self::assertSame($expectedHex, $actualHex);
    }

    public static function providerWriteEmptyPointWithNanSupport() : array
    {
        return [
            [Point::xyEmpty(), WkbByteOrder::BigEndian, '00000000017ff80000000000007ff8000000000000'],
            [Point::xyEmpty(), WkbByteOrder::LittleEndian, '0101000000000000000000f87f000000000000f87f'],
            [Point::xyzEmpty(), WkbByteOrder::BigEndian, '00000003e97ff80000000000007ff80000000000007ff8000000000000'],
            [Point::xyzEmpty(), WkbByteOrder::LittleEndian, '01e9030000000000000000f87f000000000000f87f000000000000f87f'],
            [Point::xymEmpty(), WkbByteOrder::BigEndian, '00000007d17ff80000000000007ff80000000000007ff8000000000000'],
            [Point::xymEmpty(), WkbByteOrder::LittleEndian, '01d1070000000000000000f87f000000000000f87f000000000000f87f'],
            [Point::xyzmEmpty(), WkbByteOrder::BigEndian, '0000000bb97ff80000000000007ff80000000000007ff80000000000007ff8000000000000'],
            [Point::xyzmEmpty(), WkbByteOrder::LittleEndian, '01b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f'],
        ];
    }
}
