<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Polygon;

/**
 * Class PolygonEntity
 *
 * @Entity
 * @Table(name = "polygons")
 */
class PolygonEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="polygon")
     *
     * @var Polygon
     */
    private $polygon;

    /**
     * @return Polygon
     */
    public function getPolygon() : Polygon
    {
        return $this->polygon;
    }

    /**
     * @param Polygon $polygon
     *
     * @return void
     */
    public function setPolygon(Polygon $polygon) : void
    {
        $this->polygon = $polygon;
    }
}
