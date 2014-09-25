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
     * @param Geometry|string $a
     * @param Geometry|string $b
     *
     * @return GeometryException
     */
    public static function dimensionalityMix($a, $b)
    {
        $message = 'Cannot mix dimensionality in a geometry: %s and %s.';

        if ($a instanceof Geometry) {
            $a = self::typeOf($a);
        }
        if ($b instanceof Geometry) {
            $b = self::typeOf($b);
        }

        return new self(sprintf($message, $a, $b));
    }

    /**
     * @param boolean  $is3D
     * @param boolean  $isMeasured
     * @param Geometry $geometry
     *
     * @return GeometryException
     */
    public static function collectionDimensionalityMix($is3D, $isMeasured, Geometry $geometry)
    {
        return self::dimensionalityMix(self::geometryZM('GeometryCollection', $is3D, $isMeasured), $geometry);
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
        return self::geometryZM(
            $geometry->geometryType(),
            $geometry->is3D(),
            $geometry->isMeasured()
        );
    }

    /**
     * @param string  $geometryType
     * @param boolean $is3D
     * @param boolean $isMeasured
     *
     * @return string
     */
    private static function geometryZM($geometryType, $is3D, $isMeasured)
    {
        if ($is3D || $isMeasured) {
            $geometryType .= ' ';
        }

        if ($is3D) {
            $geometryType .= 'Z';
        }
        if ($isMeasured) {
            $geometryType .= 'M';
        }

        return $geometryType;
    }

    /**
     * @return GeometryException
     */
    public static function noEngineSet()
    {
        return new self('A GeometryEngine must be set to support this feature.');
    }
}
