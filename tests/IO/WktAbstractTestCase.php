<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Tests\AbstractTestCase;

use function array_merge;

/**
 * Base class for WKT reader/writer tests.
 */
abstract class WktAbstractTestCase extends AbstractTestCase
{
    final public static function providerWkt(): array
    {
        return array_merge(
            self::providerPointWkt(),
            self::providerLineStringWkt(),
            self::providerCircularStringWkt(),
            self::providerCompoundCurveWkt(),
            self::providerPolygonWkt(),
            self::providerCurvePolygonWkt(),
            self::providerMultiPointWkt(),
            self::providerMultiLineStringWkt(),
            self::providerMultiPolygonWkt(),
            self::providerGeometryCollectionWkt(),
            self::providerPolyhedralSurfaceWkt(),
            self::providerTriangleWkt(),
            self::providerTinWkt(),
        );
    }

    final public static function providerPointWkt(): array
    {
        return [
            ['POINT EMPTY', [], false, false],
            ['POINT Z EMPTY', [], true, false],
            ['POINT M EMPTY', [], false, true],
            ['POINT ZM EMPTY', [], true, true],

            ['POINT(1 2)', [1, 2], false, false],
            ['POINT Z(2 3 4)', [2, 3, 4], true, false],
            ['POINT M(3 4 5)', [3, 4, 5], false, true],
            ['POINT ZM(4 5 6 7)', [4, 5, 6, 7], true, true],
        ];
    }

    final public static function providerLineStringWkt(): array
    {
        return [
            ['LINESTRING EMPTY', [], false, false],
            ['LINESTRING Z EMPTY', [], true, false],
            ['LINESTRING M EMPTY', [], false, true],
            ['LINESTRING ZM EMPTY', [], true, true],

            ['LINESTRING(0 0,1 2,3 4)', [[0, 0], [1, 2], [3, 4]], false, false],
            ['LINESTRING Z(0 1 2,1 2 3,2 3 4)', [[0, 1, 2], [1, 2, 3], [2, 3, 4]], true, false],
            ['LINESTRING M(1 2 3,2 3 4,3 4 5)', [[1, 2, 3], [2, 3, 4], [3, 4, 5]], false, true],
            ['LINESTRING ZM(2 3 4 5,3 4 5 6,4 5 6 7)', [[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], true, true],
        ];
    }

    final public static function providerCircularStringWkt(): array
    {
        return [
            ['CIRCULARSTRING EMPTY', [], false, false],
            ['CIRCULARSTRING Z EMPTY', [], true, false],
            ['CIRCULARSTRING M EMPTY', [], false, true],
            ['CIRCULARSTRING ZM EMPTY', [], true, true],

            ['CIRCULARSTRING(0 0,1 2,3 4)', [[0, 0], [1, 2], [3, 4]], false, false],
            ['CIRCULARSTRING Z(0 1 2,1 2 3,2 3 4)', [[0, 1, 2], [1, 2, 3], [2, 3, 4]], true, false],
            ['CIRCULARSTRING M(1 2 3,2 3 4,3 4 5)', [[1, 2, 3], [2, 3, 4], [3, 4, 5]], false, true],
            ['CIRCULARSTRING ZM(2 3 4 5,3 4 5 6,4 5 6 7)', [[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], true, true],
        ];
    }

    final public static function providerCompoundCurveWkt(): array
    {
        return [
            ['COMPOUNDCURVE EMPTY', [], false, false],
            ['COMPOUNDCURVE Z EMPTY', [], true, false],
            ['COMPOUNDCURVE M EMPTY', [], false, true],
            ['COMPOUNDCURVE ZM EMPTY', [], true, true],

            ['COMPOUNDCURVE((1 2,3 4),CIRCULARSTRING(3 4,5 6,7 8))', [[[1, 2], [3, 4]], [[3, 4], [5, 6], [7, 8]]], false, false],
            ['COMPOUNDCURVE Z((1 2 3,4 5 6),CIRCULARSTRING Z(4 5 6,5 6 7,6 7 8))', [[[1, 2, 3], [4, 5, 6]], [[4, 5, 6], [5, 6, 7], [6, 7, 8]]], true, false],
            ['COMPOUNDCURVE M((1 2 3,2 3 4),CIRCULARSTRING M(2 3 4,5 6 7,8 9 0))', [[[1, 2, 3], [2, 3, 4]], [[2, 3, 4], [5, 6, 7], [8, 9, 0]]], false, true],
            ['COMPOUNDCURVE ZM(CIRCULARSTRING ZM(1 2 3 4,2 3 4 5,3 4 5 6),(3 4 5 6,7 8 9 0))', [[[1, 2, 3, 4], [2, 3, 4, 5], [3, 4, 5, 6]], [[3, 4, 5, 6], [7, 8, 9, 0]]], true, true],
        ];
    }

    final public static function providerPolygonWkt(): array
    {
        return [
            ['POLYGON EMPTY', [], false, false],
            ['POLYGON Z EMPTY', [], true, false],
            ['POLYGON M EMPTY', [], false, true],
            ['POLYGON ZM EMPTY', [], true, true],

            ['POLYGON((0 0,1 2,3 4,0 0))', [[[0, 0], [1, 2], [3, 4], [0, 0]]], false, false],
            ['POLYGON Z((0 1 2,1 2 3,2 3 4,0 1 2))', [[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]], true, false],
            ['POLYGON M((1 2 3,2 3 4,3 4 5,1 2 3))', [[[1, 2, 3], [2, 3, 4], [3, 4, 5], [1, 2, 3]]], false, true],
            ['POLYGON ZM((2 3 4 5,3 4 5 6,4 5 6 7,2 3 4 5))', [[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]], true, true],

            ['POLYGON((0 0,2 0,0 2,0 0),(0 0,1 0,0 1,0 0))', [[[0, 0], [2, 0], [0, 2], [0, 0]], [[0, 0], [1, 0], [0, 1], [0, 0]]], false, false],
            ['POLYGON Z((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], true, false],
            ['POLYGON M((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], false, true],
            ['POLYGON ZM((0 0 1 2,2 0 1 2,0 2 1 2,0 0 1 2),(0 0 1 2,1 0 1 2,0 1 1 2,0 0 1 2))', [[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]], [[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]], true, true],
        ];
    }

    final public static function providerCurvePolygonWkt(): array
    {
        return [
            ['CURVEPOLYGON EMPTY', [], false, false],
            ['CURVEPOLYGON Z EMPTY', [], true, false],
            ['CURVEPOLYGON M EMPTY', [], false, true],
            ['CURVEPOLYGON ZM EMPTY', [], true, true],

            ['CURVEPOLYGON((0 0,0 9,9 9,0 0),COMPOUNDCURVE((1 2,3 4),CIRCULARSTRING(3 4,5 6,7 8,9 0,1 2)))', [[[0, 0], [0, 9], [9, 9], [0, 0]], [[[1, 2], [3, 4]], [[3, 4], [5, 6], [7, 8], [9, 0], [1, 2]]]], false, false],
            ['CURVEPOLYGON Z((0 0 1,0 9 1,9 9 1,0 0 1),CIRCULARSTRING Z(1 1 1,4 7 1,6 5 1,2 3 1,1 1 1))', [[[0, 0, 1], [0, 9, 1], [9, 9, 1], [0, 0, 1]], [[1, 1, 1], [4, 7, 1], [6, 5, 1], [2, 3, 1], [1, 1, 1]]], true, false],
            ['CURVEPOLYGON M(CIRCULARSTRING M(0 0 1,0 9 1,9 9 1,9 0 1,0 0 1),(1 1 1,4 7 1,6 5 1,1 1 1))', [[[0, 0, 1], [0, 9, 1], [9, 9, 1], [9, 0, 1], [0, 0, 1]], [[1, 1, 1], [4, 7, 1], [6, 5, 1], [1, 1, 1]]], false, true],
            ['CURVEPOLYGON ZM(CIRCULARSTRING ZM(1 2 3 4,2 3 4 5,3 4 5 6,4 5 6 7,1 2 3 4),(3 4 5 6,4 5 6 7,9 8 7 6,3 4 5 6))', [[[1, 2, 3, 4], [2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [1, 2, 3, 4]], [[3, 4, 5, 6], [4, 5, 6, 7], [9, 8, 7, 6], [3, 4, 5, 6]]], true, true],
        ];
    }

    final public static function providerMultiPointWkt(): array
    {
        return [
            ['MULTIPOINT EMPTY', [], false, false],
            ['MULTIPOINT Z EMPTY', [], true, false],
            ['MULTIPOINT M EMPTY', [], false, true],
            ['MULTIPOINT ZM EMPTY', [], true, true],

            ['MULTIPOINT(0 0,1 2,3 4)', [[0, 0], [1, 2], [3, 4]], false, false],
            ['MULTIPOINT Z(0 1 2,1 2 3,2 3 4)', [[0, 1, 2], [1, 2, 3], [2, 3, 4]], true, false],
            ['MULTIPOINT M(1 2 3,2 3 4,3 4 5)', [[1, 2, 3], [2, 3, 4], [3, 4, 5]], false, true],
            ['MULTIPOINT ZM(2 3 4 5,3 4 5 6,4 5 6 7)', [[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7]], true, true],
        ];
    }

    final public static function providerMultiLineStringWkt(): array
    {
        return [
            ['MULTILINESTRING EMPTY', [], false, false],
            ['MULTILINESTRING Z EMPTY', [], true, false],
            ['MULTILINESTRING M EMPTY', [], false, true],
            ['MULTILINESTRING ZM EMPTY', [], true, true],

            ['MULTILINESTRING((0 0,1 2,3 4,0 0))', [[[0, 0], [1, 2], [3, 4], [0, 0]]], false, false],
            ['MULTILINESTRING Z((0 1 2,1 2 3,2 3 4,0 1 2))', [[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]], true, false],
            ['MULTILINESTRING M((1 2 3,2 3 4,3 4 5,1 2 3))', [[[1, 2, 3], [2, 3, 4], [3, 4, 5], [1, 2, 3]]], false, true],
            ['MULTILINESTRING ZM((2 3 4 5,3 4 5 6,4 5 6 7,2 3 4 5))', [[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]], true, true],

            ['MULTILINESTRING((0 0,2 0,0 2,0 0),(0 0,1 0,0 1,0 0))', [[[0, 0], [2, 0], [0, 2], [0, 0]], [[0, 0], [1, 0], [0, 1], [0, 0]]], false, false],
            ['MULTILINESTRING Z((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], true, false],
            ['MULTILINESTRING M((0 0 1,2 0 1,0 2 1,0 0 1),(0 0 2,1 0 2,0 1 2,0 0 2))', [[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]], [[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]], false, true],
            ['MULTILINESTRING ZM((0 0 1 2,2 0 1 2,0 2 1 2,0 0 1 2),(0 0 1 2,1 0 1 2,0 1 1 2,0 0 1 2))', [[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]], [[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]], true, true],
        ];
    }

    final public static function providerMultiPolygonWkt(): array
    {
        return [
            ['MULTIPOLYGON EMPTY', [], false, false],
            ['MULTIPOLYGON Z EMPTY', [], true, false],
            ['MULTIPOLYGON M EMPTY', [], false, true],
            ['MULTIPOLYGON ZM EMPTY', [], true, true],

            ['MULTIPOLYGON(((0 0,1 2,3 4,0 0)))', [[[[0, 0], [1, 2], [3, 4], [0, 0]]]], false, false],
            ['MULTIPOLYGON Z(((0 1 2,1 2 3,2 3 4,0 1 2)))', [[[[0, 1, 2], [1, 2, 3], [2, 3, 4], [0, 1, 2]]]], true, false],
            ['MULTIPOLYGON M(((1 2 3,2 3 4,3 4 5,1 2 3)))', [[[[1, 2, 3], [2, 3, 4], [3, 4, 5], [1, 2, 3]]]], false, true],
            ['MULTIPOLYGON ZM(((2 3 4 5,3 4 5 6,4 5 6 7,2 3 4 5)))', [[[[2, 3, 4, 5], [3, 4, 5, 6], [4, 5, 6, 7], [2, 3, 4, 5]]]], true, true],

            ['MULTIPOLYGON(((0 0,2 0,0 2,0 0)),((0 0,1 0,0 1,0 0)))', [[[[0, 0], [2, 0], [0, 2], [0, 0]]], [[[0, 0], [1, 0], [0, 1], [0, 0]]]], false, false],
            ['MULTIPOLYGON Z(((0 0 1,2 0 1,0 2 1,0 0 1)),((0 0 2,1 0 2,0 1 2,0 0 2)))', [[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]]], [[[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]]], true, false],
            ['MULTIPOLYGON M(((0 0 1,2 0 1,0 2 1,0 0 1)),((0 0 2,1 0 2,0 1 2,0 0 2)))', [[[[0, 0, 1], [2, 0, 1], [0, 2, 1], [0, 0, 1]]], [[[0, 0, 2], [1, 0, 2], [0, 1, 2], [0, 0, 2]]]], false, true],
            ['MULTIPOLYGON ZM(((0 0 1 2,2 0 1 2,0 2 1 2,0 0 1 2)),((0 0 1 2,1 0 1 2,0 1 1 2,0 0 1 2)))', [[[[0, 0, 1, 2], [2, 0, 1, 2], [0, 2, 1, 2], [0, 0, 1, 2]]], [[[0, 0, 1, 2], [1, 0, 1, 2], [0, 1, 1, 2], [0, 0, 1, 2]]]], true, true],
        ];
    }

    final public static function providerGeometryCollectionWkt(): array
    {
        return [
            ['GEOMETRYCOLLECTION EMPTY', [], false, false],
            ['GEOMETRYCOLLECTION Z EMPTY', [], true, false],
            ['GEOMETRYCOLLECTION M EMPTY', [], false, true],
            ['GEOMETRYCOLLECTION ZM EMPTY', [], true, true],

            ['GEOMETRYCOLLECTION(POINT(1 2),LINESTRING(2 3,3 4))', [[1, 2], [[2, 3], [3, 4]]], false, false],
            ['GEOMETRYCOLLECTION Z(POINT Z(1 2 3),LINESTRING Z(2 3 4,3 4 5))', [[1, 2, 3], [[2, 3, 4], [3, 4, 5]]], true, false],
            ['GEOMETRYCOLLECTION M(POINT M(1 2 4),LINESTRING M(2 3 5,3 4 6))', [[1, 2, 4], [[2, 3, 5], [3, 4, 6]]], false, true],
            ['GEOMETRYCOLLECTION ZM(POINT ZM(1 2 3 4),LINESTRING ZM(2 3 4 5,3 4 5 6))', [[1, 2, 3, 4], [[2, 3, 4, 5], [3, 4, 5, 6]]], true, true],
        ];
    }

    final public static function providerPolyhedralSurfaceWkt(): array
    {
        return [
            ['POLYHEDRALSURFACE EMPTY', [], false, false],
            ['POLYHEDRALSURFACE Z EMPTY', [], true, false],
            ['POLYHEDRALSURFACE M EMPTY', [], false, true],
            ['POLYHEDRALSURFACE ZM EMPTY', [], true, true],

            ['POLYHEDRALSURFACE(((0 0,0 1,1 1,1 0,0 0)),((1 0,1 1,2 1,2 0,1 0)))', [[[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]], [[[1, 0], [1, 1], [2, 1], [2, 0], [1, 0]]]], false, false],
            ['POLYHEDRALSURFACE Z(((0 0 0,0 0 1,0 1 1,0 1 0,0 0 0)),((0 0 0,0 1 0,1 1 0,1 0 0,0 0 0)))', [[[[0, 0, 0], [0, 0, 1], [0, 1, 1], [0, 1, 0], [0, 0, 0]]], [[[0, 0, 0], [0, 1, 0], [1, 1, 0], [1, 0, 0], [0, 0, 0]]]], true, false],
            ['POLYHEDRALSURFACE M(((1 1 0,1 1 1,1 0 1,1 0 0,1 1 0)),((0 1 0,0 1 1,1 1 1,1 1 0,0 1 0)))', [[[[1, 1, 0], [1, 1, 1], [1, 0, 1], [1, 0, 0], [1, 1, 0]]], [[[0, 1, 0], [0, 1, 1], [1, 1, 1], [1, 1, 0], [0, 1, 0]]]], false, true],
            ['POLYHEDRALSURFACE ZM(((1 1 0 1,1 1 1 2,1 0 1 3,1 0 0 4,1 1 0 1)),((0 1 0 5,0 1 1 6,1 1 1 7,1 1 0 8,0 1 0 5)))', [[[[1, 1, 0, 1], [1, 1, 1, 2], [1, 0, 1, 3], [1, 0, 0, 4], [1, 1, 0, 1]]], [[[0, 1, 0, 5], [0, 1, 1, 6], [1, 1, 1, 7], [1, 1, 0, 8], [0, 1, 0, 5]]]], true, true],
        ];
    }

    final public static function providerTriangleWkt(): array
    {
        return [
            ['TRIANGLE EMPTY', [], false, false],
            ['TRIANGLE Z EMPTY', [], true, false],
            ['TRIANGLE M EMPTY', [], false, true],
            ['TRIANGLE ZM EMPTY', [], true, true],

            ['TRIANGLE((0 0,0 1,1 1,0 0))', [[[0, 0], [0, 1], [1, 1], [0, 0]]], false, false],
            ['TRIANGLE Z((0 0 2,0 1 2,1 1 2,0 0 2))', [[[0, 0, 2], [0, 1, 2], [1, 1, 2], [0, 0, 2]]], true, false],
            ['TRIANGLE M((1 1 1,1 2 1,2 2 1,1 1 1))', [[[1, 1, 1], [1, 2, 1], [2, 2, 1], [1, 1, 1]]], false, true],
            ['TRIANGLE ZM((1 1 1 1,1 2 1 2,2 2 1 3,1 1 1 1))', [[[1, 1, 1, 1], [1, 2, 1, 2], [2, 2, 1, 3], [1, 1, 1, 1]]], true, true],
        ];
    }

    final public static function providerTinWkt(): array
    {
        return [
            ['TIN EMPTY', [], false, false],
            ['TIN Z EMPTY', [], true, false],
            ['TIN M EMPTY', [], false, true],
            ['TIN ZM EMPTY', [], true, true],

            ['TIN(((0 0,0 1,1 0,0 0)),((0 1,1 1,1 0,0 1)))', [[[[0, 0], [0, 1], [1, 0], [0, 0]]], [[[0, 1], [1, 1], [1, 0], [0, 1]]]], false, false],
            ['TIN Z(((0 0 0,0 0 1,0 1 0,0 0 0)),((0 0 0,0 1 0,1 1 0,0 0 0)))', [[[[0, 0, 0], [0, 0, 1], [0, 1, 0], [0, 0, 0]]], [[[0, 0, 0], [0, 1, 0], [1, 1, 0], [0, 0, 0]]]], true, false],
            ['TIN M(((0 0 0,0 0 1,0 1 0,0 0 0)),((0 0 0,0 1 0,1 1 0,0 0 0)))', [[[[0, 0, 0], [0, 0, 1], [0, 1, 0], [0, 0, 0]]], [[[0, 0, 0], [0, 1, 0], [1, 1, 0], [0, 0, 0]]]], false, true],
            ['TIN ZM(((0 0 0 2,0 0 1 2,0 1 0 2,0 0 0 2)),((0 0 0 3,0 1 0 3,1 1 0 3,0 0 0 3)))', [[[[0, 0, 0, 2], [0, 0, 1, 2], [0, 1, 0, 2], [0, 0, 0, 2]]], [[[0, 0, 0, 3], [0, 1, 0, 3], [1, 1, 0, 3], [0, 0, 0, 3]]]], true, true],
        ];
    }

    /**
     * Some geometries, like CompoundCurve and MultiPoint, accept two different syntaxes.
     * This test ensures that WKT readers can handle both.
     */
    final public static function providerAlternativeSyntaxWkt(): array
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
            ], [
                'MULTIPOINT(1 2,3 4)',
                'MULTIPOINT((1 2),(3 4))',
            ], [
                'MULTIPOINT Z(1 2 3,4 5 6)',
                'MULTIPOINT Z((1 2 3),(4 5 6))',
            ], [
                'MULTIPOINT M(1 2 3,4 5 6)',
                'MULTIPOINT M((1 2 3),(4 5 6))',
            ], [
                'MULTIPOINT ZM(1 2 3 4,5 6 7 8)',
                'MULTIPOINT ZM((1 2 3 4),(5 6 7 8))',
            ], [
                'MULTIPOINT(1 2,3 4)',
                'MULTIPOINT(1 2,(3 4))',
            ], [
                'MULTIPOINT Z(1 2 3,4 5 6)',
                'MULTIPOINT Z(1 2 3,(4 5 6))',
            ], [
                'MULTIPOINT M(1 2 3,4 5 6)',
                'MULTIPOINT M((1 2 3),4 5 6)',
            ], [
                'MULTIPOINT ZM(1 2 3 4,5 6 7 8)',
                'MULTIPOINT ZM((1 2 3 4),5 6 7 8)',
            ],
        ];
    }
}
