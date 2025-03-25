<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Result;

use Brick\Geo\Engine\Database\Driver\DatabaseDriver;
use Brick\Geo\Exception\GeometryEngineException;

final readonly class Row
{
    /**
     * @param list<mixed> $values
     */
    public function __construct(
        private DatabaseDriver $driver,
        private array $values,
    ) {
    }

    public function get(int $index) : Value
    {
        if (! array_key_exists($index, $this->values)) {
            throw new GeometryEngineException(sprintf('Column %d not found in Result.', $index));
        }

        return new Value($this->driver, $this->values[$index]);
    }
}
