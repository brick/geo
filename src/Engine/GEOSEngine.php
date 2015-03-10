<?php

namespace Brick\Geo\Engine;

use Brick\Geo\Geometry;
use Brick\Geo\Exception\GeometryException;
use Brick\Geo\IO\EWKBReader;
use Brick\Geo\IO\EWKBWriter;

/**
 * GeometryEngine implementation based on the GEOS PHP bindings.
 */
class GEOSEngine implements GeometryEngine
{
    /**
     * @var \GEOSWKBReader
     */
    private $wkbReader;

    /**
     * @var \GEOSWKBWriter
     */
    private $wkbWriter;

    /**
     * @var EWKBReader
     */
    private $ewkbReader;

    /**
     * @var EWKBWriter
     */
    private $ewkbWriter;

    /**
     * Whether the GEOS version in use has support for binary read() and write() methods.
     *
     * @var boolean
     */
    private $hasReadWrite;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->wkbReader = new \GEOSWKBReader();
        $this->wkbWriter = new \GEOSWKBWriter();

        $this->ewkbReader = new EWKBReader();
        $this->ewkbWriter = new EWKBWriter();

        $this->hasReadWrite =
            method_exists($this->wkbReader, 'read') &&
            method_exists($this->wkbWriter, 'write');
    }

    /**
     * @param Geometry $geometry
     *
     * @return \GEOSGeometry
     */
    private function toGEOS(Geometry $geometry)
    {
        if ($this->hasReadWrite) {
            return $this->wkbReader->read($this->ewkbWriter->write($geometry));
        }

        return $this->wkbReader->readHEX(bin2hex($this->ewkbWriter->write($geometry)));
    }

    /**
     * @param \GEOSGeometry $geometry
     *
     * @return Geometry
     */
    private function fromGEOS(\GEOSGeometry $geometry)
    {
        if ($this->hasReadWrite) {
            return $this->ewkbReader->read($this->wkbWriter->write($geometry));
        }

        return $this->ewkbReader->read(hex2bin($this->wkbWriter->writeHEX($geometry)));
    }

    /**
     * {@inheritdoc}
     */
    public function union(Geometry $a, Geometry $b)
    {
        return $this->fromGEOS($this->toGEOS($a)->union($this->toGEOS($b)));
    }

    /**
     * {@inheritdoc}
     */
    public function difference(Geometry $a, Geometry $b)
    {
        return $this->fromGEOS($this->toGEOS($a)->difference($this->toGEOS($b)));
    }

    /**
     * {@inheritdoc}
     */
    public function envelope(Geometry $g)
    {
        return $this->fromGEOS($this->toGEOS($g)->envelope());
    }

    /**
     * {@inheritdoc}
     */
    public function length(Geometry $g)
    {
        return $this->toGEOS($g)->length();
    }

    /**
     * {@inheritdoc}
     */
    public function area(Geometry $g)
    {
        return $this->toGEOS($g)->area();
    }

    /**
     * {@inheritdoc}
     */
    public function centroid(Geometry $g)
    {
        return $this->fromGEOS($this->toGEOS($g)->centroid());
    }

    /**
     * {@inheritdoc}
     */
    public function boundary(Geometry $g)
    {
        return $this->fromGEOS($this->toGEOS($g)->boundary());
    }

    /**
     * {@inheritdoc}
     */
    public function isSimple(Geometry $g)
    {
        return $this->toGEOS($g)->isSimple();
    }

    /**
     * {@inheritdoc}
     */
    public function equals(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->equals($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function disjoint(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->disjoint($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function intersects(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->intersects($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function touches(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->touches($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function crosses(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->crosses($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function within(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->within($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function contains(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->contains($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->overlaps($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function relate(Geometry $a, Geometry $b, $matrix)
    {
        return $this->toGEOS($a)->relate($this->toGEOS($b), $matrix);
    }

    /**
     * {@inheritdoc}
     */
    public function locateAlong(Geometry $g, $mValue)
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function locateBetween(Geometry $g, $mStart, $mEnd)
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function distance(Geometry $a, Geometry $b)
    {
        return $this->toGEOS($a)->distance($this->toGEOS($b));
    }

    /**
     * {@inheritdoc}
     */
    public function buffer(Geometry $g, $distance)
    {
        return $this->fromGEOS($this->toGEOS($g)->buffer($distance));
    }

    /**
     * {@inheritdoc}
     */
    public function convexHull(Geometry $g)
    {
        return $this->fromGEOS($this->toGEOS($g)->convexHull());
    }

    /**
     * {@inheritdoc}
     */
    public function intersection(Geometry $a, Geometry $b)
    {
        return $this->fromGEOS($this->toGEOS($a)->intersection($this->toGEOS($b)));
    }

    /**
     * {@inheritdoc}
     */
    public function symDifference(Geometry $a, Geometry $b)
    {
        return $this->fromGEOS($this->toGEOS($a)->symDifference($this->toGEOS($b)));
    }

    /**
     * {@inheritdoc}
     */
    public function snapToGrid(Geometry $g, $size)
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }

    /**
     * {@inheritdoc}
     */
    public function simplify(Geometry $g, $tolerance)
    {
        return $this->fromGEOS($this->toGEOS($g)->simplify($tolerance));
    }

    /**
     * {@inheritdoc}
     */
    public function maxDistance(Geometry $a, Geometry $b)
    {
        throw GeometryException::unimplementedMethod(__METHOD__);
    }
}
