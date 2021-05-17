<?php


namespace App\Http\Controllers\Admin\V4;


use App\Http\Controllers\ControllerBackend;
use App\Models\BackendUser;
use App\Servers\RoleServers;
use Illuminate\Http\Request;


class RoleController extends ControllerBackend
{

    //后台账号  --列表 修改   (后台用户 列表 修改密码和角色)
    //角色管理  --列表 修改   (角色列表)
    //权限管理  --列表 修改   (菜单和接口 列表 添加或修改)
    //角色和权限关系管理 --列表 修改


    /**
     * 菜单和接口 列表 和查询角色已有权限
     * @api {get} /api/admin_v4/role/node_list 菜单和接口列表
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/role/node_list
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/role/node_list
     * @apiDescription 角色权限配置
     *
     * @apiParam {number} role_id 查看角色绑定了那些,传这个
     *
     * @apiSuccess {number} id id
     * @apiSuccess {number} pid 父级id
     * @apiSuccess {number} name 目录或接口名称
     * @apiSuccess {number} path 目录或接口地址
     * @apiSuccess {number} is_menu 1是api  2是目录
     * @apiSuccess {number} checked 当传role_id的时候,该值=1表示已选择该权限
     * @apiSuccess {string[]} menu 该目录的子目录
     * @apiSuccess {string[]} api 该目录下属接口
     * @apiSuccessExample {json} Request-Example:
     * {
     * "code": 200,
     * "msg": "成功",
     * "now": 1619406898,
     * "data": [
     * {
     * "id": 1,
     * "pid": 0,
     * "name": "首页",
     * "path": "#",
     * "is_menu": 2,
     * "status": 1,
     * "menu": [],
     * "api": []
     * },
     * {
     * "id": 2,
     * "pid": 0,
     * "name": "内容管理",
     * "path": "#",
     * "is_menu": 2,
     * "status": 1,
     * "menu": [
     * {
     * "id": 3,
     * "pid": 2,
     * "name": "专栏",
     * "path": "class/column",
     * "is_menu": 2,
     * "status": 1,
     * "menu": [],
     * "api": []
     * },
     * {
     * "id": 4,
     * "pid": 2,
     * "name": "讲座",
     * "path": "class/lecture",
     * "is_menu": 2,
     * "status": 1,
     * "menu": [],
     * "api": [
     * {
     * "id": 5,
     * "pid": 4,
     * "name": "讲座列表接口",
     * "path": "class/list",
     * "is_menu": 1,
     * "status": 1,
     * "menu": [],
     * "api": []
     * },
     * {
     * "id": 6,
     * "pid": 4,
     * "name": "讲座详情",
     * "path": "class/info",
     * "is_menu": 1,
     * "status": 1,
     * "menu": [],
     * "api": []
     * },
     * {
     * "id": 7,
     * "pid": 4,
     * "name": "讲座删除",
     * "path": "class/del",
     * "is_menu": 1,
     * "status": 1,
     * "menu": [],
     * "api": []
     * }
     * ]
     * }
     * ],
     * "api": []
     * }
     * ]
     * }
     */
    public function nodeList(Request $request)
    {
        $servers = new RoleServers();
        $data = $servers->nodeList($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 菜单和接口 添加或修改
     * @api {post} /api/admin_v4/role/node_list_create 添加修改接口或菜单
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/role/node_list_create
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/role/node_list_create
     * @apiDescription 角色权限配置
     *
     * @apiParam {number} id 编辑的时候使用
     * @apiParam {string} name 菜单或者接口名称
     * @apiParam {string} path 路径或地址
     * @apiParam {number} pid  父类id
     * @apiParam {number=1,2} is_menu 类型(1是接口 2是菜单)
     */
    public function nodeListCreate(Request $request)
    {
        $servers = new RoleServers();
        $data = $servers->nodeListCreate($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    public function roleSelectList(Request $request){
        $servers = new RoleServers();
        $data = $servers->roleSelectList($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 菜单和接口 删除和排序
     * @api {put} /api/admin_v4/role/node_list_status 菜单和接口 删除和排序
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/role/node_list_status
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/role/node_list_status
     * @apiDescription 角色权限配置
     *
     * @apiParam {number} id 编辑的时候使用
     * @apiParam {string=del,rank} flag 动作,删除或排序
     * @apiParam {number} rank 排序时传,1-99之间
     */
    public function nodeListStatus(Request $request)
    {
        $servers = new RoleServers();
        $data = $servers->nodeListStatus($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 后台用户 列表
     * @api {put} /api/admin_v4/admin_user/list 后台用户 列表
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/admin_user/list
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/admin_user/list
     * @apiDescription 角色权限配置
     *
     * @apiParam {string} username 账号
     * @apiParam {number} role_id 角色id
     *
     * @apiSuccess {number} id id
     * @apiSuccess {string} username 用户账号
     * @apiSuccess {string[]} role_info 用户角色
     * @apiSuccess {string} role_info.name 角色名称
     */
    public function adminList(Request $request)
    {
        $servers = new BackendUser();
        $data = $servers->list($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    public function adminCreate(Request $request){
        $servers = new BackendUser();
        $data = $servers->adminCreate($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 后台用户 修改密码和角色
     * @api {put} /api/admin_v4/admin_user/list_status 后台用户 修改密码和角色
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/admin_user/list_status
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/admin_user/list_status
     * @apiDescription 角色权限配置
     *
     * @apiParam {number} id
     * @apiParam {string=role,pwd} flag 动作(角色或密码)
     * @apiParam {number} role_id 角色id(修改角色时候需要)
     * @apiParam {string} pwd 密码(修改密码是需要)
     * @apiParam {string} re_pwd 确认密码(修改密码是需要)
     */
    public function adminListStatus(Request $request)
    {
        $servers = new BackendUser();
        $data = $servers->adminListStatus($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 角色 列表
     * @api {put} /api/admin_v4/role/role_list 角色 列表
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/role/role_list
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/role/role_list
     * @apiDescription 角色权限配置
     *
     * @apiSuccess {number} id id
     * @apiSuccess {string} name 名称
     * @apiSuccess {string[]} role 下属角色
     */
    public function roleList(Request $request)
    {
        $servers = new RoleServers();
        $data = $servers->roleList($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }


    /**
     * 角色和菜单接口的绑定
     * @api {put} /api/admin_v4/role/role_node_bind 角色和菜单接口的绑定
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/role/role_node_bind
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/role/role_node_bind
     * @apiDescription 角色权限配置
     *
     * @apiParam {number} role_id 角色id
     * @apiParam {string} node_id 模块的id,数组列表均可 1,2,3,4
     */
    public function roleNodeBind(Request $request){
        $servers = new RoleServers();
        $data = $servers->roleNodeBind($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }

    /**
     * 角色添加修改
     * @api {put} /api/admin_v4/role/create 角色添加修改
     * @apiVersion 1.0.0
     * @apiName /api/admin_v4/role/create
     * @apiGroup  后台-角色权限配置
     * @apiSampleRequest http://app.v4.api.nlsgapp.com/api/admin_v4/role/create
     * @apiDescription 角色权限配置
     *
     * @apiParam {number} [id] 角色id,编辑时候传
     * @apiParam {string} name 角色名称
     */
    public function roleCreate(Request $request){
        $servers = new RoleServers();
        $data = $servers->roleCreate($request->input(), $this->user['id'] ?? 0);
        return $this->getRes($data);
    }
}
