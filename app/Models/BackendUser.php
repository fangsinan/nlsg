<?php


namespace App\Models;


use App\Servers\V5\BackendUserToken;
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

    public function changePwd($user, $params)
    {
        $password = strval($params['pwd'] ?? '');
        $re_password = strval($params['re_pwd'] ?? '');

        if (!empty($password) && $password === $re_password) {
            $passwordCheck = BackendUserToken::passwordCheck($password);
            if ($passwordCheck['code'] === false){
                return $passwordCheck;
            }

            $n_pwd = bcrypt($password);
            $res = self::whereId($user['id'])->update(['password' => $n_pwd]);
            if ($res === false) {
                return ['code' => false, 'msg' => '失败'];
            } else {
                return ['code' => true, 'msg' => '成功'];
            }
        } else {
            return ['code' => false, 'msg' => '密码不一致'];
        }
    }


    public function roleInfo()
    {
        return $this->hasOne(Role::class, 'id', 'role_id')
            ->where('status', '=', 1)
            ->select(['id', 'name', 'pid']);
    }

    public function list($params, $admin_id)
    {
        $size = $params['size'] ?? 10;

        $query = self::query()
            ->with(['roleInfo'])
            ->select(['id', 'username', 'role_id', 'live_role'])
            ->orderBy('id');

        if (!empty($params['username'] ?? '')) {
            $query->where('username', 'like', '%' . trim($params['username']) . '%');
        }

        if (!empty($params['role_id'] ?? 0)) {
            $query->where('role', '=', intval($params['role_id']));
        }

        $list = $query->paginate($size)->toArray();

        foreach ($list['data'] as &$v) {
            if ($v['live_role'] == 21) {
                $v['live_role_bind'] = DB::table('nlsg_backend_live_role')
                    ->where('son', '=', $v['username'])
                    ->pluck('parent')->toArray();
                $v['live_role_bind'] = implode(',', $v['live_role_bind']);
            } elseif ($v['live_role'] == 23) {
                $v['live_role_bind'] = DB::table('nlsg_backend_live_role')
                    ->where('parent', '=', $v['username'])
                    ->pluck('parent')->toArray();
                $v['live_role_bind'] = implode(',', $v['live_role_bind']);
            } else {
                $v['live_role_bind'] = '';
            }
        }

        return $list;
    }

    public function adminListStatus($params, $admin_id)
    {
        $flag = $params['flag'] ?? '';
        $id = $params['id'] ?? 0;
        if (empty($id)) {
            return ['code' => false, 'msg' => 'id错误'];
        }
        $check = self::where('id', '=', $id)->first();
        if (empty($check)) {
            return ['code' => false, 'msg' => 'id错误'];
        }

        switch ($flag) {
            case 'pwd':
                $password = strval($params['pwd'] ?? '');
                $re_password = strval($params['re_pwd'] ?? '');
                if (!empty($password) && $password === $re_password) {
                    $passwordCheck = BackendUserToken::passwordCheck($password);
                    if ($passwordCheck['code'] === false){
                        return $passwordCheck;
                    }
                    $check->password = bcrypt($password);
                } else {
                    return ['code' => false, 'msg' => '密码不一致'];
                }
                break;
            case 'role':
                $check->role_id = $params['role_id'] ?? 0;
                if (empty($check->role_id)) {
                    return ['code' => false, 'msg' => '角色不能为空'];
                }
                $check_role_id = Role::where('id', '=', $params['role_id'])->first();
                if (empty($check_role_id)) {
                    return ['code' => false, 'msg' => '角色不存在'];
                }
                break;
            case 'live_role':
                $live_role_id = $params['live_role_id'] ?? 0;
                if (!in_array($live_role_id, [21, 23, 0])) {
                    return ['code' => false, 'msg' => '直播角色错误'];
                }
                $lr_data = [];
                $bind_phone = $params['bind_phone'] ?? '';
                if (!is_array($bind_phone)) {
                    $bind_phone = explode(',', $bind_phone);
                }

                if ($live_role_id == 21) {
                    //如果是老师,可以指定校长
                    if (empty($bind_phone)) {
                        DB::table('nlsg_backend_live_role')
                            ->where('son', '=', $check->username)
                            ->delete();
                    } else {
                        foreach ($bind_phone as $v) {
                            $tmp_lr_data = [];
                            $tmp_lr_data['parent'] = $v;
                            $tmp_lr_data['son'] = $check->username;
                            $lr_data[] = $tmp_lr_data;
                        }
                    }
                } elseif ($live_role_id == 23) {
                    //如果是校长,可以指定老师
                    if (empty($bind_phone)) {
                        DB::table('nlsg_backend_live_role')
                            ->where('parent', '=', $check->username)
                            ->delete();
                    } else {
                        foreach ($bind_phone as $v) {
                            $tmp_lr_data = [];
                            $tmp_lr_data['parent'] = $check->username;
                            $tmp_lr_data['son'] = $v;
                            $lr_data[] = $tmp_lr_data;
                        }
                    }
                }

                if (!empty($lr_data)) {
                    DB::table('nlsg_backend_live_role')->insert($lr_data);
                }
                $check->live_role = $live_role_id;
                break;
            default:
                return ['code' => false, 'msg' => '修改类型错误'];
        }

        $res = $check->save();
        if ($res === false) {
            return ['code' => false, 'msg' => '失败'];
        } else {
            return ['code' => true, 'msg' => '成功'];
        }
    }

    public function adminCreate($params, $admin_id)
    {
        $id = $params['id'] ?? 0;
        $username = $params['username'] ?? 0;
        if (empty($username)) {
            return ['code' => false, 'msg' => '账号不能为空'];
        }
//        $role_id = $params['role_id'] ?? 0;
//        $password = strval($params['pwd'] ?? '');
//        $re_password = strval($params['re_pwd'] ?? '');
//        if (!empty($password) && $password === $re_password) {
//            $password = bcrypt($password);
//        } else {
//            return ['code' => false, 'msg' => '密码不一致'];
//        }

        if (empty($id)) {
            $um = new BackendUser();
            $check_username = BackendUser::where('username', '=', $username)->first();
            if ($check_username) {
                return ['code' => false, 'msg' => '手机号被占用'];
            }
        } else {
            $um = BackendUser::where('id', '=', $id)->first();
            if (empty($um)) {
                return ['code' => false, 'msg' => '用户不存在'];
            }
                if ($um->username != $username) {
                    $check_username = BackendUser::where('username', '=', $username)
                        ->where('id', '<>', $id)
                        ->first();
                    if ($check_username) {
                        return ['code' => false, 'msg' => '手机号被占用'];
                    }
                }

        }
        $um->username = $username;
        $res = $um->save();

        User::firstOrCreate([
            'phone' => $username
        ], [
            'nickname' => substr_replace($username, '****', 3, 4),
        ]);

        if ($res) {
            return ['code' => true, 'msg' => '成功'];
        }
            return ['code' => false, 'msg' => '失败'];

    }

}
