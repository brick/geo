<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Polygon;

/**
 * @Entity
 * @Table(name="polygons")
 */
class PolygonEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private int $id;

    /**
     * @Column(type="polygon")
     */
    private Polygon $polygon;

    public function getPolygon() : Polygon
    {
        return $this->polygon;
    }

    public function setPolygon(Polygon $polygon) : void
    {
        $this->polygon = $polygon;
    }
}
