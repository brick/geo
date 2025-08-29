<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Engine\Internal\TypeChecker;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\LineString;
use Brick\Geo\Point;
use Exception;
use Override;
use SQLite3;
use SQLite3Stmt;

use function assert;
use function is_float;
use function is_int;

use const SQLITE3_BLOB;
use const SQLITE3_FLOAT;
use const SQLITE3_INTEGER;
use const SQLITE3_NUM;
use const SQLITE3_TEXT;

/**
 * Database engine based on a SQLite3 driver.
 *
 * The spatialite extension must be loaded in this driver.
 */
final class Sqlite3Engine extends DatabaseEngine
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

    public function getSQLite3(): SQLite3
    {
        return $this->sqlite3;
    }

    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction): Point
    {
        $result = $this->queryGeometry('ST_Line_Interpolate_Point', $lineString, $fraction);
        TypeChecker::check($result, Point::class);

        return $result;
    }

    #[Override]
    protected function executeQuery(string $query, array $parameters): array
    {
        $enableExceptions = $this->sqlite3->enableExceptions(true);

        try {
            if (isset($this->statements[$query])) {
                $statement = $this->statements[$query];
                $statement->reset();
            } else {
                $statement = $this->sqlite3->prepare($query);
                $this->statements[$query] = $statement;
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

            $sqlite3Result = $statement->execute();

            /** @var list<mixed>|false $result */
            $result = $sqlite3Result->fetchArray(SQLITE3_NUM);
        } catch (Exception $e) {
            throw GeometryEngineException::wrap($e);
        } finally {
            $this->sqlite3->enableExceptions($enableExceptions);
        }

        assert($result !== false);

        return $result;
    }
}
