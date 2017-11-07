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
    public function getPoint() : Point
    {
        return $this->point;
    }

    /**
     * @param Point $point
     *
     * @return void
     */
    public function setPoint(Point $point) : void
    {
        $this->point = $point;
    }
}
