<?php

declare(strict_types=1);

namespace Brick\Geo\IO\Internal;

use Brick\Geo\Exception\GeometryIOException;

/**
 * Tools for WKB classes.
 *
 * @internal
 */
abstract class WKBTools
{
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
     * Detects the machine byte order (big endian or little endian).
     *
     * @throws GeometryIOException
     */
    public static function getMachineByteOrder() : WKBByteOrder
    {
        /** @var WKBByteOrder|null $byteOrder */
        static $byteOrder;

        if ($byteOrder === null) {
            self::checkDoubleIs64Bit();

            $byteOrder = match (pack('L', 0x61626364)) {
                'abcd' => WKBByteOrder::BIG_ENDIAN,
                'dcba' => WKBByteOrder::LITTLE_ENDIAN,
                default => throw GeometryIOException::unsupportedEndianness(),
            };
        }

        return $byteOrder;
    }
}
