<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Point;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class PointEntity
 *
 * @Entity
 * @Table(name = "points")
 */
class PointEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="point")
     *
     * @var Point
     */
    private $point;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Point
     */
    public function getPoint()
    {
        return $this->point;
    }

    /**
     * @param Point $point
     */
    public function setPoint($point)
    {
        $this->point = $point;
    }
}