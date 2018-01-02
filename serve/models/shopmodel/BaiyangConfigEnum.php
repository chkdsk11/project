<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2017/02/04
 * Time: 16:00
 * 
 * 配置表枚举类
 */
namespace Shop\Models;

class BaiyangConfigEnum
{
    // 配置key (config_sign)
    const WEBSITE = 'webSite';  //网站地址
    const BEIAN = 'beian'; //备案号
    const SYSTEM_NAME = 'systemName'; //系统名称
    const TELPHONE = 'telphone'; //联系电话
    const LOGO = 'logo'; //系统LOGO
    const CONSIGNEE_NUM = 'consigneeNum'; //收货信息数量
    const MAIL = 'mail'; //官方邮箱
    const MAIL_PASSWORD = 'mailPassword'; //官方邮箱密码
    const DEFAULT_KEY = 'defaultKey'; //默认关键字
    const USER_GROW = 'userGrow'; //会员成长值
    const QQ_SERVICE = 'qqService'; //qq客服链接
    const DISPLAY_ADDCART = 'displayAddCart'; //是否显示处方药购物车按钮
    const MAMA = 'maMa'; //妈妈网跳转网址
    const DESTUID = 'destuid'; //小能留言客服
    const ORDER_AUDIT = 'order_audit'; //订单是否开启审核
    const ORDER_NO_AUDIT_GOODS_TYPE = 'order_no_audit_goods_type'; //订单不开启自动审核的类型
    const ORDER_AUTO_AUDIT_PASS_TIME = 'order_auto_audit_pass_time'; //订单自动审核的通过时间，单位是秒
    const DISPLAY_PC_ADDCART = 'displayPcAddCart'; //pc端是否显示购物车
    const DISPLAY_WEBCHAT_ADDCART = 'displayWebchatAddCart'; //web端是否显示购物车
    const DISPLAY_APP_ACCESORIES = 'displayAPPaccesories'; //app是否显示海外购入口标识
    const MIN_AMOUNT_FOR_PASSWORD = 'min_amount_for_password'; //最高免支付密码金额
    const O2O_INTERVAL = 'o2o_interval'; //O2O时间段间隔（分）
    const O2O_FASTEST_TIME = 'o2o_fastest_time'; //O2O下单时间向上取整后最快配送时间（分）
    const ACCESS_TOKEN = 'access_token'; //微信访问凭据
    const JSAPI_TOKEN = 'jsapi_token'; //
    const DISPLAY_WAP_ADDCART = 'displayWapAddCart'; //wap端是否显示处方药购物车
    const PRODUCT_DETAIL_AD = 'PRODUCT_DETAIL_AD'; //产品详情广告
    const CAN_SHOP_SYS = 'can_shop_sys'; //是否能够购买速愈素

}