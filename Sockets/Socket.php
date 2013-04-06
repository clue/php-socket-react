<?php

namespace Sockets;

use React\EventLoop\LoopInterface;
use Socket\Raw\Socket as RawSocket;

class Socket extends RawSocket
{
    private $poller;

    public function __construct($resource, SelectPoller $poller)
    {
        parent::__construct($resource);

        $this->poller = $poller;
    }

    public function resume()
    {
        $this->poller->addReadSocket($this->resource, array($this, 'readable'));
    }

    public function pause()
    {
        $this->poller->removeReadSocket($this->resource);
    }

    protected function readable()
    {
    }

    public function accept()
    {
        $resource = $this->assertSuccess(socket_accept($this->resource));
        return new Socket($resource, $this->poller);
    }

    public function close()
    {
        $this->pause();
        return parent::close();
    }
}
