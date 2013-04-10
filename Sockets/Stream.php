<?php

namespace Sockets;

use React\Stream\WritableStreamInterface;
use React\Stream\ReadableStreamInterface;
use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use Socket\Raw\Socket as RawSocket;

class Stream extends EventEmitter implements ReadableStreamInterface, WritableStreamInterface
{
    private $socket;
    private $poller;

    private $bufferSize = 65536;

    public function __construct(RawSocket $socket, SelectPoller $poller)
    {
        $this->socket = $socket;
        $this->poller = $poller;

        $this->buffer = new StreamBuffer($socket, $poller);

        $this->resume();
    }

    public function resume()
    {
        $this->poller->addReadSocket($this->socket->getResource(), array($this, 'handleData'));
    }

    public function pause()
    {
        $this->poller->removeReadSocket($this->socket->getResource());
    }

    public function isReadable()
    {
        return true;
    }

    public function isWritable()
    {
        return true;
    }

    public function write($data)
    {
        return $this->buffer->write($data);
    }

    public function close()
    {
        $this->emit('end', array($this));
        $this->emit('close', array($this));

        $this->pause();
        $this->buffer->close();
        $this->socket->close();

        $this->removeAllListeners();
    }

    public function end($data = null)
    {
        $that = $this;
        $this->buffer->on('close', function() use ($that) {
            $that->close();
        });
        $this->buffer->end($data);
    }

    public function pipe($dest, array $options = array())
    {

    }

    public function handleData()
    {
        $data = $this->socket->read($this->bufferSize);

        if ($data === '') {
            $this->end();
        } else {
            $this->emit('data', array($data, $this));
        }
    }
}
