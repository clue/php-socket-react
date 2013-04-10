<?php

namespace Sockets;

use React\Promise\When;

use React\Promise\Deferred;
use React\EventLoop\LoopInterface;
use Socket\Raw\Factory as RawFactory;
use \Exception;

class Factory
{
    private $loop;
    private $rawFactory;
    private $poller = null;

    public function __construct(LoopInterface $loop)
    {
        $this->loop = $loop;
        $this->rawFactory = new RawFactory();
    }

    public function createUdp4()
    {
        return new Datagram($this->rawFactory->createUdp4(), $this->getPoller());
    }

    public function createUdp6()
    {
        return new Datagram($this->rawFactory->createUdp6(), $this->getPoller());
    }

    public function createUdg()
    {
        return new Datagram($this->rawFactory->createUdg(), $this->getPoller());
    }

    /**
     *
     * @return SelectPoller
     */
    public function getPoller()
    {
        if ($this->poller === null) {
            $this->poller = new SelectPoller($this->loop);
        }
        return $this->poller;
    }
}
