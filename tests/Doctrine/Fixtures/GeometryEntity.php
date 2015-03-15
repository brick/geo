<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Geometry;

/**
 * Class GeometryEntity
 *
 * @Entity
 * @Table(name = "geometries")
 */
class GeometryEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="geometry")
     *
     * @var Geometry
     */
    private $geometry;

    /**
     * @return Geometry
     */
    public function getGeometry()
    {
        return $this->geometry;
    }

    /**
     * @param Geometry $geometry
     */
    public function setGeometry($geometry)
    {
        $this->geometry = $geometry;
    }
}
