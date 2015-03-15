<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Point;

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
