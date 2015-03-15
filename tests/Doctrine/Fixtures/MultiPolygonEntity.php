<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiPolygon;

/**
 * Class MultiPolygonEntity
 *
 * @Entity
 * @Table(name = "multipolygons")
 */
class MultiPolygonEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="multipolygon")
     *
     * @var MultiPolygon
     */
    private $multiPolygon;

    /**
     * @return MultiPolygon
     */
    public function getMultiPolygon()
    {
        return $this->multiPolygon;
    }

    /**
     * @param MultiPolygon $multiPolygon
     */
    public function setMultiPolygon($multiPolygon)
    {
        $this->multiPolygon = $multiPolygon;
    }
}
