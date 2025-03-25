<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Query;

/**
 * Marker class for passing binary data to the database.
 */
final readonly class BinaryValue
{
    public function __construct(
        public string $value,
    ) {
    }
}
