<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$factory = new Socket\React\Factory($loop);

$factory->createClient('www.google.com:80')->then(function (Socket\React\Stream $stream) {
    var_dump('Connection established');
    $stream->write("GET / HTTP/1.0\r\nHost: www.google.com\r\n\r\n");

    $stream->on('data', function($data) {
        var_dump($data);
    });
    $stream->on('close', function() {
        var_dump('Connection closed');
    });
}, function(Exception $e) {
    var_dump('Connection failed: ', $e->getMessage());
    echo $e;
});

$loop->run();
