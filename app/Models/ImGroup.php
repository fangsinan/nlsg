<?php

namespace App\Models;

use Libraries\ImClient;

class ImGroup extends Base
{

    protected $table = 'nlsg_im_group';

    public function groupUser()
    {
        return $this->hasMany(ImGroupUser::class, 'group_id', 'group_id');
    }

    public function groupUserCount($group_id, $exit_type = -1)
    {
        $query = ImGroupUser::where('group_id', '=', $group_id);

        if ($exit_type >= 0) {
            $query->where('exit_type', '=', $exit_type);
        }

        return $query->count();
    }

    public function ownerInfo()
    {
        return $this->hasOne(User::class, 'id', 'owner_account');
    }

    /**
     * 群数量
     * @param int $user_id 用户id
     * @param int $type 类型(0全部  1我创建的  2我加入的)
     */
    public function statistics(int $user_id, int $type = 0)
    {
        $query = ImGroup::query()->with(['groupUser'])
            ->where('status', '=', 2);
        switch ($type) {
            case 1:
                $query->where('owner_account', '=', $user_id);
                break;
            case 2:
                $query->whereHas('groupUser', function ($q) use ($user_id) {
                    $q->where('group_account', '=', $user_id)->where('exit_type', '=', 0);
                });
                break;
        }

        return $query->count();

    }




    //$group_id  array类型
    public static function setGroupInfo($group_id){
        if(empty($group_id) || !is_array($group_id)){
            return '0';
        }
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/get_group_info");
        //目前只修改群人数
        $post_data = [
            'GroupIdList' => $group_id,
            'GroupBaseInfoFilter' => [
                'GroupId','Type','Name','MemberNum'
            ],
        ];
        $res = ImClient::curlPost($url,json_encode($post_data));
        $res = json_decode($res,true);
        if($res['ActionStatus'] == "OK" && empty(!$res['GroupInfo'])){
            foreach ($res['GroupInfo'] as $item) {
                if($item['ErrorCode'] == 0){
                    ImGroup::where('group_id',$item['GroupId'])->update(['member_num'=>$item['MemberNum']]);
                }
            }
        }

        return ;
    }
}
