<?php


namespace App\Models;


use Illuminate\Foundation\Auth\User as Authenticatable;
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
}
