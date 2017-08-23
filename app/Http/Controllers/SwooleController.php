<?php
namespace App\Http\Controllers;

use App\User;
use App\Lib\Code;
use App\Lib\UtilityHelper;
use App\Lib\Request as REQ;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Cache;

class SwooleController extends Controller
{

    public function index()
    {
        $ws = new Swoole\Websocket\Server("0.0.0.0", 9502);
        $ssc = new SscController();
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
                $ws->tick(500, function () use ($ws, $worker_id) {
                    if ($res = Lot::getKaijiang()) {
                        foreach($ws->connections as $fd) {
                            //发送信息
                            $ws->push($fd, json_encode(['return_type' => 'new_lot', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res['lot_new']]]));
                        }
                    }
                    if ($res = Issue::getNewIssue()) {
                        foreach($ws->connections as $fd) {
                            //发送信息
                            $ws->push($fd, json_encode(['return_type' => 'new_issue', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                        }
                    }
                });
                $ws->tick(30000, function () use ($ws, $worker_id) {
                    if ($res = Lot::getBet()) {
                        foreach($ws->connections as $fd) {
                            //发送信息
                            $ws->push($fd, json_encode(['return_type' => 'bet_stat', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                        }
                    }
                });
            }
        });

        //监听WebSocket连接关闭事件
        $ws->on('close', function ($ws, $fd) {
            echo "client-{$fd} is closed\n";
        });

        $ws->start();
    }


    public function super_h5(Request $request)
    {
        $sub_data = Input::get();
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $sub = REQ::requset_all($url,'form',$sub_data);

        $callback = Input::get('callback');
        return  $callback."(".\GuzzleHttp\json_encode($sub).")";
    }

    public function super_js(Request $request)
    {
        $sub_data = Input::get();
        $page = Input::get("page","0");
        $num = Input::get("num","10");
        if($page){
            if($page > 0){
                $sub_data['start'] = ($page-1)*$num;
            }
        }
        $sub_data['appkey'] = env('JS_APPKEY');
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $url = str_replace("jsonp_","",$url);
        $sub = REQ::requset_all($url,'form',$sub_data);
        if(isset($sub['status']) && $sub['status'] == "0"){
            $sub = $sub['result'];
        }
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $_url = substr($url,0,6);
        if($_url == "jsonp_"){
            $callback = $request->input('callback');
            return  $callback."(".\GuzzleHttp\json_encode($sub).")";
        }
        return UtilityHelper::renderJson($sub);
    }

}