# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

## 0.2.1 (2014-03-04)

* Fix: Make sure `Socket\Factory::createIcmp6()` actually returns an ICMPv6 socket
  ([#8](https://github.com/clue/socket-react/pull/8))

## 0.2.0 (2014-03-04)

* BC break: More SOLID design, reuse existing code, refactor code to fix
  ambiguities and ease extending
  ([#1](https://github.com/clue/socket-react/pull/1))
  * The event loop handling has been rewritten and now resides in the
    `Socket\React\EventLoop` namespace. Whole new API regarding `SelectPoller`
    and dedicated `SocketSelectLoop`.
  * Rename `Datagram\Datagram` to `Datagram\Socket`
  * Merge `Stream\Stream` into `Stream\Connection`
  * Remove `bind()`, `connect()` and `setOptionBroadcast()` methods from
    `Datagram\Socket` and add respective options to the `Datagram\Factory` class.
    This is done in order to keep the API clean and to avoid confusion as to
    when it's safe to invoke those methods.
  * Require clue/datagram and implement its `Datagram\SocketInterface`
    for `Socket\React\Datagram\Socket`. This means that you can now pass an
    instance of this class where other libaries expect a datagram socket.
* Fix: Typo in `Socket\React\Stream\Server` that passed null
  ([#4](https://github.com/clue/socket-react/pull/4) thanks @cboden!)
* Fix: End connection if reading from stream socket fails
  ([#7](https://github.com/clue/socket-react/pull/7))
* Fix: Compatibility with hhvm
  ([#8](https://github.com/clue/socket-react/pull/8))

## 0.1.0 (2013-04-18)

* First tagged release

## 0.0.0 (2013-04-05)

* Initial concept
