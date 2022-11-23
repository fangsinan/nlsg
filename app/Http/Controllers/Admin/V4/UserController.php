<?php

namespace App\Http\Controllers\Admin\V4;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ControllerBackend;
use App\Models\CashData;
use App\Models\Comment;
use App\Models\User;
use App\Models\WorksCategory;
use App\Models\WorksCategoryRelation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use JPush;
use App\Models\Task;

class UserController extends ControllerBackend
{

    public function index(Request $request)
    {
        $phone = $request->get('phone');
//        $nickname = $request->get('nickname');
        $sex = $request->get('sex');
        $level = $request->get('level');
        $is_author = $request->get('is_author');
        $start = $request->get('start');
        $end = $request->get('end');
        $query = User::query()->where('phone','like','1%')->where('ref', '=', '0')->where('is_robot', '=', '0')
            ->when($phone, function ($query) use ($phone) {
                $query->where('phone', 'like', $phone . '%');
            })
//            ->when($nickname, function ($query) use ($nickname) {
//               $query->where('nickname', 'like', '%' . $nickname . '%');
//            })
            ->when(! is_null($sex), function ($query) use ($sex) {
                $query->where('sex', $sex);
            })
            ->when(! is_null($level), function ($query) use ($level) {
                $query->where('level', $level);
            })
            ->when(! is_null($is_author), function ($query) use ($is_author) {
                $query->where('is_author', $is_author);
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
            ->when($idcard, function ($query) use ($idcard) {
                $query->where('idcard', 'like', '%'.$idcard.'%');
            })
            ->when(! is_null($status), function ($query) use ($status) {
                $query->where('is_pass', $status);
            });

        $lists = $query
            ->select('id', 'user_id', 'truename', 'idcard_cover', 'reason','zfb_account', 'idcard', 'created_at', 'is_pass')
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
        $list = CashData::where('id', $id)->first();

        if ($type == 1) {
            CashData::where('id', $id)->update(['is_pass' => 1]);

            //审核通过
            //Task::send(9, $list->user_id);
        } elseif ($type == 2) {
            CashData::where('id', $id)->update(['is_pass' => 2, 'reason' => $reason]);
            //审核没有通过
            //Task::send(10, $list->user_id);

        }

        return success();
    }

    public function  intro(Request $request)
    {
        $id = $request->get('id');
        $user = User::select('intro')->where('id', $id)->first();
        if ($user){
            $intro = $user->intro ?? '';
            return  success($intro);
        }
    }
}
