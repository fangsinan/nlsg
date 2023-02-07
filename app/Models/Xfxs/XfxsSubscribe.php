<?php

namespace App\Models\Xfxs;


use App\Models\User;
use Illuminate\Support\Facades\DB;
use App\Models\Base;
class XfxsSubscribe extends Base
{
    protected $table = 'xfxs_subscribe';

    public function UserInfo (){
        return $this->belongsTo('App\Models\User','user_id');
    }
    protected $fillable = ['user_id','pay_time','type','order_id','status','give','remark',
        'start_time','end_time', 'relation_id', 'service_id','channel_works_list_id','is_flag','twitter_id' ];

    /**
     * $user_id  登录者用户
     * $target_id  目标id  1为专栏的id  2作品id ....
     * type 1 专栏  2作品 3直播  4会员 5线下产品  6讲座 7训练营 8专题
     * */
    static function isSubscribe ($user_id=0,$target_id=0,$type=0){
        $is_sub = 0;


        //会员都免费
        $level = User::getLevel($user_id);


        if( !in_array($type,[3,7,8]) && $level) return 1;  // 直播和训练营不校验等级

        if($user_id && $target_id && $type ){
            $where = ['relation_id' => $target_id, 'type' => $type, 'user_id' => $user_id,'status'=>1,];
            //处理专栏的关注信息
            if( !in_array($type,[1,2,3,4,5,6,7,8]) ){
                return 0;
            }

            if( in_array($type,[2,3,5,6,7]) ){  //直播永久有效不需 判断end_time
                $sub_data = XfxsSubscribe::where($where)
                    ->first();
            }else{
                $sub_data = XfxsSubscribe::where($where)
                    ->where('end_time', '>', date('Y-m-d H:i:s'))
                    ->first();
            }

            if( $sub_data ){
                $is_sub = 1;
            }

            //特殊 作品和讲座的情况下需要校验是否订阅专栏
            if($is_sub==0 && in_array($type,[2,6])){
                switch ($type) {
                    case 1:
                        $result = Column::find($target_id);
                        break;
                    case 2:
                        $result = Works::find($target_id);
                        break;
                    case 3:
                        $result = Live::find($target_id);
                        break;
                    case 6:
                        $result = Column::find($target_id);
                        break;
                }

                $id = Column::select('id')->where( [ 'user_id'=> $result['user_id'],'type'=> 1] )->first();

                if(!empty($id['id'])){
                    $sub_data = XfxsSubscribe::where([
                        'relation_id'   => $id['id'],
                        'type'          => 1,  //专栏
                        'user_id'       => $user_id,
                        'status'        =>1
                    ])->where('end_time', '>', date('Y-m-d H:i:s'))->first();
                    if($sub_data){
                        $is_sub = 1;
                    }
                }

            }

            //判断是否购买课程所对应的专题
            if($type == 2 && $is_sub==0){
                $lists_id = ListsWork::where(['type'=>1,'works_id'=>$target_id,'state'=>1])->pluck('lists_id')->toArray();
                $sub_data = XfxsSubscribe::where(['type'=>8,'status'=>1,'user_id' => $user_id,])
                    ->whereIn('relation_id',$lists_id)
                    ->where('end_time', '>', date('Y-m-d H:i:s'))
                    ->first();
                if($sub_data){
                    $is_sub = 1;
                }
            }

            //判断直播是否购买过该老师的直播  目前只有购买过王琨老师的直播才可以免费听他之后的直播
            if($type == 3 && $is_sub==0){
                $result = Live::find($target_id);
                if( $result['user_id'] == 161904 && $result['is_free']==0){
                    $res = LivePayCheck::where([
                        'teacher_id'    => $result['user_id'],
                        'user_id'       => $user_id,
                    ])->where("begin_at",'<=',$result['begin_at'])->orderBy('id', 'desc')->first();

                    if(!empty($res)){
                        $is_sub = 1;
                        if(isset($res->protect_end_time) && !empty($res->protect_end_time)){
                            $now_time=date('Y-m-d H:i:s');
                            if($now_time>$res->protect_end_time){//保护时间过期，取消免费观看权限，需重新购买
                                $is_sub = 0;
                            }
                        }
                    }
                }
            }
            if($type == 7 && $is_sub==0){
                return self::CampIsSub($target_id,$user_id);
            }

        }
        return $is_sub;
    }

}
