<?php


namespace App\Servers\V5;


class CsvHelper
{
    private $fp;

    public function __construct(array $title, string $file_name = '')
    {
        if ($file_name) {
            $file_name = trim($file_name, '.csv') . '.csv';
        } else {
            $file_name = date('Y-m-d H:i') . '-' . rand(1000, 9999) . '.csv';
        }

        header('Content-Description: File Transfer');
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="' . $file_name . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Access-Control-Allow-Origin: *");
        $this->fp = fopen('php://output', 'a');
        mb_convert_variables('GBK', 'UTF-8', $title);
        fputcsv($this->fp, $title);
    }

    public function insert(array $data)
    {
        foreach ($data as $v) {
            foreach ($v as &$vv) {
                $vv .= "\t";
            }
            mb_convert_variables('GBK', 'UTF-8', $v);
            fputcsv($this->fp, $v);
            ob_flush();
            flush();
        }
    }

    public function end()
    {
        fclose($this->fp);
        exit();
    }

}
