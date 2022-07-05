<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Servers\SubHelperServers;
use Illuminate\Http\Request;

class SubHelperController  extends ControllerBackend
{
    public function objList(){
        $servers = new SubHelperServers();
        $data = $servers->ojbList();
        return $this->getRes($data);
    }


    /**
     * 课程讲座列表
     * @api {post} /api/admin_v4/sub_helper/works_ojb_list 课程讲座列表
     * @apiVersion 4.0.0
     * @apiName /api/admin_v4/sub_helper/works_ojb_list
     * @apiGroup  后台-商品评论
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/sub_helper/works_ojb_list
     * @apiDescription 课程讲座列表
     * @apiParamExample {json} Request-Example:
     * {
     * "id":474,
     * "type":2,
     * "list":[
     * {
     * "content":"好啊"
     * },
     * {
     * "content":"好啊11"
     * },
     * {
     * "content":"好啊11"
     * }
     * ]
     * }
     */
    public function worksObjList(){
        $servers = new SubHelperServers();
        $data = $servers->comObjList();
        return $this->getRes($data);
    }


    public function open(Request $request){
        $servers = new SubHelperServers();
        $type = (int)$request->input('type',0);
        if ($type === 7 && ($this->user['role_id'] ?? 0) !== 1){
            return $this->getRes(['code'=>false,'msg'=>'没有权限']);
        }
        $data = $servers->addOpenList($request->input(),$this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    public function close(Request $request){
        $servers = new SubHelperServers();
        $data = $servers->delSubList($request->input(),$this->user['id'] ?? 0);
        return $this->getRes($data);
    }

}
