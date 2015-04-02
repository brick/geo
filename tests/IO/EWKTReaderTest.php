<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\IO\EWKTReader;

/**
 * Unit tests for class EWKTReader.
 */
class EWKTReaderTest extends EWKTAbstractTest
{
    /**
     * @dataProvider providerRead
     *
     * @param string  $ewkt       The EWKT to read.
     * @param array   $coords     The expected Point coordinates.
     * @param boolean $is3D       Whether the resulting Point has a Z coordinate.
     * @param boolean $isMeasured Whether the resulting Point has a M coordinate.
     * @param integer $srid       The expected SRID.
     */
    public function testRead($ewkt, array $coords, $is3D, $isMeasured, $srid)
    {
        $geometry = (new EWKTReader())->read($ewkt);
        $this->assertGeometryContents($geometry, $coords, $is3D, $isMeasured, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerRead()
    {
        foreach ($this->providerWKT() as list($wkt, $coords, $is3D, $isMeasured)) {
            yield [$wkt, $coords, $is3D, $isMeasured, 0];
            yield [$this->toEWKT($wkt, 4326), $coords, $is3D, $isMeasured, 4326];
        }
    }
}
