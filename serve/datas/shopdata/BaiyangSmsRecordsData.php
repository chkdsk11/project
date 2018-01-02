<?php

/**
 * 短信发送列表
 * @author yanbo
 */

namespace Shop\Datas;

use Shop\Datas\BaseData;

class BaiyangSmsRecordsData extends BaseData {

    protected static $instance = null;

    /**
     * 获取数量
     * @param type $tables
     * @param type $conditions
     * @param type $where
     * @return boolean
     */
    public function countJoin($tables, $conditions, $where) {
        $sql = "SELECT count(r.record_id) as count FROM {$tables['records']} LEFT JOIN {$tables['provider']} ON r.provider_code = p.provider_code LEFT JOIN {$tables['client']} ON r.client_code = c.client_code {$where}";
        $result = $this->modelsManager->executeQuery($sql, $conditions)->getFirst();
        if ($result) {
            return $result->count;
        }
        return false;
    }

    /**
     * 列表数据
     * @param type $selections string
     * @param type $tables []
     * @param type $conditions []
     * @param type $where string
     * @param type $order string
     * @param type $limit string
     * @return []
     */
    public function selectJoin($selections, $tables, $conditions, $where, $order, $limit) {
        $sql = "SELECT {$selections} FROM {$tables['records']} LEFT JOIN {$tables['provider']} ON r.provider_code = p.provider_code LEFT JOIN {$tables['client']} ON r.client_code = c.client_code {$where} {$order} {$limit}";
        $result = $this->modelsManager->executeQuery($sql, $conditions);
        if (count($result) > 0) {
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * 获取短信发送条数
     * @param type $selections
     * @param type $table
     * @param type $conditions
     * @param type $where
     * @return boolean
     */
    public function countRecord($selections, $table, $conditions, $where) {
        $sql = "SELECT {$selections} FROM {$table} {$where} GROUP BY provider_code";
        $result = $this->modelsManager->executeQuery($sql, $conditions);
        if (count($result) > 0) {
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * 根据供应商、分钟或小时分组，返回单位时间发送短信数量
     * @param type $sendname send/rsend
     * @param type $unit hour/minute
     * @param type $starttime 开始时间
     * @param type $endtime  结束时间
     * @return array
     */
    public function groupCount($sendname, $unit, $starttime, $endtime) {
        //发送类型
        if ($sendname == "send") {
            $send_type = 0;
        } else {
            $send_type = 1;
        }
        $conditions = array(
            'send_type' => $send_type,
            'starttime' => $starttime,
            'endtime' => $endtime
        );
        if ($unit == "minute") { //分钟
            $sql = "SELECT provider_code, FROM_UNIXTIME(UNIX_TIMESTAMP(create_time),'%H时%i分') as xunit,COUNT(*) as count FROM \Shop\Models\BaiyangSmsRecords WHERE is_success = 0 AND send_type = :send_type: AND (create_time BETWEEN :starttime: AND :endtime:) GROUP BY provider_code, DATE(create_time), HOUR(create_time), MINUTE(create_time) ORDER BY create_time ASC";
        } else {  //小时
            $sql = "SELECT provider_code, FROM_UNIXTIME(UNIX_TIMESTAMP(create_time),'%d日%H时') as xunit,COUNT(*) as count FROM \Shop\Models\BaiyangSmsRecords WHERE is_success = 0 AND send_type = :send_type: AND (create_time BETWEEN :starttime: AND :endtime:) GROUP BY provider_code, DATE(create_time), HOUR(create_time) ORDER BY create_time ASC";
        }
        $result = $this->modelsManager->executeQuery($sql, $conditions);
        if (count($result) > 0) {
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

}
