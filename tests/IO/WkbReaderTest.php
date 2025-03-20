<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Io\WkbReader;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class WkbReader.
 */
class WkbReaderTest extends WkbAbstractTestCase
{
    /**
     * @param string $wkb The WKB to read, hex-encoded.
     * @param string $wkt The expected WKT output.
     */
    #[DataProvider('providerRead')]
    public function testRead(string $wkb, string $wkt) : void
    {
        $reader = new WkbReader();
        $geometry = $reader->read(hex2bin($wkb), 4326);

        self::assertSame($wkt, $geometry->asText());
        self::assertSame(4326, $geometry->srid());
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerLittleEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach (self::providerBigEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }

    #[DataProvider('providerReadEmptyPointWithoutNanSupportThrowsException')]
    public function testReadEmptyPointWithoutNanSupportThrowsException(string $wkbHex) : void
    {
        $reader = new WkbReader();

        $this->expectException(GeometryIoException::class);
        $this->expectExceptionMessage(
            'Points with NaN (not-a-number) coordinates are not supported. If you want to read points with NaN ' .
            'coordinates as empty points (PostGIS-style), enable the $supportEmptyPointWithNan option.',
        );

        $reader->read(hex2bin($wkbHex));
    }

    public static function providerReadEmptyPointWithoutNanSupportThrowsException() : array
    {
        return [
            'xy_BigEndian' => ['00000000017ff80000000000007ff8000000000000'],
            'xy_LittleEndian' => ['0101000000000000000000f87f000000000000f87f'],
            'xyz_BigEndian' => ['00000003e97ff80000000000007ff80000000000007ff8000000000000'],
            'xyz_LittleEndian' => ['01e9030000000000000000f87f000000000000f87f000000000000f87f'],
            'xym_BigEndian' => ['00000007d17ff80000000000007ff80000000000007ff8000000000000'],
            'xym_LittleEndian' => ['01d1070000000000000000f87f000000000000f87f000000000000f87f'],
            'xyzm_BigEndian' => ['0000000bb97ff80000000000007ff80000000000007ff80000000000007ff8000000000000'],
            'xyzm_LittleEndian' => ['01b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f'],
        ];
    }

    #[DataProvider('providerReadEmptyPointWithNanSupport')]
    public function testReadEmptyPointWithNanSupport(string $wkbHex, string $expectedWkt) : void
    {
        $reader = new WkbReader(supportEmptyPointWithNan: true);

        $point = $reader->read(hex2bin($wkbHex));
        self::assertSame($expectedWkt, $point->asText());
    }

    public static function providerReadEmptyPointWithNanSupport() : array
    {
        return [
            'xy_BigEndian' => ['00000000017ff80000000000007ff8000000000000', 'POINT EMPTY'],
            'xy_LittleEndian' => ['0101000000000000000000f87f000000000000f87f', 'POINT EMPTY'],
            'xyz_BigEndian' => ['00000003e97ff80000000000007ff80000000000007ff8000000000000', 'POINT Z EMPTY'],
            'xyz_LittleEndian' => ['01e9030000000000000000f87f000000000000f87f000000000000f87f', 'POINT Z EMPTY'],
            'xym_BigEndian' => ['00000007d17ff80000000000007ff80000000000007ff8000000000000', 'POINT M EMPTY'],
            'xym_LittleEndian' => ['01d1070000000000000000f87f000000000000f87f000000000000f87f', 'POINT M EMPTY'],
            'xyzm_BigEndian' => ['0000000bb97ff80000000000007ff80000000000007ff80000000000007ff8000000000000', 'POINT ZM EMPTY'],
            'xyzm_LittleEndian' => ['01b90b0000000000000000f87f000000000000f87f000000000000f87f000000000000f87f', 'POINT ZM EMPTY'],
        ];
    }
}
