<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/25
 * Time: 10:47
 */

namespace Shop\Home\Datas;

use Shop\Models\BaiyangOrderGoodsReturn;

class BaiyangOrderGoodsReturnData extends BaseData
{
    protected static $instance=null;

    /**
     * 根据服务单号获取退款商品信息
     * @param $serviceSn
     * @return array|bool
     * @author Chensonglu
     */
    public function getReturnGoods($serviceSn)
    {
        if (!$serviceSn) {
            return false;
        }
        $column = "ogrr.service_sn,ogrr.order_sn,od.goods_id,ogr.refund_goods_number change_num,od.stock_type,"
            . "o.channel_subid channel";
        $table = "Shop\Models\BaiyangOrderGoodsReturn ogr";
        $join = " INNER JOIN Shop\Models\baiyangOrderGoodsReturnReason ogrr ON ogrr.id = ogr.reason_id"
            . " INNER JOIN Shop\Models\baiyangOrder o ON o.order_sn = ogrr.order_sn"
            . " INNER JOIN Shop\Models\BaiyangOrderDetail od ON od.id = ogr.order_goods_id";
        $condition = [
            'table' => $table,
            'join' => $join,
            'column' => $column,
            'where' => "WHERE ogrr.service_sn = :serviceSn:",
            'bind' => [
                'serviceSn' => $serviceSn,
            ],
        ];
        return $this->getData($condition);
    }
}