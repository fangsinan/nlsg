<?php


namespace App\Http\Controllers\Api\V4;


use App\Http\Controllers\Controller;
use App\Models\ImGroupModel;
use App\Models\ImGroupUserModel;

class ImGroupController extends Controller
{


    public function addGroup($params){

        if (empty($params)){
            return ["ActionStatus" => "OK","ErrorInfo" => "","ErrorCode" => 0 ];
        }

        $group_add = [
            'group_id'      => $params['GroupId'],
            'operator_account'        => $params['Operator_Account'],
            'owner_account'           => $params['Owner_Account'],
            'type'           => $params['Type'],
            'name'           => $params['Name'],

        ];
        $group_add_id = ImGroupModel::insert($group_add);

        $adds = [];
        foreach ($params['MemberList'] as $key=>$val){

            $add = [
                'group_id' => $group_add_id,
                'group_account' => $val['Member_Account'],
                'group_role' => 0,
            ];

            $adds[] = $add;

        }
        if(!empty($adds)){
            ImGroupUserModel::insert($adds);
        }




    }
}