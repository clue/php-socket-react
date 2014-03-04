# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

## 0.2.0 (2014-02-xx)

* BC break: The event loop handling has been rewritten and now resides in the
  `Socket\React\EventLoop` namespace.
* Feature: Require clue/datagram and implements its `Datagram\SocketInterface`
  for `Socket\React\Datagram\Datagram`. This means that you can now pass an
  instance of this class where other libaries expect a datagram socket.
* Fix: Typo in `Socket\React\Stream\Server` that passed null (thanks @cboden!)
* Fix: End connection if reading from stream socket fails
  ([#7](https://github.com/clue/socket-react/pull/7))

## 0.1.0 (2013-04-18)

* First tagged release

## 0.0.0 (2013-04-05)

* Initial concept

