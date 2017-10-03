<?php

declare(strict_types=1);

namespace Brick\Geo\Exception;

/**
 * Exception thrown when trying to get a non-existent value out of an empty geometry.
 */
class EmptyGeometryException extends GeometryException
{
}
