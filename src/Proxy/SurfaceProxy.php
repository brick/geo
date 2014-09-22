<?php

namespace Brick\Geo\Proxy;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\IO\WkbReader;
use Brick\Geo\IO\WktReader;

/**
 * Proxy class for Brick\Geo\Surface.
 */
class SurfaceProxy extends \Brick\Geo\Surface
{
    /**
     * The WKT or WKB data.
     *
     * @var string
     */
    private $data;

    /**
     * `true` if WKB, `false` if WKT.
     *
     * @var boolean
     */
    private $isBinary;

    /**
     * The underlying geometry, or NULL if not yet loaded.
     *
     * @var \Brick\Geo\Surface|null
     */
    private $geometry;

    /**
     * Class constructor.
     *
     * @param string  $data
     * @param boolean $isBinary
     */
    public function __construct($data, $isBinary)
    {
        $this->data     = $data;
        $this->isBinary = $isBinary;
    }

    /**
     * @return void
     *
     * @throws GeometryException
     */
    private function load()
    {
        $geometry = $this->isBinary
            ? WkbReader::read($this->data)
            : WktReader::read($this->data);

        if (! $geometry instanceof \Brick\Geo\Surface) {
            throw GeometryException::unexpectedGeometryType(\Brick\Geo\Surface::class, $geometry);
        }

        $this->geometry = $geometry;
    }

    /**
     * Returns whether the underlying geometry is loaded.
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->geometry !== null;
    }

    /**
     * Loads and returns the underlying geometry.
     *
     * @return \Brick\Geo\Surface
     */
    public function getGeometry()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText($wkt)
    {
        return new self($wkt, false);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromBinary($wkb)
    {
        return new self($wkb, true);
    }

    /**
     * {@inheritdoc}
     */
    public function asText()
    {
        if (! $this->isBinary) {
            return $this->data;
        }

        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->asText();
    }

    /**
     * {@inheritdoc}
     */
    public function asBinary()
    {
        if ($this->isBinary) {
            return $this->data;
        }

        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->asBinary();
    }
    /**
     * {@inheritdoc}
     */
    public function area()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->area();
    }

    /**
     * {@inheritdoc}
     */
    public function centroid()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->centroid();
    }

    /**
     * {@inheritdoc}
     */
    public function pointOnSurface()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->pointOnSurface();
    }

    /**
     * {@inheritdoc}
     */
    public function boundary()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->boundary();
    }

    /**
     * {@inheritdoc}
     */
    public function dimension()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->dimension();
    }

    /**
     * {@inheritdoc}
     */
    public function coordinateDimension()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->coordinateDimension();
    }

    /**
     * {@inheritdoc}
     */
    public function spatialDimension()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->spatialDimension();
    }

    /**
     * {@inheritdoc}
     */
    public function geometryType()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->geometryType();
    }

    /**
     * {@inheritdoc}
     */
    public function SRID()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->SRID();
    }

    /**
     * {@inheritdoc}
     */
    public function envelope()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->envelope();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function isSimple()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->isSimple();
    }

    /**
     * {@inheritdoc}
     */
    public function is3D()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->is3D();
    }

    /**
     * {@inheritdoc}
     */
    public function isMeasured()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->isMeasured();
    }

    /**
     * {@inheritdoc}
     */
    public function equals(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->equals($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function disjoint(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->disjoint($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function intersects(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->intersects($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function touches(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->touches($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function crosses(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->crosses($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function within(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->within($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function contains(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->contains($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function overlaps(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->overlaps($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function relate(\Brick\Geo\Surface $geometry, $matrix)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->relate($geometry, $matrix);
    }

    /**
     * {@inheritdoc}
     */
    public function locateAlong($mValue)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->locateAlong($mValue);
    }

    /**
     * {@inheritdoc}
     */
    public function locateBetween($mStart, $mEnd)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->locateBetween($mStart, $mEnd);
    }

    /**
     * {@inheritdoc}
     */
    public function distance(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->distance($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function buffer($distance)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->buffer($distance);
    }

    /**
     * {@inheritdoc}
     */
    public function convexHull()
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->convexHull();
    }

    /**
     * {@inheritdoc}
     */
    public function intersection(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->intersection($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function union(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->union($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function difference(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->difference($geometry);
    }

    /**
     * {@inheritdoc}
     */
    public function symDifference(\Brick\Geo\Surface $geometry)
    {
        if ($this->geometry === null) {
            $this->load();
        }

        return $this->geometry->symDifference($geometry);
    }

}
