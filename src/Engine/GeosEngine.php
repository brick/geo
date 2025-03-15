<?php

declare(strict_types=1);

namespace Brick\Geo\Engine;

use Brick\Geo\Curve;
use Brick\Geo\Engine\Internal\TypeChecker;
use Brick\Geo\Exception\GeometryEngineException;
use Brick\Geo\Io\EwkbReader;
use Brick\Geo\Io\EwkbWriter;
use Brick\Geo\Geometry;
use Brick\Geo\LineString;
use Brick\Geo\MultiCurve;
use Brick\Geo\MultiPoint;
use Brick\Geo\MultiSurface;
use Brick\Geo\Point;
use Brick\Geo\Surface;
use GEOSWKBReader;
use GEOSWKBWriter;
use GEOSWKTReader;
use GEOSWKTWriter;
use Override;

/**
 * GeometryEngine implementation based on the GEOS PHP bindings.
 */
final class GeosEngine implements GeometryEngine
{
    private readonly GEOSWKBReader $geosWkbReader;
    private readonly GEOSWKBWriter $geosWkbWriter;
    private readonly GEOSWKTReader $geosWktReader;
    private readonly GEOSWKTWriter $geosWktWriter;

    private readonly EwkbReader $ewkbReader;
    private readonly EwkbWriter $ewkbWriter;

    /**
     * Whether the GEOS version in use has support for binary read() and write() methods.
     *
     * These methods are available since GEOS 3.5.0.
     */
    private readonly bool $hasBinaryReadWrite;

    public function __construct()
    {
        $this->geosWkbReader = new \GEOSWKBReader();
        $this->geosWkbWriter = new \GEOSWKBWriter();

        $this->geosWktReader = new \GEOSWKTReader();
        $this->geosWktWriter = new \GEOSWKTWriter();

        $this->ewkbReader = new EwkbReader();
        $this->ewkbWriter = new EwkbWriter();

        /** @psalm-suppress RedundantCondition These methods are not available before GEOS 3.5.0 */
        $this->hasBinaryReadWrite =
            method_exists($this->geosWkbReader, 'read') &&
            method_exists($this->geosWkbWriter, 'write');
    }

    private function toGeos(Geometry $geometry) : \GEOSGeometry
    {
        if ($geometry->isEmpty()) {
            $geosGeometry = $this->geosWktReader->read($geometry->asText());
            $geosGeometry->setSRID($geometry->srid());

            return $geosGeometry;
        }

        if ($this->hasBinaryReadWrite) {
            return $this->geosWkbReader->read($this->ewkbWriter->write($geometry));
        }

        return $this->geosWkbReader->readHEX(bin2hex($this->ewkbWriter->write($geometry)));
    }

    private function fromGeos(\GEOSGeometry $geometry) : Geometry
    {
        if ($geometry->isEmpty()) {
            return Geometry::fromText($this->geosWktWriter->write($geometry), $geometry->getSRID());
        }

        if ($this->hasBinaryReadWrite) {
            return $this->ewkbReader->read($this->geosWkbWriter->write($geometry));
        }

        $ewkb = hex2bin($this->geosWkbWriter->writeHEX($geometry));
        assert($ewkb !== false);

        return $this->ewkbReader->read($ewkb);
    }

    #[Override]
    public function union(Geometry $a, Geometry $b) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($a)->union($this->toGeos($b))),
        );
    }

    #[Override]
    public function difference(Geometry $a, Geometry $b) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($a)->difference($this->toGeos($b))),
        );
    }

    #[Override]
    public function envelope(Geometry $g) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->envelope()),
        );
    }

    #[Override]
    public function length(Curve|MultiCurve $g) : float
    {
        return $this->execute(
            fn() => $this->toGeos($g)->length(),
        );
    }

    #[Override]
    public function area(Surface|MultiSurface $g) : float
    {
        return $this->execute(
            fn() => $this->toGeos($g)->area(),
        );
    }

    #[Override]
    public function azimuth(Point $observer, Point $subject) : float
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function centroid(Geometry $g) : Point
    {
        $centroid = $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->centroid()),
        );

        TypeChecker::check($centroid, Point::class);

        return $centroid;
    }

    #[Override]
    public function pointOnSurface(Surface|MultiSurface $g) : Point
    {
        $pointOnSurface = $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->pointOnSurface()),
        );

        TypeChecker::check($pointOnSurface, Point::class);

        return $pointOnSurface;
    }

    #[Override]
    public function boundary(Geometry $g) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->boundary()),
        );
    }

    #[Override]
    public function isValid(Geometry $g) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($g)->checkValidity()['valid'],
        );
    }

    #[Override]
    public function isClosed(Geometry $g) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($g)->isClosed(),
        );
    }

    #[Override]
    public function isSimple(Geometry $g) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($g)->isSimple(),
        );
    }

    #[Override]
    public function isRing(Curve $curve) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($curve)->isRing(),
        );
    }

    #[Override]
    public function makeValid(Geometry $g): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function equals(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->equals($this->toGeos($b)),
        );
    }

    #[Override]
    public function disjoint(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->disjoint($this->toGeos($b)),
        );
    }

    #[Override]
    public function intersects(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->intersects($this->toGeos($b)),
        );
    }

    #[Override]
    public function touches(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->touches($this->toGeos($b)),
        );
    }

    #[Override]
    public function crosses(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->crosses($this->toGeos($b)),
        );
    }

    #[Override]
    public function within(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->within($this->toGeos($b)),
        );
    }

    #[Override]
    public function contains(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->contains($this->toGeos($b)),
        );
    }

    #[Override]
    public function overlaps(Geometry $a, Geometry $b) : bool
    {
        return $this->execute(
            fn() => $this->toGeos($a)->overlaps($this->toGeos($b)),
        );
    }

    #[Override]
    public function relate(Geometry $a, Geometry $b, string $matrix) : bool
    {
        $result = $this->execute(
            fn() => $this->toGeos($a)->relate($this->toGeos($b), $matrix),
        );

        // giving a matrix should always return a boolean
        assert(is_bool($result));

        return $result;
    }

    #[Override]
    public function locateAlong(Geometry $g, float $mValue) : Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function locateBetween(Geometry $g, float $mStart, float $mEnd) : Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function distance(Geometry $a, Geometry $b) : float
    {
        return $this->execute(
            fn() => $this->toGeos($a)->distance($this->toGeos($b)),
        );
    }

    #[Override]
    public function buffer(Geometry $g, float $distance) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->buffer($distance)),
        );
    }

    #[Override]
    public function convexHull(Geometry $g) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->convexHull()),
        );
    }

    #[Override]
    public function concaveHull(Geometry $g, float $convexity, bool $allowHoles): Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function intersection(Geometry $a, Geometry $b) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($a)->intersection($this->toGeos($b))),
        );
    }

    #[Override]
    public function symDifference(Geometry $a, Geometry $b) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($a)->symDifference($this->toGeos($b))),
        );
    }

    #[Override]
    public function snapToGrid(Geometry $g, float $size) : Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function simplify(Geometry $g, float $tolerance) : Geometry
    {
        return $this->execute(
            fn() => $this->fromGeos($this->toGeos($g)->simplify($tolerance)),
        );
    }

    #[Override]
    public function maxDistance(Geometry $a, Geometry $b) : float
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function transform(Geometry $g, int $srid) : Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function split(Geometry $g, Geometry $blade) : Geometry
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    #[Override]
    public function lineInterpolatePoint(LineString $lineString, float $fraction) : Point
    {
        $result = $this->execute(
            fn() => $this->fromGeos($this->toGeos($lineString)->interpolate($fraction, true)),
        );

        if (! $result instanceof Point) {
            throw new GeometryEngineException('This operation yielded the wrong geometry type: ' . $result::class);
        }

        return $result;
    }

    #[Override]
    public function lineInterpolatePoints(LineString $lineString, float $fraction) : MultiPoint
    {
        throw GeometryEngineException::unimplementedMethod(__METHOD__);
    }

    /**
     * @template T
     *
     * @param \Closure(): T $action
     *
     * @return T
     *
     * @throws GeometryEngineException
     */
    private function execute(\Closure $action) : mixed
    {
        try {
            return $action();
        } catch (\Exception $e) {
            throw GeometryEngineException::wrap($e);
        }
    }
}
