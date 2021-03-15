<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiPolygon;

/**
 * Class MultiPolygonEntity
 *
 * @Entity
 * @Table(name = "multipolygons")
 */
class MultiPolygonEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="multipolygon")
     *
     * @var MultiPolygon
     */
    private $multiPolygon;

    /**
     * @return MultiPolygon
     */
    public function getMultiPolygon() : MultiPolygon
    {
        return $this->multiPolygon;
    }

    /**
     * @param MultiPolygon $multiPolygon
     *
     * @return void
     */
    public function setMultiPolygon(MultiPolygon $multiPolygon) : void
    {
        $this->multiPolygon = $multiPolygon;
    }
}
