<?php
/**
 * Created by Zend Studio.
 * User: Administrator
 * Date: 2016/3/23
 * Time: 14:00
 * 
 * 验证码
 * 使用前提：已注入di服务中
 * 使用方法：访问CodeController中index方法即可
 */
namespace Shop\Libs;

use Shop\Libs\LibraryBase;

class Code extends LibraryBase
{
    // 宽度
    private static $width = 100;
    
    // 高度
    private static $height = 40;
    
    // imagick
    private static $imagick = null;
    
    // imagedraw
    private static $imagedraw = null;
    
    // imagickpixel
    private static $imagickPixel = null;
    
    // 图片底层颜色
    private static $backgroundColor = 'white';
    
    public function __construct()
    {
        if (empty(static::$imagick)) {
            static::$imagick = new \Imagick();
        }
        if (empty(static::$imagedraw)) {
            static::$imagedraw = new \ImagickDraw();
        }
        if (empty(static::$imagickPixel)) {
            static::$imagickPixel = new \ImagickPixel(static::$backgroundColor);
        }
    }
    
    /**
     * 生成验证码字符串
     */
    private static function codeText()
    {
        return substr(str_shuffle('123456789123456789abcdefghijkmnpqrstuvwxyz'), 0, 4);
        //return rand(1000,9999);
    }
    
    /**
     * 生成图片验证码
     * @param unknown $prefix => session前缀
     * @param unknown $codeName => session标识符
     * @param number $time => 时间
     * @param string $md5 => 是否进行MD5加密
     * 查看图片打印效果，则需要关闭控制器的视图
     */
    public function createCodeImage($codeName, $md5 = false)
    {
        static::$imagick->newImage(static::$width, static::$height, static::$imagickPixel);
        static::$imagedraw->setFont(APP_PATH . '/static/assets/fonts/msyh.ttf');
        static::$imagedraw->setFontSize(34);
        static::$imagedraw->setFillColor('#FF6600');
        $code = static::codeText();
        // 用session保存验证码
        if ($md5) {
            $this->session->set($codeName, md5($code));
        } else {
            $this->session->set($codeName, $code);

        }
        static::$imagick->annotateImage(static::$imagedraw,12,32,0,$code);
        // 添加干扰线
        static::$imagedraw->setFillColor('#AA6600');
        static::$imagick->annotateImage(static::$imagedraw,rand(0, 30),rand(25,35),rand(-10, 10),"~_^_-_");
        //static::$imagick->addNoiseImage(\Imagick::NOISE_GAUSSIAN, \Imagick::CHANNEL_MAGENTA);
        static::$imagick->addNoiseImage(\Imagick::NOISE_MULTIPLICATIVEGAUSSIAN , \Imagick::CHANNEL_DEFAULT         );
        static::$imagick->borderImage("white",1,1);
        static::$imagick->setImageFormat('png');
        ob_clean();
        header("Expires:-1");
        header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        header("Pragma:no-cache");
        header('Content-type:image/png');
        echo static::$imagick;

    }
   
    public function __destruct()
    {
        static::$imagick->clear();
        static::$imagick->destroy();
        static::$imagedraw->clear();
        static::$imagedraw->destroy();
        static::$imagickPixel->clear();
        static::$imagickPixel->destroy();
    }
    
}