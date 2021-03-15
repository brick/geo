<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiPoint;

/**
 * Class MultiPointEntity
 *
 * @Entity
 * @Table(name = "multipoints")
 */
class MultiPointEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="multipoint")
     *
     * @var MultiPoint
     */
    private $multiPoint;

    /**
     * @return MultiPoint
     */
    public function getMultiPoint() : MultiPoint
    {
        return $this->multiPoint;
    }

    /**
     * @param MultiPoint $multiPoint
     *
     * @return void
     */
    public function setMultiPoint(MultiPoint $multiPoint) : void
    {
        $this->multiPoint = $multiPoint;
    }
}
