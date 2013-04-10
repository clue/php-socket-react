<?php

namespace Sockets;

use React\Socket\ServerInterface;
use Evenement\EventEmitter;
use Socket\Raw\Socket as RawSocket;

class Server extends EventEmitter implements ServerInterface
{
    private $socket;
    private $poller;

    public function __construct(RawSocket $socket, SelectPoller $poller)
    {
        $this->socket = $socket;
        $this->poller = $poller;
    }

    public function listen($port, $host = '127.0.0.1')
    {
        // TODO: IPv6? UNIX?
        $address = $host . ':' . $port;

        $this->socket->bind($address);
        $this->socket->listen();
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
        // TODO: return Connection in order to provide getRemoteAddress()
        return new Stream($clentSocket, $this->poller);
    }

    public function getPort()
    {
        $name = $this->socket->getSockName();
        return (int) substr(strrchr($name, ':'), 1);
    }

    public function shutdown()
    {
        $this->poller->removeReadSocket($this->socket->getResource());
        $this->socket->close();
    }
}
