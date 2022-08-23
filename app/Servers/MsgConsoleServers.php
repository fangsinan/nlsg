<?php


namespace App\Servers;


use App\Models\Message\MessageType;

class MsgConsoleServers
{

    //推送任务列表
    public function jobList($params, $admin)
    {
        return [__LINE__];
    }

    //创建推送任务
    public function createJob($params, $admin)
    {
        return [__LINE__];
    }

    //消息模板列表
    public function templateList($params, $admin)
    {
        return [__LINE__];
    }

    //创建模板
    public function createTemplate($params, $admin)
    {
        return [__LINE__];
    }


    public function msgTypeList()
    {
        return MessageType::query()
            ->where('pid', '=', 0)
            ->with(['childList:id,title,pid'])
            ->select(['id', 'title', 'pid'])
            ->get();
    }
}
