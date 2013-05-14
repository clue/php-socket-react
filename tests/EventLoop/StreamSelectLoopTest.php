<?php

use React\EventLoop\StreamSelectLoop;

class StreamSelectLoopTest extends AbstractLoopTest
{
    function createLoop()
    {
        return new StreamSelectLoop();
    }
}
