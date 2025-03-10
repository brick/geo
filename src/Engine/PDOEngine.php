<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Override;
use PDO;
use PDOException;
use PDOStatement;

/**
 * Database engine based on a PDO driver.
 */
final class PDOEngine extends DatabaseEngine
{
    /**
     * The database connection.
     */
    private readonly PDO $pdo;

    /**
     * A cache of the prepared statements, indexed by query.
     *
     * @var array<string, PDOStatement>
     */
    private array $statements = [];

    public function __construct(PDO $pdo, bool $useProxy = true)
    {
        parent::__construct($useProxy);

        $this->pdo = $pdo;
    }

    public function getPDO() : PDO
    {
        return $this->pdo;
    }

    #[Override]
    protected function executeQuery(string $query, array $parameters) : array
    {
        /** @var int $errMode */
        $errMode = $this->pdo->getAttribute(PDO::ATTR_ERRMODE);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            if (! isset($this->statements[$query])) {
                $this->statements[$query] = $this->pdo->prepare($query);
            }

            $statement = $this->statements[$query];

            $index = 1;

            foreach ($parameters as $parameter) {
                if ($parameter instanceof GeometryParameter) {
                    $statement->bindValue($index++, $parameter->data, $parameter->isBinary ? PDO::PARAM_LOB : PDO::PARAM_STR);
                    $statement->bindValue($index++, $parameter->srid, PDO::PARAM_INT);
                } else {
                    $type = match (true) {
                        is_int($parameter) => PDO::PARAM_INT,
                        is_bool($parameter) => PDO::PARAM_BOOL,
                        default => PDO::PARAM_STR,
                    };

                    $statement->bindValue($index++, $parameter, $type);
                }
            }

            $statement->execute();

            /** @var list<mixed>|false $result */
            $result = $statement->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            throw GeometryEngineException::wrap($e);
        } finally {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, $errMode);
        }

        assert($result !== false);

        return $result;
    }

    #[Override]
    protected function getGeomFromWKBSyntax(): string
    {
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
            return 'ST_GeomFromWKB(BINARY ?, ?)';
        }

        return parent::getGeomFromWKBSyntax();
    }

    #[Override]
    protected function getParameterPlaceholder(string|float|int|bool $parameter): string
    {
        if ($this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME) === 'pgsql') {
            if (is_int($parameter)) {
                // https://stackoverflow.com/q/66625661/759866
                // https://externals.io/message/113521
                return 'CAST (? AS INTEGER)';
            }
        }

        return parent::getParameterPlaceholder($parameter);
    }
}
