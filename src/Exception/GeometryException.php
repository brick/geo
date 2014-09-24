<?php

namespace Brick\Geo\Exception;

use Brick\Geo\Geometry;

/**
 * Default exception thrown by Geometry objects.
 */
class GeometryException extends \Exception
{
    /**
     * @return GeometryException
     */
    public static function unsupportedPlatform()
    {
        return new self('This platform has an unsupported endianness.');
    }

    /**
     * @param string $methodName
     *
     * @return GeometryException
     */
    public static function unimplementedMethod($methodName)
    {
        $message = sprintf('%s() is currently not implemented.', $methodName);

        return new self($message);
    }

    /**
     * @return GeometryException
     */
    public static function invalidWkt()
    {
        return new self('Invalid WKT.');
    }

    /**
     * @return GeometryException
     */
    public static function invalidWkb()
    {
        return new self('Invalid WKB.');
    }

    /**
     * @param string $wkbType
     *
     * @return GeometryException
     */
    public static function unsupportedWkbType($wkbType)
    {
        $message = sprintf('Unsupported WKB type: %s.', $wkbType);

        return new self($message);
    }

    /**
     * @param Geometry $geometry
     *
     * @return GeometryException
     */
    public static function unsupportedGeometryType(Geometry $geometry)
    {
        $message = sprintf('Unsupported geometry type: %s.', $geometry->geometryType());

        return new self($message);
    }

    /**
     * @param string $expectedClass
     * @param mixed  $value
     *
     * @return GeometryException
     */
    public static function unexpectedGeometryType($expectedClass, $value)
    {
        $value = is_object($value) ? get_class($value) : gettype($value);
        $message = sprintf('Unexpected geometry type: expected %s, got %s.', $expectedClass, $value);

        return new self($message);
    }

    /**
     * @param Geometry $a
     * @param Geometry $b
     *
     * @return GeometryException
     */
    public static function dimensionalityMix(Geometry $a, Geometry $b)
    {
        $message = 'Cannot mix dimensionality in a geometry: %s and %s.';
        $message = sprintf($message, self::typeOf($a), self::typeOf($b));

        return new self($message);
    }

    /**
     * Returns the geometry type with dimensionality, such as Point ZM.
     *
     * @param Geometry $geometry
     *
     * @return string
     */
    private static function typeOf(Geometry $geometry)
    {
        $type = $geometry->geometryType();

        $z = $geometry->is3D();
        $m = $geometry->isMeasured();

        if ($z || $m) {
            $type .= ' ';
        }
        if ($z) {
            $type .= 'Z';
        }
        if ($m) {
            $type .= 'M';
        }

        return $type;
    }

    /**
     * @return GeometryException
     */
    public static function noEngineSet()
    {
        return new self('A GeometryEngine must be set to support this feature.');
    }
}
