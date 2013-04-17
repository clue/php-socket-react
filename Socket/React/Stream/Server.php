<?php

namespace Socket\React\Stream;

use Socket\React\SelectPoller;
use React\Socket\ServerInterface;
use Evenement\EventEmitter;
use Socket\Raw\Factory as RawFactory;
use Socket\Raw\Socket as RawSocket;

class Server extends EventEmitter implements ServerInterface
{
    private $factory;
    /**
     *
     * @var RawSocket
     */
    private $socket = null;
    private $poller;

    public function __construct(SelectPoller $poller, RawFactory $factory = null)
    {
        $this->poller = $poller;
        $this->factory = $factory;
    }

    public function listen($port, $host = '127.0.0.1')
    {
        if (strpos($host, ':') !== false) {
            // IPv6 addressing has to use square brackets
            $host = '[' . $host . ']';
        }
        $address = 'tcp://' . $host . ':' . $port;

        return $this->listenAddress($address);
    }

    public function listenAddress($address)
    {
        if ($this->factory === null) {
            $this->factory = new RawFactory();
        }
        $this->socket = $this->factory->createServer($address);
        if ($this->socket->getType() !== SOCK_STREAM) {
            $this->socket->close();
            throw new Exception('Not a stream address scheme');
        }
        $this->socket->setBlocking(false);

        $that = $this;
        $socket = $this->socket;
        $this->poller->addReadSocket($this->socket->getResource(), function() use ($socket, $that) {
            $clientSocket = $socket->accept();

            $that->handleConnection($clientSocket);
        });
    }

    public function handleConnection(RawSocket $clientSocket)
    {
        $clientSocket->setBlocking(false);

        $client = $this->createConnection($clientSocket);

        $this->emit('connection', array($client));
    }

    protected function createConnection(RawSocket $clientSocket)
    {
        return new Connection($clentSocket, $this->poller);
    }

    public function getPort()
    {
        $name = $this->socket->getSockName();
        return (int) substr(strrchr($name, ':'), 1);
    }

    public function shutdown()
    {
        $this->poller->removeReadSocket($this->socket->getResource());

        $this->socket->shutdown();
        $this->socket->close();
    }
}
