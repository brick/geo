<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

/**
 * @internal
 */
final class WKBGeometryHeader
{
    public function __construct(
        public readonly int $geometryType,
        public readonly bool $hasZ,
        public readonly bool $hasM,
        public readonly ?int $srid = null,
    ) {
    }
}
