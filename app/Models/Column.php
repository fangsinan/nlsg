<?php


namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;

class Column extends BaseModel
{
    protected $table = 'nlsg_column';
    public $timestamps = false;

    // 允许批量赋值
    protected  $fillable = ['name','user_id'];


    public function getDateFormat()
    {
        return time();
    }


    public function user()
    {
        return $this->hasOne('App\Models\User', 'user_id','id');
    }


    public function get($field){
        $email = DB::table('nlsg_column')
            ->where('status', 1)
            ->orderBy('sort', 'desc')
            ->get($field)
            ->map(function ($value) {
                return (array)$value;
            })->toArray();
        return $email;
    }











    //添加 返回id
    public function cityadd($data)
    {
        return $this->insertGetId($data);
    }
    //单条查找
    public function getfind($id)
    {
        if($this->where('id',$id)->first()){
            return $this->where('id',$id)->first()->toArray();
        }else{
            return [];
        }
    }
    //查询用户有几个uid,返回数量
    public function countCity($uid){
        if($this->where('uid',$uid)->first()){
            return $this->where('uid',$uid)->count();
        }else{
            return [];
        }
    }

    /**
     * 修改管理员信息
     * @param $id
     * @param $data
     * @return bool
     */
    public function upAdmin($id,$data)
    {
        if($this->find($id)){
            return $this->where('id',$id)->update($data);
        }else{
            return false;
        }
    }

    //加条件，时间
    //查询用户的认购的城数
    public function buy_num($uid){
        $startDate = date('Y-m-01', strtotime(date("Y-m-d")));
        $endDate = date('Y-m-d', strtotime("$startDate +1 month -1 day"));
        // 将日期转换为Unix时间戳
        $endDate=$endDate." 22:59:59";
        $startDateStr = strtotime($startDate);
        $endtDateStr = strtotime($endDate);
        return $this->where('uid',$uid)->where('buy_type',1)->whereBetween('create_time', array($startDateStr,$endtDateStr))->sum('buy_num');
    }
    /**
     * 根据id查找城池信息 只返回某个字段的值
     * @param $id
     * @return array
     */
    public function getCityName($id)
    {
        if($this->where('city_id',$id)->first()){
            return $this->where('city_id',$id)->lists('city_name')[0];
        }else{
            return [];
        }
    }


}