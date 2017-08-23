<?php
use \App\Http\Controllers\SscController;

$ws = new Swoole\Websocket\Server("0.0.0.0", 9502);
$ws->on('Open', function($ws, $req) {
    $res = SscController::get_qici();
    $ws->push($req->fd, json_encode($res));
});

//监听WebSocket消息事件
$ws->on('message', function ($ws, $frame) {
    $data = json_decode($frame->data, true);
    if (!empty($data)) {
        if ('bet' == $data['service']) {
            $res = SscController::update($data['data']);
        } elseif ('get_num' == $data['service']) {
            $res = SscController::getnum();
        }
    }
    $ws->push($frame->fd, $res);
    echo "Message: {$frame->data}\n";
});

//function onWorkerStart(swoole_server $serv, $worker_id)
$ws->on('WorkerStart', function ($ws, $worker_id) {
    if (!$ws->taskworker && 0 == $worker_id) {
        $ws->tick(30000, function () use ($ws, $worker_id) {
            echo "ssd";
        });
    }
});

$ws->on('Message', function($server, $frame) {
    echo "message: ".$frame->data;
    $server->push($frame->fd, "sssddd");
});

$ws->on('Close', function($server, $fd) {
    echo "connection close: ".$fd;
});

$ws->start();
?>