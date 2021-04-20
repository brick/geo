<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiPoint;

/**
 * @Entity
 * @Table(name="multipoints")
 */
class MultiPointEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private int $id;

    /**
     * @Column(type="multipoint")
     */
    private MultiPoint $multiPoint;

    public function getMultiPoint() : MultiPoint
    {
        return $this->multiPoint;
    }

    public function setMultiPoint(MultiPoint $multiPoint) : void
    {
        $this->multiPoint = $multiPoint;
    }
}
