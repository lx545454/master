<?php
namespace App\Http\Middleware;
use Closure;
use App\Lib\UtilityHelper;
use App\Lib\Code;
use App\Lib\RedisHelper;
use Illuminate\Support\Facades\App;
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

		$sign = $request->input('sign');
		$params = $log = $request->query->all();
		//剔除sign
		unset($params['sign']);
//		//如果无参数,通过
//		if (is_array($params) && empty($params)) {
//			return $next($request);
//		}

		$generated = UtilityHelper::createSign($params);
        Log::info('loginfo', ['url'=>$method.'>>>'.$url,'params' => $log,'sign'=>$generated,'server'=>$request]);
		//判断两边sign是否正确
		if ($sign == $generated) {
			return $next($request);
		} else {
            return UtilityHelper::showError(Code::SIGN_ERROR);
		}
	}
}