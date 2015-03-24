<?php

namespace Brick\Geo\Tests;

use Brick\Geo\GeometryCollection;

/**
 * Unit tests for class GeometryCollection.
 */
class GeometryCollectionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider providerIsEmpty
     *
     * @param string  $wkt     The WKT of the GeometryCollection to test.
     * @param boolean $isEmpty Whether the geometry is expected to be empty.
     */
    public function testIsEmpty($wkt, $isEmpty)
    {
        $this->assertSame($isEmpty, GeometryCollection::fromText($wkt)->isEmpty());
    }

    /**
     * @return array
     */
    public function providerIsEmpty()
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY', true],
            ['GEOMETRYCOLLECTION (POINT (1 2))', false],
            ['GEOMETRYCOLLECTION (POINT EMPTY)', true],
            ['GEOMETRYCOLLECTION (POINT EMPTY, LINESTRING EMPTY)', true]
        ];
    }
}
