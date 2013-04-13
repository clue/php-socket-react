<?php

require_once __DIR__.'/../vendor/autoload.php';

$loop = React\EventLoop\Factory::create();

$address = 'localhost:1337';

$factory = new Socket\React\Datagram\Factory($loop);

$factory->createClient($address)->then(function (Socket\React\Datagram\Datagram $socket) use ($loop, $address) {
    var_dump('Client socket connected to ' . $address . ' created');

    var_dump('Sending "test"');
    $socket->send('test');

    $socket->on('message', function($data, $peer) {
        var_dump('Received', $data, 'from', $peer);
        echo PHP_EOL;
    });
    $socket->on('close', function() {
        var_dump('Connection closed');
    });
    $socket->on('error', function($error) {
        var_dump('Error');
        echo $error;
    });

    var_dump('Reading and forwarding everything from STDIN');
    $loop->addReadStream(STDIN, function() use ($socket) {
        $line = trim(fgets(STDIN,8192));
        var_dump('Sending input', $line);
        $socket->send($line);
        echo PHP_EOL;
    });

    // send tick message every 2 seconds
//    $loop->addPeriodicTimer(2.0, function () use ($socket) {
//         $socket->send('tick');
//     });

}, function(Exception $e) {
    var_dump('Creation failed: ', $e->getMessage());
    echo $e;
});

$loop->run();
