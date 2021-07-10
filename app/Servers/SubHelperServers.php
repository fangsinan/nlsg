<?php


namespace App\Servers;


use App\Models\Column;
use App\Models\Live;
use App\Models\Works;
use Illuminate\Support\Facades\DB;

class SubHelperServers
{
    public function ojbList()
    {
        $column_list = Column::where('status', '=', 1)
            ->where('type', '=', 2)
            ->select('id', DB::raw('6 as type'), 'name as title')
            ->get();

        $works_list = Works::where('status', '=', 4)
            ->where('type', '=', 2)
            ->select('id', DB::raw('2 as type'), 'title')
            ->get();

        $live_list = Live::whereIn('status', [1,4])
            ->where('is_finish', '=', 0)
            ->where('is_del', '=', 0)
            ->where('is_test', '=', 0)
            ->select('id', DB::raw('3 as type'), 'title')
            ->get();

        $xly_list = Column::where('status', '=', 1)
            ->where('type', '=', 3)
            ->select('id', DB::raw('7 as type'), 'name as title')
            ->get();

        return [
            'column_list' => $column_list,
            'works_list' => $works_list,
            'live_list' => $live_list,
            'xly_list' => $xly_list,
        ];
    }

    public function comObjList(){

    }

    public function addOpenList($params, $admin_id)
    {
        $id = $params['id'] ?? 0;
        $type = $params['type'] ?? 0;
        if (empty($id) || empty($type)) {
            return ['code' => false, 'msg' => '参数错误'];
        }
        $phone = $params['phone'] ?? '';
        $phone = preg_replace('/[^0-9]/i', ',', $phone);
        $phone = explode(',', $phone);
        if (empty($phone)) {
            return ['code' => false, 'msg' => '手机号信息错误'];
        }

        $error_phone = [];
        $add_data = [];
        foreach ($phone as $v) {
            if (strlen($v) !== 11) {
                $error_phone[] = $v;
                continue;
            }
            $temp_add_data = [];
            $temp_add_data['phone'] = $v;
            $temp_add_data['works_type'] = $type;
            $temp_add_data['works_id'] = $id;
            $temp_add_data['status'] = 1;

            if ($type == 2 && $id == 404) {
                $temp_add_data['is_sendsms'] = 1;
            } else {
                $temp_add_data['is_sendsms'] = 0;
            }

            $temp_add_data['admin_id'] = $admin_id;
            $add_data[] = $temp_add_data;
        }

        if (!empty($add_data)) {
            $res = DB::table('works_list_of_sub')
                ->insert($add_data);
        } else {
            $res = true;
        }

        $error_phone = implode(',', $error_phone);
        $msg = '';
        if (!empty($error_phone)) {
            $msg = '无效号码:' . $error_phone;
        }

        if ($res) {
            return ['code' => true, 'msg' => '登记成功(1至2分钟后将自动开通).' . $msg];
        } else {
            return ['code' => false, 'msg' => '失败.' . $msg];
        }

    }
}
