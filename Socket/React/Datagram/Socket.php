<?php

namespace Socket\React\Datagram;

use Datagram\Socket as DatagramSocket;
use Datagram\Buffer as DatagramBuffer;
use Socket\React\Datagram\Buffer;
use React\EventLoop\LoopInterface;
use Exception;

class Socket extends DatagramSocket
{
    public function __construct(LoopInterface $loop, $socket, DatagramBuffer $buffer = null)
    {
        if ($buffer === null) {
            $buffer = new Buffer($loop, $socket);
        }

        parent::__construct($loop, $socket, $buffer);
    }

    public function resume()
    {
        $this->loop->addReadStream($this->socket->getResource(), array($this, 'onReceive'));
    }

    public function pause()
    {
        $this->loop->removeReadStream($this->socket->getResource());
    }

    public function getRemoteAddress()
    {
        return $this->socket->getPeerName();
    }

    public function getLocalAddress()
    {
        return $this->socket->getSockName();
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
