<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Geometry;
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

    /**
     * @param string $wkb The WKB to read, hex-encoded.
     * @param string $wkt The expected WKT output.
     */
    #[DataProvider('providerRead')]
    public function testReadAsProxy(string $wkb, string $wkt) : void
    {
        $reader = new WKBReader();
        $geometry = $reader->readAsProxy(hex2bin($wkb), 4326);

        $reflectionClass = new \ReflectionClass(Geometry::class);
        self::assertTrue($reflectionClass->isUninitializedLazyObject($geometry));

        self::assertSame($wkt, $geometry->asText());
        self::assertSame(4326, $geometry->SRID());

        self::assertFalse($reflectionClass->isUninitializedLazyObject($geometry));
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
}
