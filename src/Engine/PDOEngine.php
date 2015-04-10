<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;

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
     * Class constructor.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * @return \PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeQuery($query, array $parameters)
    {
        $errMode = $this->pdo->getAttribute(\PDO::ATTR_ERRMODE);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

        try {
            $statement = $this->pdo->prepare($query);

            $index = 1;

            foreach ($parameters as $parameter) {
                if ($parameter instanceof Geometry) {
                    $statement->bindValue($index++, $parameter->asBinary(), \PDO::PARAM_LOB);
                    $statement->bindValue($index++, $parameter->SRID(), \PDO::PARAM_INT);
                } else {
                    $statement->bindValue($index++, $parameter);
                }
            }

            $statement->execute();

            $result = $statement->fetch(\PDO::FETCH_NUM);
        } catch (\PDOException $e) {
            $errorClass = substr($e->getCode(), 0, 2);

            // 42XXX = syntax error or access rule violation; reported on undefined function.
            // 22XXX = data exception; reported by MySQL 5.7 on unsupported geometry.
            if ($errorClass == '42' || $errorClass == '22') {
                throw GeometryEngineException::operationNotSupportedByEngine($e);
            }

            throw $e;
        }

        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, $errMode);

        return $result;
    }
}
