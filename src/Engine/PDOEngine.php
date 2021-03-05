<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;

/**
 * Database engine based on a PDO driver.
 */
class PDOEngine extends DatabaseEngine
{
    /**
     * The database connection.
     *
     * @var \PDO
     */
    private $pdo;

    /**
     * A cache of the prepared statements, indexed by query.
     *
     * @var \PDOStatement[]
     */
    private $statements = [];

    /**
     * Class constructor.
     *
     * @param \PDO $pdo
     * @param bool $useProxy
     */
    public function __construct(\PDO $pdo, bool $useProxy = true)
    {
        parent::__construct($useProxy);

        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPDO() : \PDO
    {
        return $this->pdo;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeQuery(string $query, array $parameters) : array
    {
        /** @var int $errMode */
        $errMode = $this->pdo->getAttribute(\PDO::ATTR_ERRMODE);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        try {
            if (! isset($this->statements[$query])) {
                $this->statements[$query] = $this->pdo->prepare($query);
            }

            $statement = $this->statements[$query];

            $index = 1;

            foreach ($parameters as $parameter) {
                if ($parameter instanceof GeometryParameter) {
                    $statement->bindValue($index++, $parameter->data, $parameter->isBinary ? \PDO::PARAM_LOB : \PDO::PARAM_STR);
                    $statement->bindValue($index++, $parameter->srid, \PDO::PARAM_INT);
                } else {
                    $statement->bindValue($index++, $parameter);
                }
            }

            $statement->execute();

            $result = $statement->fetch(\PDO::FETCH_NUM);
        } catch (\PDOException $e) {
            $errorClass = substr((string) $e->getCode(), 0, 2);

            // 42XXX = syntax error or access rule violation; reported on undefined function.
            // 22XXX = data exception; reported by MySQL 5.7 on unsupported geometry.
            if ($errorClass === '42' || $errorClass === '22') {
                throw GeometryEngineException::operationNotSupportedByEngine($e);
            }

            throw $e;
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, $errMode);

        assert($result !== false);

        return $result;
    }

    protected function getGeomFromWKBSyntax(): string
    {
        if ($this->pdo->getAttribute(\PDO::ATTR_DRIVER_NAME) === 'mysql') {
            return 'ST_GeomFromWKB(_binary ?, ?)';
        }

        return parent::getGeomFromWKBSyntax();
    }
}
