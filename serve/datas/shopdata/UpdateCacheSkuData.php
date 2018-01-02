<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangSkuInfo;
use Shop\Models\BaiyangSkuDefault;
use Shop\Models\BaiyangVideo;
use Shop\Models\BaiyangSkuAd;
use Shop\Models\BaiyangSpu;
use Shop\Models\BaiyangGoodsImages;
use Shop\Models\BaiyangCategory;
use Shop\Models\BaiyangCategoryProductRule;
use Shop\Models\BaiyangProductRule;
use Shop\Models\CacheGoodsKey;

class UpdateCacheSkuData extends BaseData
{
    protected static $instance=null;


    /**
     * @desc 更新sku数据缓存
     * @param int $id
     * @param bool $WhetherReturn 是否返回数据,默认不返回
     * @author 梁伟
     */
    public function updateSkuInfo($sku_id,$WhetherReturn=false)
    {
        $tables['skuTable'] = '\Shop\Models\BaiyangGoods a';
        $tables['skuInfoTable'] = '\Shop\Models\BaiyangSkuInfo b';
        $conditions['id'] = $sku_id;
        $where = 'a.id=:id:';
        $selections = 'a.id,a.supplier_id,a.barcode,a.prod_code,a.period,a.goods_name,a.prod_name_common,a.specifications,a.goods_image,a.big_path,a.small_path,a.name_desc,a.introduction,a.gift_yes,a.price,a.packing,a.virtual_stock,a.unit,a.goods_price,a.market_price,a.min_limit_price,a.guide_price,a.goods_number,a.v_stock,a.is_use_stock,a.attr_list,a.weight,a.size,a.meta_title,a.meta_keyword,a.meta_description,a.is_on_sale,a.is_hot,a.is_recommend,a.product_type,a.manufacturer,a.medicine_type,a.freight_temp_id,a.video_id,a.spu_id,a.rule_value_id,a.is_unified_price,a.bind_gift,a.sku_alias_name,a.sku_mobile_name,a.sku_pc_subheading,a.sku_mobile_subheading,a.attribute_value_id,a.sale_timing_app,a.sale_timing_wap,a.packaging_type,a.is_use_stock,a.is_global,a.sku_label,a.rule_value0,a.rule_value1,a.rule_value2,a.comment_number,a.sales_number,a.rate_of_praise,a.sale_timing_wechat,a.status,a.brand_id global_brand_id,a.usage';
        $selections .= ',b.id as sku_info_id,b.virtual_stock_default,b.virtual_stock_pc,b.virtual_stock_app,b.virtual_stock_wap,b.goods_price_pc,b.market_price_pc,b.goods_price_app,b.market_price_app,b.goods_price_wap,b.market_price_wap,b.ad_id_pc,b.ad_id_mobile,b.sku_detail_pc,b.sku_detail_mobile,b.whether_is_gift,b.gift_pc,b.gift_app,b.gift_wap,b.market_price_wechat,b.goods_price_wechat,b.market_price_wechat,b.virtual_stock_wechat,b.returned_goods_time';
        $phql = "SELECT {$selections} FROM {$tables['skuTable']} LEFT JOIN {$tables['skuInfoTable']} ON a.id = b.sku_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_INFO.(int)$sku_id,$result);
            $this->RedisCache->delete(CacheGoodsKey::SKU_HOT);
            $this->RedisCache->delete(CacheGoodsKey::SKU_RECOMMEND);

        }
        if($WhetherReturn){
            return $result;
        }
    }

    /**
     * @desc 更新sku默认缓存信息
     * @param int $spu_id
     * @return array
     * @author 梁伟
     */
    public function updateSkuDefault($spu_id)
    {
        //获取缓存信息
        $selections = '*';
        $where = 'spu_id=:spu_id:';
        $conditions = array( 'spu_id' => $spu_id );
        //改变数据库连接
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSkuDefault WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        $this->RedisCache->setValue(CacheGoodsKey::SKU_DEFAULT.(int)$spu_id,$result);
    }

    /**
     * @desc 更新品牌名缓存
     * @param int $brand_id
     * @return array()
     * @author 梁伟
     */
    public function updateSkuBrand($brand_id)
    {
        $selections = '*';
        $where = 'id=:id: limit 1';
        $conditions = array(
            'id'=>(int)$brand_id,
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangBrands WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        $this->RedisCache->setValue(CacheGoodsKey::SKU_BRAND_NAME.(int)$brand_id,$result);
    }

    /**
     * @desc 更新视频信息缓存
     * @param $video_id
     * @return array
     * @author 梁伟
     */
    public function updateSkuVideo($video_id)
    {
        $selections = '*';
        $where = 'id=:id: AND status=:status:';
        $conditions = array(
            'id'=>$video_id,
            'status'=>10
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangVideo WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        $this->RedisCache->setValue(CacheGoodsKey::SKU_VIDEO.(int)$video_id,$result);
    }

    /**
     * @desc 更新广告信息缓存
     * @param $id
     * @return array
     * @author 梁伟
     */
    public function updateSkuAd($id)
    {
        $selections = 'ad_name,content';
        $where = 'id=:id:';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSkuAd WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_AD.(int)$id,$result);
        }
    }

    /**
     * @desc 更新spu信息缓存
     * @param $spu_id
     * @return array
     * @author 梁伟
     */
    public function updateSpu($spu_id)
    {
        $selections = 'spu_id,spu_name,brand_id,drug_type,category_id,goods_image,big_path,small_path,freight_temp_id,category_path';
        $where = 'spu_id=:id:';
        $conditions = array(
            'id'=>$spu_id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSpu WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        $this->RedisCache->setValue(CacheGoodsKey::SKU_SPU.(int)$spu_id,$result);
    }

    /**
     * @desc        更新商品图片信息缓存
     * @param       $sku_id
     * @return      array()
     * @author 梁伟
     */
    public function updateSkuImg($sku_id)
    {
        $selections = 'goods_image sku_image,goods_middle_image sku_middle_image,goods_big_image sku_big_image';
        $where = 'goods_id=:id: and is_default != :is_default: ORDER BY sort';
        $conditions = array(
            'id'=>$sku_id,
            'is_default'=>1
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoodsImages WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_IMG.(int)$sku_id,$result);
            return $result;
        }
        return false;
    }

    /**
     * @desc        更新商品图片默认信息缓存
     * @param       $spu_id
     * @return      array()
     * @author 梁伟
     */
    public function updateSpuImg($spu_id)
    {
        $selections = 'goods_image sku_image,goods_middle_image sku_middle_image,goods_big_image sku_big_image,sort';
        $where = 'spu_id=:id: ORDER BY sort';
        $conditions = array(
            'id'=>$spu_id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoodsImages WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SPU_IMG.$spu_id,$result);
        }
        return false;
    }

    /**
     * 更新分类品规关联信息缓存
     * @param $category_id 三级分类id
     * @return array
     * @author 梁伟
     */
    public function updateSkuRule($category_id)
    {
        $selections = '*';
        $where = 'category_id=:id:';
        $conditions = array(
            'id'=>$category_id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangCategoryProductRule WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::CATEGORY_RULE.(int)$category_id,$result);
        }
    }

    /**
     * 更新品规信息缓存
     * @param $id 品规id
     * @return array
     * @author 梁伟
     */
    public function updateSkuRuleName($id)
    {
        $selections = 'name';
        $where = 'id=:id:';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangProductRule WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::RULE_NAME.(int)$id,$result);
        }
    }

    /**
     * 更新多品规信息缓存
     * @param $spu_id
     * @return array
     * @author 梁伟
     */
    public function updateSkuRules($spu_id)
    {
        $selections = 'id,rule_value_id,is_on_sale,sale_timing_app,sale_timing_wap,sale_timing_wechat,rule_value0,rule_value1,rule_value2';
        $where = 'spu_id=:id: order by add_rule_time asc';
        $conditions = array(
            'id'    =>  $spu_id,
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoods WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SPU_RULE_VALUE.(int)$spu_id,$result);
        }
    }

    /**
     * 更新分类信息缓存---当前分类
     * @param int $id
     * @return array
     * @author 梁伟
     */
    public function updateCategory($id)
    {
        $selections = 'id,category_name,alias,pid,level,is_enable';
        $where = 'id=:id:';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangCategory WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_CATEGORY_BACKSTAGE.(int)$id,$result);
        }
    }

    /**
     * 更新分类子分类信息
     * @param int $id 父分类ID
     * @return array
     * @author 梁伟
     */
    public function updateSonCategory($id)
    {
        $selections = 'id,category_name,alias,pid,level,is_enable';
        $where = 'pid=:id:';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangCategory WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::CATEGORY_SON.(int)$id,$result);
        }
    }

    /**
     * 更新热门商品信息缓存
     * @return array()
     * @author 梁伟
     */
    public function getHotSku()
    {
        $phql = "SELECT id FROM \Shop\Models\BaiyangGoods WHERE is_hot=:value: and spu_id!=0 order by update_time desc  limit 60";
        $result = $this->modelsManager->executeQuery($phql,['value'=>1]);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_HOT,$result);
        }
    }

    /**
     * 更新推荐商品信息缓存
     * @return array()
     * @author 梁伟
     */
    public function getRecommendSku()
    {
        $phql = "SELECT id FROM \Shop\Models\BaiyangGoods WHERE is_recommend=:value: and spu_id!=0 order by update_time desc  limit 60";
        $result = $this->modelsManager->executeQuery($phql,['value'=>1]);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_RECOMMEND,$result);
        }
    }

    /**
     * @desc 更新说明书
     * @param int $id
     * @author 梁伟
     */
    public function updateSkuInstruction($id)
    {
        $phql = "SELECT * FROM \Shop\Models\BaiyangSkuInstruction WHERE sku_id=:id:";
        $result = $this->modelsManager->executeQuery($phql,['id'=>$id]);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue(CacheGoodsKey::SKU_INSTRUCTION.(int)$id,$result);
        }
    }
    
}