<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\Geometry;

/**
 * @Entity
 * @Table(name="geometries")
 */
class GeometryEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private int $id;

    /**
     * @Column(type="geometry")
     */
    private Geometry $geometry;

    public function getGeometry() : Geometry
    {
        return $this->geometry;
    }

    public function setGeometry(Geometry $geometry) : void
    {
        $this->geometry = $geometry;
    }
}
