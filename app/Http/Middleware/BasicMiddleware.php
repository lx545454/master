<?php
namespace App\Http\Middleware;
use Closure;
use App\Lib\UtilityHelper;
use App\Lib\Code;
use App\Lib\RedisHelper;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Input;
use Log;

class BasicMiddleware {
	public function handle($request, Closure $next) {
        //首先判断get及post方法
        $method = $request->method();
        $url = $request->server()['REDIRECT_URL'];
        $app = app();
        $routes = $app->getRoutes();
        $key = $method.$url;
        if (!isset($routes[$key])) {
            return UtilityHelper::showError(Code::HTTP_REQUEST_METHOD_ERROR);
        }
        $ip = $request->getClientIp();
        if($method == "GET"){
            return $next($request);
        }
		$sign = $request->input('sign');
		$params = $log = Input::get();
		//剔除sign
		unset($params['sign']);
//		//如果无参数,通过
//		if (is_array($params) && empty($params)) {
//			return $next($request);
//		}

		$generated = UtilityHelper::createSign($params);
		$str = UtilityHelper::_getSign($params);
		//判断两边sign是否正确
		if ($sign == $generated) {
			return $next($request);
		} else {
            Log::info('requestLog',['url'=>$method.'>>>'.$url,'str'=>$str,'params' =>$log,'sign'=>$generated]);
            return UtilityHelper::showError(Code::SIGN_ERROR);
		}
	}
}