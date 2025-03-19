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

    #[DataProvider('providerWriteEmptyPointThrowsException')]
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new WkbWriter();

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
