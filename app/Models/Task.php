<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use JPush;
class Task extends Base
{

    protected $table = 'nlsg_task';

     protected $fillable = [
        'id','user_id','subject','source_id','info_id','type',
    ];
    /**
     * 消息任务
     * @param integer $type 类型  1. 精品课 2.讲座 3.360会员 4.电商 5.直播课 6.幸福套餐 7.训练营 8.商品发货 9.认证审核通过  10.认证审核没有通过 11.收益返佣提醒 12.回复想法 13.喜欢你的想法
     * @param integer $user_id  用户id
     * @param integer $source_id 来源id
     * @param integer $info_id   章节id
     * @param string  $title   标题
     * @param boolean $ordernum 订单号
     * @param boolean $express  物流
     * @param integer $price  价格
     * @return void
     */
    public static function send($type=1, $user_id=0, $source_id=0, $info_id=0, $title='',$ordernum=false, $express=false, $price=0,$nickname='')
    {
        if (!$type){
            return  false;
        }
        switch ($type){
            case  1 :
                $data = [
                    'subject'   => '发布了新课程' . $title,
                    'user_id'   => $user_id,
                    'source_id' => $source_id,
                    'info_id'   => $info_id,
                    'type'    => 1
                ];
                break;
            case  2 :
                $data = [
                    'subject'    => '发布了新讲座' .$title,
                    'user_id'    => $user_id,
                    'source_id'  => $source_id,
                    'info_id'    => $info_id,
                    'type'       => 2
                ];
                break;
            case  3 :
                 $data = [
                    'subject'  => '您已成功购买幸福360会员',
                    'user_id'  => $user_id,
                    'type'  => 3
                ];
                break;
            case 4 :
                $data = [
                    'subject' => '发布了商品' .$title,
                    'user_id' => $user_id,
                    'source_id' => $source_id,
                    'type'    => 4,
                ];
                break;
            case 5:
                $data = [
                    'subject'=> '发布了直播课'.$title,
                    'user_id'=> $user_id,
                    'source_id' => $source_id,
                    'type'   => 5
                ];
                break;

            case 6:
                $data = [
                    'subject' => '您已成功购买幸福套餐',
                    'user_id' => $user_id,
                    'type'    => 6
                ];
                break;
            case 7 :
                $data = [
                    'subject' => '您已成功购买30天亲子训练营',
                    'user_id' => $user_id,
                    'type'    => 7
                ];
                break;
            case  8:
                $data = [
                    'subject' => '您购买的订单'.$ordernum.'，已通过'.$express.'发货',
                    'order_num' => $ordernum,
                    'express'   => $express,
                    'user_id' => $user_id,
                    'type'    => 8
                ];
                break;
            case 9:
                $data = [
                    'subject' => '您资料已审核通过',
                    'user_id' => $user_id,
                    'type'   => 9
                ];
                break;
            case 10:
                $data = [
                    'subject' => '您的资料没有通过审核',
                    'user_id' => $user_id,
                    'type'   => 10
                ];
                break;
            case  11:
                $data  = [
                    'subject' => $nickname .'购买' .$title.'，您获得返佣：'.$price.'元',
                    'user_id' => $user_id,
                    'type'    => 11
                ];
                break;
        }

        Task::create($data);

        return true;
    }

    public function push()
    {
        $lists = Task::select('id','user_id','subject','type','source_id','info_id')
            ->where('status', 1)
            ->orderBy('created_at','desc')
            ->get()
            ->toArray();
        if (!empty($lists)){
            foreach ($lists as $item) {
                JPush::pushNow(strval($item['user_id']), $item['subject'],['type'=>$item['type'],'id'=>$item['source_id'],'info_id'=>$item['info_id']]);
            }
        }
    }
}
