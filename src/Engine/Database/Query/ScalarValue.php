<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Query;

/**
 * Marker class for passing scalar values to the database.
 */
final readonly class ScalarValue
{
    /**
     * @param scalar $value
     */
    public function __construct(
        public mixed $value,
    ) {
    }
}
