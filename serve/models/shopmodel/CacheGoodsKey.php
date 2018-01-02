<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/10/10 0010
 * Time: 上午 9:09
 */

namespace Shop\Models;

class CacheGoodsKey
{
    const SKU_INFO                      ='soa_SkuInfo_';                //sku详情信息的缓存前缀
    const SKU_DEFAULT                   ='soa_SkuDefault_';             //sku默认信息的缓存前缀
    const SKU_AD                        ='soa_SkuAd_';                  //sku广告信息的缓存前缀
    const SKU_SPU                       ='soa_SkuSpu_';                 //spu信息的缓存前缀
    const SKU_IMG                       ='soa_SkuImg_';                 //sku图片信息的缓存前缀
    const SPU_IMG                       ='soa_SpuImg_';                 //sku默认图片信息的缓存前缀
    const CATEGORY_RULE                 ='soa_CategoryProductRule_';    //分类品规关系信息的缓存前缀
    const CATEGORY_SON                  ='soa_CategorySon_';             //所有子分类信息的缓存前缀
    const RULE_NAME                     ='soa_Rule_name_';              //多品规信息的缓存前缀
    const SPU_RULE_VALUE                ='soa_SpuRuleValue_';           //相同spu商品多品规信息的缓存前缀
    const SKU_CATEGORY_BACKSTAGE        ='soa_Sku_Category_Backstage_'; //后台分类信息的缓存前缀
    const SKU_HOT                       ='soa_Sku_is_hot';              //热门信息的缓存前缀
    const SKU_RECOMMEND                 ='soa_Sku_is_recommend';        //推荐信息的缓存前缀
    const SKU_TIMING                    ='soa_Sku_Timing';              //定时上下架sku信息
    const SKU_TIMING_TIME               ='soa_Sku_Timing_Time';         //定时上下架时间信息
    const SKU_BRAND_NAME                ='soa_Sku_Brand_Name_';         //品牌名缓存前缀
    const SKU_VIDEO                     ='soa_Sku_Video_';              //视频缓存前缀
    const SKU_INSTRUCTION               ='soa_Sku_Instruction_';        //说明书缓存前缀
    const GLOBAL_GOODS                  ='soa_Global_Goods_';           //海外购商品缓存
    const SPU_RULE_VALUE_ALL            ='soa_Rule_Value_All_';         //相同品规名下所有品规值
	const BRAND_CACHE_TIME='900';//品牌相关缓存时间
    const BRAND_COUNT='soa_brand_count_key_';//获取所有品牌总数的缓存前缀
    const BRAND_LIST='soa_brand_list_key_';//获取所有品牌列表数据的缓存前缀
    const BRAND_GOODS_COUNT='soa_brand_goods_count_';//获取指定品牌商品总数的缓存前缀
    const BRAND_GOODS_LIST='soa_brand_goods_list_';//获取指定品牌商品列表数量的缓存前缀
    const ONE_BRAND_INFO='soa_one_brand_info_';//获取单个品牌信息的缓存前缀


}