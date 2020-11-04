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
    public function __construct($size, $set = false)
    {
        parent::__construct(ceil($size / 2), $set);
        $this->setSize($size);
    }

    /**
     * Is odd key
     *
     * @param int $key
     * @return bool
     */
    private function isOdd($key)
    {
        return ($key & 1) === 1;
    }

    /**
     * @inheritdoc
     */
    public function get($key)
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
    public function reset($key)
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
    public function set($key)
    {
        $this->validateKey($key);
        if ($this->isOdd($key)) {
            return;
        }
        parent::set($key / 2);
    }
}
