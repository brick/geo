<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Polygon;
use Doctrine\ORM\Mapping as ORM;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Polygon
     */
    public function getPolygon()
    {
        return $this->polygon;
    }

    /**
     * @param Polygon $polygon
     */
    public function setPolygon($polygon)
    {
        $this->polygon = $polygon;
    }
}