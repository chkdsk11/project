<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/10 0010
 * Time: 上午 9:09
 *
 * 缓存枚举类
 * 使用方法：先use Shop\Models\CacheKey; 然后CacheKey::AUTH_KEY即可;
 */

namespace Shop\Models;

class CacheKey
{
    const FAILED_LOGIN_TOTAL = 5; //运营后台用户登录最大失败次数
    const LOCK_TIME = 7200;   //用户登录失败上限后，锁定时间
    const HALF_AN_HOUR = 1800;   //半小时 【公共时间】
    const AUTH_KEY='soa_login_';    //缓存使用的前辍
    const SITE_KEY='soa_BaiyangSite';  //缓存站点使用
    const MENU_KEY='soa_menu_key';  //缓存功能表的key
    const ROLE_KEY='soa_role_key';  //缓存角色表的key
    const PER_MENU_KEY='soa_per_menu_key';  //缓存个人权限的key
    const ADMIN_SITE_KEY='soa_admin_site_key';  //缓存站点key
    const ADMIN_RESOURCE_KEY='soa_admin_resource_key';  //缓存权权资源
    const ADMIN_ROLE_KEY='soa_admin_role_key';  //缓存角色表的key
    const ADMIN_LOGIN_KEY='/login/login';  //管理员登录的key [不存放redis]
    const ADMIN_LOGOUT_KEY='/login/logout';  //管理员退出的key [不存放redis]
    const ADMIN_TO_ADMIN='Kamh5DZb_&E_dUkf|+:fw[m>PZ4Z;!$PF$*[|vU/';  //统一后台与PC互跳的key [不存放redis]
    const ADMIN_IDS='soa___admin_ids__';  //统一后台管理员登录的id
    const PC_ADMIN_URL='pc_admin_url';  //PC后台跳转url [不存放redis]
    const SOA_ALL_REDIS_KEYS_ARR='_soa_redis_all_keys_index_';  // 存放除session下的redis的key

    const MAKE_ORDER_PROMOTION = 'soa_makeOrderPromotion_';  //凑单促销列表的前缀
    const CART_LIMIT_BUY_KEY = 'soa_cartLimitBuyKey_';  //购物车限购用到的数据的前缀
    const ALL_CHANGE_PROMOTION = 'soa_allChangePromotion_';  //切换全场活动的前缀
    const ORDER_SN = 'soa_orderSn_';  // 生成订单号的前缀
    const EXCHANGE_COUPON_FAIL = 'soa_exchange_coupon_fail'; //获取用户失败前缀
    const ES_STOCK_KEY = 'soa_esStockKey'; // 同步库存到ES的队列KEY(6库)
    const EFFECTIVE_PROMOTION = 'soa_effectivePromotion'; // 有效的促销活动(进行中+未开始)
    const GOODS_SET = 'soa_goods_set_s'; //商品套餐前缀
    const REGION_LIST = 'soa_region_list'; //省市区
    const REGION_KEYS_LIST = 'soa_region_keys_list'; //省市区key
    const CPS_ORDER_KEY = 'soa_cpsOrderKey'; //CPS KEY
    const ERP_ORDER_RETURN_REASON_NOTICE = 'erp_order_return_reason_notice'; //erp退款申请(推送失败从新推送)

    const ALL_PRODUCT_RULE = 'soa_all_product_role'; //所有商品品规 ID为key name为value
    const ALL_Region = 'soa_all_region';
    const COUPON_ADD_ACTCODE = 'soa_coupon_add_actcode'; //优惠券生成激活码
    const PC_ALL_AD = 'pc_allAd'; //pc广告位缓存
    const PC_LevelCategoryList = 'pc_levelCategoryList'; //pc商品分类列表缓存
    const PC_LevelRegionList = 'pc_levelRegionList'; //pc地区列表缓存
}