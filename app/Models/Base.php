<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class Base extends Model {

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected function getSqlBegin() {
        DB::connection()->enableQueryLog();
    }

    protected function getSql() {
        dd(DB::getQueryLog());
    }

    protected function emptyA2C($data) {
        if (is_object($data)) {
            if ($data->isEmpty()) {
                return new class {};
            } else {
                return $data;
            }
        } else {
            if (empty($data)) {
                return new class {};
            } else {
                return $data;
            }
        }
    }


    /**
     * @param array
     * 二维数组 搜索字段名称 条件 字段(多个逗号拼接)
     *
    [
    ['id','=','test'],
    ['title','like','title'],
    ['','raw','title','(id=3 or id=1)'], //sql语句
    ['','or',[
    ['title','like','title'],
    ['id','like','title']
    ]
    ],
    ],
    [
    'test'=>1,
    'title'=>'抖音'
    ]
    select * from `crm_user_source` where (`id` = ? and `title` like ?) and (id=3 or id=1) and ((`title` like ?) or (`id` like ?)
     * 获取搜索条件
     * @param array $items
     * @param array $params
     * @return \Illuminate\Database\Eloquent\Builder
     * 获取筛选条件
     */
    public static function getQueryWhere($items=[],$params=[]){

        $Query=self::query();
        // 搜索区域
        $map = getParamsMap($items,$params);
        $whereIn=[];
        foreach ($map as $k=>$v){

            if($v[1]=='=' || $v[1]=='eq'){

                if(is_array($v[2])){
                    unset($map[$k]);
                    $whereIn[]=$v;
                }else{
                    $valArr=explode(',',$v[2]);
                    if(count($valArr) >1){
                        $v[2]=$valArr;
                        $whereIn[]=$v;
                        unset($map[$k]);
                    }
                }
            }
        }

        if($map){
            $Query->where($map);
        }

        foreach ($whereIn as $val){
            $Query->whereIn($val[0],$val[2]);
        }

        foreach ($items as $item){
            //or
            if(isset($item[1])&& $item[1]=='or' &&isset($item[2]) &&is_array($item[2])){

                $orWhere = getParamsMap($item[2],$params);
                $Query->where(function ($query)use ($orWhere){
                    foreach ($orWhere as $w){
                        $query->orWhere([$w]);
                    }
                });
            }

            //whereRaw sql
            if(isset($item[1]) && $item[1]=='raw' && isset($params[$item[2]]) && isset($item[3])){
                $Query->whereRaw($item[3]);
            }

            //where auth
            if(isset($item[1]) && $item[1]=='auth' && isset($item[2]) && isset($item[0])){

                if(is_array($item[2])){
                    $value=$item[2];
                }else{
                    $value=explode(',',$item[2]);
                }
                $Query->whereIn($item[0],$value);
            }

            //whereIn
            if(isset($item[1]) && $item[1]=='in' && !empty($params[$item[2]]) && isset($item[0])){
                $value=explode(',',$params[$item[2]]);
                $Query->whereIn($item[0],$value);
            }
        }

        return $Query;
    }

}
