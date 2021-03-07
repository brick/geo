<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * @psalm-immutable
 */
class WKBGeometryHeader
{
    public int $geometryType;
    public bool $hasZ;
    public bool $hasM;
    public ?int $srid;

    public function __construct(int $geometryType, bool $hasZ, bool $hasM, ?int $srid = null)
    {
        $this->geometryType = $geometryType;
        $this->hasZ         = $hasZ;
        $this->hasM         = $hasM;
        $this->srid         = $srid;
    }
}
