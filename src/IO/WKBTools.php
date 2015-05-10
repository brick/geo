<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;

/**
 * Tools for WKB classes.
 */
abstract class WKBTools
{
    const BIG_ENDIAN    = 0;
    const LITTLE_ENDIAN = 1;

    /**
     * @return void
     *
     * @throws GeometryIOException
     */
    private static function checkDoubleIs64Bit()
    {
        if (strlen(pack('d', 0.0)) !== 8) {
            throw new GeometryIOException('The double type is not 64 bit on this platform.');
        }
    }

    /**
     * @param integer $byteOrder
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public static function checkByteOrder($byteOrder)
    {
        if ($byteOrder !== self::BIG_ENDIAN && $byteOrder !== self::LITTLE_ENDIAN) {
            throw new \InvalidArgumentException('Invalid byte order: ' . var_export($byteOrder, true));
        }
    }

    /**
     * Detects the machine byte order (big endian or little endian).
     *
     * @return integer
     *
     * @throws GeometryIOException
     */
    public static function getMachineByteOrder()
    {
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
