<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;
use Brick\Geo\Geometry;
use Brick\Geo\IO\Internal\AbstractWkbReader;
use Brick\Geo\IO\Internal\EwkbTools;
use Brick\Geo\IO\Internal\WkbBuffer;
use Brick\Geo\IO\Internal\WkbGeometryHeader;
use Override;

/**
 * Reads geometries out of the Extended WKB format designed by PostGIS.
 */
final class EwkbReader extends AbstractWkbReader
{
    /**
     * @throws GeometryIOException
     */
    public function read(string $ewkb) : Geometry
    {
        $buffer = new WkbBuffer($ewkb);
        $geometry = $this->readGeometry($buffer, 0);

        if (! $buffer->isEndOfStream()) {
            throw GeometryIOException::invalidWkb('unexpected data at end of stream');
        }

        return $geometry;
    }

    #[Override]
    protected function readGeometryHeader(WkbBuffer $buffer) : WkbGeometryHeader
    {
        $header = $buffer->readUnsignedLong();

        $srid = null;

        if ($header >= 0 && $header < 4000) {
            $geometryType = $header % 1000;
            $dimension = ($header - $geometryType) / 1000;

            $hasZ = ($dimension === 1 || $dimension === 3);
            $hasM = ($dimension === 2 || $dimension === 3);
        } else {
            $geometryType = $header & 0xFFF;

            $hasZ    = (($header & EwkbTools::Z) !== 0);
            $hasM    = (($header & EwkbTools::M) !== 0);
            $hasSRID = (($header & EwkbTools::S) !== 0);

            if ($hasSRID) {
                $srid = $buffer->readUnsignedLong();
            }
        }

        return new WkbGeometryHeader($geometryType, $hasZ, $hasM, $srid);
    }
}
