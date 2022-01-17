<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Relations\HasMany;

class RecommendConfig extends Base
{
    protected $table = 'nlsg_recommend_config';

    protected $fillable = [
        'title', 'icon_pic', 'show_position', 'jump_type', 'modular_type', 'is_show', 'sort', 'icon_mark','jump_url',
    ];


    public $show_position_array = [
//        '1' => '顶部按钮',
//        '2' => '中间ICON',
        '3' => '底部模块',
    ];

    public $jump_type_array = [
//        '1'  => '首页',
//        '2'  => '每日琨说',
//        '3'  => '专栏',
//        '5'  => '讲座',
//        '6'  => '360会员',
//        '7'  => '训练营',
//        '8'  => '商场',
//        '9'  => '线下门票',
//        '10' => '直播',
//        '14' => '热门榜单',
//        '15' => '课程全部分类页面',
//        '16' => 'banner',
//        '17' => '短视频',
//        '18' => '活动类型',
        '4'  => '课程',
        '11' => '大咖主持人',
        '13' => '精品专题',
    ];

    public $modular_type_array = [
//        '1'  => 'banner',
//        '2'  => 'icon',
//        '3'  => '每日琨说',
//        '4'  => '直播',
//        '6'  => '短视频',
//        '10' => '热门榜单',

        //works  下面只有能是works   绑定一个list_id
        '5'  => '精品课程',
        '8'  => '主题课程',
        '11' => '亲子专题',


        '7'  => '大咖主讲人',//is_author
        '9'  => '精品专题',//推荐多个list 本身list_id=0

    ];

    public function recommendInfo(): HasMany {
        return $this->hasMany(Recommend::class, 'position', 'id');
    }

}
