<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Models\CashData;
use App\Models\Comment;
use App\Models\User;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $id = $request->get('id');
        $nickname = $request->get('nickname');
        $sex = $request->get('sex');
        $level = $request->get('level');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = User::when($id, function ($query) use ($id) {
            $query->where('id', $id);
        })
            ->when(! is_null($sex), function ($query) use ($sex) {
                $query->where('sex', $sex);
            })
            ->when(! is_null($level), function ($query) use ($level) {
                $query->where('level', $level);
            })
            ->when($start && $end, function ($query) use ($start, $end) {
                $query->whereBetween('created_at', [
                    Carbon::parse($start)->startOfDay()->toDateTimeString(),
                    Carbon::parse($end)->endOfDay()->toDateTimeString(),
                ]);
            });

        $lists = $query->select('id', 'nickname', 'phone', 'sex', 'level', 'province', 'city')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }

    /**
     *  提现审核
     * @return \Illuminate\Http\JsonResponse
     */

    public function apply(Request $request)
    {
        $idcard = $request->get('idcard');
        $name = $request->get('name');
        $idcard = $request->get('idcard');
        $status = $request->get('status');
        $query = CashData::with('user:id,nickname')
            ->when($name, function ($query) use ($name) {
                $query->where('truename', 'like', '%'.$name.'%');
            })
            ->when($phone, function ($query) use ($phone) {
                $query->where('phone', 'like', '%'.$phone.'%');
            })
            ->when($idcard, function ($query) use ($idcard) {
                $query->where('idcard', 'like', '%'.$idcard.'%');
            })
            ->when(! is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            });

        $lists = $query
            ->select('id', 'user_id', 'truename', 'idcard_cover', 'zfb_account', 'idcard', 'created_at', 'is_pass')
            ->orderBy('created_at', 'desc')
            ->paginate(10)
            ->toArray();
        return success($lists);
    }


    public function pass(Request $request)
    {
        $type = $request->get('type') ?? 1;
        $id = $request->get('id');
        $reason = $request->get('reason');
        if ($type == 1) {
            CashData::where('id', $id)->update(['is_pass' => 1]);
        } elseif ($type == 2) {
            CashData::where('id', $id)->update(['is_pass' => 2, 'reason' => $reason]);
        }
        return success();
    }
}
