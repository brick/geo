<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKTReader;
use Brick\Geo\IO\EWKTWriter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class EWKTReader.
 */
class EWKTReaderTest extends EWKTAbstractTestCase
{
    /**
     * @param string $ewkt       The EWKT to read.
     * @param array  $coords     The expected Point coordinates.
     * @param bool   $is3D       Whether the resulting Point has a Z coordinate.
     * @param bool   $isMeasured Whether the resulting Point has a M coordinate.
     * @param int    $srid       The expected SRID.
     */
    #[DataProvider('providerRead')]
    public function testRead(string $ewkt, array $coords, bool $is3D, bool $isMeasured, int $srid) : void
    {
        $geometry = (new EWKTReader())->read($ewkt);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerWKT() as [$wkt, $coords, $is3D, $isMeasured]) {
            yield [$wkt, $coords, $is3D, $isMeasured, 0];
            yield [self::toEWKT($wkt, 4326), $coords, $is3D, $isMeasured, 4326];
        }
    }

    #[DataProvider('providerAlternativeSyntax')]
    public function testAlternativeSyntax(string $canonicalEWKT, string $alternativeEWKT): void
    {
        $wktReader = new EWKTReader();
        $wktWriter = new EWKTWriter();
        $wktWriter->setPrettyPrint(false);

        $canonical = $wktReader->read($canonicalEWKT);
        $alternative = $wktReader->read($alternativeEWKT);

        // EWKTWriter always writes the canonical form.
        self::assertSame($canonicalEWKT, $wktWriter->write($canonical));
        self::assertSame($canonicalEWKT, $wktWriter->write($alternative));
    }

    public static function providerAlternativeSyntax(): \Generator
    {
        foreach (self::providerAlternativeSyntaxWKT() as [$canonicalWKT, $alternativeWKT]) {
            yield [$canonicalWKT, $alternativeWKT];
            yield [self::toEWKT($canonicalWKT, 4326), self::toEWKT($alternativeWKT, 4326)];
        }
    }
}
