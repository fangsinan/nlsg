<?php

namespace App\Servers\V5;

use App\Models\BackendUser;
use App\Models\ShortLink;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;

class ShortLinkServers
{

    //获取短链接管理员
    public function linkAdminList($params)
    {

        //获取管理员列表
        $AdminArr = ShortLink::query()->where('admin_id', '>', 0)->groupBy('admin_id')->pluck('admin_id')->toArray();
        if(empty($AdminArr)){
            return [];
        }
        $AdminInfo=BackendUser::query()->select(['id','username','user_remark'])->whereIn('id', $AdminArr)->get()->toArray();

        return $AdminInfo;

    }

    //获取短链接
    public function linkGet($params)
    {
        $id = (int)($params['id'] ?? 0);

        $info= ShortLink::query()->where('id', '=', $id)->select(['code'])->first();

        if(empty($info)){
            return ['code' => false, 'msg' => '无此信息'];
        }else{
            return 'https://a.nlsgapp.net/a/'.$info['code'];
        }
    }

    //获取短链接列表
    public function getList($params): LengthAwarePaginator
    {

        $size        = $params['size'] ?? 10;
        $order_by    = $params['order_by'] ?? 'id_desc';

        $name        = $params['name'] ?? '';
        $code        = $params['code'] ?? '';
        $admin_id    = $params['admin_id'] ?? 0;
        $status      = $params['status'] ?? 1;
        $begin_at = $params['begin_at'] ?? '';
        $end_at   = $params['end_at'] ?? '';

        $query = ShortLink::query()->select(['id', 'type', 'name', 'code', 'url', 'status','admin_id','created_at'])
            ->where('type', '=', 1) //type 1 只支持长期有效
           ;
        $query->with([
            'backendUser:id,username,user_remark',
        ]);

        if(!empty($name)){ //名称
            $query->where('name', 'like', '%' . $name . '%');
        }
        if(!empty($code)){ //编号
            $query->where('code', '=', $code);
        }
        if(!empty($admin_id)){ //管理员
            $query->where('admin_id', '=', $admin_id);
//            $query->wherehas('backendUser', function ($q) use ($phone) {
//                $q->where('username', 'like', '%' . $phone . '%');
//            });
        }
        if(!empty($status) && in_array($status,[1,2])){ //状态 1有效 2无效
            $query->where('status', '=', $status);
        }
        //创建时间
        if(!empty($begin_at) && !empty($end_at)) {
            $query->when($begin_at, function ($q, $begin_at) {
                $q->where('created_at', '>=', $begin_at);
            });
            $query->when($end_at, function ($q, $end_at) {
                $end_at = $end_at . ' 23:59:59';
                $q->where('created_at', '<=', $end_at);
            });
        }

        switch ($order_by) {
            case 'id_asc':
                $query->orderBy('id');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        return $query->paginate($size);

    }

    //查看短链接
    public function linkShow($params)
    {
        $id = (int)($params['id'] ?? 0);

        $query=ShortLink::query()->where('id', '=', $id);
        $query->with([
            'backendUser:id,username,user_remark',
        ]);
        $info=$query->first()->toArray();

        if(empty($info)){
            return ['code' => false, 'msg' => '无此信息'];
        }else{
            return $info;
        }
    }

    //添加编辑短链接
    public function LinkAddEdit($params, $admin): array
    {

        $flag=(int)($params['flag'] ?? 1); //1 添加 2 编辑
        if(!in_array($flag,[1,2])){
            return ['code' => false, 'msg' => '状态有误'];
        }
        $p                   = [];
        $p['name']         = $params['name'] ?? '';
        $p['url']       = $params['url'] ?? '';
        $p['status']       = (int)($params['status'] ?? 1);

        $validator = Validator::make($p, [
                'name'         => 'bail|required|string|max:100',
                'url'         => 'bail|required|string|max:255',
                'status'   => 'bail|required|integer|in:1,2',
            ]
        );

        if ($validator->fails()) {
            return ['code' => false, 'msg' => $validator->messages()->first()];
        }

        try {
            if ($flag == 1) {
                $p['admin_id'] = $admin['id'] ?? 0; //管理员id
                $p['code'] = $this->getUniqueCode();
                $p['created_at'] = date('Y-m-d H:i:s', time());

                $add_res = ShortLink::query()->create($p);
                if (!$add_res) {
                    return ['code' => false, 'msg' => '添加失败'];
                }

            } else {
                $id = (int)($params['id'] ?? 0);
                if (empty($id)) {
                    return ['code' => false, 'msg' => '编辑信息id为空'];
                }
                $LinkInfo = ShortLink::query()->where('id', $id)->select(['id'])->first();
                if (empty($LinkInfo)) {
                    return ['code' => false, 'msg' => '编辑信息id有误'];
                }
                $p['updated_at'] = date('Y-m-d H:i:s', time());

                $update_res = ShortLink::query()->where('id', $id)->update($p);
                if (!$update_res) {
                    return ['code' => false, 'msg' => '更新失败'];
                }
            }
        }catch (\Exception $e){
            return ['code' => false, 'msg' => $e->getMessage()];
        }

//        DB::beginTransaction();
//        DB::rollBack();
//        DB::commit();

        return ['code' => true, 'msg' => '更新成功'];
    }

    //获取唯一code
    public function getUniqueCode(){

        $while_flag = true;
        while ($while_flag) {
            $code=self::getRandomString(3);
            $LinkObj=ShortLink::query()->select(['id'])->where('code',$code)->first();
            if(empty($LinkObj)){
                $while_flag = false;
                break;
            }
        }

        return $code;
    }

    //生成随机字符串
    public function getRandomString($num)
    {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str='';
        $length=strlen($chars)-1;
        for ($i = 0; $i < $num; $i++){
            $str .= $chars[mt_rand(0, $length)];
        }
        return $str;
    }


}
