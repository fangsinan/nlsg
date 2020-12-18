<?php


namespace App\Servers;


use App\Models\ConfigModel;
use Illuminate\Support\Facades\DB;

class ConfigServers
{
    public function mallKeywords($params)
    {
        $keywords = ConfigModel::getData(21, 1);
        $keywords = explode(',', $keywords);

        $hot_words = ConfigModel::getData(20, 1);
        $hot_words = json_decode($hot_words, true);

        return ['keywords' => $keywords, 'hot_words' => $hot_words];
    }

    public function editMallKeywords($params)
    {
        $keywords = $params['keywords'] ?? [];
        $hot_words = $params['hot_words'] ?? [];

        DB::beginTransaction();
        $res_flag = true;

        if (!empty($keywords) && is_array($keywords)) {
            $res = ConfigModel::whereId(21)->update([
                'value' => implode(',',$keywords)
            ]);
            if ($res === false) {
                $res_flag = false;
            }
        }

        if (!empty($hot_words) && is_array($hot_words)) {
            $res = ConfigModel::whereId(20)->update([
                'value' => json_encode($hot_words,JSON_UNESCAPED_UNICODE)
            ]);
            if ($res === false) {
                $res_flag = false;
            }
        }

        if ($res_flag === false) {
            DB::rollBack();
            return ['code' => false, 'msg' => '失败'];
        } else {
            DB::commit();
            return ['code' => true, 'msg' => '成功'];
        }


    }

}
