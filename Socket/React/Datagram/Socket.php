<?php

namespace Socket\React\Datagram;

use React\EventLoop\LoopInterface;
use Evenement\EventEmitter;
use Socket\Raw\Socket as RawSocket;
use Datagram\SocketInterface;
use \Exception;

class Socket extends EventEmitter implements SocketInterface
{
    private $socket;
    private $loop;
    private $buffer;
    private $bufferSize = 65536;

    public function __construct(RawSocket $socket, LoopInterface $loop)
    {
        $this->socket = $socket;
        $this->loop = $loop;

        $this->buffer = new DatagramBuffer($socket, $loop);

        $this->resume();
    }

    public function resume()
    {
        $this->loop->addReadStream($this->socket->getResource(), array($this, 'handleRead'));
    }

    public function pause()
    {
        $this->loop->removeReadStream($this->socket->getResource());
    }

    /**
     * send given $data as a datagram message to given $remote address or connect()ed target
     *
     * @param string      $data   datagram message to be sent
     * @param string|null $remote remote/peer address to send message to. can be null if your client socket is connect()ed
     * @return boolean
     * @uses DatagramBuffer::send()
     * @see self::connect()
     */
    public function send($data, $remote = null)
    {
        return $this->buffer->send($data, $remote);
    }

    public function handleRead()
    {
        try {
            $data = $this->socket->recvFrom($this->bufferSize, 0, $remote);
        }
        catch (Exception $e) {
            $this->emit('error', array($e, $this));
            return;
        }

        $this->emit('message', array($data, $remote, $this));
    }

    public function close()
    {
        $this->emit('close', array($this));

        $this->pause();
        $this->buffer->close();

        try {
            $this->socket->shutdown();
        }
        catch (Exception $ignore) {
        }
        $this->socket->close();

        $this->removeAllListeners();
    }
}
