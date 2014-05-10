<?php

namespace Socket\React\Datagram;

use React\EventLoop\StreamSelectLoop;
use Socket\React\EventLoop\SelectPoller;
use React\Promise\When;
use React\Promise\Deferred;
use React\EventLoop\LoopInterface;
use Socket\Raw\Factory as RawFactory;
use Socket\Raw\Socket as RawSocket;
use Exception;

class Factory
{
    private $loop;
    private $socketLoop = null;
    private $rawFactory;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->rawFactory = new RawFactory();
    }

    /**
     * Create datagram client socket connect()ed to given remote address
     *
     * Please note that unlike streaming sockets (TCP/IP),
     * datagram sockets usually have no concept of an
     * "established connection", i.e. the remote side will NOT be notified
     * of any "connection attempt" and no data has to be exchanged.
     *
     * Usually, there's no /need/ to connect() datagram sockets. If you
     * want to send to a specific remote address, see the $remote parameter
     * in send() as an alternative. Connect()ing the datagram client to
     * the remote side once may be preferrable as it frees you from having
     * to pass the remote address along with every send() call and only
     * requires a single host name resolution instead of having to perform
     * it with every send() call.
     *
     * @param string $address
     * @param array  $context (optional) "bindto" or "broadcast" context options
     * @return PromiseInterface to return a \Socket\React\Datagram\Datagram
     * @uses RawFactory::createFromString()
     * @uses RawSocket::setOption() to toggle broadcast option
     * @uses RawSocket::bind() to bind to given local address
     * @uses RawSocket::setBlocking() to turn on non-blocking mode
     * @uses RawSocket::connect() to initiate connection
     * @todo consider adding additional socket options
     * @todo use async DNS resolver for "bindto" context
     */
    public function createClient($address, $context = array())
    {
        $that = $this;
        $factory = $this->rawFactory;

        return $this->resolve($address)->then(function ($address) use ($factory, $that, $context){
            $scheme = 'udp';
            $socket = $factory->createFromString($address, $scheme);
            if ($socket->getType() !== SOCK_DGRAM) {
                $socket->close();
                throw new Exception('Not a datagram address scheme');
            }

            if (isset($context['broadcast']) && $context['broadcast']) {
                $socket->setOption(SOL_SOCKET, SO_BROADCAST, 1);
            }

            if (isset($context['bindto'])) {
                $socket->bind($context['bindto']);
            }

            $socket->setBlocking(false);
            $socket->connect($address);

            return $that->createFromRaw($socket);
        });
    }

    /**
     * create datagram server socket waiting for incoming messages on the given local address
     *
     * @param string $address
     * @param array  $context (optional) "broadcast" context option
     * @return PromiseInterface to return a \Socket\React\Datagram\Datagram
     * @uses RawFactory::createFromString()
     * @uses RawSocket::setBlocking() to turn on non-blocking mode
     * @uses RawSocket::bind() to initiate connection
     */
    public function createServer($address, $context = array())
    {
        $that = $this;
        $factory = $this->rawFactory;

        return $this->resolve($address)->then(function ($address) use ($factory, $that, $context){
            $scheme = 'udp';
            $socket = $factory->createFromString($address, $scheme);
            if ($socket->getType() !== SOCK_DGRAM) {
                $socket->close();
                throw new Exception('Not a datagram address scheme');
            }

            if (isset($context['broadcast']) && $context['broadcast']) {
                $socket->setOption(SOL_SOCKET, SO_BROADCAST, 1);
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
        return $this->createFromRaw($this->rawFactory->createIcmp6());
    }

    public function createFromRaw(RawSocket $rawSocket)
    {
        return new Socket($this->getSocketLoop(), $rawSocket);
    }

    /**
     * return a loop interface that supports adding socket resources
     *
     * @return LoopInterface
     */
    protected function getSocketLoop()
    {
        if ($this->socketLoop === null) {
            if ($this->loop instanceof StreamSelectLoop) {
                $this->socketLoop = new SelectPoller($this->loop);
            } else {
                $this->socketLoop = $this->loop;
            }
        }
        return $this->socketLoop;
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
