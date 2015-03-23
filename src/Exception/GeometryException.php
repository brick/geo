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
     * @param string $geometryType
     *
     * @return static
     */
    public static function unsupportedGeometryType($geometryType)
    {
        $message = sprintf('Unsupported geometry type: %s.', $geometryType);

        return new static($message);
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
     * @param Geometry $geometry     The incompatible geometry.
     * @param string   $geometryType The container geometry type.
     * @param boolean  $is3D         Whether the container has a Z coordinate.
     * @param boolean  $isMeasured   Whether the container has a M coordinate.
     *
     * @return GeometryException
     */
    public static function incompatibleDimensionality(Geometry $geometry, $geometryType, $is3D, $isMeasured)
    {
        $message = 'Incompatible dimensionality: %s cannot contain %s.';

        $a = self::geometryType($geometryType, $is3D, $isMeasured);
        $b = self::typeOf($geometry);

        return new self(sprintf($message, $a, $b));
    }

    /**
     * @param Geometry $geometry     The incompatible geometry.
     * @param string   $geometryType The container geometry type.
     * @param integer  $srid         The container SRID.
     *
     * @return GeometryException
     */
    public static function incompatibleSRID(Geometry $geometry, $geometryType, $srid)
    {
        $message = 'Incompatible SRID: %s with SRID %d cannot contain %s with SRID %d.';

        return new self(sprintf($message, $geometryType, $srid, $geometry->geometryType(), $geometry->SRID()));
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
        return self::geometryType(
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
    private static function geometryType($geometryType, $is3D, $isMeasured)
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
