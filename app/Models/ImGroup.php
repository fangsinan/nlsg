<?php

namespace App\Models;

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

}
