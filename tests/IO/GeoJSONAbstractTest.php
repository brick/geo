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
            $this->providerLineStringGeoJSON(),
            $this->providerPolygonGeoJSON()
        );
    }

    /**
     * @return array
     */
    public function providerPointGeoJSON() : array
    {
        return [
            ['{"type":"Point","coordinates":[]}', [], false, false],
            ['{"type":"Point","coordinates":[1,2]}', [1, 2], false, false]
        ];
    }

    /**
     * @return array
     */
    public function providerLineStringGeoJSON() : array
    {
        return [
            ['{"type":"LineString","coordinates":[]}', [], false, false],
            ['{"type":"LineString","coordinates":[[1,2],[3,4]]}', [[1, 2], [3, 4]], false, false]
        ];
    }

    /**
     * @return array
     */
    public function providerPolygonGeoJSON() : array
    {
        return [
            [
                '{"type":"Polygon","coordinates":[[[0,0],[1,2],[3,4],[0,0]]]}',
                [[[0, 0], [1, 2], [3, 4], [0, 0]]],
                false,
                false
            ]
        ];
    }
}
