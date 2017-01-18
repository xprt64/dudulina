<?php
/******************************************************************************
 * Copyright (c) 2016 Constantin Galbenu <gica.galbenu@gmail.com>             *
 ******************************************************************************/

namespace tests\unit\Gica\Cqrs;


use Gica\Cqrs\Event\ObjectToJsonConverter;

class EventToJsonConverterTest extends \PHPUnit_Framework_TestCase
{

    public function test()
    {
        $serializer = new ObjectToJsonConverter();

        $event = new MyEvent('xxx', 2);

        $expected = [
            'a' => 'xxx',
            'b' => 2,
        ];

        $this->assertEquals($expected, $serializer->serializeObject($event));

    }
}

class MyEvent implements \Gica\Cqrs\Event
{
    private $a;
    private $b;

    /**
     * MyEvent constructor.
     * @param $a
     * @param $b
     */
    public function __construct($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }
}