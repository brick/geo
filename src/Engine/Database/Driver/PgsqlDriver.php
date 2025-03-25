<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Driver;

use Brick\Geo\Engine\Database\Driver\Internal\TypeConverter;
use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Engine\Database\Result\Row;
use Brick\Geo\Exception\GeometryEngineException;
use Override;
use PgSql\Connection;

/**
 * Database driver using the pgsql extension for PostgreSQL.
 */
final class PgsqlDriver implements DatabaseDriver
{
    public function __construct(
        private readonly Connection $connection,
    ) {
    }

    #[Override]
    public function executeQuery(string|BinaryValue|ScalarValue ...$query) : Row
    {
        $position = 1;

        $queryString = '';
        $queryParams = [];

        foreach ($query as $queryPart) {
            if ($queryPart instanceof BinaryValue) {
                $queryString .= '$' . $position++ . '::bytea';
                /** @var string */
                $queryParams[] = pg_escape_bytea($this->connection, $queryPart->value);
            } elseif ($queryPart instanceof ScalarValue) {
                $queryString .= '$' . $position++;

                if (is_int($queryPart->value)) {
                    $queryString .= '::int';
                    $queryParams[] = $queryPart->value;
                } elseif (is_float($queryPart->value)) {
                    $queryString .= '::float';
                    $queryParams[] = $queryPart->value;
                } elseif (is_bool($queryPart->value)) {
                    $queryString .= '::bool';
                    $queryParams[] = $queryPart->value ? 't' : 'f';
                } else {
                    $queryParams[] = $queryPart->value;
                }
            } else {
                $queryString .= $queryPart;
            }
        }

        // Mute warnings and back up the current error reporting level.
        $errorReportingLevel = error_reporting(0);

        try {
            $value = pg_prepare($this->connection, '', $queryString);

            if ($value === false) {
                $this->throwLastError();
            }

            $result = pg_execute($this->connection, '', $queryParams);

            if ($result === false) {
                $this->throwLastError();
            }

            /** @var list<list<mixed>>|false $rows */
            $rows = pg_fetch_all($result, PGSQL_NUM);

            if ($rows === false) {
                $this->throwLastError();
            }

            if (count($rows) !== 1) {
                throw new GeometryEngineException(sprintf('Expected exactly one row, got %d.', count($rows)));
            }
        } finally {
            // Restore the error reporting level.
            error_reporting($errorReportingLevel);
        }

        return new Row($this, $rows[0]);
    }

    /**
     * @throws GeometryEngineException
     */
    private function throwLastError() : never
    {
        throw new GeometryEngineException('The engine return an error: ' . pg_last_error($this->connection));
    }

    #[Override]
    public function convertBinaryResult(mixed $value) : string
    {
        if (is_string($value)) {
            return pg_unescape_bytea($value);
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('string', $value);
    }

    #[Override]
    public function convertStringResult(mixed $value) : string
    {
        if (is_string($value)) {
            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('string', $value);
    }

    #[Override]
    public function convertIntResult(mixed $value) : int
    {
        if (is_string($value)) {
            return TypeConverter::convertStringToInt($value);
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('integer string', $value);
    }

    #[Override]
    public function convertFloatResult(mixed $value) : float
    {
        if (is_string($value) && is_numeric($value)) {
            return (float) $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('number or numeric string', $value);
    }

    #[Override]
    public function convertBoolResult(mixed $value) : bool
    {
        return match ($value) {
            't' => true,
            'f' => false,
            default => throw GeometryEngineException::unexpectedDatabaseReturnType('t or f', $value),
        };
    }
}
