<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/22
 * Time: 17:40
 */

namespace Shop\Datas;

use Shop\Models\BaiyangOrderGoodsReturnReason;

class BaiyangOrderGoodsReturnReasonData extends BaseData
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;


    /**
     * 服务单总数
     * @param string $where 查询条件
     * @param string $join 关联表
     * @return bool|int 记录数
     * @author Chensonglu
     */
    public function getRefundNum($where = '', $join = '')
    {
        $sql = "SELECT COUNT(DISTINCT ogrr.service_sn) AS counts FROM baiyang_order_goods_return_reason ogrr {$join} {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (isset($ret['counts'])) {
            return (int)$ret['counts'];
        }
        return false;
    }

    /**
     * 查询所有服务单数据
     * @param string $column 查询字段
     * @param string $where 条件
     * @param string $join 联表
     * @param string $group 分组
     * @param string $order 排序
     * @param string $limit 分页/限制条数
     * @return bool
     * @author Chensonglu
     */
    public function getRefundAll($column = '*',$where = '',$join = '',$group = '',$order = '',$limit = '')
    {
        $sql = "SELECT {$column} FROM baiyang_order_goods_return_reason ogrr {$join} {$where} {$group} {$order} {$limit}";
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