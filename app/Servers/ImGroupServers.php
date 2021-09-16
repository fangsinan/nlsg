<?php


namespace App\Servers;


use App\Models\Column;
use App\Models\ImGroup;
use App\Models\ImGroupTop;
use App\Models\ImGroupUser;
use App\Models\ImUser;
use Illuminate\Support\Facades\DB;
use Libraries\ImClient;

class ImGroupServers
{
    public function groupList($params, $user_id)
    {
        $size = $params['size'] ?? 10;
        $owner_type = $params['owner_type'] ?? 0;
        $group_role = $params['group_role'] ?? 0;

        if (!empty($params['user_id'] ?? 0)) {
            $user_id = $params['user_id'];
        }

        $query = DB::table('nlsg_im_group as g')
            ->leftJoin('nlsg_im_group_top as gt', function ($q) use ($user_id) {
                $q->whereRaw('g.id = gt.group_id')->where('gt.user_id', '=', $user_id);
            })
            ->leftJoin('nlsg_user as owner', 'g.owner_account', '=', 'owner.id')
            ->select([
                'g.id', 'g.group_id', 'g.operator_account', 'g.owner_account', 'g.type', 'g.name',
                'g.status', 'g.created_at', 'owner.phone as owner_phone', 'owner.id as owner_id',
                'owner.nickname as owner_nickname', 'g.member_num', 'column_id as works_id',
//                DB::raw('(case gt.id when gt.id > 0 then 1 else 0 end) as is_top'),
                DB::raw('if(gt.id>0,1,0) AS is_top'),
                DB::raw('2000 as max_num')
            ])->orderBy('gt.id', 'desc');

        switch ((int)$owner_type) {
            case 1:
                //我创建的
                $query->where('g.owner_account', '=', $user_id);
                break;
            case 2:
                //我加入的
                $join_group_id_list = ImGroupUser::query()
                    ->where('group_account', '=', $user_id)
                    ->where('exit_type', '=', 0)
                    ->pluck('group_id')
                    ->toArray();
                $query->whereIn('g.group_id', $join_group_id_list);
                break;
        }

        switch ((int)$group_role) {
            case 1:
                $join_group_id_list = ImGroupUser::query()
                    ->where('group_account', '=', $user_id)
                    ->where('group_role', '=', 1)
                    ->where('exit_type', '=', 0)
                    ->pluck('group_id')
                    ->toArray();
                $query->whereIn('g.group_id', $join_group_id_list);
                break;
            case 2:
                $join_group_id_list = ImGroupUser::query()
                    ->where('group_account', '=', $user_id)
                    ->where('group_role', '=', 2)
                    ->where('exit_type', '=', 0)
                    ->pluck('group_id')
                    ->toArray();
                $query->whereIn('g.group_id', $join_group_id_list);
                break;
            case 9:
                $join_group_id_list = ImGroupUser::query()
                    ->where('group_account', '=', $user_id)
                    ->whereIn('group_role', [1, 2])
                    ->where('exit_type', '=', 0)
                    ->pluck('group_id')
                    ->toArray();
                $query->whereIn('g.group_id', $join_group_id_list);
                break;
        }


        switch ($params['ob'] ?? '') {
            case 'time_asc':
                $query->orderBy('g.created_at');
                break;
            case 'time_desc':
                $query->orderBy('g.created_at', 'desc');
                break;
        }

        if (!empty($params['name'] ?? '')) {
            $query->where('g.name', 'like', '%' . $params['name'] . '%');
        }

        if (!empty($params['status'] ?? 0)) {
            $query->where('g.status', '=', $params['status']);
        }

        $query->orderBy('g.id', 'desc');

        $list = $query->paginate($size);

        foreach ($list as $v) {
            $v->admin = DB::table('nlsg_im_group_user as gu')
                ->join('nlsg_user as u', 'gu.group_account', '=', 'u.id')
                ->where('group_id', '=', $v->group_id)
                ->where('group_role', '<>', 0)
                ->orderBy('group_role')
                ->select(['group_account', 'u.phone', 'u.nickname', 'group_role'])
                ->get();
            $v->worksInfo = Column::query()
                ->where('id', '=', $v->works_id ?? 0)
                ->select(['id', 'name', 'subtitle'])
                ->first();
        }

        return $list;
    }


    public function groupList_del($params, $user_id)
    {
        $size = $params['size'] ?? 10;

        $query = ImGroup::query();

        switch ($params['ob'] ?? '') {
            case 'time_asc':
                $query->orderBy('created_at');
                break;
            case 'time_desc':
                $query->orderBy('created_at', 'desc');
                break;
        }

        if (!empty($params['name'] ?? '')) {
            $query->where('name', 'like', '%' . $params['name'] . '%');
        }

        $query->orderBy('id', 'desc');

        $query->with(['ownerInfo:id,phone,nickname']);

        $query->select([
            'id', 'group_id', 'operator_account', 'owner_account', 'type', 'name', 'status', 'created_at'
        ]);

        $res = $query->paginate($size);
        $iguModel = new ImGroup();
        foreach ($res as &$v) {
            $v->group_count = $iguModel->groupUserCount($v->group_id, 0);
        }
        return $res;
    }

    public function statistics($params, $user_id): array
    {
        $m = new ImGroup();
        //群总数
        $res['total_counts'] = $m->statistics($user_id, 0);
        //我创建的
        $res['owner_counts'] = $m->statistics($user_id, 1);
        //我加入的
        $res['join_counts'] = $m->statistics($user_id, 2);

        return $res;
    }

    public function changeTop($params, $user_id)
    {
        $group_id = $params['group_id'] ?? '';
        if (empty($group_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $group_id = ImGroup::getId($group_id);
        $group_id = $group_id['id'];
        if (empty($group_id)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $check = ImGroupTop::query()
            ->where('group_id', '=', $group_id)
            ->where('user_id', '=', $user_id)
            ->first();

        //退出 置顶 取消置顶 转让 解散
        $flag = $params['flag'] ?? '';
        switch ($flag) {
            case 'top':
                $res = ImGroupTop::firstOrCreate([
                    'user_id' => $user_id,
                    'group_id' => $group_id
                ]);
                break;
            case 'cancel_top':
                if ($check) {
                    $res = ImGroupTop::where('user_id', '=', $user_id)
                        ->where('group_id', '=', $group_id)
                        ->delete();
                } else {
                    $res = true;
                }
                break;
            default:
                return ['code' => false, 'msg' => '参数错误'];
        }
        if ($res == false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }
    }

    public function bindWorks($params, $user_id)
    {
        $group_id = $params['group_id'] ?? 0;
        $works_id = $params['works_id'] ?? 0;

        $group_id = ImGroup::getId($group_id);
        $group_id = $group_id['id'];
        if (empty($group_id)) {
            return ['code' => false, 'msg' => '群信息错误'];
        }

        $check_works = Column::where('id', '=', $works_id)->where('type', '=', 3)->first();
        if (empty($check_works)) {
            return ['code' => false, 'msg' => '课程错误'];
        }

        $res = ImGroup::where('id', '=', $group_id)->update([
            'works_id' => $works_id
        ]);

        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }


    //添加/删除成员入群
    public function editJoinGroup($params)
    {

        if (empty($params['type']) || empty($params['group_id']) || empty($params['user_id'])) {
            return ['code' => false, 'msg' => 'request error'];

        }

        $imGroup = ImGroup::select('type')->where(['group_id' => $params['group_id']])->first();
        if (empty($imGroup)) {
            return ['code' => false, 'msg' => '该群不存在'];
        }
        if (!empty($imGroup['type']) && $imGroup['type'] == "AVChatRoom") {
            return ['code' => false, 'msg' => 'AVChatRoom 不支持该操作'];
        }


        if ($params['type'] == 'add') {
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/add_group_member");
            $post_data['GroupId'] = $params['group_id'];
            foreach ($params['user_id'] as $v) {
                $post_data['MemberList'][] = [
                    'Member_Account' => (string)$v,
                ];
            }
        } elseif ($params['type'] == 'del') {
            $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/delete_group_member");
            $post_data = [
                'GroupId' => $params['group_id'],
                'Silence' => $params['silence'] ?? '',
                'Reason' => $params['reason'] ?? '',
                'MemberToDel_Account' => $params['user_id'],
            ];
        } else {
            return ['code' => false, 'msg' => 'type error'];
        }

        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);
        //修改群人数
        ImGroup::setGroupInfo([$params['group_id']]);


        if ($res['ActionStatus'] == 'OK') {
            return [];
        } else {
            return ['code' => false, 'msg' => $res['ErrorCode']];
        }

    }

    //创建群
    public function createGroup($params, $user_id)
    {

        if (empty($params['Name'])) {
            return ['code' => false, 'msg' => '群名称错误'];
        }

        if (count($params['user_id']) < 2) {
            return ['code' => false, 'msg' => '初始群最少添加两个用户'];
        }

        $post_data = [
            'Owner_Account' => (string)$user_id,
            'Type' => "Public",
            'Name' => $params['Name'],
            'MemberList' => [
//                [
//                    "Member_Account"=> "211172", // 成员（必填）
//                    "Role" => "Admin" // 赋予该成员的身份，目前备选项只有 Admin（选填）
//                ],
            ]
        ];

        foreach ($params['user_id'] as $k => $v) {
            $post_data['MemberList'][] = ['Member_Account' => (string)$v];
        }
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/create_group");
        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);
        return $res;
    }

    //删除群
    public function destroyGroup($params, $user_id)
    {

        if (empty($params['GroupId'])) {
            return ['code' => false, 'msg' => 'GroupId错误'];
        }
        $group = ImGroup::where(['group_id' => $params['GroupId'], 'owner_account' => $user_id, 'status' => 1])->first();
        if (empty($group)) {
            return ['code' => false, 'msg' => 'Group error'];
        }

        $post_data = [
            'GroupId' => (string)$params['GroupId'],
        ];
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/destroy_group");
        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);
        return $res;
    }


    //转让群
    public function changeGroupOwner($params, $user_id)
    {

        if (empty($params['GroupId']) || empty($params['NewOwner_Account'])) {
            return ['code' => false, 'msg' => 'GroupId or new_user_id error'];
        }

        $group = ImGroup::where(['group_id' => $params['GroupId'], 'owner_account' => $user_id, 'status' => 1])->first();
        if (empty($group)) {
            return ['code' => false, 'msg' => 'Group error'];
        }

        $post_data = [
            'GroupId' => (string)$params['GroupId'],
            'NewOwner_Account' => (string)$params['NewOwner_Account'],
        ];
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/change_group_owner");
        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);
        if ($res['ActionStatus'] == "OK") {
            ImGroup::where(['group_id' => $params['GroupId'], 'owner_account' => $user_id, 'status' => 1])
                ->update(['owner_account' => $params['NewOwner_Account']]);

            //变更普通成员
            ImGroupUser::where(['group_id' => $params['GroupId'], 'group_account' => $user_id,])
                ->update(['group_role' => 0]);
            //变更新群主
            ImGroupUser::where(['group_id' => $params['GroupId'], 'group_account' => $params['NewOwner_Account'],])
                ->update(['group_role' => 1]);
        }

        return $res;
    }


    function getGroupMemberInfo($params, $user_id)
    {


        if (empty($params['GroupId'])) {
            return ['code' => false, 'msg' => 'GroupId错误'];
        }
        $group = ImGroup::where(['group_id' => $params['GroupId'], 'status' => 1])->first();
        if (empty($group)) {
            return ['code' => false, 'msg' => 'Group error'];
        }

        $post_data = [
            'GroupId' => (string)$params['GroupId'],
            'Limit' => $params['Limit'] ?? 100,
            'Offset' => $params['Offset'] ?? 0,
        ];
        $url = ImClient::get_im_url("https://console.tim.qq.com/v4/group_open_http_svc/get_group_member_info");
        $res = ImClient::curlPost($url, json_encode($post_data));
        $res = json_decode($res, true);
        $uids = array_column($res['MemberList'], 'Member_Account');
        $userProfileItem = ImUser::select("tag_im_to_account", "tag_im_nick", "tag_im_image")
            //->whereIn('tag_im_to_account',array_slice($uids, 0,20))->get()->toArray();
            ->whereIn('tag_im_to_account', $uids)->get()->toArray();
        $new_user = [];
        foreach ($userProfileItem as $item) {
            $new_user[$item['tag_im_to_account']] = $item;
        }
        foreach ($res['MemberList'] as $key => &$val) {
            $val['tag_im_nick'] = $new_user[$val['Member_Account']]['tag_im_nick'] ?? '';
            $val['tag_im_image'] = $new_user[$val['Member_Account']]['tag_im_image'] ?? '';
        }

        return $res;
    }


}
