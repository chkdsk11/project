<?php
/**
 * Created by Zend Studio.
 * User: Administrator
 * Date: 2016/3/23
 * Time: 14:00
 * 
 * 邮箱发送
 * 使用前提：已经在di中注入该Library
 * 使用方法：$this->mail->send($title,$content,$sendTo,$filePaht);
 * 返回结果：array('success'=>true)/array('warning'=>'errorInfo');
 *
 */

namespace Shop\Libs;

require_once __DIR__ . '/mail/class.phpmailer.php';

use Shop\Libs\LibraryBase;

class Mail extends LibraryBase
{
    /***
     * 邮件对象
     * @var unknown
     */
    static private $mail = null;
    
    /**
     * 发送人邮箱（不含后缀）
     * @var unknown
     */
    static private $host;
    
    /**
     * 发送人邮箱用户名
     * @var unknown
     */
    static private $userName;
    
    /**
     * 发送人邮箱密码
     * @var unknown
     */
    static private $password;
    
    /**
     * 编码
     * @var unknown
     */
    static private $charSet;
    
    /**
     * 发送人完整邮箱地址
     * @var unknown
     */
    static private $from;
    
    /**
     * 发送人署名
     * @var unknown
     */
    static private $fromName;
    
    /**
     * 邮件标题
     * @var unknown
     */
    static private $subject;
    
    /**
     * 发送内容（支持HTML）
     * @var unknown
     */
    static private $msgHtml;
    
    /**
     * 发送地址
     * @var unknown
     */
    static private $addAddress;
   
    /**
     * 错误信息
     * @var unknown
     */
    static private $error;
    
    /**
     * 初始化：
     * host，userName，password，charSet，from，fromName
     * @param unknown $param
     */
    public function __construct($param)
    {
        if (empty(static::$mail)) {
            static::$mail = new \PHPMailer();
        }
        static::$host = $param['host'];
        static::$userName = $param['userName'];
        static::$password = $param['password'];
        static::$charSet = $param['charSet'];
        static::$from = $param['from'];
        static::$fromName = $param['fromName'];
    }
    
    /**
     * 发送邮件
     * @param unknown $title -> 发送标题
     * @param unknown $content -> 发送内容
     * @param unknown $address -> 发送地址（数组）
     * @param unknown $filePath -> 发送附件（数组）
     */
    public function send($title, $content, $address, $filePath = array())
    {
        if (empty($title) || empty($content) || empty($address)) {
            static::$error = "Check whether the parameters are complete";
            return array(
                'warning' => static::$error,
            );
        }
        
        // 设置使用SMTP邮件发送
        static::$mail->IsSMTP();
        
        // 开启SMTP认证
        static::$mail->SMTPAuth     = true;
        
        // 设置SMTP服务器，注册邮箱的服务器地址
        static::$mail->Host         = static::$host;
        
        // 发信人的邮箱用户名
        static::$mail->Username     = static::$userName;
        
        // 发信人的邮箱密码
        static::$mail->Password     = static::$password;
        
        /* 发送内容  */
        // 指定邮件内容格式为：HTML
        static::$mail->IsHTML(true);
        
        // 编码
        static::$mail->CharSet      = static::$charSet;
        
        // 发件人完整的邮箱名称
        static::$mail->From         = static::$from;
        
        // 发信人署名
        static::$mail->FromName     = static::$fromName;
        
        // 信件标题
        static::$mail->Subject      = $title;
        
        // 信件主体内容
        static::$mail->MsgHTML($content);
        
        // 收件人地址(群发/单独）
        if (is_array($address)) {
            foreach ($address as $eachAddress) {
                if (is_array($eachAddress)) {
                    static::$error = "Allowing only one-dimensional arrays";
                    return array(
                        'warning' => static::$error,
                    );
                } 
                static::$mail->AddAddress($eachAddress);
            }
        } elseif (is_string($address)) {
            static::$mail->AddAddress($address);
        }
        
        // 附件（多附件/单附件）
        if (is_array($filePath) && !empty($filePath)) {
            foreach ($filePath as $eachFilePath) {
                if (is_array($eachFilePath)) {
                    static::$error = "Allowing only one-dimensional arrays";
                    return array(
                        'warning' => static::$error,
                    );
                }
                static::$mail->AddAttachment($eachFilePath);
            }
        } elseif(is_string($filePath) && !empty($filePath)) {
            static::$mail->AddAttachment($filePath);
        }
        
        // 使用send函数进行发送
        if (static::$mail->Send()) {
            return array(
                'success' => true,
            );
        } else {
            static::$error = static::$mail->ErrorInfo;
            return array(
                'warning' => static::$error,
            );
        }
        
    }
    
}