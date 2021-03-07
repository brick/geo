<?php

declare(strict_types=1);

namespace Brick\Geo\Doctrine\Types;

use Brick\Geo\Geometry;
use Brick\Geo\Proxy\GeometryProxy;
use Brick\Geo\Proxy\ProxyInterface;
use Doctrine\DBAL\Platforms\MySqlPlatform;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\DBAL\Types\Type;

/**
 * Doctrine type for Geometry.
 */
class GeometryType extends Type
{
    /**
     * The default SRID to use for geometries when talking to the database.
     *
     * This is the SRID that will be used when retrieving geometries from the database,
     * as the WKT and WKB formats do not include the SRID information.
     *
     * Due to current limitations in Doctrine, this will also be used when sending geometries to the database,
     * in place of the actual SRID of the geometry.
     *
     * @see https://github.com/doctrine/orm/issues/4114
     *
     * @var int
     */
    public static $srid = 0;

    /**
     * @psalm-return class-string<ProxyInterface&Geometry>
     */
    protected function getProxyClassName() : string
    {
        return GeometryProxy::class;
    }

    public function getName()
    {
        return 'Geometry';
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform)
    {
        if ($platform instanceof PostgreSqlPlatform) {
            return 'GEOMETRY';
        }

        return strtoupper($this->getName());
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if (is_resource($value)) {
            $value = stream_get_contents($value);
        }

        $proxyClassName = $this->getProxyClassName();

        return new $proxyClassName($value, true, self::$srid);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Geometry) {
            return $value->asBinary();
        }

        $type = is_object($value) ? get_class($value) : gettype($value);

        throw new \UnexpectedValueException(sprintf('Expected %s, got %s.', Geometry::class, $type));
    }

    public function convertToDatabaseValueSQL($sqlExpr, AbstractPlatform $platform)
    {
        if ($platform instanceof MySqlPlatform) {
            $sqlExpr = sprintf('CAST(%s AS BINARY)', $sqlExpr);
        }

        return sprintf('ST_GeomFromWKB(%s, %d)', $sqlExpr, self::$srid);
    }

    public function convertToPHPValueSQL($sqlExpr, $platform)
    {
        return sprintf('ST_AsBinary(%s)', $sqlExpr);
    }

    public function canRequireSQLConversion()
    {
        return true;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform)
    {
        return true;
    }

    public function getBindingType()
    {
        return \PDO::PARAM_LOB;
    }
}
