<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Engine\Database\Driver\DatabaseDriver;
use Brick\Geo\Engine\Database\Internal\AbstractDatabaseEngine;
use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Engine\Database\Result\Row;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\EwkbReader;
use Brick\Geo\Io\EwkbWriter;
use Override;

/**
 * Database engine based on PostgreSQL with the PostGIS extension.
 */
final readonly class PostgisEngine extends AbstractDatabaseEngine
{
    private EwkbReader $ewkbReader;
    private EwkbWriter $ewkbWriter;

    public function __construct(
        private DatabaseDriver $driver,
    ) {
        $this->ewkbReader = new EwkbReader();
        $this->ewkbWriter = new EwkbWriter();
    }

    /**
     * Builds and executes a SQL query for a GIS function.
     *
     * @param string              $function        The SQL GIS function to execute.
     * @param (Geometry|scalar)[] $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param bool                $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @throws GeometryEngineException
     */
    private function query(string $function, array $parameters, bool $returnsGeometry) : Row
    {
        $query = ['SELECT '];

        if ($returnsGeometry) {
            $query[] = 'ST_AsEWKB(';
        }

        $query[] = $function . '(';

        foreach ($parameters as $key => $parameter) {
            if ($key !== 0) {
                $query[] = ',';
            }

            if ($parameter instanceof Geometry) {
                $query[] = 'ST_GeomFromEWKB(';
                $query[] = new BinaryValue($this->ewkbWriter->write($parameter));
                $query[] = ')';
            } else {
                $query[] = new ScalarValue($parameter);
            }
        }

        $query[] = ')';

        if ($returnsGeometry) {
            $query[] = ')';
        }

        return $this->driver->executeQuery(...$query);
    }

    /**
     * Queries a GIS function returning a boolean value.
     *
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    #[Override]
    public function queryBool(string $function, Geometry|string|float|int|bool ...$parameters) : bool
    {
        return $this->query($function, $parameters, false)->get(0)->asBool();
    }

    /**
     * Queries a GIS function returning a floating point value.
     *
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    #[Override]
    public function queryFloat(string $function, Geometry|string|float|int|bool ...$parameters) : float
    {
        return $this->query($function, $parameters, false)->get(0)->asFloat();
    }

    /**
     * Queries a GIS function returning a Geometry object.
     *
     * @param string             $function   The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    #[Override]
    public function queryGeometry(string $function, Geometry|string|float|int|bool ...$parameters) : Geometry
    {
        $ewkb = $this->query($function, $parameters, true)->get(0)->asBinary();

        return $this->ewkbReader->read($ewkb);
    }
}
