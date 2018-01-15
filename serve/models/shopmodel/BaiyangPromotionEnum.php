<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/8/26
 * Time: 16:00
 * 
 * 促销活动枚举类
 * 使用方法：echo \Shop\Models\BaiyangPromotionEnum::FULL_MINUS;  //输出5
 */
namespace Shop\Models;

class BaiyangPromotionEnum
{
    //活动类型
    const FULL_MINUS   = 5;  //满减
    const FULL_OFF     = 10; //满折
    const FULL_GIFT    = 15; //满赠
    const EXPRESS_FREE = 20; //包邮
    const COUPON       = 25; //优惠券
    const LIMIT_BUY    = 30; //限购
    const LIMIT_TIME   = 35; //限时优惠
    const INCREASE_BUY = 40; //加价购
    const TREATMENT    = 45; //疗程
    const MEMBER_PRICE = 50; //会员价
    const GOODS_GROUP  = 55; //套餐
    const MOM_PRICE    = 60; //辣妈价

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

    //适用人群
    const ALL_PEOPLE        = 10; //所有人
    const HAVE_NOT_SHOPPING = 20; //未购物会员(首次下单：新会员]
    const HAVE_SHOPPING     = 30; //已购物会员(老会员]

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
    const UNIT_TIME = 3; //次

    //限时优惠类型
    const DISCOUNT_TYPE = 1;  //折扣
    const PRICE_TYPE    = 2; //优惠价

    //会员等级(限购、限时优惠]
    const ALL_LEVEL        = 10; //所有

    //门槛单位
    const UNIT_YUAN = 'yuan'; //元
    const UNIT_JIAN = 'item'; //件
    const UNIT_ZHONG = 'kind';//种

    //渠道
    const SELF_SITE = 0;
    const CHANNEL_IOS = 89;
    const CHANNEL_ANDROID = 90;
    const CHANNEL_WECHAT = 85;
    const CHANNEL_PC = 95;
    const CHANNEL_WAP = 91;

    // 单个商品最大购买数
    const GOODS_MAX_PURCHASE_NUMBER = 200;

    /**
     * @desc 优惠类型下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-02
     */
    public static $OfferType = [
        self::FULL_MINUS   => '满减',
        self::FULL_OFF     => '满折',
        self::FULL_GIFT    => '满赠',
        self::EXPRESS_FREE => '包邮',
        self::INCREASE_BUY => '加价购'
    ];

    const ALL_RX = 'all';
    const RX='rx';
    const NON_RX='non_rx';

    public static $CouponRx=[
        self::ALL_RX =>"全部",
        self::RX => "处方",
        self::NON_RX => "非处方"
    ];

    /**
     * @desc 互斥活动选项
     * @author 吴俊华
     * @date 2016-09-02
     */
    public static $MutexPromotion = [
        self::COUPON       => '优惠券',
        self::FULL_MINUS   => '满减',
        self::FULL_OFF     => '满折',
        self::FULL_GIFT    => '满赠',
        self::EXPRESS_FREE => '包邮',
        self::INCREASE_BUY => '加价购'
    ];

    /**
     * @desc 适用人群下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-05
     */
    public static $ForPeople = [
        self::ALL_PEOPLE        => '所有人',
        self::HAVE_NOT_SHOPPING => '新会员',
        self::HAVE_SHOPPING     => '老会员'
    ];

    /**
     * @desc 适用范围下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-05
     */
    public static $ForScope = [
        self::ALL_RANGE      => '全场',
        self::CATEGORY_RANGE => '品类',
        self::BRAND_RANGE    => '品牌',
        self::SINGLE_RANGE   => '单品'
    ];

    /**
     * @desc 适用平台下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-07
     */
    public static $ForPlatform = [
        self::SITE_PC     => 'PC',
        self::SITE_APP    => 'APP',
        self::SITE_WAP    => 'WAP',
        self::SITE_WeChat => '微商城',
    ];

    /**
     * @desc 活动状态下拉框选项卡
     * @author 吴俊华
     * @date 2016-09-07
     */
    public static $PromotionStatus = [
        self::PROMOTION_NOT_START  => '未开始',
        self::PROMOTION_PROCESSING => '进行中',
        self::PROMOTION_HAVE_ENDED => '已结束',
        self::PROMOTION_CANCEL     => '已取消'
    ];

    /**
     * @desc 限购单位下拉选项卡
     * @author 吴俊华
     * @date 2016-10-19
     */
    public static $LimitBuyUnit = [
        self::UNIT_ITEM => '件',
        self::UNIT_KIND => '种',
        self::UNIT_TIME => '次'
    ];

    /**
     * @desc 限购单位下拉选项卡
     * @author 吴俊华
     * @date 2016-10-19
     */
    public static $LimitBuyUnitEN = [
        self::UNIT_ITEM => 'item',
        self::UNIT_KIND => 'kind',
        self::UNIT_TIME => 'times'
    ];

    /**
     * @desc 优惠类型下拉选项卡
     * @author 吴俊华
     * @date 2016-10-19
     */
    public static $LimitTimeType = [
        self::DISCOUNT_TYPE => '折扣',
        self::PRICE_TYPE    => '优惠价'
    ];

    /**
     * @desc 会员等级下拉框选项卡
     * @author 吴俊华
     * @date 2016-10-19
     */
    public static $MemberLevel = [
        self::ALL_LEVEL => '所有'
    ];

    /**
     * @desc 限购适用范围下拉框选项卡
     * @author 吴俊华
     * @date 2016-10-19
     */
    public static $LimitBuyForScope = [
        //self::ALL_RANGE      => '全场',
        self::CATEGORY_RANGE => '品类',
        self::BRAND_RANGE    => '品牌',
        self::SINGLE_RANGE   => '单品',
        self::MORE_RANGE     => '多单品'
    ];

    /**
     * @desc 其他促销活动类型选项(限购、限时优惠]
     * @author 吴俊华
     * @date 2016-10-19
     */
    public static $promotionType = [
        self::LIMIT_BUY  => '限购',
        self::LIMIT_TIME => '限时优惠'
    ];

    /**
     * @desc 活动类型转换
     * @author 吴俊华
     * @date 2016-10-28
     */
    public static $PromotionTransform = [
        self::FULL_MINUS   => 'fullMinus',
        self::FULL_OFF     => 'fullOff',
        self::FULL_GIFT    => 'fullGift',
        self::EXPRESS_FREE => 'expressFree',
        self::INCREASE_BUY => 'increaseBuy',
        self::LIMIT_BUY    => 'limitBuy',
        self::LIMIT_TIME   => 'limitTime',
    ];

    /**
     * @desc 限购单位
     * @author 吴俊华
     * @date 2016-11-17
     */
    public static $LIMIT_UNIT = [
        self::UNIT_ITEM => '件',
        self::UNIT_KIND => '种',
        self::UNIT_TIME => '次'
    ];

    /**
     * @desc 门槛单位
     * @author 吴俊华
     * @date 2016-12-01
     */
    public static $FULL_UNIT = [
        self::UNIT_YUAN => '元',
        self::UNIT_JIAN => '件',
    ];

    /**
     * @desc 添加促销活动时的互斥活动选项
     * @author 吴俊华
     * @date 2016-12-05
     */
    public static $MutexAlone = [
        self::COUPON       => '优惠券',
        self::FULL_MINUS   => '满减',
        self::FULL_OFF     => '满折',
        self::FULL_GIFT    => '满赠',
        self::EXPRESS_FREE => '包邮',
        self::INCREASE_BUY => '加价购',
        self::GOODS_GROUP  => '套餐',
        self::TREATMENT    => '疗程',
        self::MEMBER_PRICE => '会员价',
        self::LIMIT_TIME   => '限时优惠',
    ];

    public static $channelList = [
        self::SELF_SITE => '本站',
        self::CHANNEL_ANDROID => 'Android客户端',
        self::CHANNEL_IOS => 'IOS客户端',
        self::CHANNEL_WECHAT => '微商城',
        self::CHANNEL_PC => 'PC',
        self::CHANNEL_WAP => 'WAP'
    ];

}