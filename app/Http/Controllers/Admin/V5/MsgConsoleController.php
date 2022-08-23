<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\MsgConsoleServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MsgConsoleController extends ControllerBackend
{
    //todo 推送任务列表
    public function jobList(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->jobList($request->input(), $this->user)
        );
    }

    //todo 创建推送任务
    public function createJob(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->createJob($request->input(), $this->user)
        );
    }

    //todo 消息模板列表
    public function templateList(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->templateList($request->input(), $this->user)
        );
    }

    //todo 创建模板
    public function createTemplate(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->createTemplate($request->input(), $this->user)
        );
    }

    //todo 消息类型列表
    public function msgTypeList(){

    }


}
