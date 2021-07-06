# clue/socket-react 

[![Build Status](https://travis-ci.org/clue/php-socket-react.svg?branch=master)](https://travis-ci.org/clue/php-socket-react)
[![installs on Packagist](https://img.shields.io/packagist/dt/clue/socket-react?color=blue&label=installs%20on%20Packagist)](https://packagist.org/packages/clue/socket-react)

Binding for raw sockets (ext-sockets) in React PHP.

## Quickstart example

Once [installed](#install), you can use the following example to send UDP broadcast datagrams:

```php
$loop = React\EventLoop\Factory::create();

$factory = new Socket\React\Datagram\Factory($loop);

$promise = $factory->createClient('udp://localhost:1337', array('broadcast' => true));
$promise->then(function (Socket\React\Datagram\Socket $socket) {
    $socket->send('test');

    $socket->on('message', function($data, $peer) {
        var_dump('Received', $data, 'from', $peer);
    });
});

$loop->run();
```

See also the [examples](examples).

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/socket-react": "~0.3.0"
    }
}
```

## Tests

To run the test suite, you need PHPUnit. Go to the project root and run:
````
$ phpunit tests
````

> Note: The test suite contains tests for ICMP sockets which require root access
> on unix/linux systems. Therefor some tests will be skipped unless you run
> `sudo phpunit tests` to execte the full test suite.

## License

MIT
