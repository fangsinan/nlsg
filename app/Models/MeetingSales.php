<?php


namespace App\Models;


use App\Http\Controllers\Api\V4\CreatePosterController;

class MeetingSales extends Base
{

    protected $table = 'nlsg_meeting_sales';

    public function salesIndex($params, $user_id)
    {
        $check = self::where('user_id', '=', $user_id)
            ->where('status', '=', 1)
            ->select(['id','user_id','phone','nickname','qr_code'])
            ->first();

        if (empty($check)) {
            return ['code' => false, 'msg' => '没有权限'];
        }

        if (empty($check->qr_code)) {
            //生成二维码

            $QR_url = ConfigModel::getData(33) . '?time=' . time() . '&sales_id=' . $check->id;
            $cpModel = new CreatePosterController();
            $qr_data = $cpModel->createQRcode($QR_url, true, true, true);

            $qr_url = ConfigModel::base64Upload(102, $qr_data);

            if (empty($qr_url['url'] ?? '') || empty($qr_url['name'] ?? '')) {
                return ['code' => false, 'msg' => '创建二维码失败,请重试'];
            }

            $check->qr_code = $qr_url['url'] . $qr_url['name'];
            $check->save();
        }

        return $check;
    }

}
