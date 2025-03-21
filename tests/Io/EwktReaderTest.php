<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Io;

use Brick\Geo\Io\EwktReader;
use Brick\Geo\Io\EwktWriter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class EwktReader.
 */
class EwktReaderTest extends EwktAbstractTestCase
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
        $ewktReader = new EwktReader();
        $geometry = $ewktReader->read($ewkt);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerWkt() as [$wkt, $coords, $is3D, $isMeasured]) {
            yield [$wkt, $coords, $is3D, $isMeasured, 0];
            yield [self::toEwkt($wkt, 4326), $coords, $is3D, $isMeasured, 4326];
        }
    }

    #[DataProvider('providerAlternativeSyntax')]
    public function testAlternativeSyntax(string $canonicalEwkt, string $alternativeEwkt): void
    {
        $wktReader = new EwktReader();
        $wktWriter = new EwktWriter(prettyPrint: false);

        $canonical = $wktReader->read($canonicalEwkt);
        $alternative = $wktReader->read($alternativeEwkt);

        // EWKTWriter always writes the canonical form.
        self::assertSame($canonicalEwkt, $wktWriter->write($canonical));
        self::assertSame($canonicalEwkt, $wktWriter->write($alternative));
    }

    public static function providerAlternativeSyntax(): \Generator
    {
        foreach (self::providerAlternativeSyntaxWkt() as [$canonicalWkt, $alternativeWkt]) {
            yield [$canonicalWkt, $alternativeWkt];
            yield [self::toEwkt($canonicalWkt, 4326), self::toEwkt($alternativeWkt, 4326)];
        }
    }
}
