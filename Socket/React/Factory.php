<?php

namespace Socket\React;

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
     * @uses RawFactory::createFromString()
     * @uses RawSocket::setBlocking() to turn on non-blocking mode
     * @uses RawSocket::connect() to initiate async connection
     * @uses SelectPoller::addWriteSocket() to wait for connection result once
     * @uses RawSocket::assertAlive() to check connection result
     */
    public function createClient($address)
    {
        $that = $this;
        $factory = $this->rawFactory;

        return $this->resolve($address)->then(function ($address) use ($factory, $that){
            $deferred = new Deferred();

            $socket = $factory->createFromString($address, $scheme);
            if ($socket->getType() !== SOCK_STREAM) {
                $socket->close();
                throw new Exception('Not a stream address scheme');
            }

            $socket->setBlocking(false);

            try{
                // socket is nonblocking, so connect should emit EINPROGRESS
                $socket->connect($address);

                // socket is already connected immediately?
                $deferred->resolve(new Stream($socket, $that->getPoller()));
            }
            catch(Exception $exception)
            {
                if ($exception->getCode() === SOCKET_EINPROGRESS) {
                    // connection in progress => wait for the socket to become writable
                    $that->getPoller()->addWriteSocket($socket->getResource(), function ($resource, $poller) use ($deferred, $socket){
                        // only poll for writable event once
                        $poller->removeWriteSocket($resource);

                        try {
                            // assert that socket error is 0 (no TCP RST received)
                            $socket->assertAlive();
                        }
                        catch (Exception $e) {
                            // error returned => connected failed
                            $socket->close();

                            $deferred->reject(new Exception('Error while establishing connection' , $e->getCode(), $e));
                            return;
                        }

                        // no error => connection established
                        $deferred->resolve(new Stream($socket, $poller));
                    });
                } else {
                    // re-throw any other socket error
                    $socket->close();
                    $deferred->reject($exception);
                }
            }
            return $deferred->promise();
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
