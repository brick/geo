<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\MultiLineString;

/**
 * Class MultiLineStringEntity
 *
 * @Entity
 * @Table(name = "multilinestrings")
 */
class MultiLineStringEntity {

    /**
     * @Id
     * @Column(type="integer")
     * @GeneratedValue
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="multilinestring")
     *
     * @var MultiLineString
     */
    private $multiLineString;

    /**
     * @return MultiLineString
     */
    public function getMultiLineString()
    {
        return $this->multiLineString;
    }

    /**
     * @param MultiLineString $multiLineString
     */
    public function setMultiLineString($multiLineString)
    {
        $this->multiLineString = $multiLineString;
    }
}
