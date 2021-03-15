<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

use Brick\Geo\Geometry;

/**
 * Exception thrown when cordinate systems are mixed.
 */
class CoordinateSystemException extends GeometryException
{
    public static function sridMix(int $srid1, int $srid2) : CoordinateSystemException
    {
        return new CoordinateSystemException(sprintf(
            'SRID mix: cannot mix SRID %d with SRID %d.',
            $srid1,
            $srid2
        ));
    }

    public static function sridCompositionMix(Geometry $reference, Geometry $culprit) : CoordinateSystemException
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
