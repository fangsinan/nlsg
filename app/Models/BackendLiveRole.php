<?php


namespace App\Models;
use Illuminate\Support\Facades\DB;

class BackendLiveRole extends Base
{
    protected $table = 'nlsg_backend_live_role';

    public function getDataUserId($phone){
        return DB::table('nlsg_backend_live_role as b')
            ->join('nlsg_user as u','b.son','=','u.phone')
            ->where('b.parent','=',$phone)
            ->pluck('u.id')
            ->toArray();
    }

}
