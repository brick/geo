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
    public function providerGeoJSON() : array
    {
        return array_merge(
            $this->providerPointGeoJSON(),
            $this->providerMultiPointGeoJSON(),
            $this->providerLineStringGeoJSON(),
            $this->providerMultiLineStringGeoJSON(),
            $this->providerPolygonGeoJSON(),
            $this->providerMultiPolygonGeoJSON()
        );
    }

    /**
     * @return array
     */
    public function providerPointGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"Point","coordinates":[]}}',
                [],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"Point","coordinates":[1,2]}}',
                [1, 2],
                false,
                false
            ],
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
    public function providerMultiPointGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"MultiPoint","coordinates":[]}}',
                [],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"MultiPoint","coordinates":[[1,0],[1,1]]}}',
                [[1, 0], [1, 1]],
                false,
                false
            ],
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
    public function providerLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"LineString","coordinates":[]}}',
                [],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"LineString","coordinates":[[1,2],[3,4]]}}',
                [[1, 2], [3, 4]],
                false,
                false
            ],
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
    public function providerMultiLineStringGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"MultiLineString","coordinates":[]}}',
                [],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"MultiLineString","coordinates":[[[1,0],[1,1]],[[2,2],[1,3]]]}}',
                [[[1, 0], [1, 1]], [[2, 2], [1, 3]]],
                false,
                false
            ],
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
    public function providerPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[]}}',
                [],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}}',
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"Polygon","coordinates":[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]}}',
                [
                    [[1000, 0], [1010, 0], [1010, 10], [1000, 10], [1000, 0]],
                    [[1002, 2], [1008, 2], [1008, 8], [1002, 8], [1002, 2]]
                ],
                false,
                false
            ],
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
    public function providerMultiPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"MultiPolygon","coordinates":[]}}',
                [],
                false,
                false
            ],
            [
                '{"type":"Feature","properties":{},"geometry":{"type":"MultiPolygon","coordinates":[[[[1,2],[1,2],[1,3],[1,2]]],[[[1000,0],[1010,0],[1010,10],[1000,10],[1000,0]],[[1002,2],[1008,2],[1008,8],[1002,8],[1002,2]]]]}}',
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
            ],
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
}
