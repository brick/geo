<?php

namespace Brick\Geo\Exception;

/**
 * Exception thrown by geometry engines.
 *
 * This exception is notably thrown when a specific method is not implemented by a geometry engine.
 */
class GeometryEngineException extends GeometryException
{
    /**
     * @param \Exception $e
     *
     * @return GeometryEngineException
     */
    public static function operationNotSupportedByDatabase(\Exception $e)
    {
        return new self('This operation is not supported by the target database.', $e->getCode(), $e);
    }
}
