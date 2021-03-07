<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;

/**
 * Tools for WKB classes.
 */
abstract class WKBTools
{
    public const BIG_ENDIAN    = 0;
    public const LITTLE_ENDIAN = 1;

    /**
     * @throws GeometryIOException
     */
    private static function checkDoubleIs64Bit() : void
    {
        if (strlen(pack('d', 0.0)) !== 8) {
            throw new GeometryIOException('The double type is not 64 bit on this platform.');
        }
    }

    /**
     * @throws \InvalidArgumentException
     */
    public static function checkByteOrder(int $byteOrder) : void
    {
        if ($byteOrder !== self::BIG_ENDIAN && $byteOrder !== self::LITTLE_ENDIAN) {
            throw new \InvalidArgumentException('Invalid byte order: ' . var_export($byteOrder, true));
        }
    }

    /**
     * Detects the machine byte order (big endian or little endian).
     *
     * @psalm-return self::BIG_ENDIAN|self::LITTLE_ENDIAN
     *
     * @throws GeometryIOException
     */
    public static function getMachineByteOrder() : int
    {
        /** @psalm-var self::BIG_ENDIAN|self::LITTLE_ENDIAN|null $byteOrder */
        static $byteOrder;

        if ($byteOrder === null) {
            self::checkDoubleIs64Bit();

            switch (pack('L', 0x61626364)) {
                case 'abcd':
                    $byteOrder = self::BIG_ENDIAN;
                    break;

                case 'dcba':
                    $byteOrder = self::LITTLE_ENDIAN;
                    break;

                default:
                    throw GeometryIOException::unsupportedEndianness();
            }
        }

        return $byteOrder;
    }
}
