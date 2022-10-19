<?php


namespace App\Http\Controllers\Admin\V5;


use App\Http\Controllers\ControllerBackend;
use App\Servers\V5\FeedbackServers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends ControllerBackend
{
    public function list(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->list($request->input(), $this->user));
    }

    //批量
    public function changeStatus(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->changeStatus($request->input(), $this->user));
    }

    //批量去回复
    public function toReply(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->toReply($request->input(), $this->user));
    }

    public function templateList(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->templateList($request->input(), $this->user));
    }

    public function templateCreate(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->templateCreate($request->input(), $this->user));
    }

    public function templateChangeStatus(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->templateChangeStatus($request->input(), $this->user));
    }

    //帮助与反馈的类型列表
    public function typeList(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->typeList($request->input(), $this->user));
    }

    //添加类型
    public function typeCreate(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->typeCreate($request->input(), $this->user));
    }

    //类型状态修改
    public function typeChangeStatus(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->typeChangeStatus($request->input(), $this->user));
    }

    //帮助列表
    public function helpList(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->helpList($request->input(), $this->user));
    }

    //创建帮助
    public function helpCreate(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->helpCreate($request->input(), $this->user));
    }

    //帮助状态修改
    public function helpChangeStatus(Request $request): JsonResponse
    {
        return $this->getRes((new FeedbackServers())->helpChangeStatus($request->input(), $this->user));
    }

}
