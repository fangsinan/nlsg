<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class ImReport extends Base
{
    protected $table = 'nlsg_im_report';

    public function add($params,$user)
    {
        $data = [];
//        $data['from_account'] = $params['from_account'] ?? 0;
        $data['from_account'] = $user['id'];
        $data['to_account'] = $params['to_account'] ?? 0;
        $data['group_id'] = $params['group_id'] ?? '';
        $data['msg'] = $params['msg'] ?? '';
        $data['type'] = $params['type'] ?? 0;

        if (empty($data['from_account']) || empty($data['to_account']) || empty($data['type'])) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        $res = DB::table('nlsg_im_report')->insert($data);
        if ($res == false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }

    }

}
