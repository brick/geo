<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Io\EwkbReader;
use Brick\Geo\Io\EwktWriter;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

use function hex2bin;

/**
 * Unit tests for class EwkbReader.
 */
class EwkbReaderTest extends EwkbAbstractTestCase
{
    /**
     * @param string $ewkb The EWKB to read, hex-encoded.
     * @param string $ewkt The expected EWKT output.
     */
    #[DataProvider('providerRead')]
    public function testRead(string $ewkb, string $ewkt): void
    {
        $reader = new EwkbReader();
        $writer = new EwktWriter();

        $geometry = $reader->read(hex2bin($ewkb));
        self::assertSame($ewkt, $writer->write($geometry));
    }

    public static function providerRead(): Generator
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

        // WKB being valid EWKB, we test the reader against WKB as well

        foreach (self::providerBigEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }

        foreach (self::providerLittleEndianWkb() as [$wkt, $wkb]) {
            yield [$wkb, $wkt];
        }
    }
}
