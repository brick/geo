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
        $message = 'Incompatible dimensionality: cannot mix %s with %s.';

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
        $message = 'Incompatible SRID: cannot mix %s using SRID %d with %s using SRID %d.';

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
     * @param string $className
     * @param string $methodName
     *
     * @return GeometryException
     */
    public static function atLeastOneGeometryExpected($className, $methodName)
    {
        return new self(sprintf('%s::%s() expects at least 1 geometry, 0 given.', $className, $methodName));
    }
}
