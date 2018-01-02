<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/11/16 0016
 * Time: 上午 10:15
 */

namespace Shop\Models;

/**
 * Class OrderEnum
 * @package Shop\Models
 * Order服务相关常量
 */
class OrderEnum
{
    /**
     * 订单号前辍
     */
    const PC = '95';  //pc订单号前辍
    const ANDROID = '90';   //android订单号前辍
    const IOS = '89';
    const WAP = '91';   //wap订单号前辍
    const WECHAT = '85';    //微商城订单号前辍
    const KJ = 'G';   //跨境订单号前辍
    const USER_ORDER_LOCK_KEY = 'soa_userorder_'; //用户订单读写前辍

    /**
     * 订单读写行为配置
     */
    const ORDER_SN_KEY = 'soa_order_sn_count';   //订单号生成key
    const USER_ORDER_LOCK_TIME = 5;   //用户订单强制5秒读主库

    /**
     * 订单平台
     */
    const PLATFORM_PC = 'pc'; //pc站点
    const PLATFORM_WAP = 'wap';   //wap站点
    const PLATFORM_APP = 'app';   //app站点
    const PLATFORM_WECHAT = 'wechat'; //微商城

    /**
     * 订单的几个状态
     */
    const ORDER_PAYING = 'paying';    //待付款
    const ORDER_SHIPPING = 'shipping';    //待发货
    const ORDER_SHIPPED = 'shipped';  //待收货
    const ORDER_EVALUATING = 'evaluating';    //待评价
    const ORDER_REFUND = 'refund';    //退款｜售后
    const ORDER_CANCELED = 'canceled';    //取消订单
    const ORDER_FINISHED = 'finished';    //订单完成
    const ORDER_ALL = 'all';  //所有
    const ORDER_CLOSED = 'closed';  //交易关闭

    /*
     * 订单支付类型
     */
    const ORDER_PAY_ONLINE = 1;   //在线付款
    const ORDER_PAY_CASH = 0;    //货到付款

    // 待付款的48小时后取消
    const CANCEL_TIME = 172800;

    // 药品类型
    public static $MedicineType = [1, 2, 3, 4];

    // 支付方式名称
    public static $PaymentName = [
        '1' => '支付宝支付',
        '2' => '微信支付',
        '3' => '货到付款',
        '4' => '红包支付',
        '5' => '苹果支付',
        '6' => '银联支付',
        '7' => '余额支付',
    ];

    // 物流公司对应的编号
    public static $LogisticsNo = [
        'yuantong' => '圆通快递',
        'yunda' => '韵达快运',
        'huitongkuaidi' => '百世汇通',
        'ems' => 'EMS',
        'shunfeng' => '顺丰快递',
        'shentong' => '申通',
    ];

    //退款状态
    //移动端：0-申请中，1-拒绝退款，2-退款中（同意退款），3-退款完成，4-等待退货
    //PC端：0-退款中/售后中，1-拒绝退款，2-退款中，3-退款完成
    public static $RefundStatus = [
        '0' => '退款中/售后中',
        '1' => '拒绝退款',
        '2' => '退款中',
        '3' => '退款完成',
        '4' => '等待退货',
        '5' => '物流已提交',
        '6' => '取消退款',
        '7' => '您服务单的商品已收到,等待财务审核',
    ];

    //发票内容
    public static $receiptContent = array(
        10 => '药品',
        11 => '生活用品',
        12 => '医疗用品',
        13 => '医疗器械',
        14 => '计生用品',
        15 => '食品',
        16 => '明细',
    );

    // 评价状态
    const EVALUATING       = '0'; // 待评价
    const APPEND_EVALUATED = '1'; // 追加评价(已评价未晒图)
    const EVALUATED        = '2'; // 已评价(已评价与晒图)
    const NOT_EVALUATED    = '3'; // 不能评价

}