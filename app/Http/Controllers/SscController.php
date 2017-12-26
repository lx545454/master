<?php
namespace App\Http\Controllers;

use App\Dicofnum;
use App\Lunbotu;
use App\Lib\Code;
use App\Lib\UtilityHelper as H;
use App\Lib\Request as REQ;
use App\Xiaoxi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Lib\ssc;
use Log;
use App\Lib\Logs;


class SscController extends Controller
{


    public function goupdate($request=[]){
        $data = $request['bet_data'];
        Log::info('outLog',['data1'=>$data]);
        foreach ($data as $k=>$v){
            Log::info('outLog',['data2'=>$v]);
            self::update($v);
        }
        return H::renderJson([], 0, '投注成功');
    }
    public function update($request=[])
    {
        $playType = $request['playType'] ?? "";
        $number = $request['number'] ?? "";
        $beishu = $request['beishu'] ?? "1";
        $qici = $request['qici'] ?? "";
        $money = $request['money'] ?? "";
        $uid = $request['uid'] ?? "";

        //判断期次是否存在
        $res = DB::table('game_ssc')->where('qici',$qici)->where('state',1)->first();
        if(!$res){
            return H::showErrorMess("期次({$qici})不存在或已经结束");
        }

        if($uid){
            DB::table('ssc_order')->insert(
                ['uid'=>$uid,'number'=>$number,'playType'=>$playType,'beishu'=>$beishu,'qici'=>$qici,'money'=>$money]
            );
        }else{
            return H::showErrorMess("需要传入用户信息");
        }
        $tableName = 'dicofnum_'.$qici;
        $zhushu = 0;
        switch ($playType){
            case "101":
               $data = explode(',',$number);
               if(count($data)>1){
                   foreach ($data as $k=>$v){
                       if($v == "_"){
                          continue;
                       }
                       $param = ssc::getLocationBykey($k);
                       if(!$param){
                           return  H::showErrorMess("投注格式错误：超出5位数");
                       }
                        DB::table($tableName)->where($param,$v)->update([
                            'num' => DB::raw("num + {$beishu}*100000/10000"),
                            'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                        ]);
                       $zhushu +=1;
                   }
               }else{
                   return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
               }

               break;
            case "102":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    $sum = 0;
                    foreach ($data as $k=>$v) {
                        if($sum>2){
                            return  H::showErrorMess("投注格式错误：任二只能传入2个位置");
                        }
                        if ($v == "_") {
                            continue;
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                        $sum++;
                    }

                        $vArr1 = explode('-',$value[0]);
                        $vArr2 = explode('-',$value[1]);
                        foreach ($vArr1 as $e1=>$a1){
                            foreach ($vArr2 as $e2=>$a2){
                                DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->update([
                                    'num' => DB::raw("num + {$beishu}*100000/1000"),
                                    'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                                ]);
                            }
                        }
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }

                break;
            case "103":
                $location  = ['0'=>'shi','1'=>'ge','2'=>'bai','3'=>'qian','4'=>'wan'];
                $data = explode('-',$number);
                if(count($data)==1){
                    foreach ($location as $k1=>$v1){
                        foreach ($location as $k2=>$v2){
                            if($k1<$k2){
                                DB::table($tableName)->where($v1,$data[0])->where($v2,$data[0])->update([
                                    'num' => DB::raw("num + {$beishu}*100000/1000"),
                                    'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                                ]);
                            }
                        }
                    }
                }elseif (count($data)>1 && count($data)<6){
                    foreach ($location as $k1=>$v1){
                        foreach ($location as $k2=>$v2){
                            if($k1<$k2){
                                foreach ($data as $k3=>$v3){
                                    foreach ($data as $k4=>$v4){
                                        if($k3!=$k4){
                                            DB::table($tableName)->where($v1,$v3)->where($v2,$v4)->update([
                                                'num' => DB::raw("num + {$beishu}*100000/1000"),
                                                'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                                            ]);
                                        }
                                    }
                                }

                            }
                        }
                    }
                }else{
                    return  H::showErrorMess("投注格式错误：任二圈只能投入1-5位数");
                }
                break;
            case "104":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if ($v == "_") {
                            continue;
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }
                    $count = count($location);
                    switch ($count){
                        case 1:
                            DB::table($tableName)->where($location[0],$value[0])->update([
                                'num' => DB::raw("num + {$beishu}*100000*5/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            break;
                        case 2:
                            DB::table($tableName)->where($location[0],$value[0])->update([
                                'num' => DB::raw("num + {$beishu}*100000/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[1],$value[1])->update([
                                'num' => DB::raw("num + {$beishu}*100000/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->update([
                                'num' => DB::raw("num + {$beishu}*100000/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            break;
                        case 3:
                            $pos = strpos($number,'_,_');
                            if($pos===0){
                               foreach ($data as $k1=>$v1){
                                   foreach ($data as $k2=>$v2){
                                       if($k2!=$k1){
                                           foreach ($data as $k3=>$v3){
                                               if($k3 !=$v2 && $k3!=$k1){
                                                   DB::table($tableName)->where($location[0],$v1)->where($location[1],$v2)->where($location[2],$v3)->update([
                                                       'num' => DB::raw("num + {$beishu}*100000/100"),
                                                       'zong' => DB::raw("zong + 100000/100"),//参与人数与倍数无关
                                                   ]);
                                               }
                                           }
                                       }
                                   }
                               }
                            }
                            DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->update([
                                'num' => DB::raw("num + {$beishu}*100000/1000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[0],$value[0])->where($location[2],$value[2])->update([
                                'num' => DB::raw("num + {$beishu}*100000/1000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[1],$value[1])->where($location[2],$value[2])->update([
                                'num' => DB::raw("num + {$beishu}*100000/1000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[0],$value[0])->update([
                                'num' => DB::raw("num + {$beishu}*100000/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[1],$value[1])->update([
                                'num' => DB::raw("num + {$beishu}*100000/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            DB::table($tableName)->where($location[2],$value[2])->update([
                            'num' => DB::raw("num + {$beishu}*100000/10000"),
                            'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                            ]);
                            break;
                        case 4:
                            $pos = strpos($number,'_');
                            if($pos===0){
                                $vArr1 = explode('-',$value[0]);
                                $vArr2 = explode('-',$value[1]);
                                $vArr3 = explode('-',$value[2]);
                                $vArr4 = explode('-',$value[3]);
                                foreach ($vArr1 as $e1=>$a1){
                                    foreach ($vArr2 as $e2=>$a2){
                                        foreach ($vArr3 as $e3=>$a3){
                                            foreach ($vArr4 as $e4=>$a4){
                                                DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],$a4)->update([
                                                    'num' => DB::raw("num + {$beishu}*10/116"),
                                                    'zong' => DB::raw("zong + 10/116"),//参与人数与倍数无关
                                                ]);

                                                DB::table($tableName)->where($location[0],'<>',$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],$a4)->update([
                                                    'num' => DB::raw("num + {$beishu}*100000/1305"),
                                                    'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                                                ]);

                                                DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],'<>',$a4)->update([
                                                    'num' => DB::raw("num + {$beishu}*100000/1305"),
                                                    'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                                                ]);
                                            }
                                        }
                                    }
                                }
                            }
                            foreach ($value as $k1=>$v1){
                                foreach ($value as $k2=>$v2){
                                    if($k1<$k2){
                                        DB::table($tableName)->where($location[$k1],$v1)->where($location[$k2],$v2)->update([
                                            'num' => DB::raw("num + {$beishu}*100000/1000"),
                                            'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                                        ]);
                                    }
                                }
                                DB::table($tableName)->where($location[$k1],$v1)->update([
                                    'num' => DB::raw("num + {$beishu}*100000/10000"),
                                    'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                                ]);
                            }

                            break;
                        case 5:
                            DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],$value[3])->where($location[4],$value[4])->update([
                                'num' => DB::raw("num + {$beishu}*1"),
                                'zong' => DB::raw("zong + 1"),//参与人数与倍数无关
                                'big' => DB::raw("big + 1"),//参与人数与倍数无关
                            ]);
                            foreach ($value as $k1=>$v1){
                                DB::table($tableName)->where($location[$k1],$v1)->update([
                                    'num' => DB::raw("num + {$beishu}*100000/10000"),
                                    'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                                ]);
                            }
                            break;
                    }


                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "11":
                $data = explode(',',$number);
                $value = "";
                if(count($data)>1){
                    foreach ($data as $k=>$v) {

                        if ($v == "_") {
                            continue;
                        }else{
                            if($k!=4){
                                return  H::showErrorMess("投注格式错误：一星只能投个位");
                            }
                        }
                        $value = $v;
                    }

                    $vArr1 = explode('-',$value);
                    foreach ($vArr1 as $e1=>$a1){
                            DB::table($tableName)->where('ge',$a1)->update([
                                'num' => DB::raw("num + {$beishu}*100000/10000"),
                                'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                        ]);
                    }
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "21":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if ($v == "_") {
                            if($k==3){
                                return  H::showErrorMess("投注格式错误：二星直选十位不能为空");
                            }elseif ($k==4){
                                return  H::showErrorMess("投注格式错误：二星直选个位不能为空");
                            }
                            continue;
                        }else{
                            if($k==3 || $k==4){
                            }else{
                                return  H::showErrorMess("投注格式错误：二星直选只能传入个位和十位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    $vArr1 = explode('-',$value[0]);
                    $vArr2 = explode('-',$value[1]);
                    foreach ($vArr1 as $e1=>$a1){
                        foreach ($vArr2 as $e2=>$a2){
                            DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->update([
                                'num' => DB::raw("num + {$beishu}*100000/1000"),
                                'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                            ]);
                        }
                    }
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "22":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if(strpos($v,'-')){
                            return  H::showErrorMess("投注格式错误：二星复选每位只能有一个号码");
                        }
                        if ($v == "_") {
                            if($k==3){
                                return  H::showErrorMess("投注格式错误：二星直选十位不能为空");
                            }elseif ($k==4){
                                return  H::showErrorMess("投注格式错误：二星直选个位不能为空");
                            }
                            continue;
                        }else{
                            if($k==3 || $k==4){
                            }else{
                                return  H::showErrorMess("投注格式错误：二星直选只能传入个位和十位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1000"),
                        'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[1],$value[1])->update([
                        'num' => DB::raw("num + {$beishu}*100000/10000"),
                        'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                    ]);
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "23":
                $location  = ['0'=>'shi','1'=>'ge'];
                if(!is_numeric($number)){
                    H::showErrorMess("投注格式错误：二星和值需要单个数值");
                }else{
                    if((int)$number*1<0 || (int)$number>18){
                       return H::showErrorMess("投注格式错误：二星和值范围：0-18");
                    }
                }
                for($i=0; $i<=9; $i++){
                    if($number-$i>9 || $number-$i<0){
                    }else{
                        DB::table($tableName)->where($location[0],$i)->where($location[1],$number-$i)->update([
                            'num' => DB::raw("num + {$beishu}*100000/1000"),
                            'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                        ]);
                    }

                }

                break;
            case "24":
                $location  = ['0'=>'shi','1'=>'ge'];
                $data = explode('-',$number);
                if(count($data)<2){
                   return H::showErrorMess("投注格式错误：二星组选复试至少2个数字");
                }
                foreach ($data as $k=>$v){
                    foreach ($data as $k1=>$v1){
                        $double = 2;
                        if($k == $k1){
                            $double = 1;
                        }
                        DB::table($tableName)->where($location[0],$v)->where($location[1],$v1)->update([
                            'num' => DB::raw("num + {$beishu}*100000/1000*{$double}"),
                            'zong' => DB::raw("zong + 100000/1000*{$double}"),//参与人数与倍数无关
                        ]);
                    }
                }

                break;
            case "25":
                $location  = ['0'=>'shi','1'=>'ge'];
                $data = explode('-',$number);
                if(count($data)!=2){
                    return H::showErrorMess("投注格式错误：二星组选单试只能传入2个数字");
                }

                DB::table($tableName)->where($location[0],$data[0])->where($location[1],$data[1])->update([
                    'num' => DB::raw("num + {$beishu}*100000/1000*2"),
                    'zong' => DB::raw("zong + 100000/1000*2"),//参与人数与倍数无关
                ]);

                DB::table($tableName)->where($location[0],$data[1])->where($location[1],$data[1])->update([
                    'num' => DB::raw("num + {$beishu}*100000/1000*2"),
                    'zong' => DB::raw("zong + 100000/1000*2"),//参与人数与倍数无关
                ]);

                break;
            case "26":
                $location  = ['0'=>'shi','1'=>'ge'];
                if(!is_numeric($number)){
                   return H::showErrorMess("投注格式错误：二星组选包胆需要单个数值");
                }else{
                    if($number<0 || $number>9){
                      return  H::showErrorMess("投注格式错误：二星组选包胆范围：0-9");
                    }
                }

                DB::table($tableName)->where($location[0],$number)->where($location[1],$number)->update([
                    'num' => DB::raw("num + {$beishu}*100000/1000"),
                    'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                ]);

                DB::table($tableName)->where($location[0],$number)->where($location[1],'<>',$number)->update([
                    'num' => DB::raw("num + {$beishu}*100000/2000"),
                    'zong' => DB::raw("zong + 100000/2000"),//参与人数与倍数无关
                ]);

                DB::table($tableName)->where($location[1],$number)->where($location[0],'<>',$number)->update([
                    'num' => DB::raw("num + {$beishu}*100000/2000"),
                    'zong' => DB::raw("zong + 100000/2000"),//参与人数与倍数无关
                ]);
                break;
            case "27":
                $location  = ['0'=>'shi','1'=>'ge'];
                if(!is_numeric($number)){
                   return H::showErrorMess("投注格式错误：二星和值需要单个数值");
                }else{
                    if($number<0 || $number>18){
                       return H::showErrorMess("投注格式错误：二星和值范围：0-18");
                    }
                }
                for($i=0; $i<=9; $i++){
                    if($number-$i>9 || $number-$i<0){
                    }else{
                        DB::table($tableName)->where($location[0],$i)->where($location[1],$number-$i)->update([
                            'num' => DB::raw("num + {$beishu}*100000/2000"),
                            'zong' => DB::raw("zong + 100000/2000"),//参与人数与倍数无关
                        ]);
                    }

                }
                break;
            case "31":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if ($v == "_") {
                            if($k==3){
                                return  H::showErrorMess("投注格式错误：三星直选十位不能为空");
                            }elseif ($k==4){
                                return  H::showErrorMess("投注格式错误：三星直选个位不能为空");
                            }elseif ($k==2){
                                return  H::showErrorMess("投注格式错误：三星直选百位不能为空");
                            }
                            continue;
                        }else{
                            if($k==3 || $k==4 || $k==2){
                            }else{
                                return  H::showErrorMess("投注格式错误：三星直选只能传入个位和十位和百位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    $vArr1 = explode('-',$value[0]);
                    $vArr2 = explode('-',$value[1]);
                    $vArr3 = explode('-',$value[2]);
                    foreach ($vArr1 as $e1=>$a1){
                        foreach ($vArr2 as $e2=>$a2){
                            foreach ($vArr3 as $e3=>$a3){
                                DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->where($location[2],$a3)->update([
                                    'num' => DB::raw("num + {$beishu}*100000/100"),
                                    'zong' => DB::raw("zong + 100000/100"),//参与人数与倍数无关
                                ]);
                            }
                        }
                    }
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "32":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if(strpos($v,'-')){
                            return  H::showErrorMess("投注格式错误：三星复选每位只能有一个号码");
                        }
                        if ($v == "_") {
                            if($k==3){
                                return  H::showErrorMess("投注格式错误：三星直选十位不能为空");
                            }elseif ($k==4){
                                return  H::showErrorMess("投注格式错误：三星直选个位不能为空");
                            }elseif ($k==2){
                                return  H::showErrorMess("投注格式错误：三星直选百位不能为空");
                            }
                            continue;
                        }else{
                            if($k==3 || $k==4 || $k==2){
                            }else{
                                return  H::showErrorMess("投注格式错误：三星直选只能传入个位和十位和百位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/100"),
                        'zong' => DB::raw("zong + 100000/100"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[1],$value[1])->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1000"),
                        'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/10000"),
                        'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                    ]);

                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "33":
                break;
            case "34":
                break;
            case "35":
                break;
            case "36":
                break;
            case "41":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if ($v == "_") {
                            if($k==3){
                                return  H::showErrorMess("投注格式错误：四星直选十位不能为空");
                            }elseif ($k==4){
                                return  H::showErrorMess("投注格式错误：四星直选个位不能为空");
                            }elseif ($k==2){
                                return  H::showErrorMess("投注格式错误：四星直选百位不能为空");
                            }elseif ($k==1){
                                return  H::showErrorMess("投注格式错误：四星直选千位不能为空");
                            }
                            continue;
                        }else{
                            if($k==3 || $k==4 || $k==2 || $k==1){
                            }else{
                                return  H::showErrorMess("投注格式错误：四星直选只能传入个位和十位和百位和千位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    $vArr1 = explode('-',$value[0]);
                    $vArr2 = explode('-',$value[1]);
                    $vArr3 = explode('-',$value[2]);
                    $vArr4 = explode('-',$value[3]);
                    foreach ($vArr1 as $e1=>$a1){
                        foreach ($vArr2 as $e2=>$a2){
                            foreach ($vArr3 as $e3=>$a3){
                                foreach ($vArr4 as $e4=>$a4){
                                    DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],$a4)->update([
                                        'num' => DB::raw("num + {$beishu}*10/116"),
                                        'zong' => DB::raw("zong + 10/116"),//参与人数与倍数无关
                                    ]);

                                    DB::table($tableName)->where($location[0],'<>',$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],$a4)->update([
                                        'num' => DB::raw("num + {$beishu}*100000/1305"),
                                        'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                                    ]);

                                    DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],'<>',$a4)->update([
                                        'num' => DB::raw("num + {$beishu}*100000/1305"),
                                        'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                                    ]);
                                }
                            }
                        }
                    }
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "42":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if(strpos($v,'-')){
                            return  H::showErrorMess("投注格式错误：四星复选每位只能有一个号码");
                        }
                        if ($v == "_") {
                            if($k==3){
                                return  H::showErrorMess("投注格式错误：四星直选十位不能为空");
                            }elseif ($k==4){
                                return  H::showErrorMess("投注格式错误：四星直选个位不能为空");
                            }elseif ($k==2){
                                return  H::showErrorMess("投注格式错误：四星直选百位不能为空");
                            }elseif ($k==1){
                                return  H::showErrorMess("投注格式错误：四星直选千位不能为空");
                            }
                            continue;
                        }else{
                            if($k==3 || $k==4 || $k==2 || $k==1){
                            }else{
                                return  H::showErrorMess("投注格式错误：四星直选只能传入个位和十位和百位和千位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],$value[3])->update([
                        'num' => DB::raw("num + {$beishu}*10/116"),
                        'zong' => DB::raw("zong + 10/116"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],'<>',$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],$value[3])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1305"),
                        'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],'<>',$value[3])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1305"),
                        'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/100"),
                        'zong' => DB::raw("zong + 100000/100"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[1],$value[1])->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1000"),
                        'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/10000"),
                        'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                    ]);

                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "51":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if ($v == "_") {
                            return  H::showErrorMess("投注格式错误：五星直选任何位置都不能为空");
                            continue;
                        }else{
                            if($k>4){
                                return  H::showErrorMess("投注格式错误：五星直选只能传入个位和十位和百位和千位和万位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    $vArr1 = explode('-',$value[0]);
                    $vArr2 = explode('-',$value[1]);
                    $vArr3 = explode('-',$value[2]);
                    $vArr4 = explode('-',$value[3]);
                    $vArr5 = explode('-',$value[4]);
                    foreach ($vArr1 as $e1=>$a1){
                        foreach ($vArr2 as $e2=>$a2){
                            foreach ($vArr3 as $e3=>$a3){
                                foreach ($vArr4 as $e4=>$a4){
                                    foreach ($vArr5 as $e5=>$a5){
                                        DB::table($tableName)->where($location[0],$a1)->where($location[1],$a2)->where($location[2],$a3)->where($location[3],$a4)->where($location[4],$a5)->update([
                                            'num' => DB::raw("num + {$beishu}*1"),
                                            'zong' => DB::raw("zong + 1"),//参与人数与倍数无关
                                            'big' => DB::raw("big + 1"),//参与人数与倍数无关
                                        ]);
                                    }
                                }
                            }
                        }
                    }
                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "52":
                $location  = $value =  [];
                $data = explode(',',$number);
                if(count($data)>1){
                    foreach ($data as $k=>$v) {
                        if(strpos($v,'-')){
                            return  H::showErrorMess("投注格式错误：五星复选每位只能有一个号码");
                        }
                        if ($v == "_") {
                            return  H::showErrorMess("投注格式错误：五星复选任何位置都不能为空");
                            continue;
                        }else{
                            if($k>4){
                                return  H::showErrorMess("投注格式错误：五星直选只能传入个位和十位和百位和千位和万位");
                            }
                        }
                        $p = ssc::getLocationBykey($k);
                        if($p){
                            $location[] = $p;
                            $value[] = $v;
                        }else{
                            return  H::showErrorMess("投注格式错误：超出5位数");
                        }
                    }

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],$value[3])->where($location[4],$value[4])->update([
                        'num' => DB::raw("num + {$beishu}*1"),
                        'zong' => DB::raw("zong + 1"),//参与人数与倍数无关
                        'big' => DB::raw("big + 1"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],$value[3])->update([
                        'num' => DB::raw("num + {$beishu}*10/116"),
                        'zong' => DB::raw("zong + 10/116"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],'<>',$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],$value[3])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1305"),
                        'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->where($location[3],'<>',$value[3])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1305"),
                        'zong' => DB::raw("zong + 100000/1305"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[0],$value[0])->where($location[1],$value[1])->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/100"),
                        'zong' => DB::raw("zong + 100000/100"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[1],$value[1])->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/1000"),
                        'zong' => DB::raw("zong + 100000/1000"),//参与人数与倍数无关
                    ]);

                    DB::table($tableName)->where($location[2],$value[2])->update([
                        'num' => DB::raw("num + {$beishu}*100000/10000"),
                        'zong' => DB::raw("zong + 100000/10000"),//参与人数与倍数无关
                    ]);

                }
                else{
                    return H::showErrorMess("投注格式错误：需要五位数以逗号分隔");
                }
                break;
            case "53":
                break;
            case "61":
                break;
            default:
                return H::showErrorMess("玩法不存在，请参考列表");

        }
        DB::table('game_ssc')->where('qici',$qici)->increment('money',$money);

//        return H::renderJson([], 0, '投注成功');
    }
    public function getNum($request = []){
        $qici = $request['qici'] ?? "";
        $str = $request['str'] ?? "";
        if($str){
            $str_arr = explode(',',$str);
            foreach ($str_arr as &$item) {
                $item+=100001;
            }
        }else{
            $str_arr = [];
        }

        $ssc = DB::table('game_ssc')->where('qici',$qici)->first();
        if($ssc && isset($ssc->qici)){
            $tableName = 'dicofnum_'.$qici;
            $limitMoney = ($ssc->peilv*$ssc->money/100)*100000;//用100000来规避小数类型
            $db = DB::table($tableName)->where('num', '<=',$limitMoney);
            if($str_arr){
                $db = $db->whereIn('id',$str_arr);
            }
//            $limitMoney = ($ssc->peilv*$ssc->money/100 - rand(0,20))*100000/58000;//用100000来规避小数类型
//            $numRes = DB::table($tableName)->whereBetween('num', [$limitMoney-100, $limitMoney])->first();
            $numRes = $db->get()->toArray();
            if(!$numRes){
                $numRes = DB::table($tableName)->where('num', '<=',$limitMoney)->get()->toArray();
            }
            $count = count($numRes);
            $key = rand(0,($count-1));
            $numRes = $numRes[$key];
            $data = ['number'=>$numRes->wan.','.$numRes->qian.','.$numRes->bai.','.$numRes->shi.','.$numRes->ge];
            DB::table("game_ssc")->where('id',$ssc->id)->update([
                'number' => $numRes->wan.' '.$numRes->qian.' '.$numRes->bai.' '.$numRes->shi.' '.$numRes->ge,
            ]);
//            return H::renderJson($data, 0,"第{$qici}期开奖结果");
        }else{
            return H::showErrorMess("未传入期次或其次不存在");
        }


    }

    public function get_qici($request = []){
        $ssc = DB::table('game_ssc')->where('state',1)->first();
        if(!$ssc){
            $this->add_qici();
            $ssc = DB::table('game_ssc')->where('state',1)->first();
        }
        $ssc->endTime = date("Y-m-d G:H:s",strtotime($ssc->createTime)+$ssc->duration);
        return H::renderJson($ssc);
    }

    public function add_qici($request=[]){
        $peilv = "90";
        $ssc = DB::table('game_ssc')->orderBy('id','desc')->first();
        if($ssc){
            $qici = $ssc->qici;
        }else{
            $qici = "17000001";
        }
        if($qici){
            $qici += 1;
            $res = DB::table("game_ssc")->insert([
                'qici'=>$qici,
                'peilv'=>$peilv,
                'state'=>'1',
                'duration'=>'120',
            ]);
            if($res){
                $createRes = DB::select("call insert_test_val('{$qici}');");
                if($createRes){
                    return H::renderJson([], 0, "期次（{$qici}）创建成功");
                }
            }
        }
        return H::showErrorMess("初始化失败");

    }
    public function getLocationBykey($k){
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

    public function auto_add_ticket($request=[]){
        $peilv = "58";
        $ssc = DB::table('game_ssc')->orderBy('id','desc')->first();

        if($ssc && isset($ssc->qici)){
            $qici = $ssc->qici +1;
            $res = DB::table("game_ssc")->insert([
                'qici'=>$qici,
                'peilv'=>$peilv,
                'state'=>'1',
            ]);
            if($res){
                $createRes = DB::select("call insert_test_val('{$qici}');");
                if($createRes){
                    return H::renderJson([], 0, "期次（{$qici}）创建成功");
                }
            }
        }
        return H::showErrorMess("初始化失败");

    }

    public function get_qicis($request=[]){
        $ssc = DB::table('game_ssc')->orderBy('id','desc')->get()->toArray();
        if($ssc){
            return H::renderJson($ssc);
        }else{
            $this->add_qici();
            $ssc = DB::table('game_ssc')->get()->toArray();
        }
        return H::renderJson($ssc);

    }

    public function get_qici_detail($request=[]){
        $qici = $request['qici'] ?? "";
        if($qici){
            $ssc = DB::table("ssc_order")->where('qici','=',$qici)->get()->toArray();
            return H::renderJson($ssc);
        }else{
            return H::showErrorMess("需要期次");
        }
    }

    public function checkssc($request=[]){
//        $qici = $request['qici'] ?? "";
            $str = env('SSC_STR');
            $ssc = DB::table("game_ssc")->orderBy('id','desc')->first();
            $strtime = (strtotime($ssc->createTime)+$ssc->duration)-time();
            Logs::debug('check',$strtime.'---'.json_encode($ssc));
            if($ssc){
                if((strtotime($ssc->createTime)+$ssc->duration)<time()){
                    DB::table("game_ssc")->where('id',$ssc->id)->update([
                        'state' => '2',
                    ]);
                    self::getNum(['str'=>$str,'qici'=>$ssc->qici]);
                    self::add_qici();

                }else{
                    echo "未结束";
                }
            }else{
                self::add_qici();
            }
    }

    //返奖规则
    public function fjgz($Arr){
        $playType = $Arr['playType'] ?? "0";
        switch ($playType){
            case 11:
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