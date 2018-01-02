<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/9/9
 * Time: 16:00
 * 
 * 促销限制活动枚举类
 * 使用方法：echo \Shop\Models\BaiyangLimitPromotionEnum::LIMIT_BUY;  //输出30
 */
namespace Shop\Models;

class BaiyangLimitPromotionEnum
{
    //活动类型
    const LIMIT_BUY  = 30; //限购
    const LIMIT_TIME = 35; //限时优惠

    //互斥活动
    const FULL_MINUS   = 5;  //满减
    const FULL_OFF     = 10; //满折
    const FULL_GIFT    = 15; //满赠
    const EXPRESS_FREE = 20; //包邮
    const COUPON       = 25; //优惠券

    //活动状态
    const PROMOTION_NOT_START  = 10; //未开始
    const PROMOTION_PROCESSING = 20; //进行中
    const PROMOTION_HAVE_ENDED = 30; //已结束
    const PROMOTION_CANCEL     = 40; //已取消

    //站点
    const SITE_PC     = 1; //PC
    const SITE_APP    = 2; //APP
    const SITE_WAP    = 3; //WAP
    const SITE_WeChat = 4; //微商城

    //会员等级
    const ALL_PEOPLE        = 10; //所有

    //活动级别
    const PROMOTION_ONE   = 1; //一级
    const PROMOTION_TWO   = 2; //二级
    const PROMOTION_THREE = 3; //三级

    //适用范围
    const ALL_RANGE      = 'all';      //全场
    const CATEGORY_RANGE = 'category'; //品类
    const BRAND_RANGE    = 'brand';    //品牌
    const SINGLE_RANGE   = 'single';   //单品
    const MORE_RANGE     = 'more';     //多单品

    //限购单位
    const UNIT_ITEM = 1; //件
    const UNIT_KIND = 2; //种

    //优惠类型
    const DISCOUNT_TYPE = 1;  //折扣
    const PRICE_TYPE    = 2; //优惠价

    /**
     * @desc 活动类型选项
     * @author 吴俊华
     * @date 2016-09-09
     */
    public static $promotionType = array(
        self::LIMIT_BUY  => '限购',
        self::LIMIT_TIME => '限时优惠'
    );

    /**
     * @desc 互斥活动选项
     * @author 吴俊华
     * @date 2016-09-09
     */
    public static $MutexPromotion = array(
        self::COUPON       => '优惠券',
        self::FULL_MINUS   => '满减',
        self::FULL_OFF     => '满折',
        self::FULL_GIFT    => '满赠',
        self::EXPRESS_FREE => '包邮'
    );

    /**
     * @desc 会员等级下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-05
     */
    public static $MemberLevel = array(
        self::ALL_PEOPLE => '所有'
    );

    /**
     * @desc 适用范围下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-05
     */
    public static $ForScope = array(
        self::ALL_RANGE      => '全场',
        self::CATEGORY_RANGE => '品类',
        self::BRAND_RANGE    => '品牌',
        self::SINGLE_RANGE   => '单品',
        self::MORE_RANGE     => '多单品',
    );

    /**
     * @desc 适用平台下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-09
     */
    public static $ForPlatform = array(
        self::SITE_PC  => 'PC',
        self::SITE_APP => 'APP',
        self::SITE_WAP => 'WAP'
    );

    /**
     * @desc 活动状态下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-09
     */
    public static $PromotionStatus = array(
        self::PROMOTION_NOT_START  => '未开始',
        self::PROMOTION_PROCESSING => '进行中',
        self::PROMOTION_HAVE_ENDED => '已结束',
        self::PROMOTION_CANCEL     => '已取消'
    );

    /**
     * @desc 限购单位下拉选项卡
     * @author 吴俊华
     * @date 2016-09-09
     */
    public static $LimitBuyUnit = array(
        self::UNIT_ITEM => '件',
        self::UNIT_KIND => '种'
    );

    /**
     * @desc 优惠类型下拉选项卡
     * @author 吴俊华
     * @date 2016-09-09
     */
    public static $OfferType = array(
        self::DISCOUNT_TYPE => '折扣',
        self::PRICE_TYPE    => '优惠价'
    );

}