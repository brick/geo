<?php

namespace Brick\Geo\Service;

use Brick\Geo\Geometry;
use Brick\Geo\Curve;
use Brick\Geo\MultiCurve;
use Brick\Geo\Surface;
use Brick\Geo\MultiSurface;
use Brick\Geo\GeometryException;

/**
 * Database implementation of the GeometryService.
 *
 * The target database must have support for GIS functions.
 */
class DatabaseService implements GeometryService
{
    /**
     * The database connection.
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * Class constructor.
     *
     * Note: this changes the PDO error handling to `ERRMODE_EXCEPTION`.
     *
     * @param \PDO $pdo
     */
    public function __construct(\PDO $pdo)
    {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo = $pdo;
    }

    /**
     * Builds a SQL query for a GIS function
     *
     * @param string  $function        The SQL GIS function to execute.
     * @param array   $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param boolean $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return string
     */
    protected function buildQuery($function, array $parameters, $returnsGeometry)
    {
        $geometryPlaceholder = sprintf('ST_GeomFromWkb(?, %s)', Geometry::WGS84);
        $standardPlaceholder = '?';

        foreach ($parameters as & $parameter) {
            if ($parameter instanceof Geometry) {
                $parameter = $geometryPlaceholder;
            } else {
                $parameter = $standardPlaceholder;
            }
        }

        $parameters = implode(', ', $parameters);
        $query = sprintf('%s(%s)', $function, $parameters);

        if ($returnsGeometry) {
            $query = sprintf('ST_AsBinary(%s)', $query);
        }

        return sprintf('SELECT %s', $query);
    }

    /**
     * Builds and executes a SQL query for a GIS function.
     *
     * @param string  $function        The SQL GIS function to execute.
     * @param array   $parameters      The Geometry objects or scalar values to pass as parameters.
     * @param boolean $returnsGeometry Whether the GIS function returns a Geometry.
     *
     * @return mixed
     *
     * @throws GeometryException
     */
    protected function query($function, array $parameters, $returnsGeometry)
    {
        $query = $this->buildQuery($function, $parameters, $returnsGeometry);

        try {
            $statement = $this->pdo->prepare($query);

            foreach ($parameters as $index => $parameter) {
                if ($parameter instanceof Geometry) {
                    /** @var Geometry $parameter */
                    $statement->bindValue(1 + $index, $parameter->asBinary(), \PDO::PARAM_LOB);
                } elseif (is_scalar($parameter)) {
                    $statement->bindValue(1 + $index, $parameter, \PDO::PARAM_STR);
                } else {
                    $message = 'The Geometry Service does not support parameters of type ' ;
                    throw new GeometryException($message . gettype($parameter));
                }
            }

            $statement->execute();
        } catch (\PDOException $e) {
            $message = 'The Geometry Service failed to query the database: ';
            throw new GeometryException($message . $e->getMessage(), 0, $e);
        }

        return $statement->fetchColumn();
    }

    /**
     * Executes a SQL query returning a boolean value.
     *
     * @param string $function   The SQL GIS function to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return boolean
     */
    protected function queryBoolean($function, array $parameters)
    {
        $result = $this->query($function, $parameters, false);

        return (boolean) $result;
    }

    /**
     * Executes a SQL query returning a floating point value.
     *
     * @param string $function   The SQL GIS function to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return float
     */
    protected function queryFloat($function, array $parameters)
    {
        $result = $this->query($function, $parameters, false);

        return (float) $result;
    }

    /**
     * Executes a SQL query returning a Geometry object.
     *
     * @param string $function   The SQL GIS function to execute.
     * @param array  $parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @return \Brick\Geo\Geometry
     */
    protected function queryGeometry($function, array $parameters)
    {
        $result = $this->query($function, $parameters, true);

        return Geometry::fromBinary($result);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Contains', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function intersects(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Intersects', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function union(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Union', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function intersection(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Intersection', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function difference(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_Difference', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function envelope(Geometry $g)
    {
        return $this->queryGeometry('ST_Envelope', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function centroid(Geometry $g)
    {
        return $this->queryGeometry('ST_Centroid', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function length(Geometry $g)
    {
        if (! $g instanceof Curve && ! $g instanceof MultiCurve) {
            throw new GeometryException('length() can only be called on a Curve or a MultiCurve');
        }

        return $this->queryFloat('ST_Length', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function area(Geometry $g)
    {
        if (! $g instanceof Surface && ! $g instanceof MultiSurface) {
            throw new GeometryException('area() can only be called on a Surface or a MultiSurface');
        }

        return $this->queryFloat('ST_Area', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function boundary(Geometry $g)
    {
        return $this->queryGeometry('ST_Boundary', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function isSimple(Geometry $g)
    {
        return $this->queryBoolean('ST_IsSimple', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Equals', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function disjoint(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Disjoint', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function touches(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Touches', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function crosses(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Crosses', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function within(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Within', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(Geometry $a, Geometry $b)
    {
        return $this->queryBoolean('ST_Overlaps', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function relate(Geometry $a, Geometry $b, $intersectionPatternMatrix)
    {
        return $this->queryBoolean('ST_Relate', [$a, $b, $intersectionPatternMatrix]);
    }

    /**
     * {@inheritdoc}
     */
    public function locateAlong(Geometry $g, $mValue)
    {
        return $this->queryGeometry('ST_LocateAlong', [$g, $mValue]);
    }

    /**
     * {@inheritdoc}
     */
    public function locateBetween(Geometry $g, $mStart, $mEnd)
    {
        return $this->queryGeometry('ST_LocateBetween', [$g, $mStart, $mEnd]);
    }

    /**
     * {@inheritdoc}
     */
    public function distance(Geometry $a, Geometry $b)
    {
        return $this->queryFloat('ST_Distance', [$a, $b]);
    }

    /**
     * {@inheritdoc}
     */
    public function buffer(Geometry $g, $distance)
    {
        return $this->queryGeometry('ST_Buffer', [$g, $distance]);
    }

    /**
     * {@inheritdoc}
     */
    public function convexHull(Geometry $g)
    {
        return $this->queryGeometry('ST_ConvexHull', [$g]);
    }

    /**
     * {@inheritdoc}
     */
    public function symDifference(Geometry $a, Geometry $b)
    {
        return $this->queryGeometry('ST_SymDifference', [$a, $b]);
    }
}
