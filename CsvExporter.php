<?php
/**
 * Created by PhpStorm.
 * User: Nairoj
 * Date: 2019/4/17
 * Time: 15:30
 */

class CsvExporter
{
    private static $instance;

    private $filename = null;
    private $handler = null;

    public function __construct($filename = 'default')
    {
        $date = date("m-d");
        $dir = defined('CEFILE_PATH') ? CEFILE_PATH : 'cache_file/';
        if (1 != preg_match('/^\w+$/', $filename)) {
            $filename = 'no_regular_filename';
        }
        //win服务器 文件名不能有:
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
            $filename = '[' . date('H-i-s') . '] ' . $filename;
        else {
            $filename = '[' . date('H:i:s') . '] ' . $filename;
        }
        $this->filename = $dir . $date . '/' . $filename . '.csv';
        $path_parts = pathinfo($this->filename);
        $csv_folder = $path_parts["dirname"];
        if (!is_dir($csv_folder)) {
            mkdir($csv_folder, 0755, true);
        }
        $this->handler = fopen($this->filename, 'a+');
        fwrite($this->handler, chr(0xEF) . chr(0xBB) . chr(0xBF));//BOM
    }

    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function setTitle(array $title)
    {
        //title是一维数组
        if (count($title) != count($title, 1)) {
            $data = json_encode($title, true);
        }
        $handler = fopen($this->filename, 'c');
        fputcsv($this->handler, $data);
    }

    public function appendBody(array $data)
    {
        if (count($data) == count($data, 1)) {
            fputcsv($this->handler, $data);
        } else {
            foreach ($data as $item)
                fputcsv($this->handler, $item);
        }
    }

    public function exportCsv($filename)
    {
        header("Content-Type: text/csv");
        header("Content-Disposition:filename=" . $filename);
        readfile($this->filename);
    }

    public function getDataArray()
    {
        $data = [];
        $handler = fopen($this->filename, 'r');
        while (!feof($handler)) {
            $temp = fgetcsv($handler);
            if ($temp)
                $data[] = $temp;
        }
        fclose($handler);
        return $data;
    }

    public function __destruct()
    {
        fclose($this->handler);
    }
}