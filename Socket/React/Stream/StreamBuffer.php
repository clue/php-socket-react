<?php

namespace Socket\React\Stream;

use Evenement\EventEmitter;
use React\EventLoop\LoopInterface;
use React\Stream\WritableStreamInterface;
use Socket\Raw\Socket as RawSocket;

/** @event full-drain */
// based on a copy-pase of React\Stream\Buffer
class StreamBuffer extends EventEmitter implements WritableStreamInterface
{
    private $socket;
    private $loop;

    public $listening = false;
    public $softLimit = 2048;
    private $writable = true;
    private $data = '';
    private $lastError = array(
        'number'  => 0,
        'message' => '',
        'file'    => '',
        'line'    => 0,
    );

    public function __construct(RawSocket $socket, LoopInterface $loop)
    {
        $this->socket = $socket;
        $this->loop   = $loop;
    }

    public function isWritable()
    {
        return $this->writable;
    }

    public function write($data)
    {
        if (!$this->writable) {
            return;
        }

        $this->data .= $data;

        if (!$this->listening) {
            $this->listening = true;

            $this->loop->addWriteStream($this->socket->getResource(), array($this, 'handleWrite'));
        }

        $belowSoftLimit = strlen($this->data) < $this->softLimit;

        return $belowSoftLimit;
    }

    public function end($data = null)
    {
        if (null !== $data) {
            $this->write($data);
        }

        $this->writable = false;

        if ($this->listening) {
            $this->on('full-drain', array($this, 'close'));
        } else {
            $this->close();
        }
    }

    public function close()
    {
        $this->writable = false;
        $this->listening = false;
        $this->data = '';

        $this->emit('close');
    }

    public function handleWrite()
    {

//         if (!is_resource($this->stream) || feof($this->stream)) {
//             $this->emit('error', array(new \RuntimeException('Tried to write to closed or invalid stream.')));

//             return;
//         }

        try {
            $sent = $this->socket->write($this->data);
        }
        catch (Exception $e) {
            $this->emit('error', array($e));
            return;
        }

        $len = strlen($this->data);
        if ($len >= $this->softLimit && $len - $sent < $this->softLimit) {
            $this->emit('drain');
        }

        $this->data = (string) substr($this->data, $sent);

        if (0 === strlen($this->data)) {
            $this->loop->removeWriteStream($this->socket->getResource());
            $this->listening = false;

            $this->emit('full-drain');
        }
    }
}
