<?php


namespace App\Http\Controllers\Api\V4;

use App\Models\LiveConsole;
use Illuminate\Http\Request;

class LiveConsoleController
{
    public function add(Request $request)
    {
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->add($params, $this->user['id']);
        return $this->success($data);
    }

    public function checkHelper(Request $request){
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->checkHelper($params, $this->user['id']);
        return $this->success($data);
    }

    public function changeStatus(Request $request){
        $params = $request->input();
        $model = new LiveConsole();
        $data = $model->changeStatus($params, $this->user['id']);
        return $this->success($data);
    }
}
