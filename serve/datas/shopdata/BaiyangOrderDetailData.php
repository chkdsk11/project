<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/5
 * Time: 17:28
 */

namespace Shop\Datas;

use Shop\Models\BaiyangOrderDetail;

class BaiyangOrderDetailData extends BaseData
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 查询所有订单商品信息
     * @param $orderSn array 母/子订单号
     * @param bool $isTotal 是否根据母订单号查询
     * @return bool
     * @author Chensonglu
     */
    public function getAllOrderGoods($orderSn, $isTotal = false)
    {
        if (!$orderSn) {
            return false;
        }
        if (is_array($orderSn)) {
            foreach ($orderSn as $key => $item) {
                $orderSn[$key] = "'{$item}'";
            }
            $where = $isTotal ? "od.total_sn IN (" . implode(',', $orderSn) .")" : "od.order_sn IN (" . implode(',', $orderSn) .")";
        } else {
            $where = $isTotal ? "od.total_sn = '{$orderSn}'" : "od.order_sn = '{$orderSn}'";
        }
        $column = "od.id,od.total_sn,od.order_sn,od.goods_id,od.goods_name,od.goods_image,od.price,od.unit_price,"
            . "od.goods_number,od.goods_type,spu.drug_type,g.rule_value0 name_id,g.rule_value1 name_id2,g.rule_value2 name_id3,od.promotion_price,"
            . "od.promotion_total,od.refund_goods_number,od.is_return";
        $sql = "SELECT {$column} FROM baiyang_order_detail od "
            . "LEFT JOIN baiyang_goods g ON g.id = od.goods_id "
            . "LEFT JOIN baiyang_spu spu ON spu.spu_id = g.spu_id "
            . "WHERE {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * 获取订单商品信息
     * @param $param
     *              - orderSn string 子订单号
     *              - totalSn string 母订单号
     *              - detailId int 订单商品对应ID
     * @return array|bool
     * @author Chensonglu
     */
    public function getOrderGoods($param)
    {
        $where = " WHERE 1";
        $data = [];
        if (isset($param['orderSn']) && $param['orderSn']) {
            $where .= " AND od.order_sn = '{$param['orderSn']}'";
        }
        if (isset($param['totalSn']) && $param['totalSn']) {
            $where .= " AND od.total_sn = '{$param['totalSn']}'";
        }
        if (isset($param['detailId']) && $param['detailId']) {
            $where .= " AND od.bind_id = {$param['detailId']}";
        }
        $column = "od.id,od.total_sn,od.order_sn,od.goods_id,od.goods_name,od.goods_image,od.price,od.unit_price,"
            . "od.goods_number,od.goods_type,spu.drug_type,g.rule_value0 name_id,g.rule_value1 name_id2,g.rule_value2 name_id3,od.promotion_price,"
            . "od.promotion_total,od.refund_goods_number,od.is_return";
        $sql = "SELECT {$column} FROM baiyang_order_detail od "
            . "LEFT JOIN baiyang_goods g ON g.id = od.goods_id "
            . "LEFT JOIN baiyang_spu spu ON spu.spu_id = g.spu_id "
            . "{$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * @desc 获得订单详情信息
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @param string $rw 读写行为
     * @param int $global 是否海外购订单 (1:海外购 0:普通订单)
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getOneOrderDetail(array $param, int $global = 0)
    {
        $table = $global ? 'Shop\Models\BaiyangKjOrderDetail as d' : 'Shop\Models\BaiyangOrderDetail as d';
        $condition = [
            'table' => $table,
            'join' => $param['join'],
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition);
    }

    public function getOrderDetail($orderSnStr)
    {
        if (!$orderSnStr) {
            return false;
        }
        $sql = "SELECT * FROM baiyang_order_detail WHERE order_sn IN ({$orderSnStr})";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }
}