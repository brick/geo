<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

use Brick\Geo\Geometry;

/**
 * Exception thrown when cordinate systems are mixed.
 */
class CoordinateSystemException extends GeometryException
{
    public static function sridMix(Geometry $reference, Geometry $culprit) : CoordinateSystemException
    {
        return new CoordinateSystemException(sprintf(
            'SRID mix: %s with SRID %d cannot contain %s with SRID %d.',
            $reference->geometryType(),
            $reference->SRID(),
            $culprit->geometryType(),
            $culprit->SRID()
        ));
    }

    public static function dimensionalityMix(Geometry $reference, Geometry $culprit) : CoordinateSystemException
    {
        return new CoordinateSystemException(sprintf(
            'Dimensionality mix: %s %s cannot contain %s %s.',
            $reference->geometryType(),
            $reference->coordinateSystem()->coordinateName(),
            $culprit->geometryType(),
            $culprit->coordinateSystem()->coordinateName()
        ));
    }
}
