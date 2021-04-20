<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\IO\WKBTools;
use Brick\Geo\IO\WKBWriter;
use Brick\Geo\IO\WKTReader;
use Brick\Geo\Point;

/**
 * Unit tests for class WKBWriter.
 */
class WKBWriterTest extends WKBAbstractTest
{
    /**
     * @dataProvider providerWrite
     *
     * @param string $wkt       The WKT to read.
     * @param string $wkb       The expected WKB output, hex-encoded.
     * @param int    $byteOrder The byte order to use.
     */
    public function testWrite(string $wkt, string $wkb, int $byteOrder) : void
    {
        $writer = new WKBWriter();
        $writer->setByteOrder($byteOrder);

        $reader = new WKTReader();

        $geometry = $reader->read($wkt);
        $output = $writer->write($geometry);

        self::assertSame($wkb, bin2hex($output));
    }

    public function providerWrite() : \Generator
    {
        foreach ($this->providerLittleEndianWKB() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WKBTools::LITTLE_ENDIAN];
        }

        foreach ($this->providerBigEndianWKB() as [$wkt, $wkb]) {
            yield [$wkt, $wkb, WKBTools::BIG_ENDIAN];
        }
    }

    /**
     * @dataProvider providerWriteEmptyPointThrowsException
     */
    public function testWriteEmptyPointThrowsException(Point $point) : void
    {
        $writer = new WKBWriter();

        $this->expectException(GeometryIOException::class);
        $writer->write($point);
    }

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
