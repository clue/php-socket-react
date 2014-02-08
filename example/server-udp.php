<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$address = 'localhost:1337';

$factory = new Socket\React\Datagram\Factory($loop);

$factory->createServer($address)->then(function (Socket\React\Datagram\Socket $socket) use ($loop, $address) {
    var_dump('Server listening on ' . $address .' established');

    $socket->on('message', function($data, $peer) use ($socket) {
        var_dump('Received', $data, 'from', $peer);

        // send back same message to peer
        $socket->send($data, $peer);
        echo PHP_EOL;
    });
    $socket->on('close', function() {
        var_dump('Connection closed');
    });
    $socket->on('error', function($error) {
        var_dump('Error');
        echo $error;
    });
}, function(Exception $e) {
    var_dump('Creation failed: ', $e->getMessage());
    echo $e;
});

$loop->run();
