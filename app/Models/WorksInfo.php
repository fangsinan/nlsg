<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorksInfo extends Model
{
    protected $table = 'nlsg_works_info';
    public $timestamps = false;




    public function getDateFormat()
    {
        return time();
    }


    public function getInfo($works_id,$is_sub=0,$user_id=0,$type=1){
        $where = ['status'=>4];
        if($type == 1){
            $where['pid'] = $works_id;
        }else if($type == 2){
            $where['outline_id'] = $works_id;
        }
        $works_data = WorksInfo::select([
            'id','type','title','section','introduce','url','callback_url1','callback_url1', 'callback_url2', 'callback_url3','view_num','duration','free_trial'
        ])->where($where)->orderBy('order','asc')->get()->toArray();

        foreach ($works_data as $key=>$val){
            //处理url  关注或试听
            $works_data[$key]['href_url'] = '';
            if( $is_sub == 1 || $val['free_trial'] == 1 ){
                $works_data[$key]['href_url'] = WorksInfo::GetWorksUrl ([
                    'callback_url3' => $val['callback_url3'],
                    'callback_url2' => $val['callback_url2'],
                    'callback_url1' => $val['callback_url1'],
                    'url' => $val['url'],
                ]);
            }
            unset($works_data[$key]['callback_url3']);
            unset($works_data[$key]['callback_url2']);
            unset($works_data[$key]['callback_url1']);
            unset($works_data[$key]['url']);

            $works_data[$key]['time_leng'] = 0;
            $works_data[$key]['time_number'] = 0;
            if($user_id){
                //单章节 学习记录 百分比
                $his_data = History::select('time_leng','time_number')->where([
                    'works_id'     => $works_id,
                    'worksinfo_id' => 1,
                    'user_id'      => $user_id,
                    'is_del'       => 0,
                ])->orderBy('updated_at','desc')->first();
                if($his_data){
                    $works_data[$key]['time_leng'] = $his_data->time_leng;
                    $works_data[$key]['time_number'] = $his_data->time_number;
                }
            }
        }


        return $works_data;
    }

    static function GetWorksUrl($WorkArr){
        if(!empty($WorkArr['callback_url3'])){
            return $WorkArr['callback_url3'];
        }
        if(!empty($WorkArr['callback_url2'])){
            return $WorkArr['callback_url2'];
        }
        if(!empty($WorkArr['callback_url1'])){
            return $WorkArr['callback_url1'];
        }
        return $WorkArr['url'];
    }

}