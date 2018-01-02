<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Listens;

use Shop\Home\Datas\BaiyangSkuData;
use Shop\Models\HttpStatus;

class PromotionGoodset extends BaseListen
{

    /**
     * @desc 获取套餐列表
     * @param $event
     * @param $class
     * @param $data_all
     *          -platform 【pc、app、wap】 平台
     *          -goods_id 多个个商品id
     * @return array
     *          -data
     *              -id 套餐id
     *              -group_name 套餐名字
     *              -group_introduction 套餐介绍
     *              -start_time 开始时间
     *              -end_time 结束时间
     *              -sku_info
     *                  -goods_name 商品名字
     *                  -medicine_type 处方药类型
     *                  -goods_image 商品图片
     *                  -favourable_price 价格
     *                  -favourable_goods_number 数量
     *                  -specifications 规格
     *                  -original_price 原价
     *                  -on_shelf 上架状态 1 上架 0 下架
     *                  -is_stockout 缺货
     *             -total_favourable_price 总套餐价格
     *             -total_original_price 总原价
     *          -max_save_money 最大省钱数
     */
    public function getGoodSetList($event,$class,$data_all)
    {
       return $this->GoodSet($data_all);
    }
}