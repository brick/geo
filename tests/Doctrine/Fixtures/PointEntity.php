<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Point;

/**
 * @Entity
 * @Table(name="points")
 */
class PointEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private int $id;

    /**
     * @Column(type="point")
     */
    private Point $point;

    public function getPoint() : Point
    {
        return $this->point;
    }

    public function setPoint(Point $point) : void
    {
        $this->point = $point;
    }
}
