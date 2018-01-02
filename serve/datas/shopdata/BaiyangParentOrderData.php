<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/6/27
 * Time: 15:21
 */

namespace Shop\Datas;

use Shop\Datas\BaseData;
use Shop\Models\BaiyangParentOrder;

class BaiyangParentOrderData extends BaseData
{
    protected static $instance=null;

    /**
     * 查询母订单信息
     * @param $totalSn array 母订单号
     * @param bool $isOne 是否获取一条
     * @return bool/array 母订单信息
     * @author Chensonglu
     */
    public function getParentOrder($totalSn, $isOne = true)
    {
        if (!$totalSn) {
            return false;
        } elseif (is_array($totalSn)) {
            foreach ($totalSn as $k => $val) {
                $totalSn[$k] = "'{$val}'";
            }
            $totalSnStr = implode(',', $totalSn);
        } else {
            $totalSnStr = $totalSn;
        }
        $where = $isOne ? "AND total_sn = '{$totalSnStr}'" : "AND total_sn IN ({$totalSnStr})";
        $sql = "SELECT * FROM baiyang_parent_order WHERE 1 {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $isOne ? $stmt->fetch(\PDO::FETCH_ASSOC) : $stmt->fetchall(\PDO::FETCH_ASSOC);//判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }
}