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
        $password = $params['pwd']??'';
        $re_password = $params['re_pwd']??'';

        if (!empty($password) && $password === $re_password){

        }else{
            return ['code'=>false,'msg'=>'验证码'];
        }
    }
}
