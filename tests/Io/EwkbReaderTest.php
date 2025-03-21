<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Io;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Io\EwkbReader;
use Brick\Geo\Io\EwktWriter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class EwkbReader.
 */
class EwkbReaderTest extends EwkbAbstractTestCase
{
    #[DataProvider('providerRead')]
    public function testRead(string $ewkbHex, string $expectedEwkt) : void
    {
        $ewkbReader = new EwkbReader();
        $ewktWriter = new EwktWriter();

        $geometry = $ewkbReader->read(hex2bin($ewkbHex));
        self::assertSame($expectedEwkt, $ewktWriter->write($geometry));
    }

    #[DataProvider('providerRead')]
    public function testReadWithExtraBytes(string $ewkbHex)
    {
        $ewkbReader = new EwkbReader();

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage('Invalid EWKB: unexpected data at end of stream');

        $ewkbReader->read(hex2bin($ewkbHex) . "\x00");
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerBigEndianEwkb() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach (self::providerBigEndianEwkbWithSrid() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach (self::providerLittleEndianEwkb() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach (self::providerLittleEndianEwkbWithSrid() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        /* WKB being valid EWKB, we test the reader against WKB as well */

        foreach (self::providerBigEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach (self::providerLittleEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }

    #[DataProvider('providerReadEmptyPointWithoutNanSupportThrowsException')]
    public function testReadEmptyPointWithoutNanSupportThrowsException(string $ewkbHex) : void
    {
        $reader = new EwkbReader(supportEmptyPointWithNan: false);

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage(
            'Points with NaN (not-a-number) coordinates are not supported. If you want to read points with NaN ' .
            'coordinates as empty points (PostGIS-style), enable the $supportEmptyPointWithNan option.',
        );

        $reader->read(hex2bin($ewkbHex));
    }

    public static function providerReadEmptyPointWithoutNanSupportThrowsException() : array
    {
        return [
            'xy_BigEndian' => ['00000000017ff80000000000007ff8000000000000'],
            'xy_LittleEndian' => ['0101000000000000000000f87f000000000000f87f'],
            'xyz_BigEndian' => ['00800000017ff80000000000007ff80000000000007ff8000000000000'],
            'xyz_LittleEndian' => ['0101000080000000000000f87f000000000000f87f000000000000f87f'],
            'xym_BigEndian' => ['00400000017ff80000000000007ff80000000000007ff8000000000000'],
            'xym_LittleEndian' => ['0101000040000000000000f87f000000000000f87f000000000000f87f'],
            'xyzm_BigEndian' => ['00c00000017ff80000000000007ff80000000000007ff80000000000007ff8000000000000'],
            'xyzm_LittleEndian' => ['01010000c0000000000000f87f000000000000f87f000000000000f87f000000000000f87f'],
        ];
    }

    #[DataProvider('providerReadEmptyPointWithNanSupport')]
    public function testReadEmptyPointWithNanSupport(string $ewkbHex, string $expectedEwkt) : void
    {
        $ewkbReader = new EwkbReader();
        $ewktWriter = new EwktWriter();

        $point = $ewkbReader->read(hex2bin($ewkbHex));
        self::assertSame($expectedEwkt, $ewktWriter->write($point));
    }

    public static function providerReadEmptyPointWithNanSupport() : array
    {
        return [
            'xy_BigEndian' => ['00000000017ff80000000000007ff8000000000000', 'POINT EMPTY'],
            'xy_LittleEndian' => ['0101000000000000000000f87f000000000000f87f', 'POINT EMPTY'],
            'xyz_BigEndian' => ['00800000017ff80000000000007ff80000000000007ff8000000000000', 'POINT Z EMPTY'],
            'xyz_LittleEndian' => ['0101000080000000000000f87f000000000000f87f000000000000f87f', 'POINT Z EMPTY'],
            'xym_BigEndian' => ['00400000017ff80000000000007ff80000000000007ff8000000000000', 'POINT M EMPTY'],
            'xym_LittleEndian' => ['0101000040000000000000f87f000000000000f87f000000000000f87f', 'POINT M EMPTY'],
            'xyzm_BigEndian' => ['00c00000017ff80000000000007ff80000000000007ff80000000000007ff8000000000000', 'POINT ZM EMPTY'],
            'xyzm_LittleEndian' => ['01010000c0000000000000f87f000000000000f87f000000000000f87f000000000000f87f', 'POINT ZM EMPTY'],
        ];
    }
}
