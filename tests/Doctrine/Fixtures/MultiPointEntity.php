<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiPoint;
use Doctrine\ORM\Mapping as ORM;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return MultiPoint
     */
    public function getMultiPoint()
    {
        return $this->multiPoint;
    }

    /**
     * @param MultiPoint $multiPoint
     */
    public function setMultiPoint($multiPoint)
    {
        $this->multiPoint = $multiPoint;
    }
}