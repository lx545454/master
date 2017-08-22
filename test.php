<?php
$serv = new Swoole\Websocket\Server("0.0.0.0", 9502);

$serv->on('Open', function($server, $req) {
    echo "connection open1: ".$req->fd;
});

$serv->on('Message', function($server, $frame) {
    echo "message: ".$frame->data;
    $server->push($frame->fd, "sssddd");
});

$serv->on('Close', function($server, $fd) {
    echo "connection close: ".$fd;
});

$serv->start();
?>