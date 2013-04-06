<?php

namespace Sockets;

class Datagram extends EventEmitter
{
    private $socket;
    private $poller;
    private $outgoing = array();
    private $bufferSize = 65536;

    public function __construct(Socket $socket, SelectPoller $poller)
    {
        $this->socket = $socket;
        $this->poller = $poller;

        $this->resume();
    }

    public function resume()
    {
        $this->poller->addReadSocket($this->socket->getResource(), array($this, 'handleRead'));
    }

    public function pause()
    {
        $this->poller->removeReadSocket($this->socket->getResource());
    }

    public function send($buffer, $remote)
    {
        $this->outgoing []= array($buffer, $remote);
        $this->poller->addWriteSocket($this->socket->getResource(), array($this, 'handleWrite'));
    }

    public function handleWrite()
    {
        list($buffer, $remote) = array_shift($this->outgoing);
        $this->socket->sendTo($buffer, 0, $remote);

        if (!$this->outgoing) {
            $this->poller->removeWriteSocket($this->socket->getResource());
        }
    }

    public function handleRead()
    {
        $data = $this->socket->rcvFrom($this->bufferSize, 0, $remote = null);

        if ($data === '') {
            $this->close();
        } else {
            $this->emit('message', array($data, $remote));
        }
    }

    public function close()
    {
        $this->emit('end', array($this));
        $this->emit('close', array($this));

        $this->pause();
        $this->outgoing = array();
        $this->socket->close();

        $this->removeAllListeners();
    }
}
