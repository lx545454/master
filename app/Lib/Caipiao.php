<?php
namespace App\Lib;

class Caipiao {
	public static $LotteryCode = [
	    '11' => 'ssq',
	    '12' => 'fc3d',
	    '14' => 'dlt',
	    '16' => 'pl3',

    ];


	
	public static function getLotteryCode($code) {
		return self::$LotteryCode[$code];
	}
}