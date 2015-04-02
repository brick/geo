<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\WKTReader;

/**
 * Unit tests for class WKTReader.
 */
class WKTReaderTest extends WKTAbstractTest
{
    /**
     * @dataProvider providerRead
     *
     * @param string  $wkt        The WKT to read.
     * @param array   $coords     The expected Point coordinates.
     * @param boolean $is3D       Whether the resulting Point has a Z coordinate.
     * @param boolean $isMeasured Whether the resulting Point has a M coordinate.
     * @param integer $srid       The SRID to use.
     */
    public function testRead($wkt, array $coords, $is3D, $isMeasured, $srid)
    {
        $geometry = (new WKTReader())->read($wkt, $srid);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerRead()
    {
        foreach ($this->providerWKT() as list($wkt, $coords, $is3D, $isMeasured)) {
            yield [$wkt, $coords, $is3D, $isMeasured, 0];
            yield [$this->alter($wkt), $coords, $is3D, $isMeasured, 4326];
        }
    }

    /**
     * Adds extra spaces to a WKT string, and changes its case.
     *
     * The result is still a valid WKT string, that the reader should be able to handle.
     *
     * @param string $wkt
     *
     * @return string
     */
    private function alter($wkt)
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
