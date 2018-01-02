<?php
/**
 * Created by phpstorm
 * User: Administrator
 * Date: 2016/3/23
 * Time: 14:00
 * 
 * AES 加密与解密
 * 使用前提：已注入DI服务中
 * 使用方法：$result = base64_encode($this->aes->encrypt($param))
 *        rtrim($this->aes->decrypt(base64_decode($result)), "\0");
 			 通常加解密结果都要进行base64编码和解码
 * 返回结果：加密返回aes加密结果，解密返回aes解密结果
 */

namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class Aes extends LibraryBase
{
    /**
     * 
     * @var unknown
     */
    static private $key = null;
    
    /**
     * 
     * @var unknown
     */
    static private $iv = null;
    
    /**
     * 
     * @param unknown $key
     * @param unknown $iv
     */
    public function __construct($key, $iv)
    {
        static::$key = $key;
        static::$iv = $iv;
    }
    
    /**
     * 数据加密
     * @param unknown $data
     * @return string
     */
    public function Encrypt($data)
    {
        if(!empty(static::$key) && !empty(static::$iv)) {
            $encryptData = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, static::$key, $data, MCRYPT_MODE_CBC, static::$iv);
            return $encryptData;
        }
    }
    
    /**
     * 数据解密
     * @param unknown $data
     * @return string
     */
    public function Decrypt($data)
    {
        if(!empty(static::$key) && !empty(static::$iv)) {
            $decrypData = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, static::$key, $data, MCRYPT_MODE_CBC, static::$iv);
            return $decrypData;
        }
    }
    
    
}