<?php

declare(strict_types=1);

namespace Brick\Geo\IO\Internal;

/**
 * @internal
 */
final class WkbGeometryHeader
{
    public function __construct(
        public readonly int $geometryType,
        public readonly bool $hasZ,
        public readonly bool $hasM,
        public readonly ?int $srid = null,
    ) {
    }
}
