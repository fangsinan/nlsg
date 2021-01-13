<?php

namespace App\Models;


class Subscribe extends Base
{
    protected $table = 'nlsg_subscribe';


    public function UserInfo (){
        return $this->belongsTo('App\Models\User','user_id');
    }
    protected $fillable = ['user_id','pay_time','type','order_id','status','start_time','end_time', 'relation_id', 'service_id', ];




    /**
     * $user_id  登录者用户
     * $target_id  目标id  1为专栏的id  2作品id ....
     * type 1 专栏  2作品 3直播  4会员 5线下产品  6讲座
     * */
    static function isSubscribe ($user_id=0,$target_id=0,$type=0){
        $is_sub = 0;


        //会员都免费
        $level = User::getLevel($user_id);
        if($type!=3 && $level) return 1;

        if($user_id && $target_id && $type ){
            $where = ['type' => $type, 'user_id' => $user_id,];
            //处理专栏的关注信息
            if( !in_array($type,[1,2,3,4,5,6]) ){
                return 0;
            }

            $where['relation_id'] = $target_id;
            if( in_array($type,[3,5]) ){  //直播永久有效不需 判断end_time
                $sub_data = Subscribe::where($where)
                    ->first();
            }else{
                $sub_data = Subscribe::where($where)
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

                $sub_data = Subscribe::where([
                    'type' => 1,  //专栏
                    'user_id' => $user_id,
                    'relation_id' => $id['id'],
                    ])->where('end_time', '>', date('Y-m-d H:i:s'))->first();
                if($sub_data){
                    $is_sub = 1;
                }
            }
        }
        return $is_sub;
    }

}
