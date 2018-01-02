<?php
/**
 * Created by Zend Studio.
 * User: Administrator
 * Date: 2016/4/12
 * Time: 14:00
 * 
 * 验证码
 * 使用前提：已注入di服务中
 * 使用方法：$this->curl->sendPost($url, array('key1' => 'value1', 'key2' => 'value2'));
 *        $this->curl->sendGet($url, array('key1' => 'value1', 'key2' => 'value2'));
 */
namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class Curl extends LibraryBase
{
    // 超时时间
    public $timeout = 10;
    
    
    // Post请求头
    public $postHeaders = array(
        'Content-type' => "application/x-www-form-urlencoded",
        'charset' => "UTF-8",
    );
    
    /**
     * CURL 模拟发送Post请求
     * @param unknown $url
     * @param unknown $data
     * @return mixed
     */
    public function sendPost($url, $data = array(), $header = array())
    {
        if (!empty($header)) {
            $header = array_merge($this->postHeaders, $header);
        } else {
            $header = $this->postHeaders;
        }
        $headerTmp = array();
        foreach ($header as $key => $value) {
            $headerTmp[] = $key . ': ' . $value;
        }
//        $httpHeader = (isset($headerTmp) && !empty($headerTmp)) ? array(rtrim(implode(";", $headerTmp), ";")) : array(); // 请求头
        $curlSetopt = array(
            CURLOPT_POST => 1, // 发送一个Post请求
            CURLOPT_HEADER => 0, // 不会将头文件的信息作为数据流输出，而是显示返回的信息
            CURLOPT_HTTPHEADER => $headerTmp, // 一个用来设置HTTP头字段的数组
            CURLOPT_URL => $url, // 需要获取的URL地址
            CURLOPT_FRESH_CONNECT => 1, // 强制获取一个新的连接
            CURLOPT_RETURNTRANSFER => 1, // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            CURLOPT_FORBID_REUSE => 1, // 在完成交互以后强迫断开连接，不能重用
            CURLOPT_TIMEOUT => $this->timeout, // 设置超时时间
            CURLOPT_POSTFIELDS => $data, // Post提交的数据包
        );
        $ch = curl_init(); // 启动一个CURL会话
        curl_setopt_array($ch, $curlSetopt); // 批量设置CURL传输项
        $result = curl_exec($ch); // 执行CURL操作
        curl_close($ch); // 关闭CURL会话
        return $result;
    }
    
    /**
     * CURL 模拟发送Get请求
     * @param unknown $url
     * @param array $data
     */
    public function sendGet($url, $data = array(), $header = array())
    {
        // 如果API有额外的请求头添加,则组装header请求头
        if (!empty($header)) {
            $headerTmp = array();
            foreach ($header as $key => $value) {
                $headerTmp[] = $key . ': ' . $value;
            }
        }
        $httpHeader = (isset($headerTmp) && !empty($headerTmp)) ? array(rtrim(implode(";", $headerTmp), ";")) : array(); // 请求头
        $url = $url . '?' . http_build_query($data); // 请求url
        //var_dump($httpHeader);var_dump($url);die;
        $curlSetopt = array(
            CURLOPT_HEADER => 0, // 不会将头文件的信息作为数据流输出，而是显示返回的信息
            CURLOPT_HTTPHEADER => $httpHeader, // 一个用来设置HTTP头字段的数组
            CURLOPT_URL => $url, // 需要获取的URL地址
            CURLOPT_FRESH_CONNECT => 1, // 强制获取一个新的连接
            CURLOPT_RETURNTRANSFER => 1, // 将curl_exec()获取的信息以文件流的形式返回，而不是直接输出
            CURLOPT_FORBID_REUSE => 1, // 在完成交互以后强迫断开连接，不能重用
            CURLOPT_TIMEOUT => $this->timeout, // 设置超时时间
        );
        $ch = curl_init(); // 启动一个CURL会话
        curl_setopt_array($ch, $curlSetopt); // 批量设置CURL传输项
        $result = curl_exec($ch); // 执行CURL操作
        curl_close($ch); // 关闭CURL会话
        return $result;
    }
    
    /**
     * 设置超时时间：单位，秒
     * @param unknown $time
     */
    public function setTimeout($time)
    {
        if ($time > 0 && is_numeric($time)) {
            $this->timeout = intval($time);
        }
    }

    /**
     * curl 请求方法
     *
     * @param string $url 请求地址
     * @param array $data post方式数据
     * @param string $method 请求方式
     * @return bool 结果
     */
    function api_curl($url, $data=array(), $method='GET')
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION,1);
        if('POST' == $method)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            //设置HTTP头信息
            curl_setopt($ch,CURLOPT_HTTPHEADER,array("X-HTTP-Method-Override: $method"));
        }
        $result = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        return $result;
    }
    
}