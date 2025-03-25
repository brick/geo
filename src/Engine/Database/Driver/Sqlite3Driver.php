<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Driver;

use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Engine\Database\Result\Row;
use Brick\Geo\Exception\GeometryEngineException;
use Override;
use SQLite3;

/**
 * Database driver using an SQLite3 connection.
 */
final class Sqlite3Driver implements DatabaseDriver
{
    public function __construct(
        private SQLite3 $sqlite3,
    ) {
    }

    #[Override]
    public function executeQuery(string|BinaryValue|ScalarValue ...$query) : Row
    {
        $queryString = '';
        $queryParams = [];

        foreach ($query as $queryPart) {
            if ($queryPart instanceof BinaryValue) {
                $queryString .= '?';
                $queryParams[] = [$queryPart->value, SQLITE3_BLOB];
            } elseif ($queryPart instanceof ScalarValue) {
                $queryString .= '?';

                if (is_float($queryPart->value)) {
                    $queryParams[] = [$queryPart->value, SQLITE3_FLOAT];
                } elseif (is_int($queryPart->value) || is_bool($queryPart->value)) {
                    $queryParams[] = [$queryPart->value, SQLITE3_INTEGER];
                } else {
                    $queryParams[] = [$queryPart->value, SQLITE3_TEXT];
                }
            } else {
                $queryString .= $queryPart;
            }
        }

        $enableExceptions = $this->sqlite3->enableExceptions(true);

        try {
            $statement = $this->sqlite3->prepare($queryString);

            $position = 1;

            foreach ($queryParams as [$value, $type]) {
                $statement->bindValue($position++, $value, $type);
            }

            $sqlite3Result = $statement->execute();

            $result = [];

            while (false !== $row = $sqlite3Result->fetchArray(SQLITE3_NUM)) {
                /** @var list<mixed> $row */
                $result[] = $row;
            }

        } catch (\Exception $e) {
            throw GeometryEngineException::wrap($e);
        } finally {
            $this->sqlite3->enableExceptions($enableExceptions);
        }

        if (count($result) !== 1) {
            throw new GeometryEngineException(sprintf('Expected exactly one row, got %d.', count($result)));
        }

        return new Row($this, $result[0]);
    }

    #[Override]
    public function convertBinaryResult(mixed $value) : string
    {
        if (is_string($value)) {
            return $value;
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
        if (is_int($value)) {
            return $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('int', $value);
    }

    #[Override]
    public function convertFloatResult(mixed $value) : float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        throw GeometryEngineException::unexpectedDatabaseReturnType('number or numeric string', $value);
    }

    #[Override]
    public function convertBoolResult(mixed $value) : bool
    {
        return match ($value) {
            0 => false,
            1 => true,
            default => throw GeometryEngineException::unexpectedDatabaseReturnType('t or f', $value),
        };
    }
}
