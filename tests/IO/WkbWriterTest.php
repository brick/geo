<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\IO\Internal\WkbByteOrder;
use Brick\Geo\IO\WkbWriter;
use Brick\Geo\IO\WktReader;
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
        $writer = new WkbWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new WktReader();

        $geometry = $reader->read($wkt);
        $output = $writer->write($geometry);

        self::assertSame($wkb, bin2hex($output));
    }

    public static function providerWrite() : \Generator
    {
        foreach (self::providerLittleEndianWkb() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WkbByteOrder::LITTLE_ENDIAN];
        }

        foreach (self::providerBigEndianWkb() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WkbByteOrder::BIG_ENDIAN];
        }
    }

    #[DataProvider('providerWriteEmptyPointThrowsException')]
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new WkbWriter();

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
