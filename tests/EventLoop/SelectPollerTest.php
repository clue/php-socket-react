<?php

use Socket\React\EventLoop\SelectPoller;
use React\EventLoop\StreamSelectLoop;

class SelectPollerTest extends AbstractLoopTest
{
    function createLoop()
    {
        $loop = new StreamSelectLoop();

        return new SelectPoller($loop);
    }
}
