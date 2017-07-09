<?php
namespace App\Http\Middleware;
use Closure;
use App\Lib\UtilityHelper;
use App\Lib\Code;
use App\Lib\Logs;

class ErrorHandlerMiddleware {
	/**
	 * 在request后，response前输出
	 * @param unknown $request
	 * @param Closure $next
	 */
	public function handle($request, Closure $next) {
		error_reporting(E_ALL);
		set_error_handler(function ($level, $message, $file = '', $line = 0) {
			Logs::debug('errorlog', 'error_level:='.$level.' message:='.$message.' file:='.$file.' line:='.$line);
			UtilityHelper::showError(Code::FATAL_ERROR);
		});
		
		set_exception_handler(function($e) {
			Logs::debug('errorlog', 'error_message:='.$e->getMessage());
			UtilityHelper::showError(Code::FATAL_ERROR);
		});
		
		register_shutdown_function(function() {
			if (error_get_last()) {
	    		Logs::debug('errorlog', 'error_message:='.var_export(error_get_last(), true));
				UtilityHelper::showError(Code::FATAL_ERROR);
	    	}
		});
		return $next($request);
	}
}