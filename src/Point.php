<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Engine\GeometryEngineRegistry;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Exception\InvalidGeometryException;

/**
 * A Point is a 0-dimensional geometric object and represents a single location in coordinate space.
 *
 * A Point has an x-coordinate value, a y-coordinate value.
 * If called for by the associated Spatial Reference System, it may also have coordinate values for z and m.
 *
 * The boundary of a Point is the empty set.
 */
class Point extends Geometry
{
    /**
     * The x-coordinate value for this Point, or NULL if the point is empty.
     */
    private ?float $x = null;

    /**
     * The y-coordinate value for this Point, or NULL if the point is empty.
     */
    private ?float $y = null;

    /**
     * The z-coordinate value for this Point, or NULL if it does not have one.
     */
    private ?float $z = null;

    /**
     * The m-coordinate value for this Point, or NULL if it does not have one.
     */
    private ?float $m = null;

    /**
     * @param CoordinateSystem $cs        The coordinate system.
     * @param float            ...$coords The point coordinates; can be empty for an empty point.
     *
     * @throws InvalidGeometryException If the number of coordinates does not match the coordinate system.
     */
    public function __construct(CoordinateSystem $cs, float ...$coords)
    {
        parent::__construct($cs, ! $coords);

        if ($coords) {
            if (count($coords) !== $cs->coordinateDimension()) {
                throw new InvalidGeometryException(sprintf(
                    'Expected %d coordinates for Point %s, got %d.',
                    $cs->coordinateDimension(),
                    $cs->coordinateName(),
                    count($coords)
                ));
            }

            $this->x = $coords[0];
            $this->y = $coords[1];

            $hasZ = $cs->hasZ();
            $hasM = $cs->hasM();

            if ($hasZ) {
                $this->z = $coords[2];
            }

            if ($hasM) {
                $this->m = $coords[$hasZ ? 3 : 2];
            }
        }
    }

    /**
     * Creates a point with X and Y coordinates.
     */
    public static function xy(float $x, float $y, int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xy($srid), $x, $y);
    }

    /**
     * Creates a point with X, Y and Z coordinates.
     */
    public static function xyz(float $x, float $y, float $z, int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xyz($srid), $x, $y, $z);
    }

    /**
     * Creates a point with X, Y and M coordinates.
     */
    public static function xym(float $x, float $y, float $m, int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xym($srid), $x, $y, $m);
    }

    /**
     * Creates a point with X, Y, Z and M coordinates.
     */
    public static function xyzm(float $x, float $y, float $z, float $m, int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xyzm($srid), $x, $y, $z, $m);
    }

    /**
     * Creates an empty Point with XY dimensionality.
     */
    public static function xyEmpty(int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xy($srid));
    }

    /**
     * Creates an empty Point with XYZ dimensionality.
     */
    public static function xyzEmpty(int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xyz($srid));
    }

    /**
     * Creates an empty Point with XYM dimensionality.
     */
    public static function xymEmpty(int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xym($srid));
    }

    /**
     * Creates an empty Point with XYZM dimensionality.
     */
    public static function xyzmEmpty(int $srid = 0) : Point
    {
        return new Point(CoordinateSystem::xyzm($srid));
    }

    /**
     * Returns the x-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty.
     */
    public function x() : ?float
    {
        return $this->x;
    }

    /**
     * Returns the y-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty.
     */
    public function y() : ?float
    {
        return $this->y;
    }

    /**
     * Returns the z-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty, or does not have a Z coordinate.
     */
    public function z() : ?float
    {
        return $this->z;
    }

    /**
     * Returns the m-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty, or does not have a M coordinate.
     */
    public function m() : ?float
    {
        return $this->m;
    }

    /**
     * @noproxy
     */
    public function geometryType() : string
    {
        return 'Point';
    }

    /**
     * @noproxy
     */
    public function geometryTypeBinary() : int
    {
        return Geometry::POINT;
    }

    /**
     * @noproxy
     */
    public function dimension() : int
    {
        return 0;
    }

    public function toXY(): Point
    {
        if ($this->coordinateDimension() === 2) {
            return $this;
        }

        $cs = $this->coordinateSystem
            ->withZ(false)
            ->withM(false)
        ;

        $coords = $this->toArray();

        if ($coords) {
            $coords = array_slice($coords, 0, 2);
        }

        return new Point($cs, ...$coords);
    }

    public function withoutZ(): Point
    {
        if (! $this->coordinateSystem->hasZ()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withZ(false);

        $coords = [];

        if ($this->x !== null && $this->y !== null) {
            $coords[] = $this->x;
            $coords[] = $this->y;

            if ($this->m !== null) {
                $coords[] = $this->m;
            }
        }

        return new Point($cs, ...$coords);
    }

    public function withoutM(): Point
    {
        if (! $this->coordinateSystem()->hasM()) {
            return $this;
        }

        $cs = $this->coordinateSystem->withM(false);

        $coords = [];

        if ($this->x !== null && $this->y !== null) {
            $coords[] = $this->x;
            $coords[] = $this->y;

            if ($this->z !== null) {
                $coords[] = $this->z;
            }
        }

        return new Point($cs, ...$coords);
    }

    public function getBoundingBox() : BoundingBox
    {
        return (new BoundingBox())->extendedWithPoint($this);
    }

    /**
     * @psalm-return list<float>
     *
     * @return float[]
     */
    public function toArray() : array
    {
        if ($this->isEmpty) {
            return [];
        }

        /** @var list<float> $result */
        $result = [$this->x, $this->y];

        if ($this->z !== null) {
            $result[] = $this->z;
        }

        if ($this->m !== null) {
            $result[] = $this->m;
        }

        return $result;
    }

    public function swapXY() : Geometry
    {
        $that = clone $this;

        $that->x = $this->y;
        $that->y = $this->x;

        return $that;
    }

    /**
     * Returns the number of coordinates in this Point.
     *
     * Required by interface Countable.
     */
    public function count() : int
    {
        if ($this->isEmpty) {
            return 0;
        }

        return $this->coordinateSystem->coordinateDimension();
    }

    /**
     * Returns an iterator for the coordinates in this Point.
     *
     * Required by interface IteratorAggregate.
     *
     * @psalm-return ArrayIterator<int, float>
     */
    public function getIterator() : ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    /**
     * Returns the azimuth in radians of the segment defined by the given point geometries.
     * The azimuth is an angle measured from the north, and is positive clockwise:
     * North = 0; East = π/2; South = π; West = 3π/2.
     *
     * @deprecated Please use `$geometryEngine->azimuth()`.
     *
     * @param Point $subject Point representing subject of observation.
     *
     * @return float Azimuth of the subject relative to the observer.
     *
     * @throws GeometryEngineException If the operation is not supported by the engine.
     * @throws GeometryEngineException If observer and subject locations are coincident.
     */
    public function azimuth(Point $subject) : float
    {
        return GeometryEngineRegistry::get()->azimuth($this, $subject);
    }
}
