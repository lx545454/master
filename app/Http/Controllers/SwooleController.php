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
        phpinfo();
        //创建websocket服务器对象，监听0.0.0.0:9502端口
        $ws = new \swoole_server("0.0.0.0", 9502);

        //监听WebSocket连接打开事件
        $ws->on('open', function ($ws, $request) {
            $ws->push($request->fd, json_encode(['data'=>"dsds"]));
        });

        //监听WebSocket消息事件
        $ws->on('message', function ($ws, $frame) {
            $data = json_decode($frame->data, true);
            if (!empty($data)) {
                if ('stat' == $data['service']) {
                    $res = Lot::getNewStatGx115();
                    $ws->push($frame->fd, json_encode(['return_type' => 'stat', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                } elseif ('history' == $data['service']) {
                    $res = Lot::getHistoryCode($data['data']);
                    $ws->push($frame->fd, json_encode(['return_type' => 'history', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                } elseif ('pool' == $data['service']) {
                    $res = Lot::getLotPool();
                    $ws->push($frame->fd, json_encode(['return_type' => 'pool', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                } elseif ('exponent' == $data['service']) {
                    $res = Lot::getLotExponent();
                    $ws->push($frame->fd, json_encode(['return_type' => 'exponent', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                } elseif ('win' == $data['service']) {
                    $res = Lot::getLotWin();
                    $ws->push($frame->fd, json_encode(['return_type' => 'win', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                } elseif ('new_issue' == $data['service']) {
                    $res = Issue::getLastIssue();
                    $ws->push($frame->fd, json_encode(['return_type' => 'new_issue', 'return_data' => ['code' => 0, 'msg' => 'success', 'data' => $res]]));
                }
            }
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