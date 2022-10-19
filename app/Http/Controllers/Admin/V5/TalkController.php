<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\TalkServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TalkController extends ControllerBackend
{
    //会话列表
    public function list(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->list($request->input(), $this->user));
    }

    //会话状态修改
    public function changeStatus(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->changeStatus($request->input(), $this->user));
    }

    //todo 会话备注
    public function remarkCreate(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->remarkCreate($request->input(), $this->user));
    }

    //todo 备注列表
    public function remarkList(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->remarkList($request->input(), $this->user));
    }

    //todo 聊天信息列表
    public function talkList(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->talkList($request->input(), $this->user));
    }

    //todo 解决当前对话
    public function finish(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->finish($request->input(), $this->user));
    }


    //todo 快捷回复列表(公共,个人)
    public function templateList(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->templateList($request->input(), $this->user));
    }

    //todo 添加快捷回复
    public function templateListCreate(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->templateListCreate($request->input(), $this->user));
    }

    //todo 快捷回复状态修改
    public function templateListChangeStatus(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->templateListChangeStatus($request->input(), $this->user));
    }

}
