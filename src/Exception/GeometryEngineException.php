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
     * @return GeometryEngineException
     */
    public static function noEngineSet() : GeometryEngineException
    {
        return new self('A GeometryEngine must be set to support this feature.');
    }

    /**
     * @param string $methodName
     *
     * @return GeometryEngineException
     */
    public static function unimplementedMethod(string $methodName) : GeometryEngineException
    {
        $message = sprintf('%s() is currently not implemented.', $methodName);

        return new self($message);
    }

    /**
     * @param \Exception $e
     *
     * @return GeometryEngineException
     */
    public static function operationNotSupportedByEngine(\Exception $e) : GeometryEngineException
    {
        return new self('This operation is not supported by the geometry engine.', $e->getCode(), $e);
    }

    /**
     * @return GeometryEngineException
     */
    public static function operationYieldedNoResult() : GeometryEngineException
    {
        return new self('This operation yielded no result on the target database.');
    }
}
