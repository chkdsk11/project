<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2016/12/27 0027
 * Time: 9:47
 */

namespace Shop\Libs;

/**
 * 用户浏览器判断
 * Class UserAgent
 * @package Shop\Libs
 */
class UserAgent extends LibraryBase
{
    /**
     * 判断是否是手机移动端
     * @param string $userAgent
     * @return bool
     */
    public static function isMobile($userAgent)
    {
        return boolval(preg_match('/AppleWebKit.*Mobile.*/i',$userAgent)) || boolval(preg_match('/i(Phone|P(o|a)d)/i',$userAgent));
    }

    public static function isAndroid($userAgent)
    {
        return boolval(preg_match('/android/i',$userAgent));
    }

    public static function isIos($userAgent)
    {
        return boolval(preg_match('/iphone|ipod|ios/i',$userAgent));
    }

    public static function isWechat($userAgent)
    {
        return boolval(preg_match('/MicroMessenger/i',$userAgent));
    }
    public static function isQQ($userAgent)
    {
        return boolval(preg_match('/mobile.*qq/i',$userAgent));
    }

    public static function isUCBrowser($userAgent)
    {
        return boolval(preg_match('/ucbrowser/i',$userAgent));
    }

    public static function isQQBrowser($userAgent)
    {
        return boolval(preg_match('/mqqbrowser[^LightApp]/i',$userAgent));
    }

    public static function isQQBrowserLight($userAgent)
    {
        return boolval(preg_match('/MQQBrowserLightApp/i',$userAgent));
    }
    public static function isBaiyangApp($userAgent)
    {
        return boolval(preg_match('/\bbaiyang\b/i',$userAgent));
    }
    public static function isTouchMachine($userAgent)
    {
        return boolval(preg_match('/\bBaiYangTouchMachine\b/i',$userAgent));
    }
    public static function isWochacha($userAgent)
    {
        return boolval(preg_match('/\bWochacha\b/i',$userAgent));
    }
}