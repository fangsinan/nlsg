<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class History extends Base
{
    protected $table = 'nlsg_history';

    // 允许批量赋值
    protected  $fillable = [
        'relation_type','relation_id','info_id','user_id','is_del', 'time_leng','time_number','os_type'
    ];



    static function  DateTime($param_time = '')
    {
        //date_default_timezone_set('PRC');
        $ptime = strtotime($param_time);
        $etime = time() - $ptime;
        if($etime <= 0){  // 防止提前请求
            return '刚刚';
        }
        switch ($etime){
            case $etime <= 60:
                $msg = '刚刚';
                break;
            case $etime > 60 && $etime <= 60 * 60:
                $msg = floor($etime / 60) . ' 分钟前';
                break;
            case $etime > 60 * 60 && $etime <= 24 * 60 * 60:
                $msg = date('Ymd',$ptime)==date('Ymd',time()) ? '今天 '.date('H:i',$ptime) : '昨天 '.date('H:i',$ptime);
                break;
            case $etime > 24 * 60 * 60 && $etime <= 2 * 24 * 60 * 60:
                $msg = date('Ymd',$ptime)+1==date('Ymd',time()) ? '昨天 '.date('H:i',$ptime) : '前天 '.date('H:i',$ptime);
                break;
            case $etime > 2 * 24 * 60 * 60 && $etime <= 12 * 30 * 24 * 60 * 60:
                $msg = date('Y',$ptime)==date('Y',time()) ? date('m-d ',$ptime) : date('Y-m-d ',$ptime);
                break;
            default: $msg = date('Y-m-d ',$ptime);
        }
        return $msg;
    }

    public function columns()
    {
        return $this->belongsTo('App\Models\Column','relation_id','id');
    }

    public function works()
    {
        return $this->belongsTo('App\Models\Works','relation_id','id');
    }

    public function chapter()
    {
        return $this->belongsTo('App\Models\WorksInfo','info_id', 'id');
    }



    static function getHistoryCount($relation_id=0,$relation_type=1,$user_id){
        if($relation_id == 0 || $user_id == 0){
            return 0;
        }

        $his_count = self::where([
            'relation_type' => $relation_type,
            'relation_id' => $relation_id,
            'user_id' => $user_id,
            'is_del' => 0,
        ])->count();
        return $his_count;
    }


    //最新章节
    static function getHistoryData($relation_id, $relation_type, $user_id){
        //继续学习的章节[时间倒序 第一条为最近学习的章节]
        $historyData = History::select('relation_id','info_id','time_number','time_leng')->where([
            'user_id'=>$user_id,
            'is_del'=>0,
            'relation_id'=>$relation_id,  // 讲座用的对应课程id
            'relation_type'=>$relation_type,
        ])->orderBy('updated_at','desc')->first();
        if($historyData){
            $historyData = $historyData?$historyData->toArray():[];
            $title = WorksInfo::select('title','introduce','section','duration')->where('id',$historyData['info_id'])->first();
            $historyData['title'] = $title->title ?? '';
            $historyData['introduce'] = $title->introduce ?? '';
            $historyData['section'] = $title->section ?? '';
            $historyData['info_duration'] = $title->duration ?? '0:0';
            $historyData['his_duration'] = TimeToMinSec($historyData['time_number']);
            
        }else{
            $historyData = (object)[];
        }


        return $historyData;
    }


    public function userInfo(){
        return $this->hasOne(User::class,'id','user_id');
    }


}
