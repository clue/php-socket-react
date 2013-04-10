<?php

namespace Sockets;

use React\EventLoop\LoopInterface;
use \Exception;

// modelled closely after React\EventLoop\StreamSelectLoop
class SelectPoller
{
    private $loop;
    private $tid = null;

    private $pollInterval = 0.01;
    private $pollDurationSec = 0;
    private $pollDurationUsec = 0;

    private $readSockets = array();
    private $readListeners = array();
    private $writeSockets = array();
    private $writeListeners = array();

    public function __construct(LoopInterface $loop)
    {
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

    public function resume()
    {
        if ($this->tid === null && ($this->read || $this->write)) {
            $this->tid = $this->loop->addPeriodicTimer($this->pollInterval, array($this, 'poll'));
        }
    }

    public function pause()
    {
        if ($this->tid !== null) {
            $this->loop->cancelTimer($this->tid);
            $this->tid = null;
        }
    }

    public function poll()
    {
        $read = $this->readSockets ? $this->readSockets : null;
        $write = $this->writeSockets ? $this->writeSockets : null;
        $ret = socket_select($read, $write, $x = null, $this->pollDurationSec, $this->pollDurationUsec);
        if ($ret) {
            foreach ($read as $socket) {
                $id = (int)$socket;
                if (isset($this->readListeners[$id])) {
                    call_user_func($this->readListeners[$id], $socket, $this);
                }
            }
            foreach ($write as $socket) {
                $id = (int)$socket;
                if (isset($this->writeListeners[$id])) {
                    call_user_func($this->writeListeners[$id], $socket, $this);
                }
            }
        } else if ($ret === false) {
            throw new Exception('Socket operation "socket_select()" failed: ' . socket_strerror(socket_last_error()));
        }
    }

    public function addReadSocket($socket, $listener)
    {
        $id = (int)$socket;
        if (!isset($this->readSockets[$id])) {
            $this->readSockets[$id] = $socket;
            $this->readListeners[$id] = $listener;

            $this->resume();
        }
    }

    public function addWriteSocket($socket, $listener)
    {
        $id = (int)$socket;
        if (!isset($this->writeSockets[$id])) {
            $this->writeSockets[$id] = $socket;
            $this->writeListeners[$id] = $listener;

            $this->resume();
        }
    }

    public function removeReadSocket($socket)
    {
        $id = (int)$socket;
        unset($this->readSockets[$id], $this->readListeners[$id]);

        if (!$this->readSockets && !$this->writeSockets) {
            $this->pause();
        }
    }

    public function removeWriteSocket($socket)
    {
        $id = (int)$socket;
        unset($this->writeSockets[$id], $this->writeListeners[$id]);

        if (!$this->writeSockets && !$this->readSockets) {
            $this->pause();
        }
    }

    public function removeSocket($socket)
    {
        $this->removeReadSocket($socket);
        $this->removeWriteSocket($socket);
    }
}
