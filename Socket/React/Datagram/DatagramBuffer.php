<?php

namespace Socket\React\Datagram;

use Datagram\BufferAbstract;

class DatagramBuffer extends BufferAbstract
{
    private $listening = false;

    protected function handleWrite($data, $remoteAddress)
    {
        if ($remoteAddress === null) {
            $this->socket->send($data, 0);
        } else {
            $this->socket->sendTo($data, 0, $remoteAddress);
        }
    }

    protected function pause()
    {
        if ($this->listening) {
            $this->loop->removeWriteStream($this->socket->getResource());
            $this->listening = false;
        }
    }

    protected function resume()
    {
        if (!$this->listening) {
            $this->loop->addWriteStream($this->socket->getResource(), array($this, 'onWritable'));
            $this->listening = true;
        }
    }
}
