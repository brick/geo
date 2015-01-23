<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Geometry;
use Doctrine\ORM\Mapping as ORM;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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