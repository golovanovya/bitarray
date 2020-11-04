<?php

namespace tests;

use golovanovya\bitarray\BitArray;
use OutOfBoundsException;
use PHPUnit\Framework\TestCase;

class BitArrayTest extends TestCase
{
    /**
     * @dataProvider defaultProvider
     */
    public function testConstructor($size, $set, $expected)
    {
        $bitArray = new BitArray($size, $set);
        $this->assertEquals($bitArray->__toString(), $expected);
    }

    public function defaultProvider()
    {
        return [
            "size 0" => [0, false, ''],
            "size 1" => [1, false, "\x0"],
            "size 8" => [8, false, "\x0"],
            "size 9" => [9, false, "\x0\x0"],
            "size 0 filled" => [0, true, ''],
            "size 1 filled" => [1, true, "\xff"],
            "size 8 filled" => [8, true, "\xff"],
            "size 9 filled" => [9, true, "\xff\xff"],
        ];
    }

    public function testEmptyArray()
    {
        $bitArray = new BitArray(2);
        $bitArray->set(0);
        $this->assertEquals($bitArray->get(0), 1);
        $this->assertEquals($bitArray->get(1), 0);
        $bitArray->reset(0);
        $this->assertEquals($bitArray->get(0), 0);
        $this->assertEquals($bitArray->get(1), 0);
    }

    public function testFilledArray()
    {
        $bitArray = new BitArray(2, true);
        $bitArray->reset(0);
        $this->assertEquals($bitArray->get(0), 0);
        $this->assertEquals($bitArray->get(1), 1);
        $bitArray->set(0);
        $this->assertEquals($bitArray->get(0), 1);
        $this->assertEquals($bitArray->get(1), 1);
    }

    public function testInterfaces()
    {
        $bitArray = new BitArray(2);
        $bitArray[0] = 1;
        $this->assertEquals($bitArray[0], 1);
        $this->assertEquals($bitArray[1], 0);
        unset($bitArray[0]);
        $this->assertEquals($bitArray[0], 0);
        $this->assertEquals($bitArray[1], 0);
        $this->assertEquals($bitArray->get(0), $bitArray[0]);
        $this->assertEquals($bitArray->count(), 2);
        $this->assertEquals(count($bitArray), 2);
        $this->assertEquals($bitArray->getSize(), 2);
        foreach ($bitArray as $key => $bitValue) {
            $this->assertEquals($bitValue, $bitArray[$key]);
        }
        $bitArray->rewind();
        $encoded = json_encode($bitArray);
        $decoded = BitArray::fromJsonString($encoded);
        $this->assertEquals($bitArray, $decoded);
    }

    public function testKeyTooBigException()
    {
        $this->expectException(OutOfBoundsException::class);
        $bitArray = new BitArray(2);
        $bitArray->get(3);
    }

    public function testNegativeKeyException()
    {
        $this->expectException(OutOfBoundsException::class);
        $bitArray = new BitArray(2);
        $bitArray->get(-1);
    }
}
