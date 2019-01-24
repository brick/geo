<?php

namespace Brick\Geo\Tests\IO;

use Brick\Geo\Tests\AbstractTestCase;

/**
 * Base class for GeoJSON reader/writer tests.
 */
abstract class GeoJSONAbstractTest extends AbstractTestCase
{
    /**
     * @return array
     */
    public function providerGeometryGeoJSON() : array
    {
        return array_merge(
            $this->providerGeometryPointGeoJSON(),
            $this->providerGeometryMultiPointGeoJSON(),
            $this->providerGeometryLineStringGeoJSON(),
            $this->providerGeometryMultiLineStringGeoJSON(),
            $this->providerGeometryPolygonGeoJSON(),
            $this->providerGeometryMultiPolygonGeoJSON()
        );
    }

    /**
     * @return array
     */
    public function providerFeatureGeoJSON() : array
    {
        return array_merge(
            $this->providerFeaturePointGeoJSON(),
            $this->providerFeatureMultiPointGeoJSON(),
            $this->providerFeatureLineStringGeoJSON(),
            $this->providerFeatureMultiLineStringGeoJSON(),
            $this->providerFeaturePolygonGeoJSON(),
            $this->providerFeatureMultiPolygonGeoJSON()
        );
    }

    /**
     * @return array
     */
    public function providerFeatureCollectionGeoJSON() : array
    {
        return [
            [
                '{"type":"FeatureCollection","features":[{"type":"Feature","geometry":{"type":"Point","coordinates":[1,1]}},{"type":"Feature","geometry":{"type":"Point","coordinates":[1,3]}}]}',
                [[1, 1], [1, 3]],
                [false, false]
            ],
            [
                '{"type":"FeatureCollection","features":[{"type":"Feature","geometry":{"type":"Point","coordinates":[1,1,1]}},{"type":"Feature","geometry":{"type":"Point","coordinates":[1,3,2]}}]}',
                [[1, 1, 1], [1, 3, 2]],
                [true, true]
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerGeometryPointGeoJSON() : array
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

    /**
     * @return array
     */
    public function providerFeaturePointGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","geometry":{"type":"Point","coordinates":[]}}',
                [],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Point","coordinates":[1,2]}}',
                [1, 2],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Point","coordinates":[1,2,0]}}',
                [1, 2, 0],
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerGeometryMultiPointGeoJSON() : array
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

    /**
     * @return array
     */
    public function providerFeatureMultiPointGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[]}}',
                [],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[1,0],[1,1]]}}',
                [[1, 0], [1, 1]],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[1,0,1],[1,1,0]]}}',
                [[1, 0, 1], [1, 1, 0]],
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerGeometryLineStringGeoJSON() : array
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

    /**
     * @return array
     */
    public function providerFeatureLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","geometry":{"type":"LineString","coordinates":[]}}',
                [],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"LineString","coordinates":[[1,2],[3,4]]}}',
                [[1, 2], [3, 4]],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"LineString","coordinates":[[1,2,1],[3,4,1]]}}',
                [[1, 2, 1], [3, 4, 1]],
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerGeometryMultiLineStringGeoJSON() : array
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

    /**
     * @return array
     */
    public function providerFeatureMultiLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","geometry":{"type":"MultiLineString","coordinates":[]}}',
                [],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"MultiLineString","coordinates":[[[1,0,1],[1,1,1]],[[2,2,2],[1,3,3]]]}}',
                [[[1, 0, 1], [1, 1, 1]], [[2, 2, 2], [1, 3, 3]]],
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerGeometryPolygonGeoJSON() : array
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

    /**
     * @return array
     */
    public function providerFeaturePolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[]}}',
                [],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}}',
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]}}',
                [
                    [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                    [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                ],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[0,0,1],[1,2,1],[3,4,1],[0,0,1]]]}}',
                [[[0, 0, 1], [1, 2, 1], [3, 4, 1], [0, 0, 1]]],
                true
            ],
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[1000,0,1],[1010,0,1],[1010,10,1],[1000,10,1],[1000,0,1]],[[1002,2,2],[1008,2,2],[1008,8,2],[1002,8,2],[1002,2,2]]]}}',
                [
                    [[1000, 0, 1], [1010, 0, 1], [1010, 10, 1], [1000, 10, 1], [1000, 0, 1]],
                    [[1002, 2, 2], [1008, 2, 2], [1008, 8, 2], [1002, 8, 2], [1002, 2, 2]]
                ],
                true
            ]
        ];
    }

    /**
     * @return array
     */
    public function providerGeometryMultiPolygonGeoJSON() : array
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

    /**
     * @return array
     */
    public function providerFeatureMultiPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[]}}',
                [],
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[1,2],[1,2],[1,3],[1,2]]],[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]]}}',
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
                '{"type":"Feature","geometry":{"type":"MultiPolygon","coordinates":[[[[1,2,1],[1,2,1],[1,3,1],[1,2,1]]],[[[1000,0,2],[1010,0,2],[1010,10,2],[1000,10,2],[1000,0,2]],[[1002,2,3],[1008,2,3],[1008,8,3],[1002,8,3],[1002,2,3]]]]}}',
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
}
