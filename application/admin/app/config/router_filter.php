<?php
/**
 * Author: DengYongJun
 * Email: i@darkdin.com
 * Time: 2017/02/04/15:49
 */

$filter = new \Phalcon\Config([
    'path'=>[
        'errors/show404',
        'goodspricetag/edituser',
        'user/getUserIdByPhone',
        'menu/all',
        'index/index',
        'coupon/deliver',
        'coupon/detail',
        'coupon/actcode',
        'limitbuy/edit',
        'limittime/getGoodsList',
        'goods/getGoodsSearchComponents',
        'promotion/getGoodListByIds',
        //商品管理
        'Category/getCate',//获取分类列表数据
        'Category/isSwitch',//启用|禁用分类
        'category/getCategory',//无限极分类
        'sku/isShowAd',//广告启用|暂停切换
        'sku/getStock',//
        'sku/setStock',//
        'sku/edit',//
        'promotion/getCategory',
        'coupon/getBrandSearchComponents',
        'goods/getGoodsForCoupon',
        'sku/del',//
        'sku/edit',
        'sku/getSkuInfo',
        'spu/getAttrAll',
        'sku/getBindGift',
        'video/getAllVideo',
        'sku/editInfo',
        'sku/getInstruction',
        'spu/getMobile',
        'sku/editModel',
        'sku/getAdAll',
        'sku/getIsOnSale',
        'sku/editTiming',
        'sku/setSales',
		'coupon/getUserByInfo',
        'coupon/getScopeActivities',
        'coupon/treatedCouponData',
        'coupon/checkExistIds',
        'goods/isOnShelf',
        'promotion/verifyTimeRange',
        'goods/getGoodsForGift',
        'sku/setHot',
        'sku/setRecommend',
        'sku/setIsLock',
        'brands/update',
        'attrname/update',
        'goodsets/modifysort',
        'coupon/getBrandValidIssetId',
        'limitbuy/verifyTimeRange',
        'limittime/verifyTimeRange',
        'goodsets/getskuinfobyid',
        'coupon/addactcode',
        'coupon/getUserByOrderId',
        'sku/search',
        'coupon/postRegisterBonus',
        'sku/delskuimg',
        'sku/setSkuImgSort',
        'sku/editInstruction',
        'coupon/getBrandSearchComponents',
        'coupon/importTelList',
        'goodstreatment/search',
        'brands/search',
        'brands/unique',
        'coupon/testUniqueCoupon',
        'goodsets/modifyeditsort',
        'Sku/setSkuSort',
        'user/changepsw',
        'ad/getposition',
        'ad/searchgoods',
        'ad/ajax_check_is_group',
        'ad/ajax_check_product_num',
        'ad/ajax_get_position',
        'ad/del',
        'handyentry/editsort',
        'handyentry/hide',
        'handyentry/show',
        'handyentry/upload',
        'handyentry/wapadd',
        'adaccesories/upload',
        'adaccesories/searchgoods',
        'adaccesories/del',
        'adaccesories/searchgoods',
        'adaccesories/cancel',
        'adwap/searchgoods',
        'ad/getPosition',
        'adwap/upload',
        'goods/isGift',
    ]
]);
return $filter;