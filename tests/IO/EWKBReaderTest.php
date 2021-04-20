<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKBReader;
use Brick\Geo\IO\EWKTWriter;

/**
 * Unit tests for class EWKBReader.
 */
class EWKBReaderTest extends EWKBAbstractTest
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

    public function providerRead() : \Generator
    {
        foreach ($this->providerBigEndianEWKB() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach ($this->providerBigEndianEWKB_SRID() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach ($this->providerLittleEndianEWKB() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        foreach ($this->providerLittleEndianEWKB_SRID() as [$ewkt, $ewkb]) {
            yield [$ewkb, $ewkt];
        }

        /* WKB being valid EWKB, we test the reader against WKB as well */

        foreach ($this->providerBigEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach ($this->providerLittleEndianWKB() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }
}
