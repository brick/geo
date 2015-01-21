<?php

namespace Brick\Geo\Tests\Doctrine\Fixtures;

use Brick\Geo\LineString;
use Brick\Geo\MultiLineString;
use Doctrine\ORM\Mapping as ORM;

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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

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