<?php
/**
 * Created by PhpStorm.
 * User: sarcasme
 */

namespace Shop\Home\Datas;

use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangOrder;
use Shop\Models\BaiyangOrderDetail;
use Shop\Models\BaiyangKjOrderDetail;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Shop\Models\BaiyangOrderGoodsReturnReason;
use Shop\Models\BaiyangPromotionEnum;

class BaiyangKjOrderDetailData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 插入订单详情
     * @param $param
     * @return bool
     * @author sarcasme
     */
    public function insertOrderDetail($param) {
        foreach ($param['goodsList'] as $key => $item) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangKjOrderDetail',
                'bind'  => array(
	                'total_sn'          => $param['orderSn'],
	                'order_sn'          => $param['orderSn'],
	                'goods_id'          => $item['goods_id'],
		            'goods_order'       => $key+1,
	                'goods_name'        => $item['goods_name'],
	                'goods_custom_name' => $item['goods_custom_name'],
	                'code_ts'           => $item['code_ts'],
	                'item_record_no'    => $item['item_record_no'],
	                'goods_model'       => '无',
	                'goods_unit'        => $item['goods_unit'],
	                'goods_tax_amount'  =>  $item['goods_tax_amount'],
	                'tax_rate'          =>  $item['rate'],
	                'goods_image'       => $item['goods_image'],
	                'price'             => bcmul($item['goods_number'], $item['sku_price'], 2),
	                'unit_price'        => $item['sku_price'],
                    'promotion_total'   => bcmul($item['goods_number'], $item['sku_price'], 2),
                    'promotion_price'   => $item['sku_price'],
	                'discount_price'    => 0,
	                'discount_remark'   => 0,
	                'goods_number'      => $item['goods_number'],
	                'goods_type'        => 0,
	                'specifications'    => $item['specifications'],
	                'is_comment'        => 0,
	                'is_return'         => 0,
	                'add_time'          => time(),
	                'stock_type'        =>  $item['stock_type'],
	                'market_price'      =>  $item['market_price'],
                )
            );
            $ret = $this->addData($addData);
            if (!$ret) {
                return false;
            }
        }
        return true;
    }
}