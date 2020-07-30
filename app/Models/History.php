<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class History extends Model
{
    protected $table = 'nlsg_history';

    // 允许批量赋值
    protected  $fillable = [
        'relation_type','relation_id','info_id','user_id','is_del', 'time_leng','time_number'
    ];



    static function  DateTime($param_time = '')
    {
        //date_default_timezone_set('PRC');
        $ptime = strtotime($param_time);
        $etime = time() - $ptime;
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

}
