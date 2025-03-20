<?php

declare(strict_types=1);

namespace Brick\Geo\Proxy;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\_CLASSNAME_;
use Brick\Geo\Internal\ProxyRegistry;
use Override;

/**
 * Proxy class for _CLASSNAME_.
 *
 * @internal This class is not part of the public API and can change at any time.
 *           Please type-hint against _FQCN_ in your projects.
 */
class _CLASSNAME_Proxy extends _CLASSNAME_ implements ProxyInterface
{
    /**
     * The WKT or WKB data.
     */
    private readonly string $proxyData;

    /**
     * `true` if WKB, `false` if WKT.
     */
    private readonly bool $isProxyBinary;

    /**
     * The SRID of the underlying geometry.
     */
    private readonly int $proxySrid;

    /**
     * @param string $data     The WKT or WKB data.
     * @param bool   $isBinary Whether the data is binary (true) or text (false).
     * @param int    $srid     The SRID of the geometry.
     */
    public function __construct(string $data, bool $isBinary, int $srid = 0)
    {
        $this->proxyData     = $data;
        $this->isProxyBinary = $isBinary;
        $this->proxySrid     = $srid;
    }

    /**
     * Loads the underlying geometry.
     *
     * @throws GeometryIoException         If the proxy data is not valid.
     * @throws CoordinateSystemException   If the resulting geometry contains mixed coordinate systems.
     * @throws InvalidGeometryException    If the resulting geometry is not valid.
     * @throws UnexpectedGeometryException If the resulting geometry is not an instance of the proxied class.
     */
    private function load() : _CLASSNAME_
    {
        return $this->isProxyBinary
            ? _CLASSNAME_::fromBinary($this->proxyData, $this->proxySrid)
            : _CLASSNAME_::fromText($this->proxyData, $this->proxySrid);
    }

    #[Override]
    public function isLoaded() : bool
    {
        return ProxyRegistry::hasProxiedGeometry($this);
    }

    #[Override]
    public function getGeometry() : _CLASSNAME_
    {
        $geometry = ProxyRegistry::getProxiedGeometry($this);

        if ($geometry !== null) {
            return $geometry;
        }

        $geometry = $this->load();
        ProxyRegistry::setProxiedGeometry($this, $geometry);

        return $geometry;
    }

    #[Override]
    public function isProxyBinary() : bool
    {
        return $this->isProxyBinary;
    }

    #[Override]
    public static function fromText(string $wkt, int $srid = 0) : Geometry
    {
        return new self($wkt, false, $srid);
    }

    #[Override]
    public static function fromBinary(string $wkb, int $srid = 0) : Geometry
    {
        return new self($wkb, true, $srid);
    }

    #[Override]
    public function srid() : int
    {
        return $this->proxySrid;
    }

    #[Override]
    public function asText() : string
    {
        if (! $this->isProxyBinary) {
            return $this->proxyData;
        }

        return $this->getGeometry()->asText();
    }

    #[Override]
    public function asBinary() : string
    {
        if ($this->isProxyBinary) {
            return $this->proxyData;
        }

        return $this->getGeometry()->asBinary();
    }

// BEGIN METHOD TEMPLATE
    #[Override]
    function _TEMPLATE_()
    {
        return $this->getGeometry()->_METHOD_();
    }
// END METHOD TEMPLATE
}
