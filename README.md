# clue/socket-react [![Build Status](https://travis-ci.org/clue/php-socket-react.svg?branch=master)](https://travis-ci.org/clue/php-socket-react)

Binding for raw sockets (ext-sockets) in React PHP.

## Install

The recommended way to install this library is [through composer](http://getcomposer.org). [New to composer?](http://getcomposer.org/doc/00-intro.md)

```JSON
{
    "require": {
        "clue/socket-react": "0.2.*"
    }
}
```

## Tests

To run the test suite, you need PHPUnit. Go to the project root and run:
````
$ phpunit tests
````

Note: The test suite contains tests for ICMP sockets which require root access
on unix/linux systems. Therefor some tests will be skipped unless you run
`sudo phpunit tests` to execte the full test suite.

## License

MIT
