<?php

namespace Socket\React\Datagram;


use Socket\React\SelectPoller;
use React\Promise\When;
use React\Promise\Deferred;
use React\EventLoop\LoopInterface;
use Socket\Raw\Factory as RawFactory;
use Socket\Raw\Socket as RawSocket;
use \Exception;

class Factory
{
    private $loop;
    private $rawFactory;
    private $poller = null;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->rawFactory = new RawFactory();
    }

    /**
     * create datagram client socket connected to given address
     *
     * @param string $address
     * @return PromiseInterface to return a \Socket\React\Datagram\Datagram
     * @uses RawFactory::createFromString()
     * @uses RawSocket::setBlocking() to turn on non-blocking mode
     * @uses RawSocket::connect() to initiate connection
     * @see \Socket\React\Datagram\Datagram::connect()
     */
    public function createClient($address)
    {
        $that = $this;
        $factory = $this->rawFactory;

        return $this->resolve($address)->then(function ($address) use ($factory, $that){
            $scheme = 'udp';
            $socket = $factory->createFromString($address, $scheme);
            if ($socket->getType() !== SOCK_DGRAM) {
                $socket->close();
                throw new Exception('Not a datagram address scheme');
            }

            $socket->setBlocking(false);
            $socket->connect($address);

            return $that->createFromRaw($socket);
        });
    }

    /**
     * create datagram server socket waiting for incoming messages on the given address
     *
     * @param string $address
     * @return PromiseInterface to return a \Socket\React\Datagram\Datagram
     * @uses RawFactory::createFromString()
     * @uses RawSocket::setBlocking() to turn on non-blocking mode
     * @uses RawSocket::bind() to initiate connection
     */
    public function createServer($address)
    {
        $that = $this;
        $factory = $this->rawFactory;

        return $this->resolve($address)->then(function ($address) use ($factory, $that){
            $scheme = 'udp';
            $socket = $factory->createFromString($address, $scheme);
            if ($socket->getType() !== SOCK_DGRAM) {
                $socket->close();
                throw new Exception('Not a datagram address scheme');
            }

            $socket->setBlocking(false);
            $socket->bind($address);

            return $that->createFromRaw($socket);
        });
    }

    public function createUdp4()
    {
        return $this->createFromRaw($this->rawFactory->createUdp4());
    }

    public function createUdp6()
    {
        return $this->createFromRaw($this->rawFactory->createUdp6());
    }

    public function createUdg()
    {
        return $this->createFromRaw($this->rawFactory->createUdg());
    }

    public function createIcmp4()
    {
        return $this->createFromRaw($this->rawFactory->createIcmp4());
    }

    public function createIcmp6()
    {
        return $this->createFromRaw($this->rawFactory->createIcmp4());
    }

    public function createFromRaw(RawSocket $rawSocket)
    {
        return new Datagram($rawSocket, $this->getPoller());
    }

    /**
     *
     * @return SelectPoller
     */
    public function getPoller()
    {
        if ($this->poller === null) {
            $this->poller = new SelectPoller($this->loop);
        }
        return $this->poller;
    }

    /**
     * resolve given address via DNS if applicable
     *
     * Letting host names pass through will not break things, but it
     * requires a blocking resolution afterwards. So make sure to try to
     * resolve hostnames here.
     *
     * @param string $address
     * @return PromiseInterface
     * @todo use Resolver to perform async resolving
     */
    private function resolve($address)
    {
        return When::resolve($address);
    }
}
