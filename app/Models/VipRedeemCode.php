<?php


namespace App\Models;

use Illuminate\Support\Facades\DB;

class VipRedeemCode extends Base
{
    protected $table = 'nlsg_vip_redeem_code';


    public function create($user, $params)
    {
        $num = $params['num'] ?? 0;
        if (empty($num)) {
            return ['code' => false, 'msg' => '数量错误'];
        }

        $statistics = VipRedeemAssign::statistics($user);
        if (empty($statistics['can_use'])) {
            return ['code' => false, 'msg' => '没有配额'];
        }
        if ($num > $statistics['can_use']) {
            return ['code' => false, 'msg' => '配额不足'];
        }
        if ($num > 9999) {
            return ['code' => false, 'msg' => '一次生成数量过多'];
        }

        $now_date = date('Y-m-d H:i:s');
        $begin_str = date('y') .
            str_pad(date('z'), 3, 0, STR_PAD_LEFT) .
            str_pad($user['id'], 7, 0, STR_PAD_LEFT) .
            str_pad(rand(1, 999), 3, 0, STR_PAD_LEFT);

        $create_group = $this->getCreateGroupKey();

        $code_data = [];
        for ($i = 0; $i < $num; $i++) {
            $temp_data = [];
            $temp_data['number'] = $begin_str . str_pad($i, 5, 0, STR_PAD_LEFT);
            $temp_data['create_group'] = $create_group;
            $code_data[] = $temp_data;
        }

        DB::beginTransaction();

        $add_code_res = DB::table('nlsg_vip_redeem_code')->insert($code_data);
        if (!$add_code_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试.'];
        }

        $update_code_res = DB::table('nlsg_vip_redeem_code')
            ->where('create_group', '=', $create_group)
            ->update([
                'type' => 1,
                'name' => '360幸福大使',
                'source' => 1,
                'created_at' => $now_date,
                'updated_at' => $now_date,
                'vip_id' => $user['new_vip']['vip_id']
            ]);

        if ($update_code_res === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试.'];
        }

        $code_list = self::where('create_group', '=', $create_group)->select(['id'])->get();
        $code_user_data = [];
        foreach ($code_list as $v) {
            $temp_data = [];
            $temp_data['redeem_code_id'] = $v['id'];
            $temp_data['user_id'] = $user['id'];
            $temp_data['parent_id'] = 0;
            $temp_data['path'] = $user['id'];
            $temp_data['vip_id'] = $user['new_vip']['vip_id'];
            $temp_data['status'] = 1;
            $temp_data['created_at'] = $temp_data['updated_at'] = $now_date;
            $code_user_data[] = $temp_data;
        }

        $add_code_user_res = DB::table('nlsg_vip_redeem_user')->insert($code_user_data);
        if (!$add_code_user_res) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败,请重试'];
        }

        DB::commit();
        return ['code' => true, 'msg' => '成功'];
    }

    public function getCreateGroupKey()
    {
        $str = self::random(10);
        $check = self::where('create_group', '=', $str)->select(['id'])->first();
        if ($check) {
            $this->getCreateGroupKey();
        } else {
            return $str;
        }
    }

    public static function random($length = 16)
    {
        $string = '';
        while (($len = strlen($string)) < $length) {
            $size = $length - $len;
            $bytes = random_bytes($size);
            $string .= substr(str_replace(['/', '+', '='], '', base64_encode($bytes)), 0, $size);
        }
        return $string;
    }
}
