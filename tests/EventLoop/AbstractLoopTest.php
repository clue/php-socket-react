<?php

use Socket\React\Datagram\Factory;
use Socket\Raw\Factory as RawFactory;

abstract class AbstractLoopTest extends TestCase
{
    /**
     * @var Socket\Raw\Factory
     * @type Factory
     */
    protected $factory;

    protected $loop;

    protected $rawFactory;

    public function setUp()
    {
        $this->loop = $this->createLoop();

        $this->assertInstanceOf('React\EventLoop\LoopInterface', $this->loop);

        $this->factory = new Factory($this->loop);

        $this->rawFactory = new RawFactory();
    }

    abstract function createLoop();

    public function testClientTcp4()
    {
        $socket = $this->rawFactory->createClient('www.google.com:80');

        $loop = $this->loop;
        $this->loop->addWriteStream($socket->getResource(), function($resource, $loop) use ($socket) {
            $loop->removeWriteStream($resource);

            $socket->write("GET / HTTP/1.1\r\nHost: www.google.com\r\n\r\n");
        });

        $this->loop->addReadStream($socket->getResource(), function($resource, $loop) use ($socket) {
            $loop->removeReadStream($resource);

        });

        $this->loop->run();
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddReadStreamInvalid()
    {
        $stream = fopen('php://temp', 'r+');

        $this->loop->addReadStream($stream, null);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddWriteStreamInvalid()
    {
        $stream = fopen('php://temp', 'r+');

        $this->loop->addWriteStream($stream, null);
    }
}
