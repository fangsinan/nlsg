<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class Base extends Model {

    protected function serializeDate(DateTimeInterface $date) {
        return $date->format('Y-m-d H:i:s');
    }

    protected function getSqlBegin() {
        DB::connection()->enableQueryLog();
    }

    protected function getSql() {
        dd(DB::getQueryLog());
    }

    protected function emptyA2C($data) {
//        dd([rand(),$data->isEmpty()]);
        if (is_object($data)) {
            if ($data->isEmpty()) {
                return new class {};
            } else {
                return $data;
            }
        } else {
            if (empty($data)) {
                return new class {};
            } else {
                return $data;
            }
        }
    }

}
