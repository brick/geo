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
                [false, false],
                [false, false]
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
                false,
                false
            ],
            [
                '{"type":"Point","coordinates":[1,2]}',
                [1, 2],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Point","coordinates":[1,2]}}',
                [1, 2],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"MultiPoint","coordinates":[[1,0],[1,1]]}',
                [[1, 0], [1, 1]],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"MultiPoint","coordinates":[[1,0],[1,1]]}}',
                [[1, 0], [1, 1]],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"LineString","coordinates":[[1,2],[3,4]]}',
                [[1, 2], [3, 4]],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"LineString","coordinates":[[1,2],[3,4]]}}',
                [[1, 2], [3, 4]],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"MultiLineString","coordinates":[[[1,0],[1,1]],[[2,2],[1,3]]]}',
                [[[1, 0], [1, 1]], [[2, 2], [1, 3]]],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"MultiLineString","coordinates":[[[1,0],[1,1]],[[2,2],[1,3]]]}}',
                [[[1, 0], [1, 1]], [[2, 2], [1, 3]]],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}',
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false,
                false
            ],
            [
                '{"type":"Polygon","coordinates":[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]}',
                [
                    [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                    [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                ],
                false,
                false
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
                false,
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}}',
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false,
                false
            ],
            [
                '{"type":"Feature","geometry":{"type":"Polygon","coordinates":[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]}}',
                [
                    [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                    [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                ],
                false,
                false
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
                false,
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
                false,
                false
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
                false,
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
                false,
                false
            ]
        ];
    }
}
