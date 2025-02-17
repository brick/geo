<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Exception\SQLite3Exception;
use Brick\Geo\Geometry;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Override;
use SQLite3;
use SQLite3Stmt;

/**
 * Database engine based on a SQLite3 driver.
 *
 * The spatialite extension must be loaded in this driver.
 */
final class SQLite3Engine extends DatabaseEngine
{
    /**
     * The database connection.
     */
    private readonly SQLite3 $sqlite3;

    /**
     * A cache of the prepared statements, indexed by query.
     *
     * @var array<string, SQLite3Stmt>
     */
    private array $statements = [];

    public function __construct(SQLite3 $sqlite3, bool $useProxy = true)
    {
        parent::__construct($useProxy);

        $this->sqlite3 = $sqlite3;
    }

    public function getSQLite3() : SQLite3
    {
        return $this->sqlite3;
    }

    #[Override]
    protected function executeQuery(string $query, array $parameters) : array
    {
        if (isset($this->statements[$query])) {
            $statement = $this->statements[$query];
            $statement->reset();
        } else {
            // Temporary set the error reporting level to 0 to avoid any warning.
            $errorReportingLevel = error_reporting(0);

            // Don't use exceptions, they're just \Exception instances that don't even contain the SQLite error code!
            $enableExceptions = $this->sqlite3->enableExceptions(false);

            $statement = $this->sqlite3->prepare($query);

            // Restore the original settings.
            $this->sqlite3->enableExceptions($enableExceptions);
            error_reporting($errorReportingLevel);

            $errorCode = $this->sqlite3->lastErrorCode();

            if ($errorCode !== 0) {
                $exception = new SQLite3Exception($this->sqlite3->lastErrorMsg(), $errorCode);

                if ($errorCode === 1) {
                    // SQL error cause by a missing function, this must be reported with a GeometryEngineException.
                    throw GeometryEngineException::operationNotSupportedByEngine($exception);
                } else {
                    // Other SQLite3 error; we cannot trigger the original E_WARNING, so we throw this exception instead.
                    throw $exception;
                }
            } else {
                $this->statements[$query] = $statement;
            }
        }

        $index = 1;

        foreach ($parameters as $parameter) {
            if ($parameter instanceof GeometryParameter) {
                $statement->bindValue($index++, $parameter->data, $parameter->isBinary ? SQLITE3_BLOB : SQLITE3_TEXT);
                $statement->bindValue($index++, $parameter->srid, SQLITE3_INTEGER);
            } else {
                if (is_int($parameter)) {
                    $type = SQLITE3_INTEGER;
                } elseif (is_float($parameter)) {
                    $type = SQLITE3_FLOAT;
                } else {
                    $type = SQLITE3_TEXT;
                }

                $statement->bindValue($index++, $parameter, $type);
            }
        }

        $result = $statement->execute();

        /** @psalm-var list<mixed> */
        return $result->fetchArray(SQLITE3_NUM);
    }

    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction) : Point
    {
        $result = $this->queryGeometry('ST_Line_Interpolate_Point', $lineString, $fraction);
        if (! $result instanceof Point) {
            throw new GeometryEngineException('This operation yielded the wrong geometry type: ' . $result::class);
        }

        return $result;
    }
}
