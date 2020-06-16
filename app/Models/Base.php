<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class Base extends Model
{
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    protected function getSqlBegin(){ 
        DB::connection()->enableQueryLog();
    }
    
    protected function getSql(){
        dd(DB::getQueryLog());
    }
}
