<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Result;

use Brick\Geo\Engine\Database\Driver\DatabaseDriver;

final readonly class Value
{
    public function __construct(
        private DatabaseDriver $driver,
        private mixed $value,
    ) {
    }

    public function asBinary() : string
    {
        return $this->driver->convertBinaryResult($this->value);
    }

    public function asString() : string
    {
        return $this->driver->convertStringResult($this->value);
    }

    public function asInt() : int
    {
        return $this->driver->convertIntResult($this->value);
    }

    public function asFloat() : float
    {
        return $this->driver->convertFloatResult($this->value);
    }

    public function asBool() : bool
    {
        return $this->driver->convertBoolResult($this->value);
    }
}
