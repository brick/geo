<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\IO\WKBByteOrder;
use Brick\Geo\IO\WKBWriter;
use Brick\Geo\IO\WKTReader;
use Brick\Geo\Point;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class WKBWriter.
 */
class WKBWriterTest extends WKBAbstractTestCase
{
    /**
     * @param string       $wkt       The WKT to read.
     * @param string       $wkb       The expected WKB output, hex-encoded.
     * @param WKBByteOrder $byteOrder The byte order to use.
     */
    #[DataProvider('providerWrite')]
    public function testWrite(string $wkt, string $wkb, WKBByteOrder $byteOrder) : void
    {
        $writer = new WKBWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new WKTReader();

        $geometry = $reader->read($wkt);
        $output = $writer->write($geometry);

        self::assertSame($wkb, bin2hex($output));
    }

    public static function providerWrite() : \Generator
    {
        foreach (self::providerLittleEndianWKB() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WKBByteOrder::LITTLE_ENDIAN];
        }

        foreach (self::providerBigEndianWKB() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WKBByteOrder::BIG_ENDIAN];
        }
    }

    #[DataProvider('providerWriteEmptyPointThrowsException')]
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new WKBWriter();

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
