<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Geometry;

/**
 * Unit tests for class MultiPoint.
 */
class MultiPointTest extends AbstractTestCase
{
    /**
     * @dataProvider providerIsSimple
     *
     * @param string $wkt      The WKT of the geometry to test.
     * @param bool   $isSimple Whether the geometry is simple.
     */
    public function testIsSimple($wkt, $isSimple)
    {
        $this->assertSame($isSimple, Geometry::fromText($wkt)->isSimple());
    }

    /**
     * @return array
     */
    public function providerIsSimple()
    {
        return [
            ['MULTIPOINT EMPTY', true],
            ['MULTIPOINT (1 2)', true],
            ['MULTIPOINT (1 3)', true],
            ['MULTIPOINT (1 2, 1 3)', true],
            ['MULTIPOINT (1 2, 1 3, 1 2)', false],
            ['MULTIPOINT Z (1 2 3, 2 3 4)', true],
            ['MULTIPOINT Z (1 2 3, 1 2 4)', false],
            ['MULTIPOINT M (1 2 3, 2 3 4)', true],
            ['MULTIPOINT M (1 2 3, 1 2 4)', false],
            ['MULTIPOINT ZM (1 2 3 4, 2 3 4 5)', true],
            ['MULTIPOINT ZM (1 2 3 4, 1 2 4 3)', false]
        ];
    }
}
