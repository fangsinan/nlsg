<?php


namespace App\Servers;


use App\Imports\UsersImport;
use App\Models\Column;
use App\Models\Live;
use App\Models\Works;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class SubHelperServers
{
    public function ojbList()
    {
        $column_list = Column::where('status', '=', 1)
            ->where('type', '=', 2)
            ->select('id', DB::raw('6 as type'), 'name as title')
            ->get();

        $works_list = Works::where('status', '=', 4)
            ->where('type', '=', 2)
            ->select('id', DB::raw('2 as type'), 'title')
            ->get();

        $live_list = Live::whereIn('status', [1, 4])
            ->where('is_finish', '=', 0)
            ->where('is_del', '=', 0)
//            ->where('is_test', '=', 0)
            ->select('id', DB::raw('3 as type'), 'title')
            ->get();

        $xly_list = Column::where('status', '=', 1)
            ->where('type', '=', 3)
            ->select('id', DB::raw('7 as type'), 'name as title')
            ->get();

        return [
            'column_list' => $column_list,
            'works_list'  => $works_list,
            'live_list'   => $live_list,
            'xly_list'    => $xly_list,
        ];
    }

    public function comObjList()
    {
        $column_list = Column::where('status', '=', 1)
            ->where('type', '=', 2)
            ->select('id', DB::raw('2 as type'), 'name as title')
            ->get();

        $works_list = Works::where('status', '=', 4)
            ->where('type', '=', 2)
            ->select('id', DB::raw('4 as type'), 'title')
            ->get();

        return [
            'column_list' => $column_list,
            'works_list'  => $works_list,
        ];
    }

    function getFile($url, $save_dir = '', $filename = '', $type = 0)
    {
        if (trim($url) === '') {
            return false;
        }
        if (trim($save_dir) === '') {
            $save_dir = './';
        }
        if (0 !== strrpos($save_dir, '/')) {
            $save_dir .= '/';
        }
        //创建保存目录
        if (!file_exists($save_dir) && !mkdir($save_dir, 0777, true) && !is_dir($save_dir)) {
            return false;
        }
        //获取远程文件所采用的方法
        if ($type) {
            $ch      = curl_init();
            $timeout = 5;
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
            $content = curl_exec($ch);
            curl_close($ch);
        } else {
            ob_start();
            readfile($url);
            $content = ob_get_contents();
            ob_end_clean();
        }
        $size = strlen($content);
        //文件大小
        $fp2 = @fopen($save_dir . $filename, 'a');
        fwrite($fp2, $content);
        fclose($fp2);
        unset($content, $url);
        return array(
            'file_name' => $filename,
            'save_path' => $save_dir . $filename
        );
    }

    public function addOpenList($params, $admin_id)
    {
        $id        = (int)($params['id'] ?? 0);
        $type      = (int)($params['type'] ?? 0);
        $is_file   = (int)($params['is_file'] ?? 0);
        $file_name = $params['file_name'] ?? '';
        $url       = $params['url'] ?? '';
//        if (!empty($url)){
//            $url = str_replace('https://','http://',$url);
//        }

        if (empty($id) || empty($type)) {
            return ['code' => false, 'msg' => '参数错误'];
        }

        if ($type !== 3 && $is_file === 1) {
            $is_file = 0;
        }

        if ($is_file === 1) {
            if ($type !== 3) {
                return ['code' => false, 'msg' => '文件必须是直播类型'];
            }
            if (empty($file_name)) {
                return ['code' => false, 'msg' => '文件名错误'];
            }
            if (empty($url)) {
                return ['code' => false, 'msg' => '文件地址错误'];
            }
            //$url = 'https://image.nlsgapp.com/1111group/20210818测试开通.xlsx';

            $file = 'shs' . time() . rand(1, 999) . '.xlsx';

            if (1) {
                $ch      = curl_init();
                $timeout = 10;
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);//在需要用户检测的网页里需要增加下面两行
                //curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
                //curl_setopt($ch, CURLOPT_USERPWD, US_NAME.”:”.US_PWD);
                $content = curl_exec($ch);
            } else {
                ob_start();
                readfile($url);
                $content = ob_get_contents();
                ob_end_clean();
            }

            if (empty($content)) {
                return ['code' => false, 'msg' => '文件数据错误'];
            }

            Storage::put($file, $content);

            $check_file = Storage::exists($file);

            if (!$check_file) {
                return ['code' => false, 'msg' => '文件不存在'];
            }

            $excel_data = Excel::toArray(new UsersImport, base_path() . '/storage/app/' . $file);

            Storage::delete($file);
            $AliUploadServer = new AliUploadServers();
            $AliUploadServer->DelOss($file_name);

            $excel_data = $excel_data[0] ?? [];
            if (empty($excel_data)) {
                return ['code' => false, 'msg' => '数据错误:表1'];
            }

            $title = array_shift($excel_data);

            if ([$title[0], $title[1], $title[2]] != ["开通账号", "推荐账号", "渠道名称"]) {
                return ['code' => false, 'msg' => '表格结构错误("开通账号","推荐账号","渠道名称")'];
            }

            $error_phone = [];
            $add_data    = [];

            foreach ($excel_data as $v) {
                $phone   = preg_replace('/[^0-9]/i', ',', $v[0]);
                $t_phone = preg_replace('/[^0-9]/i', ',', $v[1]);
                if (strlen($phone) !== 11) {
                    $error_phone[] = $phone;
                    continue;
                }
                if (strlen($t_phone) !== 11) {
                    $error_phone[] = $t_phone;
                    continue;
                }
                $temp_add_data                  = [];
                $temp_add_data['phone']         = $phone;
                $temp_add_data['twitter_phone'] = $t_phone;
                $temp_add_data['flag_name']     = trim($v[2] ?? '');
                $temp_add_data['works_type']    = $type;
                $temp_add_data['works_id']      = $id;
                $temp_add_data['status']        = 1;

                if ($type == 2 && $id == 404) {
                    $temp_add_data['is_sendsms'] = 1;
                } else {
                    $temp_add_data['is_sendsms'] = 0;
                }

                $temp_add_data['admin_id'] = $admin_id;
                $add_data[]                = $temp_add_data;
            }

            if (!empty($add_data)) {
                $res = DB::table('works_list_of_sub')
                    ->insert($add_data);
            } else {
                $res = true;
            }
        } else {
            $phone = $params['phone'] ?? '';
            $phone = preg_replace('/[^0-9]/i', ',', $phone);
            $phone = explode(',', $phone);
            if (empty($phone)) {
                return ['code' => false, 'msg' => '手机号信息错误'];
            }

            $error_phone = [];
            $add_data    = [];
            foreach ($phone as $v) {
                if (strlen($v) !== 11) {
                    $error_phone[] = $v;
                    continue;
                }
                $temp_add_data               = [];
                $temp_add_data['phone']      = $v;
                $temp_add_data['works_type'] = $type;
                $temp_add_data['works_id']   = $id;
                $temp_add_data['status']     = 1;

                if ($type === 2 && $id === 404) {
                    $temp_add_data['is_sendsms'] = 1;
                } else {
                    $temp_add_data['is_sendsms'] = 0;
                }

                $temp_add_data['admin_id'] = $admin_id;
                $add_data[]                = $temp_add_data;
            }

            if (!empty($add_data)) {
                $res = DB::table('works_list_of_sub')
                    ->insert($add_data);
            } else {
                $res = true;
            }
        }

        $error_phone = implode(',', $error_phone);
        $msg         = '';
        if (!empty($error_phone)) {
            $msg = '无效号码:' . $error_phone;
        }

        if ($res) {
            return ['code' => true, 'msg' => '登记成功(1至2分钟后将自动开通).' . $msg];
        }

        return ['code' => false, 'msg' => '失败.' . $msg];
    }

    public function delSubList($params, $admin_id): array
    {
        $id    = (int)($params['id'] ?? 0);
        $type  = (int)($params['type'] ?? 0);
        $phone = $params['phone'] ?? '';
        $phone = preg_replace('/[^0-9]/i', ',', $phone);
        $phone = explode(',', $phone);
        if (empty($phone)) {
            return ['code' => false, 'msg' => '手机号信息错误'];
        }

        $error_phone = [];
        $add_data    = [];
        foreach ($phone as $v) {
            if (strlen($v) !== 11) {
                $error_phone[] = $v;
                continue;
            }
            $temp_add_data               = [];
            $temp_add_data['phone']      = $v;
            $temp_add_data['works_type'] = $type;
            $temp_add_data['works_id']   = $id;
            $temp_add_data['status']     = 1;
            $temp_add_data['admin_id']   = $admin_id;
            $add_data[]                  = $temp_add_data;
        }

        if (!empty($add_data)) {
            $res = DB::table('works_list_of_del_sub')
                ->insert($add_data);
        } else {
            $res = true;
        }
        $error_phone = implode(',', $error_phone);
        $msg         = '';
        if (!empty($error_phone)) {
            $msg = '无效号码:' . $error_phone;
        }

        if ($res) {
            return ['code' => true, 'msg' => '登记成功(1至2分钟后将自动取消).' . $msg];
        }

        return ['code' => false, 'msg' => '失败.' . $msg];
    }
}
