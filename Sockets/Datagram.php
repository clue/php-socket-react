<?php

namespace Sockets;

use Evenement\EventEmitter;
use Socket\Raw\Socket as RawSocket;

class Datagram extends EventEmitter
{
    private $socket;
    private $poller;
    private $buffer;
    private $bufferSize = 65536;

    public function __construct(RawSocket $socket, SelectPoller $poller)
    {
        $this->socket = $socket;
        $this->poller = $poller;

        $this->buffer = new DatagramBuffer($socket, $poller);

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

    public function send($data, $remote)
    {
        return $this->buffer->send($data, $remote);
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
        $this->emit('close', array($this));

        $this->pause();
        $this->buffer->close();
        $this->socket->close();

        $this->removeAllListeners();
    }

    /**
     * enable/disable sending and receiving broacasts (by setting/unsetting SO_BROADCAST option)
     *
     * @param boolean $toggle
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses RawSocket::setOption()
     */
    public function setOptionBroadcast($toggle = true)
    {
        $this->socket->setOption(SOL_SOCKET, SO_BROADCAST, (int)(bool)$toggle);
        return $this;
    }
}
