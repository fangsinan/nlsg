<?php


namespace App\Models;

class WorksInfo extends Base
{
    protected $table = 'nlsg_works_info';
    public $timestamps = false;



    public function getDateFormat()
    {
        return time();
    }

    // $type  1 单课程  2 多课程
    public function getInfo($works_id,$is_sub=0,$user_id=0,$type=1,$order='asc',$page_per_page=50,$page=0,$size=0){
        $where = ['status'=>4];
        if($type == 1){
            $where['pid'] = $works_id;
        }else if($type == 2){
            $where['outline_id'] = $works_id;
        }
        $query = WorksInfo::select([
            'id','type','title','section','introduce','url','callback_url1','callback_url1', 'callback_url2',
            'callback_url3','view_num','duration','free_trial'
        ])->where($where)->orderBy('id',$order);
            //->paginate($page_per_page)->toArray();

        if ($page && $size){
            $works_data = $query->limit($size)->offset(($page - 1) * $size)->get()->toArray();
        }else{
            $works_data = $query->limit($page_per_page)->get()->toArray();
        }

        //$works_data = $works_data_size['data'];
        foreach ($works_data as $key=>$val){
            //处理url  关注或试听
            $works_data[$key]['href_url'] = '';
            if( $is_sub == 1 || $val['free_trial'] == 1 ){
                $works_data[$key]['href_url'] = $works_data[$key]['url'];

            }else{
                unset($works_data[$key]['callback_url3']);
                unset($works_data[$key]['callback_url2']);
                unset($works_data[$key]['callback_url1']);
            }
            unset($works_data[$key]['url']);


            $works_data[$key]['time_leng'] = 0;
            $works_data[$key]['time_number'] = 0;
            if($user_id){
                //单章节 学习记录 百分比
                $his_data = History::select('time_leng','time_number')->where([
                    //'relation_type' => 2,
                    'info_id'          => $val['id'],
                    'user_id'       => $user_id,
                    'is_del'        => 0,
                ])->orderBy('updated_at','desc')->first();
                if($his_data){
                    $works_data[$key]['time_leng'] = $his_data->time_leng;
                    $works_data[$key]['time_number'] = $his_data->time_number;
                }
            }
        }


        return $works_data;
    }

    static function GetWorksUrl($WorkArr){
        if(!empty($WorkArr['callback_url3'])){
            return $WorkArr['callback_url3'];
        }
        if(!empty($WorkArr['callback_url2'])){
            return $WorkArr['callback_url2'];
        }
        if(!empty($WorkArr['callback_url1'])){
            return $WorkArr['callback_url1'];
        }
        return $WorkArr['url'];
    }

    public  function  works()
    {
        return  $this->belongsTo(Works::class, 'pid', 'id');
    }

}
