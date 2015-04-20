<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\MultiCurve;

/**
 * Unit tests for class MultiCurve.
 */
class MultiCurveTest extends AbstractTestCase
{
    /**
     * @dataProvider providerInvalidFromText
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string $wkt A valid WKT, for a non-multicurve geometry.
     */
    public function testInvalidFromText($wkt)
    {
        MultiCurve::fromText($wkt);
    }

    /**
     * @return array
     */
    public function providerInvalidFromText()
    {
        return [
            ['POINT EMPTY'],
            ['LINESTRING EMPTY'],
            ['GEOMETRYCOLLECTION EMPTY'],
            ['MULTIPOLYGON EMPTY'],
        ];
    }

    /**
     * @dataProvider providerInvalidFromBinary
     * @expectedException \Brick\Geo\Exception\GeometryException
     *
     * @param string $wkb A valid HEX WKB, for a non-multicurve geometry.
     */
    public function testInvalidFromBinary($wkb)
    {
        MultiCurve::fromBinary(hex2bin($wkb));
    }

    /**
     * @return array
     */
    public function providerInvalidFromBinary()
    {
        return [
            ['000000000200000000'],
            ['000000000300000000'],
            ['010f00000000000000'],
            ['010700000000000000'],
            ['01ee03000000000000'],
        ];
    }

    /**
     * @dataProvider providerLength
     *
     * @param string $curve  The WKT of the curve to test.
     * @param float  $length The expected length.
     */
    public function testLength($curve, $length)
    {
        $this->requiresGeometryEngine();

        $curve = MultiCurve::fromText($curve);
        $this->skipIfUnsupportedGeometry($curve);

        $actualLength = $curve->length();

        $this->assertInternalType('float', $actualLength);
        $this->assertEquals($length, $actualLength, '', 0.001);
    }

    /**
     * @return array
     */
    public function providerLength()
    {
        return [
            ['MULTILINESTRING ((1 1, 2 1))', 1],
            ['MULTILINESTRING ((1 1, 1 2))', 1],
            ['MULTILINESTRING ((1 1, 2 2))', 1.414],
            ['MULTILINESTRING ((1 1, 2 2, 3 2, 3 3))', 3.414],
            ['MULTILINESTRING ((1 1, 2 1), (2 2, 2 3))', 2],
            ['MULTILINESTRING ((1 1, 2 2), (1 1, 2 2, 3 2, 3 3))', 4.828],
        ];
    }

    /**
     * @dataProvider providerIsClosed
     *
     * @param string  $curve    The WKT of the MultiCurve to test.
     * @param boolean $isClosed Whether the MultiCurve is closed.
     */
    public function testIsClosed($curve, $isClosed)
    {
        $this->requiresGeometryEngine();

        $curve = MultiCurve::fromText($curve);
        $this->skipIfUnsupportedGeometry($curve);

//        if ($this->isGEOS('< 3.5.0')) {
//            // GEOS PHP bindings did not support isClosed() on MultiCurve in older versions.
//            $this->setExpectedException(GeometryEngineException::class);
//        }

        $this->assertSame($isClosed, $curve->isClosed());
    }

    /**
     * @return array
     */
    public function providerIsClosed()
    {
        return [
            ['MULTILINESTRING ((1 1, 2 2))', false],
            ['MULTILINESTRING ((1 1, 2 2, 3 3))', false],
            ['MULTILINESTRING ((1 1, 2 2, 3 3, 1 1))', true],
            ['MULTILINESTRING ((1 1, 2 2, 3 3, 1 1), (1 1, 2 2))', false],
            ['MULTILINESTRING ((1 1, 2 2, 3 3, 1 1), (0 0, 0 1, 1 1, 0 0))', true],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0))', false],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0, 1 1 0))', true],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0, 1 1 0), (1 1 0, 2 2 0, 3 3 0))', false],
            ['MULTILINESTRING Z ((1 1 0, 1 2 0, 2 2 0, 1 1 0), (1 1 1, 2 2 1, 3 3 1, 1 1 1))', true],
        ];
    }
}
