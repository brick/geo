<?php

declare(strict_types=1);

namespace Brick\Geo\Engine\Database\Internal;

use Brick\Geo\Curve;
use Brick\Geo\Engine\GeometryEngine;
use Brick\Geo\Engine\Internal\TypeChecker;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Geometry;
use Brick\Geo\LineString;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Surface;
use Override;

/**
 * Base class for database engines.
 *
 * This class provides standard implementations for most GIS functions, by requiring only 3 abstract methods to be
 * implemented by the concrete database engine.
 *
 * Some methods may be overridden in the concrete database engine when non-standard implementations are required.
 *
 * @internal
 */
abstract readonly class AbstractDatabaseEngine implements GeometryEngine
{
    /**
     * Queries a GIS function returning a boolean value.
     *
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    abstract protected function queryBool(string $function, Geometry|string|float|int|bool ...$parameters) : bool;

    /**
     * Queries a GIS function returning a floating point value.
     *
     * @param string          $function      The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    abstract protected function queryFloat(string $function, Geometry|string|float|int|bool ...$parameters) : float;

    /**
     * Queries a GIS function returning a Geometry object.
     *
     * @param string             $function   The SQL GIS function to execute.
     * @param Geometry|scalar ...$parameters The Geometry objects or scalar values to pass as parameters.
     *
     * @throws GeometryEngineException
     */
    abstract protected function queryGeometry(string $function, Geometry|string|float|int|bool ...$parameters) : Geometry;

    #[Override]
    public function contains(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Contains', $a, $b);
    }

    #[Override]
    public function intersects(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Intersects', $a, $b);
    }

    #[Override]
    public function union(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_Union', $a, $b);
    }

    #[Override]
    public function intersection(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_Intersection', $a, $b);
    }

    #[Override]
    public function difference(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_Difference', $a, $b);
    }

    #[Override]
    public function envelope(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_Envelope', $g);
    }

    #[Override]
    public function centroid(Geometry $g) : Point
    {
        $centroid = $this->queryGeometry('ST_Centroid', $g);
        TypeChecker::check($centroid, Point::class);

        return $centroid;
    }

    #[Override]
    public function pointOnSurface(Surface|MultiSurface $g) : Point
    {
        $pointOnSurface = $this->queryGeometry('ST_PointOnSurface', $g);
        TypeChecker::check($pointOnSurface, Point::class);

        return $pointOnSurface;
    }

    #[Override]
    public function length(Curve|MultiCurve $g) : float
    {
        return $this->queryFloat('ST_Length', $g);
    }

    #[Override]
    public function area(Surface|MultiSurface $g) : float
    {
        return $this->queryFloat('ST_Area', $g);
    }

    #[Override]
    public function azimuth(Point $observer, Point $subject) : float
    {
        return $this->queryFloat('ST_Azimuth', $observer, $subject);
    }

    #[Override]
    public function boundary(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_Boundary', $g);
    }

    #[Override]
    public function isValid(Geometry $g) : bool
    {
        return $this->queryBool('ST_IsValid', $g);
    }

    #[Override]
    public function isClosed(Geometry $g) : bool
    {
        return $this->queryBool('ST_IsClosed', $g);
    }

    #[Override]
    public function isSimple(Geometry $g) : bool
    {
        return $this->queryBool('ST_IsSimple', $g);
    }

    #[Override]
    public function isRing(Curve $curve) : bool
    {
        return $this->queryBool('ST_IsRing', $curve);
    }

    #[Override]
    public function makeValid(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_MakeValid', $g);
    }

    #[Override]
    public function equals(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Equals', $a, $b);
    }

    #[Override]
    public function disjoint(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Disjoint', $a, $b);
    }

    #[Override]
    public function touches(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Touches', $a, $b);
    }

    #[Override]
    public function crosses(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Crosses', $a, $b);
    }

    #[Override]
    public function within(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Within', $a, $b);
    }

    #[Override]
    public function overlaps(Geometry $a, Geometry $b) : bool
    {
        return $this->queryBool('ST_Overlaps', $a, $b);
    }

    #[Override]
    public function relate(Geometry $a, Geometry $b, string $matrix) : bool
    {
        return $this->queryBool('ST_Relate', $a, $b, $matrix);
    }

    #[Override]
    public function locateAlong(Geometry $g, float $mValue) : Geometry
    {
        return $this->queryGeometry('ST_LocateAlong', $g, $mValue);
    }

    #[Override]
    public function locateBetween(Geometry $g, float $mStart, float $mEnd) : Geometry
    {
        return $this->queryGeometry('ST_LocateBetween', $g, $mStart, $mEnd);
    }

    #[Override]
    public function distance(Geometry $a, Geometry $b) : float
    {
        return $this->queryFloat('ST_Distance', $a, $b);
    }

    #[Override]
    public function buffer(Geometry $g, float $distance) : Geometry
    {
        return $this->queryGeometry('ST_Buffer', $g, $distance);
    }

    #[Override]
    public function convexHull(Geometry $g) : Geometry
    {
        return $this->queryGeometry('ST_ConvexHull', $g);
    }

    #[Override]
    public function concaveHull(Geometry $g, float $convexity, bool $allowHoles) : Geometry
    {
        return $this->queryGeometry('ST_ConcaveHull', $g, $convexity, $allowHoles);
    }

    #[Override]
    public function symDifference(Geometry $a, Geometry $b) : Geometry
    {
        return $this->queryGeometry('ST_SymDifference', $a, $b);
    }

    #[Override]
    public function snapToGrid(Geometry $g, float $size) : Geometry
    {
        return $this->queryGeometry('ST_SnapToGrid', $g, $size);
    }

    #[Override]
    public function simplify(Geometry $g, float $tolerance) : Geometry
    {
        return $this->queryGeometry('ST_Simplify', $g, $tolerance);
    }

    #[Override]
    public function maxDistance(Geometry $a, Geometry $b) : float
    {
        return $this->queryFloat('ST_MaxDistance', $a, $b);
    }

    #[Override]
    public function transform(Geometry $g, int $srid) : Geometry
    {
        return $this->queryGeometry('ST_Transform', $g, $srid);
    }

    #[Override]
    public function split(Geometry $g, Geometry $blade) : Geometry
    {
        return $this->queryGeometry('ST_Split', $g, $blade);
    }

    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction) : Point
    {
        $result = $this->queryGeometry('ST_LineInterpolatePoint', $lineString, $fraction);
        TypeChecker::check($result, Point::class);

        return $result;
    }

    #[Override]
    public function lineInterpolatePoints(LineString $lineString, float $fraction) : MultiPoint
    {
        $result = $this->queryGeometry('ST_LineInterpolatePoints', $lineString, $fraction);

        if ($result instanceof MultiPoint) {
            return $result;
        }

        TypeChecker::check($result, Point::class);

        // POINT EMPTY
        if ($result->isEmpty()) {
            return new MultiPoint($result->coordinateSystem());
        }

        // POINT
        return MultiPoint::of($result);
    }
}
