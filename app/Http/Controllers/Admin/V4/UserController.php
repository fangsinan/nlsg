<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\CashData;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     *  提现审核
     * @return \Illuminate\Http\JsonResponse
     */

    public function apply(Request $request)
    {
        $idcard = $request->get('idcard');
        $name   = $request->get('name');
        $phone  = $request->get('phone');
        $status = $request->get('status');
        $query = CashData::with('user:id,nickname')
                 ->when($name, function ($query) use ($name) {
                    $query->where('truename', 'like', '%' . $name . '%');
                 })
                 ->when($phone, function ($query) use ($phone) {
                   $query->where('phone', 'like', '%' . $phone . '%');
                 })
                 ->when($idcard, function ($query) use ($idcard) {
                     $query->where('idcard', 'like', '%' . $idcard . '%');
                 })
                 ->when(!is_null($status), function ($query) use ($status) {
                      $query->where('status', $status);
                 });

        $lists = $query
                ->select('id','user_id','truename','idcard_cover','zfb_account','phone','created_at','is_pass')
                ->orderBy('created_at','desc')
                ->paginate(10)
                ->toArray();
         return success($lists);
    }


    public function  pass(Request $request)
    {
        $type = $request->get('type') ??  1;
        $id   = $request->get('id');
        if ($type ==1) {
            CashData::where('id', $id)->update(['is_pass'=>1]);
        } elseif ($type ==2) {
            CashData::where('id', $id)->update(['is_pass'=>2]);
        }
        return success();
    }
}
