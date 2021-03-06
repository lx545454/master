<?php
namespace App\Lib;

class Ssc {
	public static $playType = [
	    '101' => '任一',
	    '102' => '任二',
	    '11' => '一星直选',
	    '21' => '二星直选',
	    '22' => '二星复选',
	    '23' => '二星和值',
	    '24' => '二星组选复试',
	    '25' => '二星组选单式',
	    '26' => '二星组选包胆',
	    '27' => '二星组选和值',
	    '31' => '三星直选',
	    '32' => '三星复选',
	    '33' => '三星组三单式',
	    '34' => '三星组三复式',
	    '35' => '三星组六单式',
	    '36' => '三星组六复式',
	    '41' => '四星直选',
	    '42' => '四星复式',
	    '51' => '五星直选',
	    '52' => '五星复式',
	    '53' => '五星通选',
	    '61' => '大小单双',

    ];


	
	public static function getCode($code) {
		return self::$playType[$code];
	}

    public static function getLocationBykey($k){
        switch ($k){
            case 0:
                return "wan";
                break;
            case 1:
                return "qian";
                break;
            case 2:
                return "bai";
                break;
            case 3:
                return "shi";
                break;
            case 4:
                return "ge";
                break;
            default:
                return false;

        }
    }

}