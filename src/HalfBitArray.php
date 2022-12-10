<?php

namespace golovanovya\bitarray;

/**
 * Array of even bits
 */
class HalfBitArray extends BitArray
{
    /**
     * @inheritdoc
     */
    public function __construct(int $size, int $set = 0)
    {
        parent::__construct(ceil($size / 2), $set);
        $this->setSize($size);
    }

    /**
     * Is odd key
     */
    private function isOdd(int $key): bool
    {
        return ($key & 1) === 1;
    }

    /**
     * @inheritdoc
     */
    public function get(int $key): int
    {
        $this->validateKey($key);
        if ($this->isOdd($key)) {
            return 0;
        }
        return parent::get($key / 2);
    }

    /**
     * @inheritdoc
     */
    public function reset(mixed $key): void
    {
        $this->validateKey($key);
        if ($this->isOdd($key)) {
            return;
        }
        parent::reset($key / 2);
    }

    /**
     * @inheritdoc
     */
    public function set(int $key): void
    {
        $this->validateKey($key);
        if ($this->isOdd($key)) {
            return;
        }
        parent::set($key / 2);
    }
}
