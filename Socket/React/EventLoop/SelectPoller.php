<?php

namespace Socket\React\EventLoop;

use React\EventLoop\LoopInterface;
use \Exception;

// modelled closely after React\EventLoop\StreamSelectLoop
class SelectPoller extends SocketSelectLoop
{
    private $loop;
    private $tid = null;

    private $pollInterval = 0.01;
    private $pollDurationSec = 0;
    private $pollDurationUsec = 0;

    public function __construct(LoopInterface $loop)
    {
        parent::__construct();

        $this->loop = $loop;
    }

    public function setPollInterval($pollInterval)
    {
        $this->pollInterval = $pollInterval;

        // restart with new interval in case it's currently running
        if ($this->tid !== null) {
            $this->pause();
            $this->resume();
        }
    }

    public function getPollInterval()
    {
        return $this->pollInterval;
    }

    public function setPollDuration($pollDuration)
    {
        $this->pollDurationSec = (int)$pollDuration;
        $this->pollDurationUsec = (int)(($pollDuration - (int)$pollDuration) * 1000000);
    }

    public function getPollDuration()
    {
        return ($this->pollDurationSec + $this->pollDurationUsec / 1000000);
    }


    /**
     * notify poller to schedule polling ASAP for next tick
     *
     * this doesn't neccessary have to actually do anything (and in fact
     * it does NOT at the moment...), but it's purpose is to notify the
     * main loop that something in this poller instance has (likely) changed
     * and polling should be performed ASAP. This could re-schedule the
     * timer to poll in the next available tick instead of waiting for the
     * timer to expire.
     *
     * @return self $this (chainable)
     * @todo actually do something. this is a no-op currently
     */
    public function notify()
    {
        return $this;
    }

    private function resume()
    {
        if ($this->tid === null && $this->hasListeners()) {
            $this->tid = $this->loop->addPeriodicTimer($this->pollInterval, array($this, 'poll'));
        }
    }

    private function pause()
    {
        if ($this->tid !== null && !$this->hasListeners()) {
            $this->loop->cancelTimer($this->tid);
            $this->tid = null;
        }
    }

    public function poll()
    {
        $this->runStreamSelect();
    }

    protected function getNextEventTimeInMicroSeconds()
    {
        return $this->getPollDuration() * 1000000;
    }

    public function addReadStream($stream, $listener)
    {
        parent::addReadStream($stream, $listener);
        $this->resume();
    }

    public function addWriteStream($stream, $listener)
    {
        parent::addWriteStream($stream, $listener);
        $this->resume();
    }

    public function removeReadStream($stream)
    {
        parent::removeReadStream($stream);
        $this->pause();
    }

    public function removeWriteStream($stream)
    {
        parent::removeWriteStream($stream);
        $this->pause();
    }

    public function removeStream($stream)
    {
        parent::removeStream($stream);
        $this->pause();
    }

    public function tick()
    {
        return $this->loop->tick();
    }

    public function run()
    {
        return $this->loop->run();
    }

    public function stop()
    {
        return $this->loop->stop();
    }
}
