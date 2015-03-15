<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\LineString;

/**
 * Class LineStringEntity
 *
 * @Entity
 * @Table(name = "linestrings")
 */
class LineStringEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="linestring")
     *
     * @var LineString
     */
    private $lineString;

    /**
     * @return LineString
     */
    public function getLineString()
    {
        return $this->lineString;
    }

    /**
     * @param LineString $lineString
     */
    public function setLineString($lineString)
    {
        $this->lineString = $lineString;
    }
}
