<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class WorksInfo extends Model
{
    protected $table = 'nlsg_works_info';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = [''];


    public function getDateFormat()
    {
        return time();
    }

    public function getInfo($works_id,$is_sub=0){
        $works_data = WorksInfo::select([
            'id','type','title','section','introduce','url','callback_url1','callback_url1', 'callback_url2', 'callback_url3','view_num','duration','free_trial'
        ])->where('status',4)->where('pid',$works_id)->orderBy('order','asc')->get()->toArray();


        foreach ($works_data as $key=>$val){
            //处理url  关注或试听
            if( $is_sub == 1 || $val['free_trial'] == 1 ){
                $works_data[$key]['url'] = WorksInfo::GetWorksUrl ([
                    'callback_url3' => $val['callback_url3'],
                    'callback_url2' => $val['callback_url2'],
                    'callback_url1' => $val['callback_url1'],
                    'url' => $val['url'],
                ]);
            }else{
                //url置为空
                $works_data[$key]['url'] = '';
                $works_data[$key]['callback_url1'] = '';
                $works_data[$key]['callback_url2'] = '';
                $works_data[$key]['callback_url3'] = '';

            }

        }
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