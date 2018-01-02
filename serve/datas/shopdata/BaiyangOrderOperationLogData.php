<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/18
 * Time: 17:09
 */

namespace Shop\Datas;

use Shop\Models\BaiyangOrderOperationLog;

class BaiyangOrderOperationLogData extends BaseData
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 添加订单、服务单操作日志及备注
     * @param $param
     * @param int $time
     * @return bool|string
     * @author Chensonglu
     */
    public function addOperationLog($param, $time = 0)
    {
        if (!isset($param['belong_sn']) || !isset($param['belong_type']) || !isset($param['content']) || !isset($param['operation_type'])) {
            return false;
        }
        $time = !$time ? time() : $time;
        $param['operator_id'] = $this->session->get('admin_id');
        $param['add_time'] = $time;
        $param['operation_log'] = isset($param['operation_log']) ? $param['operation_log'] : '';
        return $this->insert('Shop\Models\BaiyangOrderOperationLog',$param);
    }

    /**
     * 查询所有订单/服务单备注总数
     * @param $param array 订单号/服务单号
     * @return array|bool
     * @author Chensonglu
     */
    public function getAllRemarkNum($param)
    {
        if (!$param) {
            return false;
        }
        if (is_array($param)) {
            foreach ($param as $key => $item) {
                $param[$key] = "'{$item}'";
            }
            $belongSn = implode(',', $param);
        } else {
            $belongSn = $param;
        }
        $sql = "SELECT belong_sn,COUNT(1) counts FROM baiyang_order_operation_log "
            . "WHERE belong_sn IN ({$belongSn}) AND operation_type = 1 GROUP BY belong_sn";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return array_column($ret, 'counts', 'belong_sn');
        }
        return false;
    }

    /**
     * 备注总数
     * @param $belongSn string 号
     * @return bool|int
     * @author Chensonglu
     */
    public function remarkNum($belongSn)
    {
        return $this->countData([
            'table' => "Shop\Models\BaiyangOrderOperationLog",
            'where' => "WHERE operation_type = :type: AND belong_sn = :belongSn:",
            'bind' => [
                'type' => 1,
                'belongSn' => $belongSn,
            ],
        ]);
    }

    /**
     * 获取操作日志
     * @param $param
     *              - orderSn 订单号
     *              - serviceSn 服务单号
     *              - type 操作类型 1、添加备注 2、审核 3、退款
     * @return array
     * @author Chensonglu
     */
    public function getOperationLog($param)
    {
        $where = " WHERE 1";
        $limit = "";
        $data = [];
        $returnOne = false;
        if (isset($param['orderSn']) && $param['orderSn']) {
            $where .= " AND opl.belong_sn = :orderSn: AND belong_type = 1";
            $data['orderSn'] = $param['orderSn'];
        }
        if (isset($param['serviceSn']) && $param['serviceSn']) {
            $where .= " AND opl.belong_sn = :serviceSn: AND belong_type = 2";
            $data['serviceSn'] = $param['serviceSn'];
        }
        if (isset($param['type']) && $param['type']) {
            $where .= " AND opl.operation_type = :type:";
            $data['type'] = $param['type'];
            $limit = " LIMIT 1";
            $returnOne  = true;
        }
        if (!$data) {
            return false;
        }
        return $this->getData([
            'column' => 'opl.belong_sn,opl.content,opl.operation_type,opl.add_time,au.admin_account username',
            'table' => 'Shop\Models\BaiyangOrderOperationLog opl',
            'join' => 'LEFT JOIN Shop\Models\BaiyangAdmin au ON opl.operator_id = au.id',
            'where' => $where,
            'bind' => $data,
            'order' => 'ORDER BY opl.add_time DESC',
            'limit' => $limit,
        ], $returnOne);
    }
}