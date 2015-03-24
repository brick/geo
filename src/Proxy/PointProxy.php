<?php

namespace Brick\Geo\Proxy;

use Brick\Geo\Exception\GeometryException;
use Brick\Geo\IO\WKBReader;
use Brick\Geo\IO\WKTReader;
use Brick\Geo\Point;

/**
 * Proxy class for Point.
 */
class PointProxy extends Point implements ProxyInterface
{
    /**
     * The WKT or WKB data.
     *
     * @var string
     */
    private $proxyData;

    /**
     * `true` if WKB, `false` if WKT.
     *
     * @var boolean
     */
    private $proxyIsBinary;

    /**
     * The SRID of the underlying geometry.
     *
     * @var integer
     */
    private $proxySRID;

    /**
     * The underlying geometry, or NULL if not yet loaded.
     *
     * @var Point|null
     */
    private $proxyGeometry;

    /**
     * Class constructor.
     *
     * @param string  $data     The WKT or WKB data.
     * @param boolean $isBinary Whether the data is binary (true) or text (false).
     * @param integer $srid     The SRID of the geometry.
     */
    public function __construct($data, $isBinary, $srid = 0)
    {
        $this->proxyData     = $data;
        $this->proxyIsBinary = $isBinary;
        $this->proxySRID     = $srid;
    }

    /**
     * @return void
     *
     * @throws GeometryException
     */
    private function load()
    {
        $geometry = $this->proxyIsBinary
            ? (new WKBReader())->read($this->proxyData, $this->proxySRID)
            : (new WKTReader())->read($this->proxyData, $this->proxySRID);

        if (! $geometry instanceof Point) {
            throw GeometryException::unexpectedGeometryType(Point::class, $geometry);
        }

        $this->proxyGeometry = $geometry;
    }

    /**
     * {@inheritdoc}
     */
    public function isLoaded()
    {
        return $this->proxyGeometry !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getGeometry()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText($wkt, $srid = 0)
    {
        return new self($wkt, false, $srid);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromBinary($wkb, $srid = 0)
    {
        return new self($wkb, true, $srid);
    }

    /**
     * {@inheritdoc}
     */
    public function asText()
    {
        if (! $this->proxyIsBinary) {
            return $this->proxyData;
        }

        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->asText();
    }

    /**
     * {@inheritdoc}
     */
    public function asBinary()
    {
        if ($this->proxyIsBinary) {
            return $this->proxyData;
        }

        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->asBinary();
    }


    /**
     * {@inheritdoc}
     */
    public function x()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->x();
    }

    /**
     * {@inheritdoc}
     */
    public function y()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->y();
    }

    /**
     * {@inheritdoc}
     */
    public function z()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->z();
    }

    /**
     * {@inheritdoc}
     */
    public function m()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->m();
    }

    /**
     * {@inheritdoc}
     */
    public function withX($x)
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withX($x);
    }

    /**
     * {@inheritdoc}
     */
    public function withY($y)
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withY($y);
    }

    /**
     * {@inheritdoc}
     */
    public function withZ($z)
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withZ($z);
    }

    /**
     * {@inheritdoc}
     */
    public function withM($m)
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withM($m);
    }

    /**
     * {@inheritdoc}
     */
    public function withoutZ()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withoutZ();
    }

    /**
     * {@inheritdoc}
     */
    public function withoutM()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withoutM();
    }

    /**
     * {@inheritdoc}
     */
    public function withoutZM()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withoutZM();
    }

    /**
     * {@inheritdoc}
     */
    public function withSRID($srid)
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->withSRID($srid);
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function coordinateDimension()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->coordinateDimension();
    }

    /**
     * {@inheritdoc}
     */
    public function spatialDimension()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->spatialDimension();
    }

    /**
     * {@inheritdoc}
     */
    public function SRID()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->SRID();
    }

    /**
     * {@inheritdoc}
     */
    public function isEmpty()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->isEmpty();
    }

    /**
     * {@inheritdoc}
     */
    public function is3D()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->is3D();
    }

    /**
     * {@inheritdoc}
     */
    public function isMeasured()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->isMeasured();
    }

}
