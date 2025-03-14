<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\WKTReader;
use Brick\Geo\IO\WKTWriter;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Unit tests for class WKTReader.
 */
class WKTReaderTest extends WKTAbstractTestCase
{
    /**
     * @param string $wkt        The WKT to read.
     * @param array  $coords     The expected Point coordinates.
     * @param bool   $is3D       Whether the resulting Point has a Z coordinate.
     * @param bool   $isMeasured Whether the resulting Point has a M coordinate.
     * @param int    $srid       The SRID to use.
     */
    #[DataProvider('providerRead')]
    public function testRead(string $wkt, array $coords, bool $is3D, bool $isMeasured, int $srid) : void
    {
        $geometry = (new WKTReader())->read($wkt, $srid);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    public static function providerRead() : \Generator
    {
        foreach (self::providerWKT() as [$wkt, $coords, $is3D, $isMeasured]) {
            yield [$wkt, $coords, $is3D, $isMeasured, 0];
            yield [self::alter($wkt), $coords, $is3D, $isMeasured, 4326];
        }
    }

    #[DataProvider('providerAlternativeSyntaxWKT')]
    public function testAlternativeSyntax(string $canonicalWKT, string $alternativeWKT): void
    {
        $wktReader = new WKTReader();
        $wktWriter = new WKTWriter();
        $wktWriter->setPrettyPrint(false);

        $canonical = $wktReader->read($canonicalWKT);
        $alternative = $wktReader->read($alternativeWKT);

        // WKTWriter always writes the canonical form.
        self::assertSame($canonicalWKT, $wktWriter->write($canonical));
        self::assertSame($canonicalWKT, $wktWriter->write($alternative));
    }

    /**
     * Adds extra spaces to a WKT string, and changes its case.
     *
     * The result is still a valid WKT string, that the reader should be able to handle.
     */
    private static function alter(string $wkt) : string
    {
        $search = [' ', '(', ')', ','];
        $replace = [];

        foreach ($search as $char) {
            $replace[] = " $char ";
        }

        $wkt = str_replace($search, $replace, $wkt);

        return strtolower(" $wkt ");
    }
}
