<?php

declare(strict_types=1);

namespace Brick\Geo\Io\Internal;

use Brick\Geo\Exception\GeometryIoException;
use Brick\Geo\Io\ByteOrder;

/**
 * Tools for WKB classes.
 *
 * @internal
 */
final readonly class WkbTools
{
    // EWKB only
    final public const Z = 0x80000000;
    final public const M = 0x40000000;
    final public const S = 0x20000000;

    /**
     * @throws GeometryIoException
     */
    private static function checkDoubleIs64Bit() : void
    {
        if (strlen(pack('d', 0.0)) !== 8) {
            throw new GeometryIoException('The double type is not 64 bit on this platform.');
        }
    }

    /**
     * Detects the machine byte order (big endian or little endian).
     *
     * @throws GeometryIoException
     */
    public static function getMachineByteOrder() : ByteOrder
    {
        /** @var ByteOrder|null $byteOrder */
        static $byteOrder;

        if ($byteOrder === null) {
            self::checkDoubleIs64Bit();

            $byteOrder = match (pack('L', 0x61626364)) {
                'abcd' => ByteOrder::BigEndian,
                'dcba' => ByteOrder::LittleEndian,
                default => throw GeometryIoException::unsupportedEndianness(),
            };
        }

        return $byteOrder;
    }
}
