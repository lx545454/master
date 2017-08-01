<?php
namespace App\Http\Middleware;
use Closure;

class OutputMiddleware {
	/**
	 * 在request后，response前输出
	 * @param unknown $request
	 * @param Closure $next
	 */
	public function handle($request, Closure $next) {
		header("Content-type: application/json;charset=utf-8");
		//添加http缓存(需要时打开)
// 		$seconds_to_cache = 3600;
// 		$ts = gmdate("D, d M Y H:i:s", time() + $seconds_to_cache) . " GMT";
// 		header("Expires: $ts"); 
// 		header("Pragma: cache");
// 		header("Cache-Control: max-age=".$seconds_to_cache);
        $url = str_replace('/api/v1/','',$request->server()['REDIRECT_URL']);
        $_url = substr($url,0,6);
        $response = $next($request);
        if($_url == "jsonp_"){
            $callback = $request->input('callback');
            return  $callback."(".\GuzzleHttp\json_encode($response->origin).")";
            return $response->setCallback($request->input('callback'));
        }

		return $response;
	}
}