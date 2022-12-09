<?php


namespace App\Servers\V5;


class HelperService
{

    /**
     * 获取查询返回字段,配合model中$fillable字段使用,hide是隐藏fillable的字段,show是fillable中没配置的字段
     * save等操作不更新的字段不要写入fillable中
     * @param $model
     * @param array $hide
     * @param string[] $show
     * @return array|string
     */
    static public function setSelect($model, array $hide = [], array $show = [])
    {
        $model    = new $model();
        $fillable = $model->getFillable();
        if (empty($fillable)) {
            return '*';
        }
        $fillable = array_merge(['id'], $show, $fillable);

        return array_unique(array_diff($fillable, $hide));
    }

    /**
     * ORM查询条件配置
     * @param $query
     * @param $params
     * @param $field
     * [
     *  'field'=>'',字段名称
     *  'operator'=>'',操作符,目前可选值：=,>,<,>=,<=,like,not like,in,not in
     *  'like_type'=>'',如果是like查询,可以设置left,right,both.默认both
     *  'model'=>'',如果是子查询,可以设置子查询的字段名称
     *  'alias'=>'',字段别名,优先使用
     *  'can_zero'=>'',0是否可以作为值查询
     *  'can_empty'=>''空置是否可以作为值查询
     * ]
     */
    static public function queryWhen(&$query, $params, $field)
    {
        foreach ($field as $v) {
            $temp_v     = $params[$v['field']] ?? null;
            $can_zero   = $v['can_zero'] ?? false;
            $can_empty  = $v['can_empty'] ?? false;
            $operator   = $v['operator'] ?? '=';
            $like_type  = $v['like_type'] ?? 'both';
            $field_name = $v['alias'] ?? $v['field'];

            if ($temp_v === null || $temp_v === '') {
                continue;
            }

            if (($temp_v === 0 || $temp_v === '0') && $can_zero === false) {
                continue;
            }

            if ($temp_v === '' && $can_empty === false) {
                continue;
            }

            if (empty($v['model'] ?? '')) {
                self::setWhere($query, $operator, $field_name, $temp_v, $like_type);
            } else {
                $query->whereHas($v['model'], function ($q) use ($operator, $temp_v, $v, $like_type, $field_name) {
                    self::setWhere($q, $operator, $field_name, $temp_v, $like_type);
                });
            }
        }
    }

    static public function setOrderBy(&$query, $str = '')
    {
        if (!empty($str)) {
            $cut_index = strrpos($str, '_');
            if ($cut_index !== false) {
                $field = substr($str, 0, $cut_index);
                $order = substr($str, $cut_index + 1);
                $query->orderBy($field, $order);
            }
        }
        $query->orderBy('id', 'desc');
    }

    static private function setWhere(&$q, $operator, $field_name, $value, $like_type)
    {
        if ($operator === 'like' || $operator === 'not like') {
            $q->where($field_name, $operator, self::likeValueString($value, $like_type));
        } elseif ($operator === 'in' || $operator === 'not in') {
            if (is_string($value)) {
                $value = explode(',', $value);
            }
            if ($operator === 'in') {
                $q->whereIn($field_name, $value);
            } else {
                $q->whereNotIn($field_name, $value);
            }
        } else {
            $q->where($field_name, $operator, $value);
        }
    }

    static private function likeValueString($v, $t): string
    {
        return ($t === 'right' ? '' : '%') . $v . ($t === 'left' ? '' : '%');
    }

    static function toChineseNum($num): string
    {
        $char    = array("零", "一", "二", "三", "四", "五", "六", "七", "八", "九");
        $dw      = array("", "十", "百", "千", "万", "亿", "兆");
        $res     = "";
        $proZero = false;
        for ($i = 0; $i < strlen($num); $i++) {
            if ($i > 0) $temp = (int)(($num % pow(10, $i + 1)) / pow(10, $i)); else $temp = (int)($num % pow(10, 1));
            if ($proZero == true && $temp == 0) continue;
            if ($temp == 0) $proZero = true; else $proZero = false;
            if ($proZero) {
                if ($res == "") continue;
                $res = $char[$temp] . $res;
            } else $res = $char[$temp] . $dw[$i] . $res;
        }
        if ($res == "一十") $res = "十";

        if (mb_strlen($res) === 3 && mb_substr($res, 0, 2) === '一十') {
            $res = mb_substr($res, 1);
        }

        return $res;
    }
}
