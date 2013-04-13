<?php

namespace Socket\React\Stream;

use Socket\React\Stream\Stream;
use React\Socket\ConnectionInterface;

// class Connection extends Stream in order to provide getRemoteAddress()
// TODO: consider if Stream is actually still needed or can be merged?
class Connection extends Stream implements ConnectionInterface
{
    public function getRemoteAddress()
    {
        $name = $this->socket->getPeerName();
        return trim(substr($name, 0, strrpos($name, ':')), '[]');
    }
}
