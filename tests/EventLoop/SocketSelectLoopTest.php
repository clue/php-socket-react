<?php

use Socket\React\EventLoop\SocketSelectLoop;
use Socket\React\Datagram\Factory;

class SocketSelectLoopTest extends AbstractLoopTest
{
    function createLoop()
    {
        return new SocketSelectLoop();
    }
}
