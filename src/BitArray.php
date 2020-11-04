<?php

namespace golovanovya\bitarray;

use ArrayAccess;
use Countable;
use Iterator;
use JsonSerializable;
use OutOfBoundsException;

/**
 * Array of bits
 */
class BitArray implements Iterator, Countable, JsonSerializable, ArrayAccess
{
    private $array;
    private $size;
    private $position;

    /**
     * Constructor array of bits
     *
     * @param int $size size of array
     * @param int $set initialized value true = 1, false = 0
     */
    public function __construct($size, $set = false)
    {
        $this->setSize($size);
        $this->rewind();
        $placeholder = $set ? 0xff : 0;
        $this->fill($placeholder);
    }

    /**
     * Set default
     *
     * @param int $size
     * @param int $placeholder
     */
    private function fill($placeholder)
    {
        $bytes = intval(ceil($this->count() / 8));
        $this->array = str_repeat(chr($placeholder), $bytes);
    }

    /**
     * Set array size
     *
     * @param int $size
     * @return void
     */
    protected function setSize($size)
    {
        if ($size < 0) {
            throw new \InvalidArgumentException('Size can\'t be less then 0, ' . $size . ' given');
        }
        $this->size = $size;
    }

    /**
     * Alias for getSize() for Countable interface
     */
    public function count()
    {
        return $this->getSize();
    }

    /**
     * Array size
     *
     * @return void
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Calculate bit position
     *
     * @param int $pos
     * @return int
     */
    private function getBitPos($pos)
    {
        return $pos % 8;
    }

    /**
     * Calculate byte position
     *
     * @param int $pos
     * @return int
     */
    private function getBytePos($pos)
    {
        return intdiv($pos, 8);
    }

    /**
     * Get byte from array by position
     *
     * @param int $pos
     * @return int
     */
    private function getByte($pos)
    {
        return ord($this->array[$pos]);
    }
    
    /**
     * Modify bit in byte
     *
     * @param int $byte
     * @param int $pos
     * @return int modified byte
     */
    private function resetBit($pos, $byte)
    {
        return $byte & ~(1 << $pos);
    }

    /**
     * Modify bit in byte
     *
     * @param int $byte
     * @param int $pos
     * @return int
     */
    private function setBit($pos, $byte)
    {
        return $byte | (1 << $pos);
    }

    /**
     * Get bit value from byte
     *
     * @param int $byte
     * @param int $pos
     * @return bool
     */
    private function getBit($pos, $byte)
    {
        $mask = 1 << $pos;
        return ($byte & $mask) >> $pos;
    }

    /**
     * Validate key
     *
     * @param int $key
     * @return bool
     */
    private function validate($key)
    {
        return $key >= 0
            && $key < $this->size;
    }

    /**
     * Validate key
     *
     * @param int $key
     * @return boolean
     * @throws OutOfBoundsException
     */
    protected function validateKey($key)
    {
        if (!$this->validate($key)) {
            throw new OutOfBoundsException($key);
        }
    }

    public function offsetExists($offset)
    {
        return $this->validate($offset);
    }

    /**
     * Get array item by key
     *
     * @param int $key
     * @return int
     */
    public function get($key)
    {
        $this->validateKey($key);
        $bit = $this->getBitPos($key);
        $byte = $this->getBytePos($key);
        return $this->getBit($bit, $this->getByte($byte));
    }

    /**
     * Alias get for ArrayAccess
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Set value
     * @throws OutOfBoundsException
     */
    public function offsetSet($offset, $value)
    {
        if (boolval($value)) {
            $this->set($offset);
        } else {
            $this->reset($offset);
        }
    }

    /**
     * Set 0 for key
     * @param $offset array key
     */
    public function offsetUnset($offset)
    {
        $this->reset($offset);
    }

    /**
     * Set 0 for array item value by key
     *
     * @param int $key
     * @return void
     */
    public function reset($key)
    {
        $this->validateKey($key);
        $bit = $this->getBitPos($key);
        $byte = $this->getBytePos($key);
        $this->array[$byte] = chr($this->resetBit($bit, $this->getByte($byte)));
    }

    /**
     * Set 1 for array item value by key
     *
     * @param int $key
     * @return void
     */
    public function set($key)
    {
        $this->validateKey($key);
        $bit = $this->getBitPos($key);
        $byte = $this->getBytePos($key);
        $this->array[$byte] = chr($this->setBit($bit, $this->getByte($byte)));
    }

    /** begin Iterator methods */
    /**
     * Reset position
     *
     * @return void
     */
    public function rewind()
    {
        $this->position = 0;
    }

    /**
     * Get current value
     *
     * @return int
     */
    public function current()
    {
        return $this->get($this->position);
    }

    /**
     * Get current position
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Set next position
     *
     * @return void
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * Validate position
     *
     * @return bool
     */
    public function valid()
    {
        return $this->validate($this->position);
    }

    /** end Iterator methods */

    /**
     * Array of bits in string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->array;
    }

    public function jsonSerialize()
    {
        return ["size" => $this->count(), "array" => $this->array];
    }

    public static function fromJsonString($jsonString)
    {
        $decoded = json_decode($jsonString, true);
        $size = $decoded['size'];
        $array = $decoded['array'];
        $bitArray = new BitArray($size);
        $bitArray->array = $array;
        return $bitArray;
    }
}
