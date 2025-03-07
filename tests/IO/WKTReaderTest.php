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

    /**
     * In WKT, CompoundCurve has a special case: the LINESTRING keyword can be explicitly stated, or omitted.
     * The tests above cover only the implicit form, which is the format used by WKBWriter.
     *
     * These additional tests ensure that both forms can be read correctly.
     */
    #[DataProvider('providerReadCompoundCurve')]
    public function testReadCompoundCurve(string $implicitWKT, string $explicitWKT): void
    {
        $wktReader = new WKTReader();
        $wktWriter = new WKTWriter();
        $wktWriter->setPrettyPrint(false);

        $compoundCurveImplicit = $wktReader->read($implicitWKT);
        $compoundCurveExplicit = $wktReader->read($explicitWKT);

        // WKTWriter always writes the implicit form.
        self::assertSame($implicitWKT, $wktWriter->write($compoundCurveImplicit));
        self::assertSame($implicitWKT, $wktWriter->write($compoundCurveExplicit));
    }

    public static function providerReadCompoundCurve(): array
    {
        return [
            [
                'COMPOUNDCURVE((1 2,3 4),CIRCULARSTRING(3 4,5 6,7 8))',
                'COMPOUNDCURVE(LINESTRING(1 2,3 4),CIRCULARSTRING(3 4,5 6,7 8))',
            ], [
                'COMPOUNDCURVE Z((1 2 3,4 5 6),CIRCULARSTRING Z(4 5 6,5 6 7,6 7 8))',
                'COMPOUNDCURVE Z(LINESTRING Z(1 2 3,4 5 6),CIRCULARSTRING Z(4 5 6,5 6 7,6 7 8))',
            ], [
                'COMPOUNDCURVE M((1 2 3,2 3 4),CIRCULARSTRING M(2 3 4,5 6 7,8 9 0))',
                'COMPOUNDCURVE M(LINESTRING M(1 2 3,2 3 4),CIRCULARSTRING M(2 3 4,5 6 7,8 9 0))',
            ], [
                'COMPOUNDCURVE ZM(CIRCULARSTRING ZM(1 2 3 4,2 3 4 5,3 4 5 6),(3 4 5 6,7 8 9 0))',
                'COMPOUNDCURVE ZM(CIRCULARSTRING ZM(1 2 3 4,2 3 4 5,3 4 5 6),LINESTRING ZM(3 4 5 6,7 8 9 0))',
            ],
        ];
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
