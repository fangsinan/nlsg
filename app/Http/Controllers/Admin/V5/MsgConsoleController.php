<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\MsgConsoleServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MsgConsoleController extends ControllerBackend
{
    //推送任务列表
    public function jobList(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->jobList($request->input(), $this->user)
        );
    }

    //创建推送任务
    public function createJob(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->createJob($request->input(), $this->user)
        );
    }

    //任务状态修改
    public function jobStatus(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->jobStatus($request->input(), $this->user)
        );
    }

    //人工推送,打开指定页面的类型列表
    public function jPushMsgTypeList(): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->jPushMsgTypeList()
        );
    }

    //消息模板列表
    public function templateList(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->templateList($request->input(), $this->user)
        );
    }

    //消息模板状态修改
    public function templateStatus(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->templateStatus($request->input(), $this->user)
        );
    }

    //创建模板
    public function createTemplate(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->createTemplate($request->input(), $this->user)
        );
    }

    //消息类型列表
    public function msgTypeList(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->msgTypeList()
        );
    }

    //todo msg relation type的搜索配置
    public function msgRelationTypeSearchData(Request $request): JsonResponse
    {
        return $this->getRes(
            (new MsgConsoleServers())->msgRelationTypeSearchData($request->input())
        );
    }

}
