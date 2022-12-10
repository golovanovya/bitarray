<?php

namespace golovanovya\bitarray;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
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
    public function __construct(int $size, int $set = 0)
    {
        $this->setSize($size);
        $this->rewind();
        $placeholder = $set ? 0xff : 0;
        $this->fill($placeholder);
    }

    /**
     * Set default
     */
    private function fill(int $placeholder): void
    {
        $bytes = intval(ceil($this->count() / 8));
        $this->array = str_repeat(chr($placeholder), $bytes);
    }

    /**
     * Set array size
     */
    protected function setSize(int $size): void
    {
        if ($size < 0) {
            throw new InvalidArgumentException('Size can\'t be less then 0, ' . $size . ' given');
        }
        $this->size = $size;
    }

    /**
     * Alias for getSize() for Countable interface
     */
    public function count(): int
    {
        return $this->getSize();
    }

    /**
     * Array size
     */
    public function getSize(): int
    {
        return $this->size;
    }

    /**
     * Calculate bit position
     */
    private function getBitPos(int $pos): int
    {
        return $pos % 8;
    }

    /**
     * Calculate byte position
     */
    private function getBytePos(int $pos): int
    {
        return intdiv($pos, 8);
    }

    /**
     * Get byte from array by position
     */
    private function getByte(int $pos): int
    {
        return ord($this->array[$pos]);
    }

    /**
     * Modify bit in byte
     */
    private function resetBit(int $pos, int $byte): int
    {
        return $byte & ~(1 << $pos);
    }

    /**
     * Modify bit in byte
     */
    private function setBit(int $pos, int $byte): int
    {
        return $byte | (1 << $pos);
    }

    /**
     * Get bit value from byte
     */
    private function getBit(int $pos, int $byte): int
    {
        $mask = 1 << $pos;
        return ($byte & $mask) >> $pos;
    }

    /**
     * Validate key
     */
    private function validate(int $key): bool
    {
        return $key >= 0
            && $key < $this->size;
    }

    /**
     * Validate key
     * @throws OutOfBoundsException
     */
    protected function validateKey(int $key): void
    {
        if (!$this->validate($key)) {
            throw new OutOfBoundsException();
        }
    }

    /**
     * Check offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return $this->validate($offset);
    }

    /**
     * Get array item by key
     *
     * @param int $key
     * @return int
     */
    public function get(int $key): int
    {
        $this->validateKey($key);
        $bit = $this->getBitPos($key);
        $byte = $this->getBytePos($key);
        return $this->getBit($bit, $this->getByte($byte));
    }

    /**
     * Alias get for ArrayAccess
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->get($offset);
    }

    /**
     * Set value
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (boolval($value)) {
            $this->set($offset);
        } else {
            $this->reset($offset);
        }
    }

    /**
     * Unset bit by key
     */
    public function offsetUnset(mixed $offset): void
    {
        $this->reset($offset);
    }

    /**
     * Unset bit for array item value by key
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
     * Set bit for array item value by key
     */
    public function set(int $key): void
    {
        $this->validateKey($key);
        $bit = $this->getBitPos($key);
        $byte = $this->getBytePos($key);
        $this->array[$byte] = chr($this->setBit($bit, $this->getByte($byte)));
    }

    /** begin Iterator methods */
    /**
     * Rewind position position
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Get current value
     */
    public function current(): int
    {
        return $this->get($this->position);
    }

    /**
     * Get current position
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Set next position
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Validate position
     */
    public function valid(): bool
    {
        return $this->validate($this->position);
    }

    /** end Iterator methods */

    /**
     * Array of bits in string
     */
    public function __toString(): string
    {
        return $this->array;
    }

    public function jsonSerialize()
    {
        return ["size" => $this->count(), "array" => $this->array];
    }

    /**
     * Create from json string
     */
    public static function fromJsonString(string $jsonString): self
    {
        $decoded = json_decode($jsonString, true);
        $size = $decoded['size'] ?? 0;
        $array = $decoded['array'] ?? '';
        $bitArray = new BitArray($size);
        $bitArray->array = $array;
        return $bitArray;
    }
}
