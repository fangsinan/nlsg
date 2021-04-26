<?php


namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Contracts\JWTSubject;

class BackendUser extends Authenticatable implements JWTSubject
{
    protected $table = 'nlsg_backend_user';

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function changePwd($user,$params){
        $password = strval($params['pwd']??'');
        $re_password = strval($params['re_pwd']??'');

        if (!empty($password) && $password === $re_password){
            $n_pwd =  bcrypt($password);
            $res = self::whereId($user['id'])->update(['password'=>$n_pwd]);
            if ($res === false){
                return ['code'=>false,'msg'=>'失败'];
            }else{
                return ['code'=>true,'msg'=>'成功'];
            }
        }else{
            return ['code'=>false,'msg'=>'密码不一致'];
        }
    }


    public function roleInfo()
    {
        return $this->hasOne(Role::class, 'id','role_id')
            ->where('status','=',1)
            ->select(['id', 'name', 'pid']);
    }

    public function list($params, $admin_id){
        $size = $params['size'] ?? 10;

        $query =  self::query()
            ->with(['roleInfo'])
            ->select(['id','username','role_id'])
            ->orderBy('id');

        if (!empty($params['username'] ??'')){
            $query->where('username','like','%'.trim($params['username']).'%');
        }

        if (!empty($params['role_id']??0)){
            $query->where('role','=',intval($params['role_id']));
        }

        return $query
            ->paginate($size);

    }
}
