<?php

declare(strict_types=1);

namespace Brick\Geo\Proxy;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Exception\CoordinateSystemException;
use Brick\Geo\Exception\InvalidGeometryException;
use Brick\Geo\Exception\UnexpectedGeometryException;
use Brick\Geo\Geometry;
use Brick\Geo\MultiPolygon;
use Brick\Geo\Internal\ProxyRegistry;
use Override;

/**
 * Proxy class for MultiPolygon.
 *
 * @internal This class is not part of the public API and can change at any time.
 *           Please type-hint against Brick\Geo\MultiPolygon in your projects.
 */
final readonly class MultiPolygonProxy extends MultiPolygon implements ProxyInterface
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
    private function load() : MultiPolygon
    {
        return $this->isProxyBinary
            ? MultiPolygon::fromBinary($this->proxyData, $this->proxySrid)
            : MultiPolygon::fromText($this->proxyData, $this->proxySrid);
    }

    #[Override]
    public function isLoaded() : bool
    {
        return ProxyRegistry::hasProxiedGeometry($this);
    }

    #[Override]
    public function getGeometry() : MultiPolygon
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


    #[Override]
    public function toArray(): array
    {
        return $this->getGeometry()->toArray();
    }

    #[Override]
    public function dimension(): int
    {
        return $this->getGeometry()->dimension();
    }

    #[Override]
    public function project(\Brick\Geo\Projector\Projector $projector): \Brick\Geo\MultiPolygon
    {
        return $this->getGeometry()->project($projector);
    }

    #[Override]
    public function numGeometries(): int
    {
        return $this->getGeometry()->numGeometries();
    }

    #[Override]
    public function geometryN(int $n): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->geometryN($n);
    }

    #[Override]
    public function geometries(): array
    {
        return $this->getGeometry()->geometries();
    }

    #[Override]
    public function getBoundingBox(): \Brick\Geo\BoundingBox
    {
        return $this->getGeometry()->getBoundingBox();
    }

    #[Override]
    public function count(): int
    {
        return $this->getGeometry()->count();
    }

    #[Override]
    public function getIterator(): \ArrayIterator
    {
        return $this->getGeometry()->getIterator();
    }

    #[Override]
    public function withAddedGeometries(\Brick\Geo\Geometry ...$geometries): \Brick\Geo\GeometryCollection
    {
        return $this->getGeometry()->withAddedGeometries(...$geometries);
    }

    #[Override]
    public function coordinateDimension(): int
    {
        return $this->getGeometry()->coordinateDimension();
    }

    #[Override]
    public function spatialDimension(): int
    {
        return $this->getGeometry()->spatialDimension();
    }

    #[Override]
    public function isEmpty(): bool
    {
        return $this->getGeometry()->isEmpty();
    }

    #[Override]
    public function is3D(): bool
    {
        return $this->getGeometry()->is3D();
    }

    #[Override]
    public function isMeasured(): bool
    {
        return $this->getGeometry()->isMeasured();
    }

    #[Override]
    public function coordinateSystem(): \Brick\Geo\CoordinateSystem
    {
        return $this->getGeometry()->coordinateSystem();
    }

    #[Override]
    public function withSrid(int $srid): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->withSrid($srid);
    }

    #[Override]
    public function toXy(): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->toXy();
    }

    #[Override]
    public function withoutZ(): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->withoutZ();
    }

    #[Override]
    public function withoutM(): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->withoutM();
    }

    #[Override]
    public function withRoundedCoordinates(int $precision): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->withRoundedCoordinates($precision);
    }

    #[Override]
    public function swapXy(): \Brick\Geo\Geometry
    {
        return $this->getGeometry()->swapXy();
    }

    #[Override]
    public function isIdenticalTo(\Brick\Geo\Geometry $that): bool
    {
        return $this->getGeometry()->isIdenticalTo($that);
    }

}
