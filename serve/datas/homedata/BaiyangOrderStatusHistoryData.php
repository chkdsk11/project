<?php
/**
 * 订单发货信息表
 * @author 秦亮
 */
namespace Shop\Home\Datas;
class BaiyangOrderStatusHistoryData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 根据子订单编号获取订单发货信息
     * @param string $order_sn 子订单编号
     * @param bool/false $returnOne 是否查询多条
     * @return array [] 结果信息
     * @author 秦亮
     */
    public function getOrderHistory($order_sn, $returnOne = false)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderStatusHistory',
            'column' => 'status',
            'bind' => [
                'order_sn' => $order_sn,
            ],
            'where' => "where order_sn = :order_sn: order by date_created desc",
        ];
        return $this->getData($condition, $returnOne);
    }
}