<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Tests\AbstractTestCase;

/**
 * Base class for GeoJSON reader/writer tests.
 */
abstract class GeoJSONAbstractTestCase extends AbstractTestCase
{
    public static function providerGeometryGeoJSON() : array
    {
        return array_merge(
            self::providerGeometryPointGeoJSON(),
            self::providerGeometryMultiPointGeoJSON(),
            self::providerGeometryLineStringGeoJSON(),
            self::providerGeometryMultiLineStringGeoJSON(),
            self::providerGeometryPolygonGeoJSON(),
            self::providerGeometryMultiPolygonGeoJSON(),
            self::providerGeometryCollectionGeoJSON()
        );
    }

    public static function providerFeatureGeoJSON() : array
    {
        return array_merge(
            self::providerFeatureNoGeometry(),
            self::providerFeaturePointGeoJSON(),
            self::providerFeatureMultiPointGeoJSON(),
            self::providerFeatureLineStringGeoJSON(),
            self::providerFeatureMultiLineStringGeoJSON(),
            self::providerFeaturePolygonGeoJSON(),
            self::providerFeatureMultiPolygonGeoJSON(),
            self::providerFeatureGeometryCollectionGeoJSON(),
        );
    }

    public static function providerFeatureNoGeometry() : array
    {
        return [
            [
                '{"type":"Feature","properties":null,"geometry":null}',
                null,
                null,
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar","bar":"baz"},"geometry":null}',
                (object) ['foo' => 'bar', 'bar' => 'baz'],
                null,
                false
            ],
        ];
    }

    public static function providerFeatureCollectionGeoJSON() : array
    {
        return [
            [
                '{"type":"FeatureCollection","features":[{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Point","coordinates":[1,1]}},{"type":"Feature","properties":null,"geometry":{"type":"Point","coordinates":[1,3]}}]}',
                [(object) ['foo' => 'bar'], null],
                [[1, 1], [1, 3]],
                [false, false]
            ],
            [
                '{"type":"FeatureCollection","features":[{"type":"Feature","properties":null,"geometry":{"type":"Point","coordinates":[1,1,1]}},{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Point","coordinates":[1,3,2]}}]}',
                [null, (object) ['foo' => 'bar']],
                [[1, 1, 1], [1, 3, 2]],
                [true, true]
            ]
        ];
    }

    public static function providerGeometryPointGeoJSON() : array
    {
        return [
            [
                '{"type":"Point","coordinates":[]}',
                [],
                false
            ],
            [
                '{"type":"Point","coordinates":[1,2]}',
                [1, 2],
                false
            ],
            [
                '{"type":"Point","coordinates":[1,2,0]}',
                [1, 2, 0],
                true
            ]
        ];
    }

    public static function providerFeaturePointGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Point","coordinates":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar","bar":"baz"},"geometry":{"type":"Point","coordinates":[1,2]}}',
                (object) ['foo' => 'bar', 'bar' => 'baz'],
                [1, 2],
                false
            ],
            [
                '{"type":"Feature","properties":null,"geometry":{"type":"Point","coordinates":[1,2,0]}}',
                null,
                [1, 2, 0],
                true
            ]
        ];
    }

    public static function providerGeometryMultiPointGeoJSON() : array
    {
        return [
            [
                '{"type":"MultiPoint","coordinates":[]}',
                [],
                false
            ],
            [
                '{"type":"MultiPoint","coordinates":[[1,0],[1,1]]}',
                [[1, 0], [1, 1]],
                false
            ],
            [
                '{"type":"MultiPoint","coordinates":[[1,0,1],[1,1,0]]}',
                [[1, 0, 1], [1, 1, 0]],
                true
            ]
        ];
    }

    public static function providerFeatureMultiPointGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiPoint","coordinates":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiPoint","coordinates":[[1,0],[1,1]]}}',
                (object) ['foo' => 'bar'],
                [[1, 0], [1, 1]],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiPoint","coordinates":[[1,0,1],[1,1,0]]}}',
                (object) ['foo' => 'bar'],
                [[1, 0, 1], [1, 1, 0]],
                true
            ]
        ];
    }

    public static function providerGeometryLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"LineString","coordinates":[]}',
                [],
                false
            ],
            [
                '{"type":"LineString","coordinates":[[1,2],[3,4]]}',
                [[1, 2], [3, 4]],
                false
            ],
            [
                '{"type":"LineString","coordinates":[[1,2,1],[3,4,1]]}',
                [[1, 2, 1], [3, 4, 1]],
                true
            ]
        ];
    }

    public static function providerFeatureLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"LineString","coordinates":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"LineString","coordinates":[[1,2],[3,4]]}}',
                (object) ['foo' => 'bar'],
                [[1, 2], [3, 4]],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"LineString","coordinates":[[1,2,1],[3,4,1]]}}',
                (object) ['foo' => 'bar'],
                [[1, 2, 1], [3, 4, 1]],
                true
            ]
        ];
    }

    public static function providerGeometryMultiLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"MultiLineString","coordinates":[]}',
                [],
                false
            ],
            [
                '{"type":"MultiLineString","coordinates":[[[1,0],[1,1]],[[2,2],[1,3]]]}',
                [[[1, 0], [1, 1]], [[2, 2], [1, 3]]],
                false
            ],
            [
                '{"type":"MultiLineString","coordinates":[[[1,0,1],[1,1,1]],[[2,2,2],[1,3,3]]]}',
                [[[1, 0, 1], [1, 1, 1]], [[2, 2, 2], [1, 3, 3]]],
                true
            ]
        ];
    }

    public static function providerFeatureMultiLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiLineString","coordinates":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiLineString","coordinates":[[[1,0,1],[1,1,1]],[[2,2,2],[1,3,3]]]}}',
                (object) ['foo' => 'bar'],
                [[[1, 0, 1], [1, 1, 1]], [[2, 2, 2], [1, 3, 3]]],
                true
            ]
        ];
    }

    public static function providerGeometryPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Polygon","coordinates":[]}',
                [],
                false
            ],
            [
                '{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}',
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false
            ],
            [
                '{"type":"Polygon","coordinates":[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]}',
                [
                    [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                    [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                ],
                false
            ],
            [
                '{"type":"Polygon","coordinates":[[[0,0,1],[1,2,1],[3,4,1],[0,0,1]]]}',
                [[[0, 0, 1], [1, 2, 1], [3, 4, 1], [0, 0, 1]]],
                true
            ],
            [
                '{"type":"Polygon","coordinates":[[[1000,0,1],[1010,0,1],[1010,10,1],[1000,10,1],[1000,0,1]],[[1002,2,2],[1008,2,2],[1008,8,2],[1002,8,2],[1002,2,2]]]}',
                [
                    [[1000, 0, 1], [1010, 0, 1], [1010, 10, 1], [1000, 10, 1], [1000, 0, 1]],
                    [[1002, 2, 2], [1008, 2, 2], [1008, 8, 2], [1002, 8, 2], [1002, 2, 2]]
                ],
                true
            ],
        ];
    }

    public static function providerFeaturePolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Polygon","coordinates":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}}',
                (object) ['foo' => 'bar'],
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Polygon","coordinates":[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]}}',
                (object) ['foo' => 'bar'],
                [
                    [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                    [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                ],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Polygon","coordinates":[[[0,0,1],[1,2,1],[3,4,1],[0,0,1]]]}}',
                (object) ['foo' => 'bar'],
                [[[0, 0, 1], [1, 2, 1], [3, 4, 1], [0, 0, 1]]],
                true
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"Polygon","coordinates":[[[1000,0,1],[1010,0,1],[1010,10,1],[1000,10,1],[1000,0,1]],[[1002,2,2],[1008,2,2],[1008,8,2],[1002,8,2],[1002,2,2]]]}}',
                (object) ['foo' => 'bar'],
                [
                    [[1000, 0, 1], [1010, 0, 1], [1010, 10, 1], [1000, 10, 1], [1000, 0, 1]],
                    [[1002, 2, 2], [1008, 2, 2], [1008, 8, 2], [1002, 8, 2], [1002, 2, 2]]
                ],
                true
            ]
        ];
    }

    public static function providerGeometryMultiPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"MultiPolygon","coordinates":[]}',
                [],
                false
            ],
            [
                '{"type":"MultiPolygon","coordinates":[[[[1,2],[1,2],[1,3],[1,2]]],[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]]}',
                [
                    [
                        [[1, 2], [1, 2], [1, 3], [1, 2]]
                    ],
                    [
                        [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                        [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                    ]
                ],
                false
            ],
            [
                '{"type":"MultiPolygon","coordinates":[[[[1,2,1],[1,2,1],[1,3,1],[1,2,1]]],[[[1000,0,2],[1010,0,2],[1010,10,2],[1000,10,2],[1000,0,2]],[[1002,2,3],[1008,2,3],[1008,8,3],[1002,8,3],[1002,2,3]]]]}',
                [
                    [
                        [[1, 2, 1], [1, 2, 1], [1, 3, 1], [1, 2, 1]]
                    ],
                    [
                        [[1000, 0, 2], [1010, 0, 2], [1010, 10, 2], [1000, 10, 2], [1000, 0, 2]],
                        [[1002, 2, 3], [1008, 2, 3], [1008, 8, 3], [1002, 8, 3], [1002, 2, 3]]
                    ]
                ],
                true
            ]
        ];
    }

    public static function providerFeatureMultiPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiPolygon","coordinates":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiPolygon","coordinates":[[[[1,2],[1,2],[1,3],[1,2]]],[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]]}}',
                (object) ['foo' => 'bar'],
                [
                    [
                        [[1, 2], [1, 2], [1, 3], [1, 2]]
                    ],
                    [
                        [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                        [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                    ]
                ],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"MultiPolygon","coordinates":[[[[1,2,1],[1,2,1],[1,3,1],[1,2,1]]],[[[1000,0,2],[1010,0,2],[1010,10,2],[1000,10,2],[1000,0,2]],[[1002,2,3],[1008,2,3],[1008,8,3],[1002,8,3],[1002,2,3]]]]}}',
                (object) ['foo' => 'bar'],
                [
                    [
                        [[1, 2, 1], [1, 2, 1], [1, 3, 1], [1, 2, 1]]
                    ],
                    [
                        [[1000, 0, 2], [1010, 0, 2], [1010, 10, 2], [1000, 10, 2], [1000, 0, 2]],
                        [[1002, 2, 3], [1008, 2, 3], [1008, 8, 3], [1002, 8, 3], [1002, 2, 3]]
                    ]
                ],
                true
            ]
        ];
    }

    public static function providerGeometryCollectionGeoJSON() : array
    {
        return [
            [
                '{"type":"GeometryCollection","geometries":[]}',
                [],
                false
            ],
            [
                '{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[0,1]},{"type":"LineString","coordinates":[[0,1],[1,1]]}]}',
                [
                    [0,1],
                    [[0,1],[1,1]]
                ],
                false
            ],
            [
                '{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[0,1,2]},{"type":"LineString","coordinates":[[0,1,2],[1,1,3]]}]}',
                [
                    [0,1,2],
                    [[0,1,2],[1,1,3]]
                ],
                true
            ],
        ];
    }

    public static function providerFeatureGeometryCollectionGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"GeometryCollection","geometries":[]}}',
                (object) ['foo' => 'bar'],
                [],
                false
            ],
            [
                '{"type":"Feature","properties":{"foo":"bar"},"geometry":{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[0,1]},{"type":"LineString","coordinates":[[0,1],[1,1]]}]}}',
                (object) ['foo' => 'bar'],
                [
                    [0,1],
                    [[0,1],[1,1]]
                ],
                false
            ],
            [
                '{"type":"Feature","properties":null,"geometry":{"type":"GeometryCollection","geometries":[{"type":"Point","coordinates":[0,1,2]},{"type":"LineString","coordinates":[[0,1,2],[1,1,3]]}]}}',
                null,
                [
                    [0,1,2],
                    [[0,1,2],[1,1,3]]
                ],
                true
            ]
        ];
    }
}
