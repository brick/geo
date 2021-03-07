<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Geometry;

/**
 * Represents a geometry parameter sent to the database engine.
 *
 * This object is used to carry a representation of the geometry from the abstract DatabaseEngine to one of its
 * concrete implementations, like PDOEngine or SQLite3Engine.
 *
 * @psalm-immutable
 */
class GeometryParameter
{
    /**
     * The WKT or WKB data.
     *
     * @var string
     */
    public $data;

    /**
     * True for WKB, false for WKT.
     *
     * @var bool
     */
    public $isBinary;

    /**
     * @var int
     */
    public $srid;

    public function __construct(Geometry $geometry, bool $isBinary)
    {
        $this->data     = $isBinary ? $geometry->asBinary() : $geometry->asText();
        $this->isBinary = $isBinary;
        $this->srid     = $geometry->SRID();
    }
}
