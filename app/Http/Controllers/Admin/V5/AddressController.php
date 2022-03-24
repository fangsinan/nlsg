<?php

namespace App\Http\Controllers\Admin\V5;

use App\Http\Controllers\ControllerBackend;
use App\Models\MallAddress;
use Illuminate\Http\Request;

class AddressController extends ControllerBackend
{
    public function create(Request $request) {
        $params = $request->input();
        $user_id = $params['user_id'] ?? 0;
        if (empty($user_id)) {
            return $this->getRes([
                'code' => false,
                'msg'  => '用户id错误',
            ]);
        }
        $params = $request->input();
        $model  = new MallAddress();
        $data   = $model->create($params, $user_id);
        return $this->getRes($data);
    }
}
