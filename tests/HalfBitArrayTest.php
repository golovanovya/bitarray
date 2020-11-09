<?php

namespace tests;

use golovanovya\bitarray\HalfBitArray;
use PHPUnit\Framework\TestCase;

class HalfBitArrayTest extends TestCase
{
    /**
     * @dataProvider defaultProvider
     */
    public function testConstructor($size, $set, $expected)
    {
        $bitArray = new HalfBitArray($size, $set);
        $this->assertEquals($bitArray->__toString(), $expected);
    }

    public function defaultProvider()
    {
        return [
            "size 0" => [0, false, ''],
            "size 1" => [1, false, "\x0"],
            "size 8" => [8, false, "\x0"],
            "size 9" => [9, false, "\x0"],
            "size 16" => [16, false, "\x0"],
            "size 17" => [17, false, "\x0\x0"],
            "size 0 filled" => [0, true, ''],
            "size 1 filled" => [1, true, "\xff"],
            "size 8 filled" => [8, true, "\xff"],
            "size 9 filled" => [9, true, "\xff"],
            "size 16 filled" => [16, true, "\xff"],
            "size 17 filled" => [17, true, "\xff\xff"],
        ];
    }

    public function testEmptyArray()
    {
        $bitArray = new HalfBitArray(2);
        $bitArray->set(0);
        $this->assertEquals($bitArray->get(0), 1);
        $this->assertEquals($bitArray->get(1), 0);
        $bitArray->reset(0);
        $this->assertEquals($bitArray->get(0), 0);
        $this->assertEquals($bitArray->get(1), 0);
        $bitArray->set(1);
        $this->assertEquals($bitArray->get(1), 0);
        $bitArray->reset(1);
        $this->assertEquals($bitArray->get(1), 0);
    }

    public function testFilledArray()
    {
        $bitArray = new HalfBitArray(2, true);
        $bitArray->reset(0);
        $this->assertEquals($bitArray->get(0), 0);
        $this->assertEquals($bitArray->get(1), 0);
        $bitArray->set(0);
        $this->assertEquals($bitArray->get(0), 1);
        $this->assertEquals($bitArray->get(1), 0);
    }
}
