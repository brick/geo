<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Driver;

use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Engine\Database\Result\Row;
use Brick\Geo\Exception\GeometryEngineException;

interface DatabaseDriver
{
    /**
     * Executes a SQL query and returns exactly one row.
     *
     * Accepts one or more query components where each element can be:
     * - a string (inserted directly into the query),
     * - a BinaryValue or ScalarValue instance (typically replaced with a placeholder
     *   and bound as a prepared statement parameter).
     *
     * Example: executeQuery('SELECT ST_Length(ST_GeomFromEWKB(', new BinaryValue($ewkb), '))')
     *
     * @throws GeometryEngineException If the query fails, or if the result is not exactly one row.
     */
    public function executeQuery(string|BinaryValue|ScalarValue ...$query) : Row;

    /**
     * @throws GeometryEngineException
     */
    public function convertBinaryResult(mixed $value) : string;

    /**
     * @throws GeometryEngineException
     */
    public function convertStringResult(mixed $value) : string;

    /**
     * @throws GeometryEngineException
     */
    public function convertIntResult(mixed $value) : int;

    /**
     * @throws GeometryEngineException
     */
    public function convertFloatResult(mixed $value) : float;

    /**
     * @throws GeometryEngineException
     */
    public function convertBoolResult(mixed $value) : bool;
}
