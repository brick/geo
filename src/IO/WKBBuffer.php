<?php

declare(strict_types=1);

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryIOException;

/**
 * Buffer class for reading binary data out of a WKB binary string.
 */
class WKBBuffer
{
    /**
     * @var string
     */
    private $wkb;

    /**
     * @var int
     */
    private $length;

    /**
     * @var int
     */
    private $position = 0;

    /**
     * @var int
     */
    private $machineByteOrder;

    /**
     * @var bool
     */
    private $invert = false;

    /**
     * Class constructor.
     *
     * @param string $wkb
     */
    public function __construct(string $wkb)
    {
        $this->wkb = $wkb;
        $this->length = strlen($wkb);
        $this->machineByteOrder = WKBTools::getMachineByteOrder();
    }

    /**
     * Reads words from the buffer.
     *
     * @param int $words      The number of words to read.
     * @param int $wordLength The word length in bytes.
     *
     * @return string
     *
     * @throws GeometryIOException
     */
    private function read(int $words, int $wordLength) : string
    {
        $length = $words * $wordLength;

        if ($this->position + $length > $this->length) {
            throw GeometryIOException::invalidWKB('unexpected end of stream');
        }

        if ($length === 1) {
            return $this->wkb[$this->position++];
        }

        if ($this->invert) {
            $data = '';

            for ($i = 0; $i < $words; $i++) {
                $data .= strrev(substr($this->wkb, $this->position + $i * $wordLength, $wordLength));
            }
        } else {
            $data = substr($this->wkb, $this->position, $length);
        }

        $this->position += $length;

        return $data;
    }

    /**
     * Reads an unsigned char (8 bit) integer from the buffer.
     *
     * @return int
     */
    private function readUnsignedChar() : int
    {
        return unpack('C', $this->read(1, 1))[1];
    }

    /**
     * Reads an unsigned long (32 bit) integer from the buffer.
     *
     * @return int
     */
    public function readUnsignedLong() : int
    {
        return unpack('L', $this->read(1, 4))[1];
    }

    /**
     * Reads double-precision floating point numbers from the buffer.
     *
     * @param int $count The number of doubles to read.
     *
     * @return float[] A 1-based array containing the numbers.
     */
    public function readDoubles($count) : array
    {
        return unpack('d' . $count, $this->read($count, 8));
    }

    /**
     * Reads the machine byte order from the buffer and stores the result to act accordingly.
     *
     * @return void
     *
     * @throws GeometryIOException
     */
    public function readByteOrder() : void
    {
        $byteOrder = $this->readUnsignedChar();

        if ($byteOrder !== WKBTools::BIG_ENDIAN && $byteOrder !== WKBTools::LITTLE_ENDIAN) {
            throw GeometryIOException::invalidWKB('unknown byte order: ' . $byteOrder);
        }

        $this->invert = ($byteOrder !== $this->machineByteOrder);
    }

    /**
     * @param int $bytes
     *
     * @return void
     */
    public function rewind(int $bytes) : void
    {
        $this->position -= $bytes;
    }

    /**
     * Checks whether the pointer is at the end of the buffer.
     *
     * @return bool
     */
    public function isEndOfStream() : bool
    {
        return $this->position === $this->length;
    }
}
