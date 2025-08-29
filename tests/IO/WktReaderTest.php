<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Io\WktReader;
use Brick\Geo\Io\WktWriter;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;

use function str_replace;
use function strtolower;

/**
 * Unit tests for class WktReader.
 */
class WktReaderTest extends WktAbstractTestCase
{
    /**
     * @param string $wkt        The WKT to read.
     * @param array  $coords     The expected Point coordinates.
     * @param bool   $is3D       Whether the resulting Point has a Z coordinate.
     * @param bool   $isMeasured Whether the resulting Point has a M coordinate.
     * @param int    $srid       The SRID to use.
     */
    #[DataProvider('providerRead')]
    public function testRead(string $wkt, array $coords, bool $is3D, bool $isMeasured, int $srid): void
    {
        $geometry = (new WktReader())->read($wkt, $srid);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    public static function providerRead(): Generator
    {
        foreach (self::providerWkt() as [$wkt, $coords, $is3D, $isMeasured]) {
            yield [$wkt, $coords, $is3D, $isMeasured, 0];
            yield [self::alter($wkt), $coords, $is3D, $isMeasured, 4326];
        }
    }

    #[DataProvider('providerAlternativeSyntaxWkt')]
    public function testAlternativeSyntax(string $canonicalWkt, string $alternativeWkt): void
    {
        $wktReader = new WktReader();
        $wktWriter = new WktWriter();
        $wktWriter->setPrettyPrint(false);

        $canonical = $wktReader->read($canonicalWkt);
        $alternative = $wktReader->read($alternativeWkt);

        // WKTWriter always writes the canonical form.
        self::assertSame($canonicalWkt, $wktWriter->write($canonical));
        self::assertSame($canonicalWkt, $wktWriter->write($alternative));
    }

    /**
     * Adds extra spaces to a WKT string, and changes its case.
     *
     * The result is still a valid WKT string, that the reader should be able to handle.
     */
    private static function alter(string $wkt): string
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
