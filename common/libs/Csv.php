<?php
/**
 * Created by Zend Studio.
 * User: Administrator
 * Date: 2016/3/25
 * Time: 11:00
 *
 * 导入导出csv
 * 使用前提：已经在di中注入该Library
 * 使用方法：$this->csv->export(导出的数组类型数据,导出的文件名字);
 *           $this->csv->import(导入的文件路径,true/false（是否需要删除第一行内容）);
 * 返回结果：导出的结果将直接在浏览器进行下载，导入的结果把csv表结构变换成数组:array(array('a','b'),array('c','d'))
 */

namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class Csv extends LibraryBase
{
    public function __construct()
    {
        
    }
    
    /**
     * 导入csv表
     * @param unknown $filePath
     * @param string $power
     */
    public function import($filePath, $firstColumn = true)
    {
        if (empty($filePath)) {
            return false;
        }
        $result = array();
        $handle = fopen($filePath, 'r');
        if ($handle !== false) {
            while (($data = fgetcsv($handle, 0, ",")) !== false) {
                // gbk转utf-8
                foreach ($data as $key => $value) {
                    $data[$key] = iconv('gbk', 'utf-8', $value);
                }
                $result[] = $data;
            }
            fclose($handle);
        }
        if ($firstColumn) {
            unset($result[0]); // 删除首行
        }
        return array_values($result);
    }
    
    /**
     * 导出scv文件：参数必须是二维数组
     * @param array $data
     * @return boolean
     */
    public function export(array $data, $fileName = null)
    {
        if (empty($data)) {
            return false;
        }
        if (empty($fileName)) {
            $fileName = time() . ".csv";
        }

        // 把键值做为第一行
        $firstColumn = array();
        foreach ($data[0] as $key => $value) {
            $firstColumn[0][] = $key;
        }
        $dataTmp = $data;
        $result = array();        
        $dataString = '';
        foreach ($dataTmp as $key => $value) {
            foreach ($value as $k => $v) {
                $value[$k] = str_replace(',', '', $v);  // 去掉英文逗号
                $value[$k] = iconv('utf-8', 'gbk', $v); // 转码
                $value[$k] = "\t".$v; // 制表符
            }
            $result[] = implode(',', $value);
        }
        if (!empty($result)) {
            $dataString = implode("\n", $result);
        }
        $this->setHeader($fileName, $dataString);
    }
    
    /**
     * 设置导出的header信息
     * @param unknown $fileName
     * @param unknown $dataString
     */
    private function setHeader($fileName, $dataString)
    {
        header("Content-type:text/csv");
        header("Content-Disposition:attachment;filename=" . $fileName);
        header("Cache-Control:must-revalidata,post-check=0,pre-check=0");
        header("Expires:0");
        header("Pragma:public");
        echo $dataString;
    }
    
    /**
     * 转码函数
     *
     * @param mixed $content
     * @param string $from
     * @param string $to
     * @return mixed
     */
    public function charset($content, $from='gbk', $to='utf-8') {
        $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
        $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;
        if (strtoupper($from) === strtoupper($to) || empty($content)) {
            //如果编码相同则不转换
            return $content;
        }
        if (function_exists('mb_convert_encoding')) {
            if (is_array($content)){
                $content = var_export($content, true);
                $content = mb_convert_encoding($content, $to, $from);
                eval("\$content = $content;");return $content;
            }else {
                return mb_convert_encoding($content, $to, $from);
            }
        } elseif (function_exists('iconv')) {
            if (is_array($content)){
                $content = var_export($content, true);
                $content = iconv($from, $to, $content);
                eval("\$content = $content;");return $content;
            }else {
                return iconv($from,$to,$content);
            }
        } else {
            return $content;
        }
    }
    
}