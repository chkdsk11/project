<?php
/**
 * Author: 邓永军
 * Date: 2016/9/12
 */
namespace Shop\Models;
class BaiyangCouponEnum{

    //活动状态
    const COUPON_NOT_START=10; //未开始
    const COUPON_PROCESSING = 20; //领取中
    const COUPON_HAVE_OVER=30;  //已领完
    const COUPON_HAVE_ENDED=40; //已结束
    const COUPON_CANCEL=50; //已取消

    //优惠券类型
    const COUPON_FULL_MINUS = 1; //满减券
    const COUPON_REBATE=2;  //折扣券
    const COUPON_EXPRESS_FREE=3; //包邮券

    //发放方式
    const ONLINE_COUPONS  = 1; //线上优惠券
    const UNIFORM_CODE    = 2; //统一码
    const ACTIVATION_CODE = 3; //激活码

    //站点
    const SITE_PC     = 1; //PC
    const SITE_APP    = 2; //APP
    const SITE_WAP    = 3; //WAP
    const SITE_WECHAT = 4; //微商城

    const ALL_RANGE      = 'all';      //全场
    const CATEGORY_RANGE = 'category'; //品类
    const BRAND_RANGE    = 'brand';    //品牌
    const SINGLE_RANGE   = 'single';   //单品
    //const SPECIAL = 'special'; //专题

    const ALL_RX = 'all';
    const RX='rx';
    const NON_RX='non_rx';

    public static $CouponRx=array(
        self::ALL_RX =>"全部",
        self::RX => "处方",
        self::NON_RX => "非处方"
    );

    public static $CouponStatus = array(
        self::COUPON_NOT_START  => '未开始',
        self::COUPON_PROCESSING => '进行中',
        self::COUPON_HAVE_OVER => '已领完',
        self::COUPON_HAVE_ENDED => '已结束',
        self::COUPON_CANCEL     => '已取消'
    );

    public static $OfferType = array(
        self::COUPON_FULL_MINUS   => '满减券',
        self::COUPON_REBATE     => '折扣券',
//        self::COUPON_EXPRESS_FREE    => '包邮券'
    );

    public static $ForScope = array(
        self::ALL_RANGE      => '全场',
        self::CATEGORY_RANGE => '品类',
        self::BRAND_RANGE    => '品牌',
        self::SINGLE_RANGE   => '单品',
        //self::SPECIAL => '专题'
    );

    public static $ForPlatform = array(
        self::SITE_PC  => 'PC',
        self::SITE_APP => 'APP',
        self::SITE_WAP => 'WAP',
        self::SITE_WECHAT => '微商城'
    );

}