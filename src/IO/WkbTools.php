<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;

/**
 * Helper class for WKB calculations.
 */
abstract class WkbTools
{
    const BIG_ENDIAN = 0;
    const LITTLE_ENDIAN = 1;

    /**
     * @return void
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    protected static function checkDoubleIs64Bit()
    {
        if (strlen(pack('d', 0.0)) != 8) {
            throw new GeometryException('The double type is not 64 bit on this platform');
        }
    }

    /**
     * Detects the machine byte order (big endian or little endian).
     *
     * @return integer
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public static function getMachineByteOrder()
    {
        self::checkDoubleIs64Bit();

        switch (pack('L', 0x61626364)) {
            case 'abcd':
                return self::BIG_ENDIAN;
            case 'dcba':
                return self::LITTLE_ENDIAN;
        }

        throw GeometryException::unsupportedPlatform();
    }
}
