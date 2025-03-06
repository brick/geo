<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * @internal
 */
final readonly class WKBGeometryHeader
{
    public function __construct(
        public int $geometryType,
        public bool $hasZ,
        public bool $hasM,
        public ?int $srid = null,
    ) {
    }
}
