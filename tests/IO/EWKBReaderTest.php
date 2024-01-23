<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKBReader;
use Brick\Geo\IO\EWKTWriter;

/**
 * Unit tests for class EWKBReader.
 */
class EWKBReaderTest extends EWKBAbstractTestCase
{
    /**
     * @dataProvider providerRead
     *
     * @param string $ewkb The EWKB to read, hex-encoded.
     * @param string $ewkt The expected EWKT output.
     */
    public function testRead(string $ewkb, string $ewkt) : void
    {
        $reader = new EWKBReader();
        $writer = new EWKTWriter();

        $geometry = $reader->read(hex2bin($ewkb));
        self::assertSame($ewkt, $writer->write($geometry));
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerBigEndianEWKB() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach (self::providerBigEndianEWKB_SRID() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach (self::providerLittleEndianEWKB() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach (self::providerLittleEndianEWKB_SRID() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        /* WKB being valid EWKB, we test the reader against WKB as well */

        foreach (self::providerBigEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach (self::providerLittleEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }
}
