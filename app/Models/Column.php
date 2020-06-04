<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Column extends Model
{
    protected $table = 'nlsg_column';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = ['name','user_id'];


    public function getDateFormat()
    {
        return time();
    }


    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id','id');
        //->select(['field']);
    }

    /**
     * $user_id  登录者用户
     * $target_id  目标id  1为专栏的老师id  2作品id ....
     * type 1 专栏  2作品 3直播  4会员 5线下产品
     * */
    static function isSubscribe($user_id=0,$target_id=0,$type=0){
        $is_sub = 0;

        //会员都免费
        $level = User::getLevel($user_id);
        if($level) return 1;

        if($user_id && $target_id && $type ){
            $where = [
                'type' => $type,
                'user_id' => $user_id,  //用户id
            ];

            //处理专栏的关注信息
            if($type == 1){
                $where['column_id'] = $target_id;
            }else if($type == 2){
                $where['works_id'] = $target_id;
            }else if($type == 3){
                $where['live_id'] = $target_id;
            }else{
                //type 类型错误直接返回0
                return 0;
            }
            $sub_data = Subscribe::where($where)->get()->toArray();
            if($sub_data){
                $is_sub = 1;
            }
        }
        return $is_sub;
    }

    public function get($field){
        $email = DB::table('nlsg_column')
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->get($field)
            ->map(function ($value) {
                return (array)$value;
            })->toArray();
        return $email;
    }
}