<?php

namespace Brick\Geo\Service;

use Brick\Geo\Geometry;

/**
 * Database service based on a SQLite3 driver.
 *
 * The spatialite extension must be loaded in this driver.
 */
class SQLite3Service extends DatabaseService
{
    /**
     * @var \SQLite3
     */
    private $sqlite3;

    /**
     * @param \SQLite3 $sqlite3
     */
    public function __construct(\SQLite3 $sqlite3)
    {
        $this->sqlite3 = $sqlite3;
    }

    /**
     * {@inheritdoc}
     */
    protected function executeQuery($query, array $parameters)
    {
        $statement = $this->sqlite3->prepare($query);

        foreach ($parameters as $index => $parameter) {
            if ($parameter instanceof Geometry) {
                $statement->bindValue(1 + $index, $parameter->asBinary(), SQLITE3_BLOB);
            } else {
                $statement->bindValue(1 + $index, $parameter);
            }
        }

        $result = $statement->execute();

        return $result->fetchArray(SQLITE3_NUM)[0];
    }
}
