<?php

namespace Sockets;

use React\Promise\When;

use React\Promise\Deferred;
use React\EventLoop\LoopInterface;
use Socket\Raw\Factory as RawFactory;
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
     * create stream client socket connected to given address
     *
     * @param string $address
     * @return PromiseInterface to return a \Sockets\Stream
     * @throws Exception on error
     * @uses RawFactory::createClient()
     */
    public function createClient($address)
    {
        $factory = $this->rawFactory;
        $that = $this;
        return $this->resolve($address)->then(function ($address) use ($factory, $that) {
            $socket = $factory->createClient($address);
            if ($socket->getType() !== SOCK_STREAM) {
                $socket->close();
                throw new Exception('Not a stream address scheme');
            }
            return new Stream($socket, $that->getPoller());
        });
    }

    /**
     * create stream server socket bound to and listening on the given address for incomming stream client connections
     *
     * @param string $address
     * @return PromiseInterface to return a \Sockets\Server
     * @throws Exception on error
     * @uses Server::listenAddress()
     */
    public function createServer($address)
    {
        return $this->resolve($address)->then(function ($address) {
            $server = new Server($this->getPoller(), $this->rawFactory);
            $server->listenAddress($address);

            return $server;
        });
    }

    public function createUdp4()
    {
        return new Datagram($this->rawFactory->createUdp4(), $this->getPoller());
    }

    public function createUdp6()
    {
        return new Datagram($this->rawFactory->createUdp6(), $this->getPoller());
    }

    public function createUdg()
    {
        return new Datagram($this->rawFactory->createUdg(), $this->getPoller());
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
