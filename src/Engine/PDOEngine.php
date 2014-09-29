<?php

namespace Brick\Geo\Engine;

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
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
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
        $statement = $this->pdo->prepare($query);

        foreach ($parameters as $index => $parameter) {
            if ($parameter instanceof Geometry) {
                $statement->bindValue(1 + $index, $parameter->asBinary(), \PDO::PARAM_LOB);
            } else {
                $statement->bindValue(1 + $index, $parameter);
            }
        }

        $statement->execute();

        return $statement->fetchColumn();
    }
}
