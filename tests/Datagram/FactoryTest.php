<?php

use Socket\React\Datagram\Factory;

class DatagramFactoryTest extends TestCase{
/**
     * @var Socket\Raw\Factory
     * @type Factory
     */
    protected $factory;

    public function setUp()
    {
        $loop = React\EventLoop\Factory::create();
        $this->factory = new Factory($loop);
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
