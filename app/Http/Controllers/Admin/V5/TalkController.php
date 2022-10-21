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

    //备注添加
    public function remarkCreate(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->remarkCreate($request->input(), $this->user));
    }

    //备注列表
    public function remarkList(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->remarkList($request->input(), $this->user));
    }

    //todo 获取聊天定位坐标
    public function getMsgCoordinate(Request $request): JsonResponse{

    }

    //todo 聊天信息列表
    public function talkList(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->talkList($request->input(), $this->user));
    }

    //发送聊天消息
    public function talkListCreate(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->talkListCreate($request->input(), $this->user));
    }

    //解决当前对话
    public function finish(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->finish($request->input(), $this->user));
    }

    //用户列表
    public function talkUserList(Request $request): JsonResponse
    {
        return $this->getRes((new TalkServers())->talkUserList($request->input(), $this->user));
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
