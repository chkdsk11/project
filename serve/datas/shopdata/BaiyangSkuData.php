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

class BaiyangSkuData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取sku数据
     * @param array $tables 检验的表名
     * @param array $conditions 检验条件值【键值对】
     * @param string $where 检验条件
     * @return bool true|false 结果信息
     * @author 梁伟
     */
    public function getSkuInfo($tables,$conditions,$where)
    {
        $selections = 'a.id,a.barcode,a.prod_code,a.goods_name,a.prod_name_common,a.goods_image,a.big_path,a.small_path,a.name_desc,a.introduction,a.gift_yes,a.price,a.packing,a.virtual_stock,a.unit,a.goods_price,a.market_price,a.min_limit_price,a.guide_price,a.goods_number,a.v_stock,a.is_use_stock,a.attr_list,a.weight,a.size,a.meta_title,a.meta_keyword,a.meta_description,a.is_on_sale,a.is_hot,a.is_recommend,a.product_type,a.manufacturer,a.medicine_type,a.freight_temp_id,a.video_id,a.spu_id,a.rule_value_id,a.is_unified_price,a.bind_gift,a.sku_alias_name,a.sku_mobile_name,a.sku_pc_subheading,a.sku_mobile_subheading,a.is_show,a.attribute_value_id,a.sale_timing_app,a.sale_timing_wap';
        $selections .= ',b.id as sku_info_id,b.virtual_stock_default,b.virtual_stock_pc,b.virtual_stock_app,b.virtual_stock_wap,b.goods_price_pc,b.market_price_pc,b.goods_price_app,b.market_price_app,b.goods_price_wap,b.market_price_wap,b.ad_id_pc,b.ad_id_mobile,b.sku_detail_pc,b.sku_detail_mobile,b.instructions_pc,b.instructions_mobile';
        $phql = "SELECT {$selections} FROM {$tables['skuTable']} LEFT JOIN {$tables['skuInfoTable']} ON a.id = b.sku_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * @desc 通过商品id或品牌id或分类id获取商品信息
     * @param string $where 条件
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getSkuInfoBySalesId($where)
    {
        $sql = "SELECT b.brand_id,b.category_id,a.id as goods_id,a.goods_name FROM baiyang_goods as a LEFT JOIN baiyang_spu as b on a.spu_id = b.spu_id where {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $ret;
    }

    /**
     * @desc 通过品牌id获取品牌信息
     * @param string $ids 品牌id
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getBrandInfoById($ids)
    {
        $sql = "SELECT brand_name FROM baiyang_brands where id = {$ids}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $ret;
    }

    /**
     * @desc 通过分类id获取分类信息
     * @param string $ids 分类id
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getCategoryInfoById($ids)
    {
        $sql = "SELECT category_name FROM baiyang_category where id = {$ids}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        return $ret;
    }
    
    
}