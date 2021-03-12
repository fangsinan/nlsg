<?php


namespace App\Models;


use App\Http\Controllers\Api\V4\CreatePosterController;
use Illuminate\Support\Facades\DB;

class MeetingSales extends Base
{

    protected $table = 'nlsg_meeting_sales';

    public function salesIndex($params, $user_id)
    {
        $now_date = date('Y-m-d H:i:s');
        $check = self::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->select(['id', 'user_id', 'phone', 'nickname', 'qr_code'])
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => '没有权限'];
        }

//        if (empty($check->qr_code)) {
            //生成二维码

            $sales_id_list = self::where('user_id', '=', $user_id)
                ->where('status', '=', 1)
                ->where('qr_code','=','')
                ->select(['id'])
                ->get();

            foreach ($sales_id_list as $si) {
                $QR_url = ConfigModel::getData(33) . '?time=' . time() . '&sales_id=' . $si->id;
                $cpModel = new CreatePosterController();
                $qr_data = $cpModel->createQRcode($QR_url, true, true, true);
                $qr_url = ConfigModel::base64Upload(102, $qr_data);
                if (empty($qr_url['url'] ?? '') || empty($qr_url['name'] ?? '')) {
                    return ['code' => false, 'msg' => '创建二维码失败,请重试'];
                }
                $check_temp = self::where('id', '=', $si->id)->first();
                $check_temp->qr_code = $qr_url['url'] . $qr_url['name'];
                $check_temp->save();
            }

//        }

        $check->bind = MeetingSalesBind::where('sales_id', '=', $check->id)
            ->where('status', '=', 1)
            ->where('begin_at', '<=', $now_date)
            ->where('end_at', '>=', $now_date)
            ->select(['*', DB::raw('"" as headimg')])
            ->first();

        if (empty($check->bind)) {
            $check->bind = new class {
            };
        } else {
            $bind_user = User::where('id', '=', $check->bind->dealer_user_id)->select(['id', 'headimg'])->first();
            if (!empty($bind_user)) {
                $check->bind['headimg'] = $bind_user->headimg;
            }
        }

        return $check;
    }

}
