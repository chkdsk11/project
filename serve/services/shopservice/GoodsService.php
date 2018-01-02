<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Services;

use Shop\Admin\Listen\GoodsListen;
use Shop\Datas\BaiyangGoodsData;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangPromotionEnum;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class GoodsService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * @desc 通过id或者名称查询商品信息(没有海外购，非赠品)
     * @param string $input 条件
     * @param int $promotionType 活动类型
     * @param int $isGift 是否获取赠品开关 (0:不获取 1:获取)
     * @author 邓永军
     * @return array
     */
    public function getGoodsList($input="", $promotionType = 0,$isGift = 1,$notIn='')
    {
        $where = "a.spu_id > 1 AND a.is_global = 0 AND (a.is_on_sale = 1 OR a.sale_timing_app = 1 OR a.sale_timing_wap = 1 OR a.sale_timing_wechat = 1 )";
        if($notIn){
            $where .= " AND a.id not in({$notIn})";
        }
        if(!$isGift){
            $where .= " AND ((b.whether_is_gift = 0 AND a.product_type = 0) OR (b.whether_is_gift = 1 AND (b.gift_pc = 0 OR b.gift_app = 0 OR b.gift_wap = 0)))";
        }
        if($promotionType == BaiyangPromotionEnum::FULL_GIFT || $promotionType == BaiyangPromotionEnum::INCREASE_BUY){
            $where = "a.supplier_id = 1 AND ".$where;
        }
        $where .= " limit 20";
        $baseData = BaseData::getInstance();
        if($input==""){
            $info = $baseData->select(
              'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                "\\Shop\\Models\\BaiyangGoods as a",
                [
                ],
                $where,
                "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id INNER JOIN \\Shop\\Models\\BaiyangSpu as c on c.spu_id = a.spu_id"
            );
            if($info!==false){
                return $info;
            }else{
                return "0";
            }
        }
        $input=explode(",",$input);
        $info=[];
        $where = "( a.id = :id: OR a.goods_name LIKE :goods_name: ) AND ".$where;

        foreach ($input as $tmp){
            $res = $baseData->select(
                'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                "\\Shop\\Models\\BaiyangGoods as a",
                [
                    "id"=>$tmp,
                    "goods_name"=>"%".$tmp."%",
                ],
                $where,
                "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id INNER JOIN \\Shop\\Models\\BaiyangSpu as c on c.spu_id = a.spu_id "
            );
            if(count($res)==1){
                if(isset($res[0]) && !empty($res[0]))
                $info[]=[$res[0]];
            }else{
                foreach ($res as $tmp2){
                    $info[]=[$tmp2];
                }
            }
        }
        if($info){
            return $info;
        }else{
            return "0";
        }
    }

    /**
     * @desc 通过商品ids获取商品详细信息(没有海外购，非赠品)
     * @param string $input 条件
     * @param int $promotionType 活动类型
     * @author 邓永军
     * @return mixed
     */
    public function getGoodListByIds($input, $promotionType = 0)
    {
        $where = "a.id = :id: AND a.is_global = 0 AND (a.is_on_sale = 1 OR a.sale_timing_app = 1 OR a.sale_timing_wap = 1 OR a.sale_timing_wechat = 1 ) limit 20";
        if($promotionType == BaiyangPromotionEnum::FULL_GIFT || $promotionType == BaiyangPromotionEnum::INCREASE_BUY){
            $where = "a.supplier_id = 1 AND ".$where;
        }
        $input = explode(",",$input);
        $info = [];
        $baseData = BaseData::getInstance();
        foreach ($input as $tmp) {
            $res = $baseData->getData([
                'table' => "\\Shop\\Models\\BaiyangGoods as a",
                'join' => "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id",
                'column' => 'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                'where' => 'where '.$where,
                'bind' => [
                    "id" => $tmp,
                ],
            ],true);
            if (!empty($res)) {
                $info[] = $res;
            }
        }
        return $info ? $info : 0;
    }

    /**
     * @desc 查询赠品详细(没有海外购)
     * @param string $input 条件
     * @param int $promotionType 活动类型
     * @return string
     */
    public function getGoodsForGift($input="",$promotionType = 0)
    {
        $where = "a.is_global = 0 AND (a.is_on_sale = 1 OR a.sale_timing_app = 1 OR a.sale_timing_wap = 1 OR a.sale_timing_wechat = 1 ) limit 20";
        if($promotionType == BaiyangPromotionEnum::FULL_GIFT || $promotionType == BaiyangPromotionEnum::INCREASE_BUY){
            $where = "a.supplier_id = 1 AND ".$where;
        }
        $baseData = BaseData::getInstance();
        if($input==""){
            $info = $baseData->select(
                'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                "\\Shop\\Models\\BaiyangGoods as a",
                [

                ],
                $where,


                "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id"
            );
            if($info!==false){
                return $info;
            }else{
                return "0";
            }
        }

        $input=explode(",",$input);
        $info=[];
        $where = "( a.id = :id: OR a.goods_name LIKE :goods_name: ) AND ".$where;
        foreach ($input as $tmp){
            $res = $baseData->select(
                'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                "\\Shop\\Models\\BaiyangGoods as a",
                [
                    "id"=>$tmp,
                    "goods_name"=>"%".$tmp."%",

                ],
                $where,

                "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id"
            );
            if(count($res)==1){
                if(isset($res[0]) && !empty($res[0]))
                $info[]=[$res[0]];
            }else{
                foreach ($res as $tmp2){
                    if(!empty($tmp2))
                    $info[]=[$tmp2];
                }
            }
        }
        if(count($info)>0){
            return $info;
        }else{
            return "0";
        }

    }

    /**
     * @desc 优惠券单品列表
     * @param $input
     * @return string
     */
    public function getGoodsForCoupon($input="")
    {
        $baseData = BaseData::getInstance();
        if($input==""){
            $info = $baseData->select(
                'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                "\\Shop\\Models\\BaiyangGoods as a",
                [
                    //"is_global"=>1,
                    "is_on_sale"=>1,
                    "sale_timing_app"=>1,
                    "sale_timing_wap"=>1,
                    "sale_timing_wechat" => 1
                ],
                "(a.is_on_sale = :is_on_sale: OR a.sale_timing_app = :sale_timing_app: OR a.sale_timing_wap =:sale_timing_wap: OR a.sale_timing_wechat = :sale_timing_wechat: ) limit 20",
                "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id"
            );
            if($info!==false){
                return $info;
            }else{
                return "0";
            }
        }
        $input=explode(",",$input);
        $info=[];
        foreach ($input as $tmp){
            $res = $baseData->select(
                'a.id,a.goods_name,a.goods_price as price,a.is_unified_price,b.goods_price_pc,b.goods_price_wap,b.goods_price_app,b.goods_price_wechat,b.whether_is_gift,a.product_type,b.gift_pc,b.gift_app,b.gift_wap,b.gift_wechat,a.supplier_id',
                "\\Shop\\Models\\BaiyangGoods as a",
                [
                    "id"=>$tmp,
                    "goods_name"=>"%".$tmp."%",
                    //"is_global"=>1,
                    "is_on_sale"=>1,
                    "sale_timing_app"=>1,
                    "sale_timing_wap"=>1,
                    "sale_timing_wechat" => 1
                ],
                "( a.id = :id: OR a.goods_name LIKE :goods_name: ) AND (a.is_on_sale = :is_on_sale: OR a.sale_timing_app = :sale_timing_app: OR a.sale_timing_wap =:sale_timing_wap: OR a.sale_timing_wechat = :sale_timing_wechat: )",
                "LEFT JOIN \\Shop\\Models\\BaiyangSkuInfo as b on a.id = b.sku_id"
            );
            if(count($res)==1){
                if(isset($res[0]) && !empty($res[0]))
                $info[]=[$res[0]];
            }else{
                foreach ($res as $tmp2){
                    if(!empty($tmp2))
                    $info[]=[$tmp2];
                }
            }
        }
        if(count($info)>0){
            return $info;
        }else{
            return "0";
        }
    }

    /**
     * @desc 是否上下架
     * @param $platform 参与平台类型 ["pc","app","wap"] Array
     * @param $id 商品ids Int
     * @return array
     * @author 邓永军
     */
    public function is_on_shelf($platform,$id)
    {
        $baseData = BaseData::getInstance();
        $platform=array_flip($platform);
            $res = $baseData->select(
                'a.id,a.is_on_sale,a.sale_timing_app,a.sale_timing_wap,a.sale_timing_wechat,a.goods_name,a.sku_mobile_name',
                "\\Shop\\Models\\BaiyangGoods as a",
                [
                    "id"=>$id
                ],
                "a.id = :id:"
            )[0];
        foreach ($platform as &$vv){
            $vv ='baiyang';
        }
        $flag=1;
            $msg_arr=[];
            if(isset($platform["pc"]) && !empty($platform['pc'])){
                if($res['is_on_sale'] == 0) {
                    $flag = 0;
                    $msg_arr[] = "pc端";
                }
            }
            if(isset($platform["app"]) && !empty($platform['app'])){
                if($res['sale_timing_app'] == 0){
                    $flag = 0;
                    $msg_arr[] = "app端";
                }
            }
            if(isset($platform["wap"]) && !empty($platform['wap'])){
                if($res['sale_timing_wap'] == 0){
                    $flag = 0;
                    $msg_arr[] = "wap端";
                }
            }
        if(isset($platform["wechat"]) && !empty($platform['wechat'])){
            if($res['sale_timing_wechat'] == 0){
                $flag = 0;
                $msg_arr[] = "wechat端";
            }
        }
        if($flag == 1){return ["code"=>200,"msg"=>"All is well"];}else{
            $msg_list=implode('、',$msg_arr);
            return ["code"=>400,"msg"=>"商品:".$res['goods_name']."在".$msg_list.'已经下架'];
        }
    }

    /**
     * @desc 判断是否赠品
     * @param array $platform 参与平台类型 ["pc","app","wap"]
     * @param int $id 商品ids
     * @return array
     * @author 吴俊华
     */
    public function isGift($platform,$id)
    {
        $baseData = BaseData::getInstance();
        $condition = [
            'table' => 'Shop\Models\BaiyangGoods as a',
            'join' => 'left join Shop\Models\BaiyangSkuInfo as b on a.id = b.sku_id',
            'column' => 'a.goods_name,b.gift_pc,b.gift_app,b.gift_wap',
            'where' => 'where a.id = :id:',
            'bind' => ['id' => $id],
        ];
        $data = $baseData->getData($condition,true);
        if(empty($data)){
            return ["code" => 200,"msg" => "All is well"];
        }
        $flag = 1;
        $platform = array_flip($platform);
        foreach ($platform as &$vv){
            $vv ='baiyang';
        }
        $msgArr = [];
        if(isset($platform["pc"]) && !empty($platform['pc'])){
            if($data['gift_pc'] == 1) {
                $flag = 0;
                $msgArr[] = "pc端";
            }
        }
        if(isset($platform["app"]) && !empty($platform['app'])){
            if($data['gift_app'] == 1){
                $flag = 0;
                $msgArr[] = "app端";
            }
        }
        if(isset($platform["wap"]) && !empty($platform['wap'])){
            if($data['gift_wap'] == 1){
                $flag = 0;
                $msgArr[] = "wap端";
            }
        }
        if($flag == 1){
            return ["code"=>200,"msg"=>"All is well"];
        }else{
            $msgList = implode('、',$msgArr);
            return ["code" => 400,"msg"=>"商品:".$data['goods_name']."在".$msgList.'是赠品'];
        }
    }
}
