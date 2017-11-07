<?php

namespace Brick\Geo\Tests;

use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\CoordinateSystem;
use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;

/**
 * Unit tests for class PolyhedralSurface.
 */
class PolyhedralSurfaceTest extends AbstractTestCase
{
    /**
     * @dataProvider providerCreate
     *
     * @param string[] $patchesWKT           The WKT of the patches (polygons) that compose the PolyhedralSurface.
     * @param bool     $is3D                 Whether the patches have Z coordinates.
     * @param bool     $isMeasured           Whether the patches have M coordinates.
     * @param string   $polyhedralSurfaceWKT The WKT of the expected PolyhedralSurface.
     *
     * @return void
     */
    public function testCreate(array $patchesWKT, bool $is3D, bool $isMeasured, string $polyhedralSurfaceWKT) : void
    {
        foreach ([0, 1] as $srid) {
            $instantiatePolygon = function ($patch) use ($srid) {
                return Polygon::fromText($patch, $srid);
            };

            $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
            $polyhedralSurface = new PolyhedralSurface($cs, ...array_map($instantiatePolygon, $patchesWKT));
            $this->assertWktEquals($polyhedralSurface, $polyhedralSurfaceWKT, $srid);
        }
    }

    /**
     * @return array
     */
    public function providerCreate() : array
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
     * @param string $polyhedralSurface The WKT of the PolyhedralSurface to test.
     * @param int    $numPatches        The expected number of patchs.
     *
     * @return void
     */
    public function testNumPatches(string $polyhedralSurface, int $numPatches) : void
    {
        $this->assertSame($numPatches, PolyhedralSurface::fromText($polyhedralSurface)->numPatches());
    }

    /**
     * @return array
     */
    public function providerNumPatches() : array
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
     * @param int         $n                 The patch number.
     * @param string|null $patchN        The WKT of the expected patch, or NULL if an exception is expected.
     * @param int         $srid          The SRID of the geometries.
     *
     * @return void
     */
    public function testPatchN(string $polyhedralSurface, int $n, ?string $patchN, int $srid) : void
    {
        if ($patchN === null) {
            $this->expectException(NoSuchGeometryException::class);
        }

        $patch = PolyhedralSurface::fromText($polyhedralSurface, $srid)->patchN($n);
        $this->assertWktEquals($patch, $patchN, $srid);
    }

    /**
     * @return \Generator
     */
    public function providerPatchN() : \Generator
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

        foreach ($tests as [$polyhedralSurface, $patchs]) {
            foreach ($patchs as $n => $patchN) {
                foreach ([0, 1] as $srid) {
                    yield [$polyhedralSurface, $n, $patchN, $srid];
                }
            }
        }
    }

    /**
     * Tests Countable and Traversable interfaces.
     *
     * @return void
     */
    public function testInterfaces() : void
    {
        $polyhedralSurface = PolyhedralSurface::fromText('POLYHEDRALSURFACE(((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))');

        $this->assertInstanceOf(\Countable::class, $polyhedralSurface);
        $this->assertCount(3, $polyhedralSurface);

        $this->assertInstanceOf(\Traversable::class, $polyhedralSurface);
        $this->assertSame([
            $polyhedralSurface->patchN(1),
            $polyhedralSurface->patchN(2),
            $polyhedralSurface->patchN(3)
        ], iterator_to_array($polyhedralSurface));
    }
}
