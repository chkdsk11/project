<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/4 0004
 */
namespace Shop\Home\Datas;
use Composer\Package\Loader\ValidatingArrayLoader;
use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Models\BaiyangSkuInfo;
use Shop\Models\BaiyangSkuDefault;
use Shop\Models\BaiyangVideo;
use Shop\Models\BaiyangSkuAd;
use Shop\Models\BaiyangSpu;
use Shop\Models\BaiyangGoodsImages;
use Shop\Models\BaiyangCategory;
use Shop\Models\BiayangMedicineTag;
use Shop\Models\BaiyangCategoryProductRule;
use Shop\Models\BaiyangProductRule;
use Shop\Models\BaiyangOrderForUser;
use Shop\Models\CacheGoodsKey;
use Shop\Models\BaiyangGoodsQuestionsAnswers;
use Shop\Models\BaiyangGoodsQuestionsAnswersRelation;
use Shop\Models\BaiyangBrand;
use Shop\Models\BaiyangSkuSupplier;

class BaiyangSkuData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 获取sku数据
     * @param int $sku_id
     * @param string $platform 客户端,如(pc,app，wap，wechat)
     * @return array [] 结果信息
     *          -int        id      商品ID
     *          -int        spu_id
     *          -string     name   名称
     *          -string     subheading_name    副标题
     *          -string     sku_alias_name 别名
     *          -float      sku_price  销售价
     *          -float      sku_market_price   市场价
     *          -int        sku_stock  库存
     *          -string     prod_code  批准文号
     *          -string     period 保质期
     *          -string     goods_image   商品图片
     *          -string     big_path   默认大图路径
     *          -string     small_path 封面小图路径
     *          -string     barcode    条形码
     *          -string     manufacturer   生产厂家
     *          -string     weight 重量
     *          -string     barcode    条形码
     *          -string     meta_title
     *          -string     meta_keyword
     *          -string     meta_description
     *          -string     attribute_value_id 属性值id集合
     *          -string     bind_gift  绑定赠品
     *          -string     rule_value_id  多品规值集合
     *          -int        video_id   视频id
     *          -int        freight_temp_id    运费模板id
     *          -string     packaging_type 剂型
     *          -tinyint    is_use_stock   库存类型
     *          -string     specifications 规格
     *          -tinyint    is_global  是否海外购
     *          -tinyint    product_type   是否赠品，0商品，1普通赠品，2附属赠品
     *          -string     ad 广告模板
     *          -text       sku_desc   商品详情
     *          -tinyint    sale   是否上下架
     *          -tinyint    drug_type  药物类型(1：处方药 ，2：红色非处方药，3：绿色非处方药，4：非药物)
     *          -tinyint    category_id    商品后台分类
     *          -string     category_path  后台分类路径
     *          -int        brand_id   品牌id
     *          -string     sku_label   产品标签(不同标签用“,”号隔开)
     *          -int        comment_number  评论数
     *          -int        sales_number    销售量
     *          -smallint   rate_of_praise  好评率
     * @author 梁伟
     */
    public function getSkuInfo($sku_id,$platform)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_INFO.(int)$sku_id;
        $result = $this->RedisCache->getValue($key);
//        $result =     false;//稍后取消
        if( !$result ){
            $result = $this->getGoodsRedis($sku_id);
            if(!$result) return false;
        }
        return $this->endPoints($result[0],$platform);
    }

    public function getSkuInfoLess($sku_id,$platform)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_INFO.(int)$sku_id;
        $result = $this->RedisCache->getValue($key);
//        $result =     false;//稍后取消
        if( !$result ){
            $result = $this->getGoodsRedis($sku_id);
            if(!$result) return false;
        }
        $result = $result[0];
        $array = array(
            'id' => $result['id'],
            'supplier_id' => isset($result['supplier_id']) ? $result['supplier_id'] : '' ,//20157-06-02
            'spu_id'    =>  isset($result['spu_id']) ? $result['spu_id'] : '' ,
            'is_global' => isset($result['is_global']) ? $result['is_global'] : '' ,
            'goods_image' => isset($result['goods_image']) ? $result['goods_image'] : '' ,
            'small_path' => isset($result['small_path']) ? $result['small_path'] : '' ,
            'specifications' => isset($result['specifications']) ? $result['specifications'] : '' ,
            'comment_number' => isset($result['comment_number']) ? $result['comment_number'] : '' ,
            'rate_of_praise' => isset($result['rate_of_praise']) ? $result['rate_of_praise'] : '' ,
            'is_use_stock' => isset($result['is_use_stock']) ? $result['is_use_stock'] : '' ,
            'returned_goods_time' => isset($result['returned_goods_time']) ? $result['returned_goods_time'] : '' ,
        );
        //判断要使用的库存
        if( $result['is_use_stock'] == 2 ){
            $array['sku_stock'] = $result['virtual_stock_default'];
        }else if( $result['is_use_stock'] == 3 ){
            switch( $platform ){
                case 'pc':
                    $array['sku_stock'] = $result['virtual_stock_pc'];
                    break;
                case 'app':
                    $array['sku_stock'] = $result['virtual_stock_app'];
                    break;
                case 'wap':
                    $array['sku_stock'] = $result['virtual_stock_wap'];
                    break;
                case 'wechat':
                    $array['sku_stock'] = $result['virtual_stock_wechat'];
                    break;
            }
        }else{
            $array['sku_stock'] = $result['v_stock'];
        }

        if(!$result['is_global']){
            //判断要使用的价格
            if( $result['is_unified_price'] == 1 ){
                switch( $platform ){
                    case 'pc':
                        $array['sku_price'] = $result['goods_price_pc'];
                        $array['sku_market_price'] = $result['market_price_pc'];
                        break;
                    case 'app':
                        $array['sku_price'] = $result['goods_price_app'];
                        $array['sku_market_price'] = $result['market_price_app'];
                        break;
                    case 'wap':
                        $array['sku_price'] = $result['goods_price_wap'];
                        $array['sku_market_price'] = $result['market_price_wap'];
                        break;
                    case 'wechat':
                        $array['sku_price'] = $result['goods_price_wechat'];
                        $array['sku_market_price'] = $result['market_price_wechat'];
                        break;
                }
            }else{
                $array['sku_price'] = $result['goods_price'];
                $array['sku_market_price'] = $result['market_price'];
            }

            //判断要使用的上下架信息
            switch( $platform ){
                case 'pc':
                    $array['sale'] = $result['is_on_sale'];
                    break;
                case 'app':
                    $array['sale'] = $result['sale_timing_app'];
                    break;
                case 'wap':
                    $array['sale'] = $result['sale_timing_wap'];
                    break;
                case 'wechat':
                    $array['sale'] = $result['sale_timing_wechat'];
                    break;
            }

            //分端名称或详情
            if($platform == 'pc'){
                $array['name'] = $result['goods_name'];//pc端名称
                $array['subheading_name'] = $result['sku_pc_subheading'];//pc端副标题
            }else{
                $array['name'] = $result['sku_mobile_name'];     //移动端名称
                $array['subheading_name'] = $result['sku_mobile_subheading'];//移动端副标题
            }

            //判断是否为赠品信息
            if(isset($result['whether_is_gift']) && $result['whether_is_gift'] == 1){
                switch($platform){
                    case 'pc':
                        $array['product_type'] = isset($result['gift_pc'])?$result['gift_pc']:0;
                        break;
                    case 'app':
                        $array['product_type'] = isset($result['gift_app'])?$result['gift_app']:0;
                        break;
                    case 'wap':
                        $array['product_type'] = isset($result['gift_wap'])?$result['gift_wap']:0;
                        break;
                    case 'wechat':
                        $array['product_type'] = isset($result['gift_wechat'])?$result['gift_wechat']:0;
                        break;
                }
            }else{
                $array['product_type'] = $result['product_type'];
            }

            //获取药物类型，标签，品牌和分类
            if( $result['spu_id'] > 0 ){
                $spuInfo = $this->getSkuSpu($result['spu_id']);
            }
            $array['drug_type'] = (isset($spuInfo['drug_type'])&&!empty($spuInfo['drug_type']))?$spuInfo['drug_type']:-1;//药物类型
            $array['category_id'] = isset($spuInfo['category_id'])?$spuInfo['category_id']:-1;//商品后台分类
            $array['category_path'] = isset($spuInfo['category_path'])?$spuInfo['category_path']:-1;
            $array['brand_id'] = isset($spuInfo['brand_id'])?$spuInfo['brand_id']:-1;//品牌id

            if(!isset($spuInfo) || empty($spuInfo)){
                $array['drug_type'] = $result['medicine_type'];
                return $array;
            }

            //是否是用默认主图
            if( empty($result['goods_image']) ){
                $array['goods_image'] = $spuInfo['goods_image'];
            }else{
                $array['goods_image'] = $result['goods_image'];
            }
            if( empty($result['small_path']) ) {
                $array['small_path'] = $spuInfo['small_path'];
            }else{
                $array['small_path'] = $result['small_path'];
            }
        }else{
            //海外购商品
            $array['sku_price'] = $result['goods_price'];
            $array['sku_market_price'] = $result['market_price'];
            if($platform=='pc'){
                $array['sale'] = $result['is_on_sale'];
            }else{
                $array['sale'] = $result['status'];
            }
            $array['name'] = $result['goods_name'];//pc端名称
            $array['subheading_name'] = $result['introduction'];//pc端副标题
            $array['category_id'] = -1;//
            $array['brand_id'] = $result['global_brand_id'];//
            $array['drug_type'] = -1;//
            $array['product_type'] = 0;
        }
        return $array;
    }
    //获取商品缓存
    public function getGoodsRedis($sku_id)
    {
        $key = CacheGoodsKey::SKU_INFO.(int)$sku_id;
        $tables['skuTable'] = '\Shop\Models\BaiyangGoods a';
        $tables['skuInfoTable'] = '\Shop\Models\BaiyangSkuInfo b';
        $conditions['id'] = $sku_id;
        $where = 'a.id=:id:';
        $selections = 'a.id,a.supplier_id,a.barcode,a.prod_code,a.period,a.goods_name,a.prod_name_common,a.specifications,a.goods_image,a.big_path,a.small_path,a.name_desc,a.introduction,a.gift_yes,a.price,a.packing,a.virtual_stock,a.unit,a.goods_price,a.market_price,a.min_limit_price,a.guide_price,a.goods_number,a.v_stock,a.is_use_stock,a.attr_list,a.weight,a.size,a.meta_title,a.meta_keyword,a.meta_description,a.is_on_sale,a.is_hot,a.is_recommend,a.product_type,a.manufacturer,a.medicine_type,a.freight_temp_id,a.video_id,a.spu_id,a.rule_value_id,a.is_unified_price,a.bind_gift,a.sku_alias_name,a.sku_mobile_name,a.sku_pc_subheading,a.sku_mobile_subheading,a.attribute_value_id,a.sale_timing_app,a.sale_timing_wap,a.packaging_type,a.is_use_stock,a.is_global,a.sku_label,a.rule_value0,a.rule_value1,a.rule_value2,a.comment_number,a.sales_number,a.rate_of_praise,a.sale_timing_wechat,a.status,a.brand_id global_brand_id,a.usage';
        $selections .= ',b.id as sku_info_id,b.virtual_stock_default,b.virtual_stock_pc,b.virtual_stock_app,b.virtual_stock_wap,b.goods_price_pc,b.market_price_pc,b.goods_price_app,b.market_price_app,b.goods_price_wap,b.market_price_wap,b.ad_id_pc,b.ad_id_mobile,b.sku_detail_pc,b.sku_detail_mobile,b.whether_is_gift,b.gift_pc,b.gift_app,b.gift_wap,b.market_price_wechat,b.goods_price_wechat,b.market_price_wechat,b.virtual_stock_wechat,b.returned_goods_time';
        $phql = "SELECT {$selections} FROM {$tables['skuTable']} LEFT JOIN {$tables['skuInfoTable']} ON a.id = b.sku_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        $this->RedisCache->setValue($key,$result);
        return $result;
    }

    //删除商品缓存
    public function delGoodsRedis($goodsIds)
    {
        $key = CacheGoodsKey::SKU_INFO;
        $sku = explode(',',$goodsIds);
        if(is_array($sku)){
            foreach($sku as $v){
                $this->RedisCache->delete($key.$v);
            }
        }else{
            $this->RedisCache->delete($key.$sku);
        }
        return true;
    }

    /**
     * 组织sku数据
     * @param array() $param sku详情信息
     * @param $platform 调用端，如：pc，app，wap
     * @return array
     * @author 梁伟
     */
    public function endPoints($param,$platform)
    {
        if( $param['spu_id'] ){
            $default = $this->getSkuDefault($param['spu_id']);
            if($default){
                foreach( $param as $k=>$v ){
                    if( empty($v) && isset($default[$k])){
                        $param[$k] = $default[$k];
                    }else if(empty($v)){
                        if($k=='video_id'){
                            $param[$k] = $default['sku_video'];
                        }
                        if($k=='goods_name'){
                            $param[$k] = $default['sku_pc_name'];
                        }
                        if($k=='prod_code'){
                            $param[$k] = $default['sku_batch_num'];
                        }
                    }
                }
            }
        }
        //不分端信息 20157-06-02
        $array = array(
            'id' => $param['id'],
            'supplier_id' => isset($param['supplier_id']) ? $param['supplier_id'] : '',
            'spu_id'    =>  $param['spu_id'],
            'sku_alias_name'    =>  $param['sku_alias_name'],   //别名
            'prod_code'    =>  $param['prod_code'],              //批准文号
            'period'    =>  $param['period'],//保质期
            'goods_image'    =>  $param['goods_image'],//商品图片
            'big_path'    =>  $param['big_path'],//默认大图路径
            'small_path'    =>  $param['small_path'],//封面小图路径
            'barcode'    =>  $param['barcode'],//条形码
            'manufacturer'    =>  $param['manufacturer'],//生产厂家
            'weight'    =>  $param['weight'],//重量
            'volume'  =>  $param['size'],//体积
            'barcode'    =>  $param['barcode'],//条形码
            'meta_title'    =>   $param['meta_title'],
            'meta_keyword'    =>   $param['meta_keyword'],
            'meta_description'    =>   $param['meta_description'],
            'attribute_value_id'    =>   $param['attribute_value_id'],//属性值id集合
            'bind_gift'    =>   $param['bind_gift'],//绑定赠品
            'rule_value_id'    =>   $param['rule_value_id'],//多品规值集合
            'video'    =>   $param['video_id'],//视频id
//            'freight_temp_id'    =>   $param['freight_temp_id'],//运费模板id
            'packaging_type'    =>      $param['packaging_type'],//剂型
            'is_use_stock'      =>      $param['is_use_stock'],//库存类型
            'specifications'    =>      $param['specifications'], //规格
//            'goods_number'      =>      $param['goods_number'],  //商品数量
            'is_global'      =>      $param['is_global'],  //是否海外购
            'sku_label'      =>      $param['sku_label'],  //产品标签(不同标签用“,”号隔开)
            'rule_value0'    =>   $param['rule_value0'],//第一个多品规值
            'rule_value1'    =>   $param['rule_value1'],//第二个多品规值
            'rule_value2'    =>   $param['rule_value2'],//第三个多品规值
            'comment_number'    =>   $param['comment_number'],//评论数
            'sales_number'    =>   $param['sales_number'],//销售量
            'rate_of_praise'    =>   $param['rate_of_praise'],//好评率
            'usage'         =>  $param['usage'],
            'prod_name_common'=>    $param['prod_name_common'],
            'returned_goods_time'=>    isset($param['returned_goods_time']) ? $param['returned_goods_time'] : "",
//            'global_brand_id'    =>   $param['global_brand_id'],//海外购品牌
        );

        //判断要使用的库存
        if( $param['is_use_stock'] == 2 ){
            $array['sku_stock'] = $param['virtual_stock_default'];
        }else if( $param['is_use_stock'] == 3 ){
            switch( $platform ){
                case 'pc':
                    $array['sku_stock'] = $param['virtual_stock_pc'];
                    break;
                case 'app':
                    $array['sku_stock'] = $param['virtual_stock_app'];
                    break;
                case 'wap':
                    $array['sku_stock'] = $param['virtual_stock_wap'];
                    break;
                case 'wechat':
                    $array['sku_stock'] = $param['virtual_stock_wechat'];
                    break;
            }
        }else{
            $array['sku_stock'] = $param['v_stock'];
        }
        $array['virtual_stock_default'] = $param['virtual_stock_default'];
        $array['virtual_stock_pc'] = $param['virtual_stock_pc'];
        $array['virtual_stock_app'] = $param['virtual_stock_app'];
        $array['virtual_stock_wap'] = $param['virtual_stock_wap'];
        $array['virtual_stock_wechat'] = $param['virtual_stock_wechat'];
        $array['v_stock'] = $param['v_stock'];

        if(!$param['is_global']){
            //判断要使用的价格
            if( $param['is_unified_price'] == 1 ){
                switch( $platform ){
                    case 'pc':
                        $array['sku_price'] = $param['goods_price_pc'];
                        $array['sku_market_price'] = $param['market_price_pc'];
                        break;
                    case 'app':
                        $array['sku_price'] = $param['goods_price_app'];
                        $array['sku_market_price'] = $param['market_price_app'];
                        break;
                    case 'wap':
                        $array['sku_price'] = $param['goods_price_wap'];
                        $array['sku_market_price'] = $param['market_price_wap'];
                        break;
                    case 'wechat':
                        $array['sku_price'] = $param['goods_price_wechat'];
                        $array['sku_market_price'] = $param['market_price_wechat'];
                        break;
                }
            }else{
                $array['sku_price'] = $param['goods_price'];
                $array['sku_market_price'] = $param['market_price'];
            }

            //判断要使用的上下架信息
            switch( $platform ){
                case 'pc':
                    $array['sale'] = $param['is_on_sale'];
                    break;
                case 'app':
                    $array['sale'] = $param['sale_timing_app'];
                    break;
                case 'wap':
                    $array['sale'] = $param['sale_timing_wap'];
                    break;
                case 'wechat':
                    $array['sale'] = $param['sale_timing_wechat'];
                    break;
            }

            //分端名称或详情
            if($platform == 'pc'){
                $array['name'] = $param['goods_name'];//pc端名称
                $array['subheading_name'] = $param['sku_pc_subheading'];//pc端副标题
                $array['ad']    =   $param['ad_id_pc'];//广告模板
                $array['sku_desc']  =   $param['sku_detail_pc'];//详情
            }else{
                $array['name'] = $param['sku_mobile_name'];     //移动端名称
                $array['subheading_name'] = $param['sku_mobile_subheading'];//移动端副标题
                $array['ad']    =   $param['ad_id_mobile'];//广告模板
                $array['sku_desc']  =   $param['sku_detail_mobile'];//详情
            }

            //判断是否为赠品信息
            if(isset($param['whether_is_gift']) && $param['whether_is_gift'] == 1){
                switch($platform){
                    case 'pc':
                        $array['product_type'] = isset($param['gift_pc'])?$param['gift_pc']:0;
                        break;
                    case 'app':
                        $array['product_type'] = isset($param['gift_app'])?$param['gift_app']:0;
                        break;
                    case 'wap':
                        $array['product_type'] = isset($param['gift_wap'])?$param['gift_wap']:0;
                        break;
                    case 'wechat':
                        $array['product_type'] = isset($param['gift_wechat'])?$param['gift_wechat']:0;
                        break;
                }
            }else{
                $array['product_type'] = $param['product_type'];
            }

            //获取药物类型，标签，品牌和分类
            if( $param['spu_id'] > 0 ){
                $spuInfo = $this->getSkuSpu($param['spu_id']);
            }
            $array['drug_type'] = (isset($spuInfo['drug_type'])&&!empty($spuInfo['drug_type']))?$spuInfo['drug_type']:-1;//药物类型
            $array['category_id'] = isset($spuInfo['category_id'])?$spuInfo['category_id']:-1;//商品后台分类
            $array['category_path'] = isset($spuInfo['category_path'])?$spuInfo['category_path']:-1;
            $array['brand_id'] = isset($spuInfo['brand_id'])?$spuInfo['brand_id']:-1;//品牌id
//            $array['freight_temp_id'] = isset($spuInfo['freight_temp_id'])?$spuInfo['freight_temp_id']:0;//运费模板id

            $array['ruleName'] = '';
            $array['ruleList'] = [];
            if(!isset($spuInfo) || empty($spuInfo)){
                $array['drug_type'] = $param['medicine_type'];
                return $array;
            }

            //是否是用默认主图
            if( empty($array['goods_image']) ){
                $array['goods_image'] = $spuInfo['goods_image'];
            }else{
                $array['goods_image'] = $array['goods_image'];
            }
            if( empty($array['big_path']) ){
                $array['big_path'] = $spuInfo['big_path'];
            }else{
                $array['big_path'] = $array['big_path'];
            }
            if( empty($array['small_path']) ) {
                $array['small_path'] = $spuInfo['small_path'];
            }else{
                $array['small_path'] = $array['small_path'];
            }
            $ruleData = $this->getSkuRule($array['category_id']);
            if($ruleData){
                $ruleData['name_id0'] = $ruleData['name_id'];
                $ruleData['name_id1'] = $ruleData['name_id2'];
                $ruleData['name_id2'] = $ruleData['name_id3'];
                //获取品规名与品规值
                for($i=0; $i<3; $i++){
                     $ruleName = $this->getSkuRuleName($ruleData['name_id'. $i]);
                     $ruleVal = $this->getSkuRuleName($array['rule_value'. $i], $ruleData['name_id'. $i]);
                    if(!empty($ruleName) && !empty($ruleVal)){
                        $array['ruleName'] = !empty($array['ruleName']) ? $array['ruleName']. " $ruleName:$ruleVal" : "$ruleName:$ruleVal";
                        $array['ruleList'][$i]['ruleName'] = $ruleName;
                        $array['ruleList'][$i]['ruleVal'] = $ruleVal;
                    }
                }
            }
        }else{
            //海外购商品
            $array['sku_price'] = $param['goods_price'];
            $array['sku_market_price'] = $param['market_price'];
            if($platform=='pc'){
                $array['sale'] = $param['is_on_sale'];
            }else{
                $array['sale'] = $param['status'];
            }
            $array['name'] = $param['goods_name'];//pc端名称
            $array['subheading_name'] = $param['introduction'];//pc端副标题
            $array['category_id'] = -1;//
            $array['brand_id'] = $param['global_brand_id'];//
            $array['drug_type'] = -1;//
            $array['product_type'] = 0;
            $array['ruleList'] = [];
            $array['ruleName'] = '规格:'.$param['specifications'];
//            $array['subheading_name'] = $param['introduction'];//pc端副标题
        }

        return $array;
    }

    /**
     * 获取海外购商品信息
     * @param int $goods_id
     * return array
     * @author 梁伟
     */
    public function getGlobalGoods($goods_id)
    {
        //获取缓存信息
//        $key = CacheGoodsKey::GLOBAL_GOODS.(int)$goods_id;
//        $result = $this->RedisCache->getValue($key);
//        if(!$result){
        $selections = 'id,goods_name name,introduction,goods_price sku_price,market_price sku_market_price,comment_number,sales_number,rate_of_praise,is_global,is_on_sale sale, goods_image, drug_type, brand_id, specifications, is_use_stock';
        $where = 'id=:id:';
        $conditions = array( 'id' => $goods_id );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoods WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
//            $this->RedisCache->setValue($key,$result);
//        }
        return $result[0];
    }
    
    /**
     * 获取海外购国家，国旗
     * @param int $goods_id
     * return array
     * @author 梁伟
     */
    public function getGoodsBonded($goods_id)
    {
        $tables['aTable'] = '\Shop\Models\BaiyangGoodsStockBonded a';
        $tables['bTable'] = '\Shop\Models\BaiyangKjCustom b';
        $conditions['id'] = $goods_id;
        $where = 'a.goods_id=:id:';
        $selections = 'a.r_stock sku_stock';
        $selections .= ',b.custom_name';
        $phql = "SELECT {$selections} FROM {$tables['aTable']} LEFT JOIN {$tables['bTable']} ON a.bonded_id = b.id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        return $result[0];
    }
	
    /**
     * 获取pc分类数据
     * @param int $pid 父ID
     * return array
     * @author 梁伟
     */
    public function getCategoryPc($pid=0)
    {
        $selections = 'id,category_name,category_link,category_logo';
        $where = 'pid=:pid: order by sort desc , id asc';
        $conditions = array( 'pid' => $pid );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangMainCategory WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        return $result;
    }

    /**
     * 递归获取pc分类信息
     * @param int $pid 父ID
     * @param int $act 执行次数，最大执行5次
     * return array
     * @author 梁伟
     */
    public function getMainCategoryPc($pid=0,$act=0)
    {
        if($act >= 5) return false;
        $act++;
        $list = $this->getCategoryPc($pid);
        if(!$list) return false;
        foreach($list as $k=>$v){
            $tmp = $this->getMainCategoryPc($v['id'],$act);
            $list[$k]['son'] = ($tmp)?$tmp:null;
        }
        return $list;
    }

    /**
     * 递归获取移动端分类信息
     * @param int $pid 父ID
     * @param int $act 执行次数，最大执行5次
     * return array
     * @author 梁伟
     */
    public function getMainCategoryApp($pid=0,$act=0)
    {
        if($act >= 5) return false;
        $act++;
        $list = $this->getCategoryApp($pid);
        if(!$list) return false;
        foreach($list as $k=>$v){
            $tmp = $this->getMainCategoryApp($v['id'],$act);
            $list[$k]['son'] = ($tmp)?$tmp:null;
        }
        return $list;
    }

    /**
     * 获取移动分类数据
     * @param int $pid 父ID
     * return array
     * @author 梁伟
     */
    public function getCategoryApp($pid=0)
    {
        $conditions = array(
            'pid' => $pid,
            'enable'=>1,
            'main_category'=>1,
        );
        $selections = 'category_id id,category_name,picture category_logo,product_category_id';
        $where = 'parent_id=:pid: and enable=:enable: and main_category=:main_category: order by sort desc';
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangAppCategory WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        if(!empty($result)){
            foreach($result as $k=>$v){
                if(!empty($v['category_logo']) && strstr($v['category_logo'],'http://')===false){
                    $result[$k]['category_logo'] = $this->config['domain']['appImg'].$v['category_logo'];
                }
            }
        }
        return $result;
    }

    /**
     * 获取隐私配送和关于我们
     * @param ing $id
     * return array
     * @author 梁伟
     */
    public function getArticle($id){
        $selections = '*';
        $where = 'id=:id:';
        $conditions = array( 'id' => $id );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangArticle WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        return $result[0];
    }

    /**
     * 获取海外购税保信息，保税区
     * @param int $goods_id
     * return array
     * @author 梁伟
     */
    public function getGoodsExtend($goods_id)
    {
        $selections = 'tax_rate, goods_custom_name, hs_code, item_record_no, goods_unit';
        $where = 'goods_id=:id:';
        $conditions = array( 'id' => $goods_id );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoodsExtend WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        return $result[0];
    }

    /**
     * 获取sku默认信息
     * @param int $spu_id
     * @return array
     * @author 梁伟
     */
    public function getSkuDefault($spu_id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_DEFAULT.(int)$spu_id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( !$result ){
            $selections = '*';
            $where = 'spu_id=:spu_id:';
            $conditions = array( 'spu_id' => $spu_id );
            $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSkuDefault WHERE {$where}";
            $result = $this->modelsManager->executeQuery($phql,$conditions);
            if( !count($result) ){
                return false;
            }
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
        }
        return $result[0];
    }

    /**
     * 获取说明书
     * @param int $id
     * @return array
     * @author 梁伟
     */
    public function getSkuInstruction($id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_INSTRUCTION.(int)$id;
        $result = $this->RedisCache->getValue($key);
        if( !$result ){
            $selections = '*';
            $where = 'sku_id=:id:';
            $conditions = array( 'id' => $id );
            $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSkuInstruction WHERE {$where}";
            $result = $this->modelsManager->executeQuery($phql,$conditions);
            if( !count($result) ){
                return false;
            }
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
        }
        return $result[0];
    }

    /**
     * 获取海外购商品详情
     * @param int $id
     * @return array
     * @author 梁伟
     */
    public function getGoodsDetails($id)
    {
        $selections = 'goods_desc sku_desc';
        $where = 'goods_id=:id:';
        $conditions = array( 'id' => $id );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoodsExtension WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        return $result[0];
    }

    /**
     * 获取品牌名
     * @param int $brand_id
     * @return array()
     * @author 梁伟
     */
    public function getSkuBrand($brand_id,$platform='pc')
    {
        $key = CacheGoodsKey::SKU_BRAND_NAME.(int)$brand_id;
        $result = $this->RedisCache->getValue($key);
        if(!$result){
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
            $this->RedisCache->setValue($key,$result);
        }
        //获取品牌logo信息
        $result[0]['brand_logo'] = '';
        $data = ['brand_id'=>$brand_id,'type'=>($platform=='pc')?0:1];
        $phql = "SELECT brand_logo FROM \Shop\Models\BaiyangBrandsExtend WHERE brand_id=:brand_id: and type=:type: limit 1";
        $result1 = $this->modelsManager->executeQuery($phql,$data);
        if( count($result1) && $result1[0]['brand_logo'] ){
            $result1 = $result1->toArray();
            $result[0]['brand_logo'] = $result1[0]['brand_logo'];
        }
        return $result[0];
    }

    /**
     * 获得视频信息
     * @param $video_id
     * @return array
     * @author 梁伟
     */
    public function getSkuVideo($video_id)
    {
        $key = CacheGoodsKey::SKU_VIDEO.(int)$video_id;
        $result = $this->RedisCache->getValue($key);
        if(!$result) {
            $selections = '*';
            $where = 'id=:id: AND status=:status:';
            $conditions = array(
                'id' => $video_id,
                'status' => 10
            );
            $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangVideo WHERE {$where}";
            $result = $this->modelsManager->executeQuery($phql, $conditions);
            if (!count($result)) {
                return false;
            }
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
        }
        return $result[0];
    }

    /**
     * 获得广告信息
     * @param $id
     * @return array
     * @author 梁伟
     */
    public function getSkuAd($id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_AD.(int)$id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
            return $result[0];
        }
        $selections = 'ad_name,content';
        $where = 'id=:id: and is_show=1';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSkuAd WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
            return $result[0];
        }
        return false;
    }

    /**
     * 获取spu信息
     * @param $spu_id
     * @return array
     * @author 梁伟
     */
    public function getSkuSpu($spu_id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_SPU.(int)$spu_id;
        $result = $this->RedisCache->getValue($key);
        if( $result ){
            return $result[0];
        }
        $selections = 'spu_id,spu_name,brand_id,drug_type,category_id,goods_image,big_path,small_path,freight_temp_id,category_path';
        $where = 'spu_id=:id:';
        $conditions = array(
            'id'=>$spu_id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangSpu WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
            return $result[0];
        }
        return false;
    }

    /**
     * @desc        获取海外购商品图片信息
     * @param       $sku_id
     * @return      array()
     * @author 梁伟
     */
    public function getGoodsImg($sku_id)
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
            return $result;
        }
        return [];
    }

    /**
     * @desc        获取海外购商品品牌信息
     * @param       $brand_id
     * @return      array()
     * @author 梁伟
     */
    public function getGoodsBrand($brand_id)
    {
        $selections = 'id,brand_name,brand_logo';
        $where = 'id=:id:';
        $conditions = array(
            'id'=>$brand_id,
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangBrand WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            return $result;
        }
        return [];
    }

    /**
     * @desc        获取海外购商品详情信息
     * @param       $sku_id
     * @return      array()
     * @author 梁伟
     */
    public function getGoodsExtension($sku_id)
    {
        $selections = 'goods_desc,body';
        $where = 'goods_id=:id:';
        $conditions = array(
            'id'=>$sku_id,
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoodsExtension WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            return $result[0];
        }
        return [];
    }

    /**
     * @desc        获取商品图片信息
     * @param       $sku_id
     * @param       $spu_id
     * @return      array()
     * @author 梁伟
     */
    public function getSkuImg($sku_id,$spu_id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_IMG.(int)$sku_id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
//            return $this->imgUrl($result);
            return $result;
        }
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
            $this->RedisCache->setValue($key,$result);
//            return $this->imgUrl($result);
            return $result;
        }

        //sku没有相册时，读取默认相册
        //获取缓存信息
        $key = CacheGoodsKey::SPU_IMG.(int)$spu_id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
//            return $this->imgUrl($result);
            return $result;
        }
        //判断是否有spu
        if( $spu_id <= 0 ){
            return false;
        }
        $where = 'spu_id=:id: and is_default != :is_default: ORDER BY sort';
        $conditions = array(
            'id'=>$spu_id,
            'is_default'=>1
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoodsImages WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->getValue($key,$result);
//            return $this->imgUrl($result);
            return $result;
        }
        return false;
    }

    /**
     * 转化图片路径
     * @param array() $param 图片相册信息(二维数组)
     * @return array
     * @author 梁伟
     */
    private function imgUrl($param)
    {
        $array = array();
        foreach($param as $k=>$v){
            $array[$k]['sku_image'] = $this->config['domain']['img'].$v['sku_image'];
            $array[$k]['sku_middle_image'] = $this->config['domain']['img'].$v['sku_middle_image'];
            $array[$k]['sku_big_image'] = $this->config['domain']['img'].$v['sku_big_image'];
        }
        return $array;
    }

    /**
     * 获取药品标签
     * @param $drug_type 药物类型
     * @param $platform 平台【pc、app、wap】
     * @return array
     * @author 梁伟
     */
    public function getMedicineTag($drug_type,$platform)
    {
        switch($platform){
            case 'pc': $platform = 1;break;
            case 'app': $platform = 2;break;
            case 'wap': $platform = 3;break;
            case 'wechat': $platform = 4;break;
        }
        $selections = 'tag_name,describe,url';
        $where = 'platform=:platform: and medicine_type=:medicine_type: and status=:status:';
        $conditions = array(
            'platform'=>$platform,
            'medicine_type'=>$drug_type,
            'status'=>1,
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangMedicineTag WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * 获取分类品规关联信息
     * @param $category_id 分类id
     * @return array
     * @author 梁伟
     */
    public function getSkuRule($category_id)
    {
        $category_id = $this->getCategory3($category_id);
        //获取缓存信息
        $key = CacheGoodsKey::CATEGORY_RULE.(int)$category_id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
            return $result[0];
        }

        $selections = '*';
        $where = 'category_id=:id:';
        $conditions = array(
            'id'=>$category_id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangCategoryProductRule WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
            return $result[0];
        }
    }

    /**
     * 获取品规名
     * @param $id
     * @param $pid
     * @return array
     * @author 梁伟
     */
    public function getSkuRuleName($id,$pid=0)
    {
        //获取缓存信息
        $key = CacheGoodsKey::RULE_NAME.(int)$id;
        if($pid){
            $key .= '+'.$pid;
        }
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
            return $result[0]['name'];
        }

        $selections = 'name';
        $where = 'id=:id:';
        $conditions = array(
            'id'=>$id
        );
        if($pid){
            $where .= ' and pid=:pid:';
            $conditions['pid'] = $pid;
        }
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangProductRule WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
            return $result[0]['name'];
        }
    }

    /**
     * 根据品规名ID获取所有品规值信息
     * @param $pid
     * @return array
     * @author 梁伟
     */
    public function getRuleValueAll($pid)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SPU_RULE_VALUE_ALL.(int)$pid;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if($result){
            return $result;
        }
        $selections = 'id,name';
        $where = 'pid=:pid: order by add_time asc';
        $conditions = array(
            'pid'    =>  $pid,
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangProductRule WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( !count($result) ){
            return false;
        }
        $result = $result->toArray();
        $this->RedisCache->setValue($key,$result);
        return $result;
    }

    /**
     * 获取品规值
     * @param $spu_id
     * @param $platform
     * @param bool $verification 是否验证上下架,默认验证
     * @return array
     * @author 梁伟
     */
    public function getSkuRules($spu_id,$platform,$verification=true)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SPU_RULE_VALUE.(int)$spu_id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( !$result ){
            $selections = 'id,rule_value_id,is_on_sale,sale_timing_app,sale_timing_wap,sale_timing_wechat,rule_value0,rule_value1,rule_value2';
            $where = 'spu_id=:id: order by add_rule_time asc';
            $conditions = array(
                'id'    =>  $spu_id,
            );
            $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangGoods WHERE {$where}";
            $result = $this->modelsManager->executeQuery($phql,$conditions);
            if( !count($result) ){
                return [];
            }
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
        }
        $array = array();
        //不需要验证是否上下架
        if(!$verification){
            foreach( $result as $k=>$v){
                $array[$k]['id'] = $v['id'];
                $array[$k]['rule_value_id'] = $v['rule_value_id'];
                $array[$k]['rule_value0'] = $v['rule_value0'];
                $array[$k]['rule_value1'] = $v['rule_value1'];
                $array[$k]['rule_value2'] = $v['rule_value2'];
            }
        }else{
            switch( $platform ){
                case 'pc':
                    $platform = 'is_on_sale';
                    break;
                case 'app':
                    $platform = 'sale_timing_app';
                    break;
                case 'wap':
                    $platform = 'sale_timing_wap';
                    break;
                case 'wechat':
                    $platform = 'sale_timing_wechat';
                    break;
            }

            foreach( $result as $k=>$v){
                if( $v[$platform] == 1 ){
                    $array[$k]['id'] = $v['id'];
                    $array[$k]['rule_value_id'] = $v['rule_value_id'];
                    $array[$k]['rule_value0'] = $v['rule_value0'];
                    $array[$k]['rule_value1'] = $v['rule_value1'];
                    $array[$k]['rule_value2'] = $v['rule_value2'];
                }
            }
        }
        return $array;
    }

    /**
     * 获取分类信息
     * @param int $id
     * @return array
     * @author 梁伟
     */
    public function getCategory($id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::SKU_CATEGORY_BACKSTAGE.(int)$id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
            return $result[0];
        }
        $selections = 'id,category_name,alias,pid,level,is_enable';
        $where = 'id=:id:';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangCategory WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
            return $result[0];
        }
        return false;
    }

    /**
     * 获取三级分类信息
     * @param $id
     * @param $condition 判断条件，防止出现死循环
     * @return int
     * @author 梁伟
     */
    public function getCategory3($id,$condition = 0)
    {
        $caty = $this->getCategory($id);
        if( $caty['level'] > 3 && $condition < 5){
            $condition++;
            return $this->getCategory3($caty['pid'],$condition);
        }else{
            return $caty['id'];
        }
    }

    /**
     * 根据父获取所有子分类信息
     * @param $id 父分类id
     * @return int
     * @author 梁伟
     */
    public function getSonCategory($id)
    {
        //获取缓存信息
        $key = CacheGoodsKey::CATEGORY_SON.(int)$id;
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( $result ){
            return $result;
        }
        $selections = 'id,category_name,alias,pid,level,is_enable';
        $where = 'pid=:id:';
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT {$selections} FROM \Shop\Models\BaiyangCategory WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        if( count($result) ){
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
            return $result;
        }
        return [];
    }

    /**
     * 获取推荐或热门商品信息
     * @param $param 推荐或热门(is_recommend | is_hot)
     * @return array()
     * @author 梁伟
     */
    public function getHotSku($param)
    {
        switch($param){
            case    'is_hot':$key = CacheGoodsKey::SKU_HOT;break;
            case    'is_recommend':$key = CacheGoodsKey::SKU_RECOMMEND;break;
            default :return false;
        }
        //获取缓存信息
        $result = $this->RedisCache->getValue($key);
        //$result =     false;//稍后取消
        if( !$result ){
            $phql = "SELECT id FROM \Shop\Models\BaiyangGoods WHERE {$param}=:value: and spu_id!=0 order by update_time desc  limit 60";
            $result = $this->modelsManager->executeQuery($phql,['value'=>1]);
            if( !count($result) ){
                return false;
            }
            $result = $result->toArray();
            $this->RedisCache->setValue($key,$result);
        }
        return $result;
    }

    /**
     * 查询药师回访信息
     * @param array $param  [一维数组]
     *          -string     recall  回访电话号
     *          -int        gid     商品id
     * @return array()
     * @author 梁伟
     */
    public function getRecallDoc($param)
    {
        if($param['gid']){
            $data['bind']['gid'] = $param['gid'];
            $field = 'gid';
        }else if($param['group_id']){
            $data['bind']['gid'] = $param['group_id'];
            $field = 'group_id';
        }
        $data['column'] = 'id,add_time';
        $data['table'] = '\Shop\Models\BaiyangOrderForUser';
        $data['where'] = 'where recall_phone=:recall: and '.$field.'=:gid: order by id desc limit 1';
        $data['bind']['recall'] = $param['recall'];
        $res = $this->getData($data);
        if($res){
            return $res[0];
        }
        return false;
    }

    /**
     * 商品相关问答
     * @param array $id
     * @return array()
     * @author 梁伟
     */
    public function getQuestionsAnswers($id,$page=1,$limit=8)
    {
        $conditions = array(
            'id'=>$id
        );
        $phql = "SELECT count(id) count FROM \Shop\Models\BaiyangGoodsQuestionsAnswersRelation WHERE goods_id=:id:";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        $result = $result->toArray();
        if(!$result[0]['count']){
            return false;
        }
        $arr = array();
        $arr['pageCount'] = ceil($result[0]['count']/$limit);

        $start = ($page<=1)?1:(($page>$arr['pages'])?$arr['pages']:$page);
        $arr['pageStart'] = $start;
        $arr['pageSize'] = $limit;
        $arr['pageNum'] = $result[0]['count'];
        $start = ($start-1)*$limit;
        $where = 'goods_id=:id: limit '.$start.','.$limit;
        $phql = "SELECT questions_id FROM \Shop\Models\BaiyangGoodsQuestionsAnswersRelation WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql,$conditions);
        $result = $result->toArray();

        $phql = "SELECT questions,answers FROM \Shop\Models\BaiyangGoodsQuestionsAnswers WHERE id=:id:";
        foreach($result as $v){
            $result = $this->modelsManager->executeQuery($phql,['id'=>$v['questions_id']]);
            if(!count($result)){
                continue;
            }
            $result = $result->toArray();
            $arr['content'][] = $result[0];
        }
        return $arr;

    }

    /**
     * 添加药师回访信息
     * @param array $param  [一维数组]
     *          -string     recall  回访电话号
     *          -int        gid     商品id
     * @return array()
     * @author 梁伟
     */
    public function setRecallDoc($param)
    {
        return $this->addData(['table'=>'\Shop\Models\BaiyangOrderForUser','bind'=>$param]);
    }

    /**
     * 获取浏览记录
     * @param array $param  [一维数组]
     *          -int        user_id   用户id (临时用户或真实用户id)
     *          -int        is_temp   是否为临时用户 (1为临时用户、0为真实用户)
     *          -int        sku_id    商品id
     * @return array()
     * @author 梁伟
     */
    public function getBrowse($param)
    {
        $data['column'] = 'id,goods_id,browsing_number,add_time';
        $data['table'] = '\Shop\Models\BaiyangBrowingHistory';
        $data['where'] = 'where user_id=:user_id: and is_temp=:is_temp: and add_time > '.(time()-60*60*24*30);
        $data['bind']['user_id'] = $param['user_id'];
        $data['bind']['is_temp']   =  $param['is_temp'];
        if(isset($param['sku_id']) && $param['sku_id'] > 0){
            $data['where'] .= ' and goods_id=:goods_id:';
            $data['bind']['goods_id'] = $param['sku_id'];
        }else{
            $cou = $this->countData($data);
            $limit_start = ($param['page']-1)*$param['num'];
            $pages = ceil($cou/$param['num']);
            $data['where'] .= ' order by add_time desc limit '.$limit_start.','.$param['num'];
        }

        $res = $this->getData($data);
        if($res){
            if(!isset($param['sku_id']) || empty(isset($param['sku_id']))){
                $res['pages'] = $pages;
                $res['pageNum'] = $cou;
            }
        }
        return $res;
    }

    /**
     * 添加浏览记录
     * @param array $param  [一维数组]
     * @return array()
     * @author 梁伟
     */
    public function setBrowse($param)
    {
        return $this->addData(['table'=>'\Shop\Models\BaiyangBrowingHistory','bind'=>$param]);
    }

    /**
     * @desc 获取商品是否处方药
     * @param $param
     *          - goods_id 商品id
     *          - platform 平台标识  pc app wap
     * @return array|bool|int
     * @author 邓永军
     */
    public function getDrugType($param)
    {
        return $this->getSkuInfo($param['goods_id'],$param['platform'])['drug_type'];
    }

    /**
     * @desc 根据商品id获取品类树
     * @param $goods_id 商品id
     * @param $platform 平台标识 pc app wap
     * @return mixed
     * @author 邓永军
     */
    public function getSkuCategoryPath($goods_id,$platform)
    {
        $data = $this->getSkuInfo($goods_id,$platform);
        return !isset($data['category_path']) ? '' : $data['category_path'];
    }

    /**
     * @desc 递归获取品类树
     * @param $c_id 品类id
     * @param array $num_Arr
     * @return mixed
     * @author 邓永军
     */
    public function getCategoryPath($c_id,$num_Arr=array())
    {
        $category=$this->getData([
            'column'=>'id,pid',
            'table'=>'\Shop\Models\BaiyangCategory',
            'where'=>'where id=:category_id: ',
            'bind'=>[
                'category_id'=>$c_id
            ]
        ],1);
        $data=[$category['id']];
        $num_Arr=array_merge($num_Arr,$data);

        if($category['pid'] != '0' ){
            return BaiyangSkuData::getInstance()->getCategoryPath($category['pid'],$num_Arr);
        }else{
            return implode(',',$num_Arr);
        }
    }
    /**
     * @desc 根据商品id获取品牌id
     * @param $goods_id 商品id
     * @param $platform 平台标识 pc app wap
     * @return mixed
     * @author 邓永军
     */
    public function getSkuBrandId($goods_id,$platform)
    {
        return $this->getSkuInfo($goods_id,$platform)['brand_id'];
    }

    /**
     * @desc 通过标签id获取商品id
     * @param $tagId
     * @return array|bool
     * @author 邓永军
     */
    public function getgoods_idFromTagId($tagId)
    {
        $result = $this->getData([
            'column'=>'goods_id',
            'table'=>'\Shop\Models\BaiyangGoodsPrice',
            'where'=>'where tag_id = :tag_id:',
            'bind'=>[
                'tag_id'=>$tagId
            ]
        ]);
        return $result;
    }

    /**
     * 获得sku表信息
     * @param array $param
     *      -column string
     *      -where string
     * @return []|bool
     * @author 康涛
     */
    public function getAllSkus(array $param)
    {
        $phql="select {$param['column']} from Shop\Models\BaiyangGoods";
        if(isset($param['where']) && !empty($param['where'])){
            $phql.=" where {$param['where']}";
        }
        $ret=$this->modelsManager->executeQuery($phql);
        if(count($ret)){
            $data=$ret->toArray();
            unset($ret);
            return $data;
        }
        return false;
    }

    /**
     * @desc 获取商品评论数
     * @param array $param
     *       - int goods_id 商品id
     * @return array 结果信息
     * @author 吴俊华
     */
    public function getCommentCount(array $param)
    {
        $phql = "SELECT star FROM \Shop\Models\BaiyangGoodsComment WHERE goods_id = :goods_id:";
        $bind = [
            'goods_id' => $param['goods_id'],
        ];
        $ret = $this->modelsManager->executeQuery($phql, $bind);
        if(count($ret)){
            $data = $ret->toArray();
            return $data;
        }
        return [];
    }

    public function getSkuByBrand($param)
    {
        if(isset($param['is_temp']) && !empty($param['is_temp'])){
            $is_temp = $param['is_temp'];
        }else{
            $is_temp = 0;
        }
        $nowTime = time();
        $brand_arr = explode(',',$param['brand_id']);
        $res = [];
        foreach ($brand_arr as $new_brand_id){
            $data = [
                'table' => 'Shop\Models\BaiyangCoupon',
                'column' => 'coupon_sn,coupon_name,coupon_type,discount_unit,coupon_value,min_cost,use_range,ban_join_rule,start_provide_time,end_provide_time,coupon_number,limit_number,bring_number,coupon_description',
                'where' => 'WHERE ((FIND_IN_SET(:brand_ids:,brand_ids)) AND ( :nowTime: BETWEEN start_provide_time AND end_provide_time ) AND '.$param['platform'].'_platform = 1 ) AND is_cancel = 0 AND channel_id = 0 AND goods_tag_id = 0 AND register_bonus = 0 AND group_set < 3',
                'bind' => [
                    'brand_ids' => $new_brand_id ,
                    'nowTime' => $nowTime ,
                ]
            ];
            $ret = $this->getData($data);
            $rs[] = $ret;
            foreach ($rs as $temp){
                foreach ($temp as $r){
                    switch ($r['use_range'])
                    {
                        case 'brand':
                            $use = '品牌';
                            break;
                    }
                    switch ($r['coupon_type'])
                    {
                        case '1':
                            $tip = $use. '满'.$r['min_cost'].'元减'.$r['coupon_value'].'元';
                            break;
                        case '2':
                            if($r['discount_unit'] == 1){
                                $unit='元';
                            }else{
                                $unit='件';
                            }
                            $tip = $use.'满'.$r['min_cost'].$unit.'享'.$r['coupon_value'].'折';
                            break;
                        case '3':
                            $tip = $use. '满'.$r['min_cost'].'元包邮';
                            break;
                    }
                    if($r['coupon_number'] == 0){
                        $is_over = 0;
                    }else{
                        if($r['coupon_number'] - $r['bring_number'] > 0 ){
                            $is_over = 0;
                        }else{
                            $is_over  = 1;
                        }
                    }
                    if($is_temp == 1){
                        $is_over_bring_limit = 0;
                        $r['got_num'] = 0;
                    }else{
                        if($r['limit_number'] == 0 ){
                            $is_over_bring_limit = 0;
                        }else{
                            if(BaiyangCouponRecordData::getInstance()->countCouponHasBring($param['user_id'],$r['coupon_sn']) >= $r['limit_number']){
                                $is_over_bring_limit = 1;
                            }else{
                                $is_over_bring_limit = 0;
                            }
                        }
                        $r['got_num'] = BaiyangCouponRecordData::getInstance()->countCouponHasBring($param['user_id'],$r['coupon_sn']);
                    }
                    if(isset($r['ban_join_rule']) && !empty($r['ban_join_rule'])){
                        $value = json_decode($r['ban_join_rule'],true);
                        if(!isset($value['brand']) || empty($value['brand']) || $value['brand'] == $new_brand_id){
                            if(!isset($res[$r['coupon_sn']]) || empty($res[$r['coupon_sn']])){
                                $res[$r['coupon_sn']] = [
                                    'coupon_sn' => $r['coupon_sn'],
                                    'coupon_name' => $r['coupon_name'],
                                    'coupon_type' => $r['coupon_type'],
                                    'coupon_value' => $r['coupon_value'],
                                    'start_provide_time' => $r['start_provide_time'],
                                    'end_provide_time' => $r['end_provide_time'],
                                    'promotion_type' => BaiyangPromotionEnum::COUPON,
                                    'use_range' => $r['use_range'],
                                    'is_over' => $is_over,
                                    'is_over_bring_limit' => $is_over_bring_limit,
                                    'copywriter' => $tip,
                                    'coupon_description' => $r['coupon_description'],
                                    'got_num' => $r['got_num']
                                ];
                            }
                        }
                    }else{
                        if(!isset($res[$r['coupon_sn']]) || empty($res[$r['coupon_sn']])){
                            $res[$r['coupon_sn']] = [
                                'coupon_sn' => $r['coupon_sn'],
                                'coupon_name' => $r['coupon_name'],
                                'coupon_type' => $r['coupon_type'],
                                'coupon_value' => $r['coupon_value'],
                                'start_provide_time' => $r['start_provide_time'],
                                'end_provide_time' => $r['end_provide_time'],
                                'promotion_type' => BaiyangPromotionEnum::COUPON,
                                'use_range' => $r['use_range'],
                                'is_over' => $is_over,
                                'is_over_bring_limit' => $is_over_bring_limit,
                                'copywriter' => $tip,
                                'coupon_description' => $r['coupon_description'],
                                'got_num' => $r['got_num']
                            ];
                        }
                    }

                }
            }
        }
        $jg['coupon'] = array_values($res);
        return $jg;
    }
    
        /**
     * @desc 修改商品的销量和库存
     * @param array $param
     *       - int sku_id        商品ID
     *       - int stock_type    商品库存类型
     *       - int sales_number  待更新商品的销量
     *       - int sku_stock     待更新商品的库存
     *       - string platform
     * @return array 结果信息
     * @author 柯琼远
     */
    public function editStockAndSaleNumber($param) {
        // 真实库存
        if ($param['stock_type'] == 1) {
            if(!$this->updateData([
                'column' => "v_stock = {$param['sku_stock']},sales_number = {$param['sales_number']}",
                'table' => "\\Shop\\Models\\BaiyangGoods",
                'where' => "where id = " . $param['sku_id']
            ])) return false;
        } elseif ($param['stock_type'] == 2) {
            if(!$this->updateData([
                'column' => "sales_number = {$param['sales_number']}",
                'table' => "\\Shop\\Models\\BaiyangGoods",
                'where' => "where id = " . $param['sku_id']
            ])) return false;
            if(!$this->updateData([
                'column' => "virtual_stock_default = {$param['sku_stock']}",
                'table' => "\\Shop\\Models\\BaiyangSkuInfo",
                'where' => "where sku_id = " . $param['sku_id']
            ])) return false;
        } else {
            if(!$this->updateData([
                'column' => "sales_number = {$param['sales_number']}",
                'table' => "\\Shop\\Models\\BaiyangGoods",
                'where' => "where id = " . $param['sku_id']
            ])) return false;
            if(!$this->updateData([
                'column' => "virtual_stock_{$param['platform']} = {$param['sku_stock']}",
                'table' => "\\Shop\\Models\\BaiyangSkuInfo",
                'where' => "where sku_id = " . $param['sku_id']
            ])) return false;
        }
        // 清缓存
        $redis = $this->cache;
        $redis->selectDb(9);
        $redis->delete(CacheGoodsKey::SKU_INFO . $param['sku_id']);
        return true;
    }

    /**
     * @desc 根据skuId获取商品库存信息
     * @param string $skuId skuId (多个以逗号隔开)
     * @param string $platform 平台
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getSkuStockInfo($skuId, $platform)
    {
        $skuData = [
            'sku_stock' => 0,
            'is_use_stock' => 0,
            'id' => $skuId,
        ];
        $redisData = $this->RedisCache->getValue(CacheGoodsKey::SKU_INFO.$skuId);
        if(empty($redisData)){
            $data = $this->getData([
                'table' => 'Shop\Models\BaiyangGoods a',
                'join' => 'left join Shop\Models\BaiyangSkuInfo b on a.id = b.sku_id',
                'column' => 'a.id,a.v_stock,a.is_use_stock,b.virtual_stock_default,b.virtual_stock_pc,b.virtual_stock_app,b.virtual_stock_wap,b.virtual_stock_wechat',
                'where' => 'where a.id = :sku_id:',
                'bind' => [
                    'sku_id' => $skuId
                ],
            ],true);
        }else{
            $data = $redisData[0];
        }
        if(empty($data)) return $skuData;
        // 判断要使用的库存
        if($data['is_use_stock'] == 2){
            $skuData['sku_stock'] = $data['virtual_stock_default'];
        }else if( $data['is_use_stock'] == 3 ){
            switch( $platform ){
                case 'pc':
                    $skuData['sku_stock'] = $data['virtual_stock_pc'];
                    break;
                case 'app':
                    $skuData['sku_stock'] = $data['virtual_stock_app'];
                    break;
                case 'wap':
                    $skuData['sku_stock'] = $data['virtual_stock_wap'];
                    break;
                case 'wechat':
                    $skuData['sku_stock'] = $data['virtual_stock_wechat'];
                    break;
            }
        }else{
            $skuData['sku_stock'] = $data['v_stock'];
        }
        $skuData['is_use_stock'] = $data['is_use_stock'];
        $skuData['id'] = $data['id'];
        return $skuData;
    }

    /**
     * 获取供应商名称
     * @param $param
     * @return bool|int|string
     */
    public function getShopNameByShopId($param,$rw='read'){
        $condition = [
            'table' => '\Shop\Models\BaiyangSkuSupplier',
            'column' =>$param['column'],
            'where' => 'where '.$param['where'],
            'bind' => $param['bind'],
        ];
        $data = $this->getData($condition,true);
        return !empty($data) ? $data : '';
    }
}