<?php

declare(strict_types=1);

namespace Brick\Geo;

use ArrayIterator;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Projector\Projector;
use Override;

/**
 * A Point is a 0-dimensional geometric object and represents a single location in coordinate space.
 *
 * A Point has an x-coordinate value, a y-coordinate value.
 * If called for by the associated Spatial Reference System, it may also have coordinate values for z and m.
 *
 * The boundary of a Point is the empty set.
 */
final readonly class Point extends Geometry
{
    /**
     * The x-coordinate value for this Point, or NULL if the point is empty.
     */
    public ?float $x;

    /**
     * The y-coordinate value for this Point, or NULL if the point is empty.
     */
    public ?float $y;

    /**
     * The z-coordinate value for this Point, or NULL if it does not have one.
     */
    public ?float $z;

    /**
     * The m-coordinate value for this Point, or NULL if it does not have one.
     */
    public ?float $m;

    /**
     * @param CoordinateSystem $cs        The coordinate system.
     * @param float            ...$coords The point coordinates; can be empty for an empty point.
     *
     * @throws InvalidGeometryException If the number of coordinates does not match the coordinate system.
     *
     * @psalm-suppress PossiblyUndefinedArrayOffset
     */
    public function __construct(CoordinateSystem $cs, float ...$coords)
    {
        $isEmpty = count($coords) === 0;

        parent::__construct($cs, $isEmpty);

        if ($isEmpty) {
            $this->x = null;
            $this->y = null;
            $this->z = null;
            $this->m = null;

            return;
        }

        if (count($coords) !== $cs->coordinateDimension()) {
            throw new InvalidGeometryException(sprintf(
                'Expected %d coordinates for Point %s, got %d.',
                $cs->coordinateDimension(),
                $cs->coordinateName(),
                count($coords)
            ));
        }

        $coords = array_values($coords);

        foreach ($coords as $i => $coord) {
            if (! is_finite($coord)) {
                $coordinateName = match ($i) {
                    0 => 'X',
                    1 => 'Y',
                    2 => $cs->hasZ ? 'Z' : 'M',
                    3 => 'M',
                };
                throw new InvalidGeometryException(sprintf(
                    'Coordinate #%d (%s) for Point %s is %s, this is not allowed.',
                    $i + 1,
                    $coordinateName,
                    $cs->coordinateName(),
                    is_infinite($coord) ? ($coord > 0 ? '+' : '-') . 'INF' : 'NaN',
                ));
            }
        }

        $this->x = $coords[0];
        $this->y = $coords[1];

        $this->z = $cs->hasZ ? $coords[2] : null;
        $this->m = $cs->hasM ? $coords[$cs->hasZ ? 3 : 2] : null;
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
     *
     * @deprecated Use $x property instead.
     */
    public function x() : ?float
    {
        return $this->x;
    }

    /**
     * Returns the y-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty.
     *
     * @deprecated Use $y property instead.
     */
    public function y() : ?float
    {
        return $this->y;
    }

    /**
     * Returns the z-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty, or does not have a Z coordinate.
     *
     * @deprecated Use $z property instead.
     */
    public function z() : ?float
    {
        return $this->z;
    }

    /**
     * Returns the m-coordinate value for this Point.
     *
     * Returns NULL if the Point is empty, or does not have an M coordinate.
     *
     * @deprecated Use $m property instead.
     */
    public function m() : ?float
    {
        return $this->m;
    }

    #[Override]
    public function geometryType() : string
    {
        return 'Point';
    }

    #[Override]
    public function geometryTypeBinary() : int
    {
        return Geometry::POINT;
    }

    #[Override]
    public function dimension() : int
    {
        return 0;
    }

    #[Override]
    public function getBoundingBox() : BoundingBox
    {
        return BoundingBox::new()->extendedWithPoint($this);
    }

    /**
     * @return list<float>
     */
    #[Override]
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

    #[Override]
    public function project(Projector $projector) : static
    {
        return $projector->project($this);
    }
}
