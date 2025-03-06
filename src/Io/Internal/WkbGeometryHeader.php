<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

/**
 * @internal
 */
final readonly class WkbGeometryHeader
{
    public function __construct(
        public int $geometryType,
        public bool $hasZ,
        public bool $hasM,
        public ?int $srid = null,
    ) {
    }
}
