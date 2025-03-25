<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Driver\Internal;

use Brick\Geo\Engine\Database\Driver\DatabaseDriver;
use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Engine\Database\Result\Row;
use Brick\Geo\Exception\GeometryEngineException;
use Override;
use PDO;
use PDOException;

abstract class AbstractPdoDriver implements DatabaseDriver
{
    public function __construct(
        private readonly PDO $pdo,
    ) {
    }

    /**
     * Converts the query parts to a query string and parameters to send to PDO.
     *
     * @return array{string, list<array{scalar, PDO::PARAM_*}>}
     */
    abstract protected function convertQuery(string|BinaryValue|ScalarValue ...$query) : array;

    #[Override]
    public function executeQuery(string|BinaryValue|ScalarValue ...$query) : Row
    {
        [$queryString, $queryParams] = $this->convertQuery(...$query);

        /** @var int $errMode */
        $errMode = $this->pdo->getAttribute(PDO::ATTR_ERRMODE);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        try {
            $statement = $this->pdo->prepare($queryString);

            $position = 1;

            foreach ($queryParams as [$value, $type]) {
                $statement->bindValue($position++, $value, $type);
            }

            $statement->execute();

            /** @var list<list<mixed>> $result */
            $result = $statement->fetchAll(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            throw GeometryEngineException::wrap($e);
        } finally {
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, $errMode);
        }

        if (count($result) !== 1) {
            throw new GeometryEngineException(sprintf('Expected exactly one row, got %d.', count($result)));
        }

        return new Row($this, $result[0]);
    }
}
