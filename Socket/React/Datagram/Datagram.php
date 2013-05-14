<?php

namespace Socket\React\Datagram;

use React\EventLoop\LoopInterface;
use Evenement\EventEmitter;
use Socket\Raw\Socket as RawSocket;
use Datagram\SocketInterface;
use \Exception;

class Datagram extends EventEmitter implements SocketInterface
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

    /**
     * connect this client datagram socket to the given remote address
     *
     * Please note that unlike streaming sockets (TCP/IP),
     * datagram sockets usually have no concept of an
     * "established connection", i.e. the remote side will not be notified
     * of any "connection attempt" and no data has to be exchanged.
     *
     * Usually, there's no /need/ to connect() datagram sockets. If you
     * want to send to a specific remote address, see the $remote parameter
     * in send() as an alternative. Connect()ing the datagram client to
     * the remote side once may be preferrable as it frees you from having
     * to pass the remote address along with every send() call and only
     * requires a single host name resolution instead of having to perform
     * it with every send() call.
     *
     * @param string $address remote address to connect to
     * @return self $this (chainable)
     * @throws Exception on error
     * @see self::send()
     * @uses SocketRaw::connect()
     */
    public function connect($address)
    {
        $this->socket->connect($address);
        return $this;
    }

    /**
     * bind this datagram socket to the given local address
     *
     * @param string $address
     * @return self $this (chainable)
     * @throws Exception on error (e.g. invalid address)
     * @uses RawSocket::bind()
     */
    public function bind($address)
    {
        $this->socket->bind($address);
        return $this;
    }

    /**
     * enable/disable sending and receiving broacasts (by setting/unsetting SO_BROADCAST option)
     *
     * @param boolean $toggle
     * @return self $this (chainable)
     * @throws Exception on error
     * @uses RawSocket::setOption()
     */
    public function setOptionBroadcast($toggle = true)
    {
        $this->socket->setOption(SOL_SOCKET, SO_BROADCAST, (int)(bool)$toggle);
        return $this;
    }
}
