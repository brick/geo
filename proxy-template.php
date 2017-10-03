<?php

declare(strict_types=1);

namespace Brick\Geo\Proxy;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Geometry;

/**
 * Proxy class for _CLASSNAME_.
 */
class _CLASSNAME_Proxy extends _FQCN_ implements ProxyInterface
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
     * @var bool
     */
    private $proxyIsBinary;

    /**
     * The SRID of the underlying geometry.
     *
     * @var int
     */
    private $proxySRID;

    /**
     * The underlying geometry, or NULL if not yet loaded.
     *
     * @var _FQCN_|null
     */
    private $proxyGeometry;

    /**
     * Class constructor.
     *
     * @param string $data     The WKT or WKB data.
     * @param bool   $isBinary Whether the data is binary (true) or text (false).
     * @param int    $srid     The SRID of the geometry.
     */
    public function __construct(string $data, bool $isBinary, int $srid = 0)
    {
        $this->proxyData     = $data;
        $this->proxyIsBinary = $isBinary;
        $this->proxySRID     = $srid;
    }

    /**
     * Loads the underlying geometry.
     *
     * @return void
     *
     * @throws GeometryIOException         If the proxy data is not valid.
     * @throws CoordinateSystemException   If the resulting geometry contains mixed coordinate systems.
     * @throws InvalidGeometryException    If the resulting geometry is not valid.
     * @throws UnexpectedGeometryException If the resulting geometry is not an instance of the proxied class.
     */
    private function load() : void
    {
        $this->proxyGeometry = $this->proxyIsBinary
            ? _FQCN_::fromBinary($this->proxyData, $this->proxySRID)
            : _FQCN_::fromText($this->proxyData, $this->proxySRID);
    }

    /**
     * {@inheritdoc}
     */
    public function isLoaded() : bool
    {
        return $this->proxyGeometry !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function getGeometry() : Geometry
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry;
    }

    /**
     * {@inheritdoc}
     */
    public static function fromText(string $wkt, int $srid = 0) : Geometry
    {
        return new self($wkt, false, $srid);
    }

    /**
     * {@inheritdoc}
     */
    public static function fromBinary(string $wkb, int $srid = 0) : Geometry
    {
        return new self($wkb, true, $srid);
    }

    /**
     * {@inheritdoc}
     */
    public function SRID() : int
    {
        return $this->proxySRID;
    }

    /**
     * {@inheritdoc}
     */
    public function asText() : string
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
    public function asBinary() : string
    {
        if ($this->proxyIsBinary) {
            return $this->proxyData;
        }

        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->asBinary();
    }

// BEGIN METHOD TEMPLATE
    /**
     * {@inheritdoc}
     */
    function _TEMPLATE_()
    {
        if ($this->proxyGeometry === null) {
            $this->load();
        }

        return $this->proxyGeometry->_METHOD_();
    }
// END METHOD TEMPLATE
}
