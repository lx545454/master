<?php
namespace App\Http\Middleware;
use Closure;
use App\Lib\UtilityHelper;
use App\Lib\Code;
use App\Lib\RedisHelper;
use Illuminate\Support\Facades\App;

class BasicMiddleware {
	public function handle($request, Closure $next) {
		//首先判断get及post方法
		$method = $request->method();
		$url = $request->server()['REDIRECT_URL'];
//        $routes = App::getRoutes();
//        dd($routes);
        $routes = App::getFacadeRoot();
		$key = $method.$url;
		if (!isset($routes[$key])) {
			UtilityHelper::showError(Code::HTTP_REQUEST_METHOD_ERROR);
		}
		
		$sign = $request->input('sign');
		$params = $request->query->all();
		//剔除sign
		unset($params['sign']);
		//如果无参数,通过
		if (is_array($params) && empty($params)) {
			return $next($request);
		}
		$generated = UtilityHelper::createSign($params);
		//判断两边sign是否正确
		if ($sign == $generated) {
			return $next($request);
		} else {
			UtilityHelper::showError(Code::SIGN_ERROR);
		}
	}
}