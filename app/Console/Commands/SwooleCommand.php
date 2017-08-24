<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use  App\Http\Controllers\SscController;

class SwooleCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swoole:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ws = new \Swoole\Websocket\Server("0.0.0.0", 9502);
        $ws->on('Open', function($ws, $req) {
            $ssc = new SscController();
            $res = $ssc->get_qici();
            $ws->push($req->fd, $res);
        });

//监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            $ssc = new SscController();
            $data = json_decode($frame->data, true);
            if (!empty($data)) {
                if ('bet' == $data['service']) {
                    $res = $ssc->update($data['data']);
                } elseif ('get_num' == $data['service']) {
                    $res = $ssc->getnum();
                }
                $ws->push($frame->fd, $res);
            }else{
                echo "kong";
            }

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

        $ws->on('Close', function($server, $fd) {
            echo "connection close: ".$fd;
        });

        $ws->start();
    }
}
