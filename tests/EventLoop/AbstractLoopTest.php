<?php

use Socket\React\Datagram\Factory;

abstract class AbstractLoopTest extends TestCase
{
    /**
     * @var Socket\Raw\Factory
     * @type Factory
     */
    protected $factory;

    protected $loop;

    public function setUp()
    {
        $this->loop = $this->createLoop();

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $this->loop);

        $this->factory = new Factory($this->loop);
    }

    abstract function createLoop();

    public function testCreateClientUdp4()
    {
        $promise = $this->factory->createClient('udp://127.0.0.1:53');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }
}
