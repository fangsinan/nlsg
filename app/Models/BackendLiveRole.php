<?php


namespace App\Models;
use Illuminate\Support\Facades\DB;

class BackendLiveRole extends Base
{
    protected $table = 'nlsg_backend_live_role';

    public function getDataUserId($phone){
        $list_1 =  DB::table('nlsg_backend_live_role as b')
            ->join('nlsg_user as u','b.son','=','u.phone')
            ->where('b.parent','=',$phone)
            ->where('b.status','=',1)
            ->pluck('u.id')
            ->toArray();

        $list_2 = User::where('phone','=',$phone)
            ->pluck('id')
            ->toArray();

        return array_merge($list_1,$list_2);
    }

}
