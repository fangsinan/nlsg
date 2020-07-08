<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;

/**
 * Description of ExpressCompany
 *
 * @author wangxh
 */
class ExpressCompany extends Base {

    protected $table = 'nlsg_express_company';

    public static function onlyGetName($id = 0) {
        if (!$id) {
            return '';
        }
        $data = self::find($id);
        return $data->name ?? '';
    }

}
