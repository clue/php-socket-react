# CHANGELOG

This file is a manually maintained list of changes for each release. Feel free
to add your changes here when sending pull requests. Also send corrections if
you spot any mistakes.

## 0.2.0 (2013-XX-XX)

* BC break: Whole new API regarding `SelectPoller` and dedicated `SocketSelectLoop`
* BC break: Rename `Datagram\Datagram` to `Datagram\Socket`
* BC break: Remove `bind()`, `connect()` and `setOptionBroadcast()` methods and
add respective options to the `Datagram\Factory` class (#1). This is done in
order to keep the API clean and to avoid confusion as to when it's safe to
invoke those methods.
* Feature: Implement common `Datagram\SocketInterface` from `clue/datagram`

## 0.1.0 (2013-04-18)

* First tagged release

