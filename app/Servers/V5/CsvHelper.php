<?php


namespace App\Servers\V5;


class CsvHelper
{
    private $fp;

    public function __construct(array $title,string $file_name = ''){
        if ($file_name){
            $file_name = trim($file_name,'.csv').'.csv';
        }else{
            $file_name = date('Y-m-d H:i') . '-' . rand(100, 999) . '.csv';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $this->fp = fopen('php://output', 'a');//打开output流
//        $fp = fopen('php://output', 'a');//打开output流
        mb_convert_variables('GBK', 'UTF-8', $columns);
        fputcsv($this->fp , $columns);     //将数据格式化为CSV格式并写入到output流中
    }

//    public function init(array $title,string $file_name = ''){
//        if ($file_name){
//            $file_name = trim($file_name,'.csv').'.csv';
//        }else{
//            $file_name = date('Y-m-d H:i') . '-' . rand(100, 999) . '.csv';
//        }
//
//        header('Content-Description: File Transfer');
//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment; filename="' . $file_name . '"');
//        header('Expires: 0');
//        header('Cache-Control: must-revalidate');
//        header('Pragma: public');
//        header("Access-Control-Allow-Origin: *");
//        $fp = fopen('php://output', 'a');//打开output流
//        mb_convert_variables('GBK', 'UTF-8', $columns);
//        fputcsv($fp, $columns);     //将数据格式化为CSV格式并写入到output流中
//    }


    public function insert(array $data){

    }

    public function end(){

    }

}
