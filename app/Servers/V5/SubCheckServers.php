<?php


namespace App\Servers\V5;


use App\Models\Subscribe;
use App\Servers\MsgConsoleServers;

class SubCheckServers
{
    public function relationTypeList(): array
    {
        return [
            1 => ['value' => '课程', 'key' => 1],
            2 => ['value' => '讲座', 'key' => 2],
            3 => ['value' => '直播', 'key' => 3],
            4 => ['value' => '训练营分期', 'key' => 4],
        ];
    }

    public function relationInfoList($type)
    {
        $mcs = new MsgConsoleServers();
        switch ((int)$type) {
            case 1:
                $res = $mcs->getWorksList();
                break;
            case 2:
                $res = $mcs->getColumnList();
                break;
            case 3:
                $res = $mcs->getLiveListByDate();
                break;
            case 4:
                $res = $mcs->getCampInfoList();
                break;
            default:
                $res = [];
        }

        return $res;

    }

    public function toCheck($params): array
    {
        $type        = (int)($params['type'] ?? 0);
        $relation_id = $params['relation_id'] ?? 0;
        $phone_list  = $params['phone_list'] ?? '';
        $now_date = date('Y-m-d H:i:s');

        if (empty($type) || empty($relation_id || empty($phone_list) ||in_array($type,[1,2,3,4]))) {
            return ['code' => false, 'msg' => '参数错误'.__LINE__];
        }

        $phone_list = preg_replace('/[^0-9]/i', ',', $phone_list);
        $phone_list = array_unique(array_filter(explode(',',$phone_list)));
        if (empty($phone_list)){
            return ['code' => false, 'msg' => '参数错误'.__LINE__];
        }

        $phone_list = array_chunk($phone_list,2);
        //2作品 3直播  6讲座  7训练营
        $not_sub_list = [];

        foreach ($phone_list as $v){

            $query = Subscribe::query()
                ->with([
                    'user:id,phone'
                ]);

            $query->where('status','=',1)
                ->where('is_del','=',0)
                ->whereHas('user',function($q)use($v){
                    $q->whereIn('phone',$v);
                });

            switch ($type){
                case 1:
                    $query->where('type','=',2)->where('end_time','>',$now_date);
                    break;
                case 2:
                    $query->where('type','=',6)->where('end_time','>',$now_date);
                    break;
                case 3:
                    $query->where('type','=',3);
                    break;
                case 4:
                    $query->where('type','=',7)->where('created_at','>','2022-06-01 00:00:00');
                    break;
            }

            $query->where('relation_id','=',$relation_id);
            $query->select(['id','user_id']);

            $temp_res = $query->get();

            if ($temp_res->isEmpty()){
                $not_sub_list = array_merge($not_sub_list,$v);
                continue;
            }

            $temp_v = array_flip($v);
            foreach ($temp_res as $tr_v){
                if (in_array($tr_v->user['phone'],$v)){
                    unset($temp_v[$tr_v->user['phone']]);
                }
            }
            $temp_v = array_flip($temp_v);
            $not_sub_list = array_merge($not_sub_list,$temp_v);
        }

        return $not_sub_list;

    }


}
