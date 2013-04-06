<?php

namespace Sockets;

use React\EventLoop\LoopInterface;
use \Exception;

class Factory
{
    private $loop;
    private $poller = null;

    public function __construct(LoopInterface $loop)
    {
        if (!function_exists('socket_create')) {
            throw new Exception('Sockets extension not loaded');
        }
        $this->loop = $loop;
    }

    public function createUdp4()
    {
        return $this->create(AF_INET, SOCK_DGRAM, SOL_UDP);
    }

    public function createUdp6()
    {
        return $this->create(AF_INET6, SOCK_DGRAM, SOL_UDP);
    }

    public function getPoller()
    {
        if ($this->poller === null) {
            $this->poller = new SelectPoller($this->loop);
        }
        return $this->poller;
    }

    private function create($domain, $type, $protocol)
    {
        $sock = socket_create($domain, $type, $protocol);
        if ($sock === false) {
            throw new Exception('Unable to create socket');
        }
        return new Socket($sock, $this->getPoller());
    }
}
