<?php

namespace Socket\React\Datagram;

use Datagram\Buffer as DatagramBuffer;

class Buffer extends DatagramBuffer
{
    protected function handleWrite($data, $remoteAddress)
    {
        if ($remoteAddress === null) {
            $this->socket->send($data, 0);
        } else {
            $this->socket->sendTo($data, 0, $remoteAddress);
        }
    }

    protected function handlePause()
    {
        $this->loop->removeWriteStream($this->socket->getResource());
    }

    protected function handleResume()
    {
        $this->loop->addWriteStream($this->socket->getResource(), array($this, 'onWritable'));
    }
}
