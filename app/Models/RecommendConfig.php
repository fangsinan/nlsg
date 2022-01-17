<?php

namespace App\Models;


class RecommendConfig extends Base
{
    protected $table = 'nlsg_recommend_config';

    protected $fillable = [
        'title', 'icon_pic', 'show_position', 'jump_type', 'modular_type', 'is_show', 'sort', 'icon_mark',
    ];

    public $show_position = [
        '1' => '顶部按钮',
        '2' => '中间ICON',
        '3' => '底部模块',
    ];

    public $jump_type = [
        '1'  => '首页',
        '2'  => '每日琨说',
        '3'  => '专栏',
        '4'  => '课程',
        '5'  => '讲座',
        '6'  => '360会员',
        '7'  => '训练营',
        '8'  => '商场',
        '9'  => '线下门票',
        '10' => '直播',
        '11' => '大咖主持人',
        '13' => '精品专题',
        '14' => '热门榜单',
        '15' => '课程全部分类页面',
        '16' => ' banner',
        '17' => '短视频',
        '18' => '活动类型'
    ];

    public $modular_type = [
        '1'  => ' banner',
        '2'  => 'icon',
        '3'  => '每日琨说',
        '4'  => '直播',
        '5'  => '精品课程',
        '6'  => '短视频',
        '7'  => '大咖主讲人',
        '8'  => '主题课程',
        '9'  => '精品专题',
        '10' => '热门榜单',
        '11' => '亲子专题',
    ];

}
