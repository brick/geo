<?php

declare(strict_types=1);

namespace Brick\Geo\Tests;

use Brick\Geo\CoordinateSystem;
use Brick\Geo\Exception\NoSuchGeometryException;
use Brick\Geo\Polygon;
use Brick\Geo\PolyhedralSurface;
use Countable;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use Traversable;

use function array_map;
use function iterator_to_array;

/**
 * Unit tests for class PolyhedralSurface.
 */
class PolyhedralSurfaceTest extends AbstractTestCase
{
    /**
     * @param string[] $patchesWkt           The WKT of the patches (polygons) that compose the PolyhedralSurface.
     * @param bool     $is3D                 Whether the patches have Z coordinates.
     * @param bool     $isMeasured           Whether the patches have M coordinates.
     * @param string   $polyhedralSurfaceWkt The WKT of the expected PolyhedralSurface.
     */
    #[DataProvider('providerCreate')]
    public function testCreate(array $patchesWkt, bool $is3D, bool $isMeasured, string $polyhedralSurfaceWkt): void
    {
        foreach ([0, 1] as $srid) {
            $instantiatePolygon = fn (string $patch) => Polygon::fromText($patch, $srid);

            $cs = new CoordinateSystem($is3D, $isMeasured, $srid);
            $polyhedralSurface = new PolyhedralSurface($cs, ...array_map($instantiatePolygon, $patchesWkt));
            $this->assertWktEquals($polyhedralSurface, $polyhedralSurfaceWkt, $srid);
        }
    }

    public static function providerCreate(): array
    {
        return [
            [['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 'POLYGON ((1 0, 1 1, 2 1, 2 0, 1 0))'], false, false, 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))'],
            [['POLYGON Z ((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0))', 'POLYGON Z ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0))'], true, false, 'POLYHEDRALSURFACE Z (((0 0 0, 0 0 1, 0 1 1, 0 1 0, 0 0 0)), ((0 0 0, 0 1 0, 1 1 0, 1 0 0, 0 0 0)))'],
            [['POLYGON M ((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0))', 'POLYGON M ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0))'], false, true, 'POLYHEDRALSURFACE M (((1 1 0, 1 1 1, 1 0 1, 1 0 0, 1 1 0)), ((0 1 0, 0 1 1, 1 1 1, 1 1 0, 0 1 0)))'],
            [['POLYGON ZM ((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1))'], true, true, 'POLYHEDRALSURFACE ZM (((1 1 0 1, 1 1 1 2, 1 0 1 3, 1 0 0 4, 1 1 0 1)))'],
        ];
    }

    /**
     * @param string $polyhedralSurface The WKT of the PolyhedralSurface to test.
     * @param int    $numPatches        The expected number of patches.
     */
    #[DataProvider('providerNumPatches')]
    public function testNumPatches(string $polyhedralSurface, int $numPatches): void
    {
        self::assertSame($numPatches, PolyhedralSurface::fromText($polyhedralSurface)->numPatches());
    }

    public static function providerNumPatches(): array
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
     * @param string      $polyhedralSurface The WKT of the PolyhedralSurface to test.
     * @param int         $n                 The patch number.
     * @param string|null $patchN            The WKT of the expected patch, or NULL if an exception is expected.
     * @param int         $srid              The SRID of the geometries.
     */
    #[DataProvider('providerPatchN')]
    public function testPatchN(string $polyhedralSurface, int $n, ?string $patchN, int $srid): void
    {
        if ($patchN === null) {
            $this->expectException(NoSuchGeometryException::class);
        }

        $patch = PolyhedralSurface::fromText($polyhedralSurface, $srid)->patchN($n);
        $this->assertWktEquals($patch, $patchN, $srid);
    }

    public static function providerPatchN(): Generator
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

        foreach ($tests as [$polyhedralSurface, $patches]) {
            foreach ($patches as $n => $patchN) {
                foreach ([0, 1] as $srid) {
                    yield [$polyhedralSurface, $n, $patchN, $srid];
                }
            }
        }
    }

    /**
     * Tests Countable and Traversable interfaces.
     */
    public function testInterfaces(): void
    {
        $polyhedralSurface = PolyhedralSurface::fromText('POLYHEDRALSURFACE(((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))');

        self::assertInstanceOf(Countable::class, $polyhedralSurface);
        self::assertCount(3, $polyhedralSurface);

        self::assertInstanceOf(Traversable::class, $polyhedralSurface);
        self::assertSame([
            $polyhedralSurface->patchN(1),
            $polyhedralSurface->patchN(2),
            $polyhedralSurface->patchN(3),
        ], iterator_to_array($polyhedralSurface));
    }

    /**
     * @param string[] $addedPatchesWkt
     */
    #[DataProvider('providerWithAddedPatches')]
    public function testWithAddedPatches(string $polyhedralSurfaceWkt, array $addedPatchesWkt, string $expectedWkt): void
    {
        $polyhedralSurface = PolyhedralSurface::fromText($polyhedralSurfaceWkt, 1234);
        $actual = $polyhedralSurface->withAddedPatches(
            ...array_map(
                fn (string $wkt) => Polygon::fromText($wkt, 1234),
                $addedPatchesWkt,
            ),
        );

        $this->assertWktEquals($polyhedralSurface, $polyhedralSurfaceWkt, 1234); // ensure immutability
        $this->assertWktEquals($actual, $expectedWkt, 1234);
    }

    public static function providerWithAddedPatches(): array
    {
        return [
            ['POLYHEDRALSURFACE EMPTY', [], 'POLYHEDRALSURFACE EMPTY'],
            ['POLYHEDRALSURFACE EMPTY', ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))'], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)))'],
            ['POLYHEDRALSURFACE EMPTY', ['POLYGON ((0 0, 0 1, 1 1, 1 0, 0 0))', 'POLYGON ((1 0, 1 1, 2 1, 2 0, 1 0))'], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))'],
            ['POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)))', [], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)))'],
            ['POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)))', ['POLYGON ((1 0, 1 1, 2 1, 2 0, 1 0))'], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))'],
            ['POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)))', ['POLYGON ((1 0, 1 1, 2 1, 2 0, 1 0))', 'POLYGON ((2 0, 2 1, 3 1, 3 0, 2 0))'], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))'],
            ['POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))', [], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))'],
            ['POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)))', ['POLYGON ((2 0, 2 1, 3 1, 3 0, 2 0))'], 'POLYHEDRALSURFACE (((0 0, 0 1, 1 1, 1 0, 0 0)), ((1 0, 1 1, 2 1, 2 0, 1 0)), ((2 0, 2 1, 3 1, 3 0, 2 0)))'],

            ['TIN EMPTY', ['TRIANGLE ((0 0, 0 1, 1 1, 0 0))'], 'TIN (((0 0, 0 1, 1 1, 0 0)))'],
            ['TIN EMPTY', ['TRIANGLE ((0 0, 0 1, 1 1, 0 0))', 'TRIANGLE ((1 0, 1 1, 2 1, 1 0))'], 'TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)))'],
            ['TIN (((0 0, 0 1, 1 1, 0 0)))', [], 'TIN (((0 0, 0 1, 1 1, 0 0)))'],
            ['TIN (((0 0, 0 1, 1 1, 0 0)))', ['TRIANGLE ((1 0, 1 1, 2 1, 1 0))'], 'TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)))'],
            ['TIN (((0 0, 0 1, 1 1, 0 0)))', ['TRIANGLE ((1 0, 1 1, 2 1, 1 0))', 'TRIANGLE ((2 0, 2 1, 3 1, 2 0))'], 'TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)), ((2 0, 2 1, 3 1, 2 0)))'],
            ['TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)))', [], 'TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)))'],
            ['TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)))', ['TRIANGLE ((2 0, 2 1, 3 1, 2 0))'], 'TIN (((0 0, 0 1, 1 1, 0 0)), ((1 0, 1 1, 2 1, 1 0)), ((2 0, 2 1, 3 1, 2 0)))'],
        ];
    }
}
