<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;
use Brick\Geo\Exception\GeometryException;

/**
 * Unit tests for class PolyhedralSurface.
 */
class PolyhedralSurfaceTest extends AbstractTestCase
{
    /**
     * @dataProvider providerCreate
     *
     * @param string[] $patches           The WKT of the patches (polygons) that compose the PolyhedralSurface.
     * @param boolean  $is3D              Whether the patches have Z coordinates.
     * @param boolean  $isMeasured        Whether the patches have M coordinates.
     * @param string   $polyhedralSurface The WKT of the expected PolyhedralSurface.
     */
    public function testCreate(array $patches, $is3D, $isMeasured, $polyhedralSurface)
    {
        foreach ([0, 1] as $srid) {
            $instantiatePolygon = function ($patch) use ($srid) {
                return Polygon::fromText($patch, $srid);
            };

            $ps = PolyhedralSurface::create(array_map($instantiatePolygon, $patches), $is3D, $isMeasured, $srid);
            $this->assertWktEquals($ps, $polyhedralSurface, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerCreate()
    {
        return [
            [['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 'POLYGON ((1 0, 1 1, 2 1, 2 0, 1 0))'], false, false, 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))'],
            [['POLYGON Z ((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0))', 'POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0))'], true, false, 'POLYHEDRALSURFACE Z (((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0)), ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0)))'],
            [['POLYGON M ((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0))', 'POLYGON M ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0))'], false, true, 'POLYHEDRALSURFACE M (((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0)), ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0)))'],
            [['POLYGON ZM ((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1))'], true, true, 'POLYHEDRALSURFACE ZM (((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1)))'],
        ];
    }

    /**
     * @dataProvider providerNumPatches
     *
     * @param string  $polyhedralSurface The WKT of the PolyhedralSurface to test.
     * @param integer $numPatches        The expected number of patchs.
     */
    public function testNumPatches($polyhedralSurface, $numPatches)
    {
        $this->assertSame($numPatches, PolyhedralSurface::fromText($polyhedralSurface)->numPatches());
    }

    /**
     * @return array
     */
    public function providerNumPatches()
    {
        return [
            ['POLYHEDRALSURFACE EMPTY', 0],
            ['POLYHEDRALSURFACE Z EMPTY', 0],
            ['POLYHEDRALSURFACE M EMPTY', 0],
            ['POLYHEDRALSURFACE ZM EMPTY', 0],
            ['POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))', 3],
            ['POLYHEDRALSURFACE Z (((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0)), ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0)))', 2],
            ['POLYHEDRALSURFACE M (((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0)), ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0)))', 2],
            ['POLYHEDRALSURFACE ZM (((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1)))', 1],
        ];
    }

    /**
     * @dataProvider providerPatchN
     *
     * @param string      $polyhedralSurface The WKT of the PolyhedralSurface to test.
     * @param integer     $n                 The patch number.
     * @param string|null $patchN        The WKT of the expected patch, or NULL if an exception is expected.
     * @param integer     $srid          The SRID of the geometries.
     */
    public function testPatchN($polyhedralSurface, $n, $patchN, $srid)
    {
        if ($patchN === null) {
            $this->setExpectedException(GeometryException::class);
        }

        $patch = PolyhedralSurface::fromText($polyhedralSurface, $srid)->patchN($n);
        $this->assertWktEquals($patch, $patchN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerPatchN()
    {
        $tests = [
            ['POLYHEDRALSURFACE EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYHEDRALSURFACE Z EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYHEDRALSURFACE M EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYHEDRALSURFACE ZM EMPTY', [
                0 => null,
                1 => null,
            ]],
            ['POLYHEDRALSURFACE(((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))', [
                0 => null,
                1 => 'POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))',
                2 => 'POLYGON ((1 0, 1 1, 2 1, 2 0, 1 0))',
                3 => 'POLYGON ((2 0, 2 1, 3 1, 3 0, 2 0))',
                4 => null,
            ]],
            ['POLYHEDRALSURFACE Z(((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0)), ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0)))', [
                0 => null,
                1 => 'POLYGON Z ((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0))',
                2 => 'POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0))',
                3 => null,
            ]],
            ['POLYHEDRALSURFACE M(((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0)), ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0)))', [
                0 => null,
                1 => 'POLYGON M ((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0))',
                2 => 'POLYGON M ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0))',
                3 => null,
            ]],
            ['POLYHEDRALSURFACE ZM(((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1)))', [
                0 => null,
                1 => 'POLYGON ZM ((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1))',
                2 => null,
            ]],
        ];

        foreach ($tests as list ($polyhedralSurface, $patchs)) {
            foreach ($patchs as $n => $patchN) {
                foreach ([0, 1] as $srid) {
                    yield [$polyhedralSurface, $n, $patchN, $srid];
                }
            }
        }
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces()
    {
        $polyhedralSurface = PolyhedralSurface::fromText('POLYHEDRALSURFACE(((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))');

        $this->assertInstanceOf(\Countable::class, $polyhedralSurface);
        $this->assertSame(3, count($polyhedralSurface));

        $this->assertInstanceOf(\Traversable::class, $polyhedralSurface);
        $this->assertSame([
            $polyhedralSurface->patchN(1),
            $polyhedralSurface->patchN(2),
            $polyhedralSurface->patchN(3)
        ], iterator_to_array($polyhedralSurface));
    }
}
