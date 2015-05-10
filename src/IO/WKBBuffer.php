<?php

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
     * @var integer
     */
    private $length;

    /**
     * @var integer
     */
    private $position = 0;

    /**
     * @var integer
     */
    private $machineByteOrder;

    /**
     * @var boolean
     */
    private $invert = false;

    /**
     * Class constructor.
     *
     * @param string $wkb
     */
    public function __construct($wkb)
    {
        $this->wkb = $wkb;
        $this->length = strlen($wkb);
        $this->machineByteOrder = WKBTools::getMachineByteOrder();
    }

    /**
     * Reads words from the buffer.
     *
     * @param integer $words      The number of words to read.
     * @param integer $wordLength The word length in bytes.
     *
     * @return string
     *
     * @throws GeometryIOException
     */
    private function read($words, $wordLength)
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
     * @return integer
     */
    private function readUnsignedChar()
    {
        return unpack('C', $this->read(1, 1))[1];
    }

    /**
     * Reads an unsigned long (32 bit) integer from the buffer.
     *
     * @return integer
     */
    public function readUnsignedLong()
    {
        return unpack('L', $this->read(1, 4))[1];
    }

    /**
     * Reads double-precision floating point numbers from the buffer.
     *
     * @param integer $count The number of doubles to read.
     *
     * @return float[] A 1-based array containing the numbers.
     */
    public function readDoubles($count)
    {
        return unpack('d' . $count, $this->read($count, 8));
    }

    /**
     * Reads the machine byte order from the buffer and stores the result to act accordingly.
     *
     * @throws GeometryIOException
     */
    public function readByteOrder()
    {
        $byteOrder = $this->readUnsignedChar();

        if ($byteOrder !== WKBTools::BIG_ENDIAN && $byteOrder !== WKBTools::LITTLE_ENDIAN) {
            throw GeometryIOException::invalidWKB('unknown byte order: ' . $byteOrder);
        }

        $this->invert = ($byteOrder !== $this->machineByteOrder);
    }

    /**
     * @param integer $bytes
     *
     * @return void
     */
    public function rewind($bytes)
    {
        $this->position -= $bytes;
    }

    /**
     * Checks whether the pointer is at the end of the buffer.
     *
     * @return boolean
     */
    public function isEndOfStream()
    {
        return $this->position === $this->length;
    }
}
