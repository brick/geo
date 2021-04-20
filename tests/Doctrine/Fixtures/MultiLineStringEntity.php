<?php

declare(strict_types=1);

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiLineString;

/**
 * @Entity
 * @Table(name="multilinestrings")
 */
class MultiLineStringEntity
{
    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     */
    private int $id;

    /**
     * @Column(type="multilinestring")
     */
    private MultiLineString $multiLineString;

    public function getMultiLineString() : MultiLineString
    {
        return $this->multiLineString;
    }

    public function setMultiLineString(MultiLineString $multiLineString) : void
    {
        $this->multiLineString = $multiLineString;
    }
}
