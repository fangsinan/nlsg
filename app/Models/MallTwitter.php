<?php


namespace App\Models;


class MallTwitter extends Base
{
    protected $table = 'nlsg_mall_twitter';


    //生成类型
    public function createJumpUrl($type, $gid, $info_id = 0, $twitter = 0, $flag = 0,$live_id=0,$live_info_id=0)
    {
        //精品课(视频,音频),专栏,商品,听书,课程(视频,音频),会员,好书
        //$host_url = Config::getInstance()->getConf('REQUEST_URI.INDEX_URL');
        $host_url = ConfigModel::getData(45)?? 'https://wechat.nlsgapp.com/';
        $url = '';
        //$u_type 1专栏 5听书   6课程 12讲座
        switch (intval($type)) {
            case 1:
                //专栏:
                $url = 'appv4/column-details?id=' . $gid . '&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 2:
                //课程视频:http://wechat.test.nlsgapp.com/works/videoinfo?work_id=128&workinfo_id=181&type=1
                $url = 'works/videoinfo?work_id=' . $gid . '&workinfo_id=' . $info_id .
                    '&type=1&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 3:
                //课程音频:http://wechat.test.nlsgapp.com/works/audioinfo?work_id=188&workinfo_id=406&type=2
                $url = 'works/audioinfo?work_id=' . $gid . '&workinfo_id=' . $info_id .
                    '&type=2&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 4:
                //课程文章:http://wechat.test.nlsgapp.com/article?id=61
                $url = 'article?id=' . $gid . '&tweeter_code=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 5:
                //听书
                $url = 'appv4/book-introduce?id=' . $gid . '&workinfo_id=' . $info_id . '&inviter=' .
                    $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 6:
                //课程:appv4/course-introduce?id=课程id
                $url = 'appv4/course-introduce?id=' . $gid . '&workinfo_id=' . $info_id .
                    '&type=1&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 7:
                //精品课音频:http://wechat.test.nlsgapp.com/excellent/audio?status=class&work_id=467&workinfo_id=2882&type=2
                $url = 'excellent/audio?work_id=' . $gid . '&workinfo_id=' . $info_id .
                    '&type=2&tweeter_code=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 8:
                //书籍:http://wechat.test.nlsgapp.com/bookDetails?id=7332415
                $url = 'bookDetails?id=' . $gid . '&tweeter_code=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 9:
                //商品:http://wechat.test.nlsgapp.com/mall/shop-details?goods_id=31
                //$url = 'mall/shop-details?goods_id=' . $gid . '&tweeter_code=' . $twitter;
                if ($flag == 3) {
                    $url = 'appv4/spell-details?time=' . time() . '&id=' . $gid . '&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                } else {
                    $url = 'appv4/shop-details?id=' . $gid . '&inviter=' . $twitter . '&time=' . time().'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                }
                break;
            case 10:
                //会员:http://wechat.test.nlsgapp.com/vipHome
                $url = 'active/vip?inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 11:
                //直播:https://wechat.nlsgapp.com/liveList?id=4
                $url = 'appv4/liveBroadcast?live_info_id=' . $live_info_id . '&inviter=' . $twitter.'&live_id='.$live_id;
                break;
            case 12:
                //讲座
                $url = 'appv4/lecture-introduce?id=' . $gid . '&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                break;
            case 13:
                //训练营都分享至父类
                
                // if($info_id){
                //     //$url = 'appv4/videoPlay?dId=' . $gid . '&info_id=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;
                //     $url = 'appv4/videoPlay?dId='. $gid .'&info_id='.$info_id.'&relationType=5&type=2';
                // }else{
                //     $url = 'appv4/lecture-introduce?type=2&id='. $gid .'&time='.time();
                // }
                $url = 'activeConsulting?share=1&id=' . $gid . '&inviter=' . $twitter.'&live_id='.$live_id.'&live_info_id='.$live_info_id;

                $is_free  = Column::where("id",$gid)->value("is_free");
                if($is_free == 1){
                    $url = 'appv4/videoPlay?share=1&dId=' . $gid . '&inviter=' . $twitter.'&info_id='.$info_id.'&relationType=5';
                }

                break;
            case 22: //三八邀请app注册
                $url = '';
                break;

            case 25:  //大咖讲书
                $url = 'activeShare?id='. $gid .'&user_id=' . $twitter.'&time='.time();
                break;
        }

        return $host_url . $url;
    }


    //  推客推荐记录
    public static function Twitter_Add($data)
    {
        $UserInfo = User::find($UserInfo = $data['user_id']);
        $time = time();
        if (!empty($UserInfo['expire_time'])) {
            $end_time = $UserInfo['expire_time'];
            if ($end_time > $time) {         //未过期
                $where = [
                    'user_id' => $data['user_id'],
                    'type' => $data['type'],
                    'cpid' => $data['cpid'],
                    'end_time' => $end_time,
                ];
                $info = MallTwitter::where($where)->first();
                if (empty($info)) {
                    //添加记录
                    $info_data = [];
                    $info_data['user_id'] = $data['user_id'];
                    $info_data['type'] = $data['type'];// 1：专栏   2：商品  3：精品课 4听书 5线下课 6邀请卡(有且只有一条记录当前用户)
                    $info_data['cpid'] = $data['cpid'];
                    $info_data['end_time'] = $end_time;
                    $info_data['ctime'] = $time;
                    //$twitterObj->add(self::$table,$info_data);
                    MallTwitter::create($info_data);
                }
            }
        }
        return;
    }
}
