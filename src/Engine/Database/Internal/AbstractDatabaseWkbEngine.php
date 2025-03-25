<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Internal;

use Brick\Geo\Engine\Database\Driver\DatabaseDriver;
use Brick\Geo\Engine\Database\Query\BinaryValue;
use Brick\Geo\Engine\Database\Query\ScalarValue;
use Brick\Geo\Engine\Database\Result\Row;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\WkbReader;
use Brick\Geo\Io\WkbWriter;
use Brick\Geo\Point;
use Override;

/**
 * Base class for database engines with standard support for WKB, but no support for EWKB.
 *
 * @internal
 */
abstract readonly class AbstractDatabaseWkbEngine extends AbstractDatabaseEngine
{
    private WkbReader $wkbReader;
    private WkbWriter $wkbWriter;

    final public function __construct(
        protected DatabaseDriver $driver,
    ) {
        $this->wkbReader = new WkbReader();
        $this->wkbWriter = new WkbWriter();
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
            $query[] = 'ST_AsBinary(g), ST_SRID(g) FROM (SELECT ';
        }

        $query[] = $function . '(';

        $first = true;
        foreach ($parameters as $parameter) {
            if ($first) {
                $first = false;
            } else {
                $query[] = ',';
            }

            if ($parameter instanceof Geometry) {
                if ($parameter instanceof Point && $parameter->isEmpty()) {
                    // WKB does not support empty points, and currently all concrete engines under this base class
                    // (MySQL, MariaDB, SpatiaLite) do not support them either.
                    throw new GeometryEngineException(static::class . ' does not support empty points');
                }
                $query[] = 'ST_GeomFromWKB(';
                $query[] = new BinaryValue($this->wkbWriter->write($parameter));
                $query[] = ',';
                $query[] = new ScalarValue($parameter->srid());
                $query[] = ')';
            } else {
                $query[] = new ScalarValue($parameter);
            }
        }

        $query[] = ')';

        if ($returnsGeometry) {
            $query[] = ' AS g) AS q';
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
    final protected function queryBool(string $function, Geometry|string|float|int|bool ...$parameters) : bool
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
    final protected function queryFloat(string $function, Geometry|string|float|int|bool ...$parameters) : float
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
    final protected function queryGeometry(string $function, Geometry|string|float|int|bool ...$parameters) : Geometry
    {
        $row = $this->query($function, $parameters, true);

        $wkb = $row->get(0)->asBinary();
        $srid = $row->get(1)->asInt();

        return $this->wkbReader->read($wkb, $srid);
    }
}
