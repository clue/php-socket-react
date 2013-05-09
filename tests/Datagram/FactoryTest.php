<?php

use Socket\React\Datagram\Factory;

class DatagramFactoryTest extends TestCase{
/**
     * @var Socket\Raw\Factory
     * @type Factory
     */
    protected $factory;

    protected $loop;

    public function setUp()
    {
        $this->loop = React\EventLoop\Factory::create();
        $this->factory = new Factory($this->loop);
    }

    public function testSupportsIpv6()
    {
        // TODO: check this check
        if (!defined('AF_INET6')) {
            $this->markTestSkipped('This system does not seem to support IPv6 sockets / addressing');
        }
    }

    public function testSupportsUnix()
    {
        // TODO: check this check
        if (!defined('AF_UNIX')) {
            $this->markTestSkipped('This system does not seem to support UDG (Unix DataGram) sockets');
        }
    }

    public function testConstructorWorks()
    {
        $this->assertInstanceOf('Socket\React\Datagram\Factory', $this->factory);
    }

    public function testCreateClientUdp4()
    {
        $promise = $this->factory->createClient('udp://127.0.0.1:53');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }

    public function testCreateClientSchemelessUdp4()
    {
        $promise = $this->factory->createClient('127.0.0.1:53');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }

    /**
     * @depends testSupportsIpv6
     */
    public function testCreateClientSchemelessUdp6()
    {
        $promise = $this->factory->createClient('[::1]:53');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }

    /**
     * creating a TCP socket fails because it is NOT a datagram socket
     */
    public function testCreateClientFailTcp()
    {
        $promise = $this->factory->createClient('tcp://www.google.com:80');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnceParameter('Exception'));

        $this->loop->tick();
    }

    public function testCreateServerUdp4()
    {
        $promise = $this->factory->createServer('udp://127.0.0.1:0');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }

    public function testCreateServerSchemelessUdp4()
    {
        $promise = $this->factory->createServer('127.0.0.1:0');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }

    /**
     * @depends testSupportsIpv6
     */
    public function testCreateServerSchemelessUdp6()
    {
        $promise = $this->factory->createServer('[::1]:0');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableOnceParameter('Socket\React\Datagram\Datagram'), $this->expectCallableNever());

        $this->loop->tick();
    }

    /**
     * creating a TCP socket fails because it is NOT a datagram socket
     */
    public function testCreateServerFailTcp()
    {
        $promise = $this->factory->createServer('tcp://127.0.0.1:0');

        $this->assertInstanceOf('React\Promise\PromiseInterface', $promise);

        $promise->then($this->expectCallableNever(), $this->expectCallableOnceParameter('Exception'));

        $this->loop->tick();
    }

    public function testCreateUdp4()
    {
        $socket = $this->factory->createUdp4();

        $this->assertInstanceOf('Socket\React\Datagram\Datagram', $socket);
    }

    /**
     * @depends testSupportsIpv6
     */
    public function testCreateUdp6()
    {
        $socket = $this->factory->createUdp6();

        $this->assertInstanceOf('Socket\React\Datagram\Datagram', $socket);
    }

    /**
     * @depends testSupportsUnix
     */
    public function testCreateUdg()
    {
        $socket = $this->factory->createUdg();

        $this->assertInstanceOf('Socket\React\Datagram\Datagram', $socket);
    }

    public function testCreateIcmp4()
    {
        try {
            $socket = $this->factory->createIcmp4();
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_EPERM) {
                // skip if not root
                return $this->markTestSkipped('No access to ICMPv4 socket (only root can do so)');
            }
            throw $e;
        }

        $this->assertInstanceOf('Socket\React\Datagram\Datagram', $socket);
    }

    /**
     * @depends testSupportsIpv6
     */
    public function testCreateIcmp6()
    {
        try {
            $socket = $this->factory->createIcmp6();
        }
        catch (Exception $e) {
            if ($e->getCode() === SOCKET_EPERM) {
                // skip if not root
                return $this->markTestSkipped('No access to ICMPv4 socket (only root can do so)');
            }
            throw $e;
        }

        $this->assertInstanceOf('Socket\React\Datagram\Datagram', $socket);
    }
}
