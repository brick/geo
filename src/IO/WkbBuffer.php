<?php

namespace Brick\Geo\IO;

use Brick\Geo\Exception\GeometryException;

/**
 * Buffer class for reading binary data out of a WKB binary string.
 */
class WkbBuffer
{
    /**
     * @var string
     */
    protected $wkb;

    /**
     * @var integer
     */
    protected $length;

    /**
     * @var integer
     */
    protected $position = 0;

    /**
     * @var integer
     */
    protected $machineByteOrder;

    /**
     * @var boolean
     */
    protected $invert = false;

    /**
     * Class constructor.
     *
     * @param string $wkb
     */
    public function __construct($wkb)
    {
        $this->wkb = $wkb;
        $this->length = strlen($wkb);
        $this->machineByteOrder = WkbTools::getMachineByteOrder();
    }

    /**
     * Reads $length bytes from the buffer.
     *
     * @param integer $length
     *
     * @return string
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    protected function read($length)
    {
        if ($this->position + $length > $this->length) {
            throw GeometryException::invalidWkb();
        }

        $data = substr($this->wkb, $this->position, $length);
        $this->position += $length;

        return $this->invert ? strrev($data) : $data;
    }

    /**
     * Reads one byte from the buffer.
     *
     * @return integer
     */
    protected function readByte()
    {
        $data = unpack('cbyte', $this->read(1));

        return $data['byte'];
    }

    /**
     * Reads the machine byte order from the buffer and stores the result to act accordingly.
     *
     * @throws \Brick\Geo\Exception\GeometryException
     */
    public function readByteOrder()
    {
        $byteOrder = $this->readByte();

        if ($byteOrder != WkbTools::BIG_ENDIAN && $byteOrder != WkbTools::LITTLE_ENDIAN) {
            throw GeometryException::invalidWkb();
        }

        $this->invert = ($byteOrder != $this->machineByteOrder);
    }

    /**
     * Reads an unsigned integer from the buffer.
     *
     * @return integer
     */
    public function readUnsignedInteger()
    {
        $data = unpack('Luint', $this->read(4));

        return $data['uint'];
    }

    /**
     * Reads a double-precision floating point value from the buffer.
     *
     * @return float
     */
    public function readDouble()
    {
        $data = unpack('ddouble', $this->read(8));

        return $data['double'];
    }

    /**
     * Checks whether the pointer is at the end of the buffer.
     *
     * @return boolean
     */
    public function isEndOfStream()
    {
        return $this->position == $this->length;
    }
}
