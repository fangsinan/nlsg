<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Auth extends Model
{
    /**
     * 权限检测
     * @param $input
     * @param $roleId
     * @return bool
     */
    public function authCheck($input, $roleId)
    {
        if (1 == $roleId) {
            return true;
        }

        $roleModel = new Role();
        $roleAuthNodeMap = $roleModel->getRoleAuthNodeMap($roleId);

        if (empty($roleAuthNodeMap)) {
            return false;
        }

        if ( ! isset($roleAuthNodeMap[$input])) {
            return false;
        }

        return true;
    }

}
