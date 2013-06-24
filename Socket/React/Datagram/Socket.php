<?php

namespace Socket\React\Datagram;

use Datagram\Socket as DatagramSocket;
use Socket\React\Datagram\DatagramBuffer;
use Exception;

class Socket extends DatagramSocket
{
    public function resume()
    {
        $this->loop->addReadStream($this->socket->getResource(), array($this, 'onReceive'));
    }

    public function pause()
    {
        $this->loop->removeReadStream($this->socket->getResource());
    }

    protected function createBuffer()
    {
        return new DatagramBuffer($this->loop, $this->socket);
    }

    protected function handleReceive(&$remote)
    {
        return $this->socket->recvFrom($this->bufferSize, 0, $remote);
    }

    protected function handleClose()
    {
        try {
            $this->socket->shutdown();
        }
        catch (Exception $ignore) {
        }
        $this->socket->close();
    }
}
