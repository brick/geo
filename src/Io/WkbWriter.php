<?php

declare(strict_types=1);

namespace Brick\Geo\Io;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Geometry;
use Brick\Geo\Io\Internal\AbstractWkbWriter;
use Override;

/**
 * Writes geometries in the WKB format.
 */
final readonly class WkbWriter extends AbstractWkbWriter
{
    /**
     * @param bool $supportEmptyPointWithNan Whether to support PostGIS-style empty points with NaN coordinates.
     *
     * @throws GeometryIoException
     */
    public function __construct(
        ?ByteOrder $byteOrder = null,
        bool $supportEmptyPointWithNan = false,
    ) {
        parent::__construct($byteOrder, $supportEmptyPointWithNan);
    }

    #[Override]
    protected function packHeader(Geometry $geometry, bool $outer) : string
    {
        $geometryType = $geometry->geometryTypeBinary();

        $cs = $geometry->coordinateSystem();

        if ($cs->hasZ()) {
            $geometryType += 1000;
        }

        if ($cs->hasM()) {
            $geometryType += 2000;
        }

        return $this->packUnsignedInteger($geometryType);
    }
}
