<?php

/**
 * 短信供应商
 * @author yanbo
 */

namespace Shop\Datas;

use Shop\Datas\BaseData;

class BaiyangSmsProviderData extends BaseData {

    protected static $instance = null;

    /**
     * 密码修改记录数量
     * @param type $tables
     * @param type $conditions
     * @param type $whereStr
     * @return int
     */
    public function countJoin($tables, $conditions, $whereStr) {
        $sql = "SELECT count(d.id) as count FROM {$tables['password']} INNER JOIN {$tables['provider']} ON d.provider_id = p.provider_id LEFT JOIN {$tables['admin']} ON d.create_at = a.id {$whereStr}";
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
    public function selectJoin($selections, $tables, $conditions, $whereStr, $order, $limit) {
        $sql = "SELECT {$selections} FROM {$tables['password']} INNER JOIN {$tables['provider']} ON d.provider_id = p.provider_id LEFT JOIN {$tables['admin']} ON d.create_at = a.id {$whereStr} {$order} {$limit}";
        $result = $this->modelsManager->executeQuery($sql, $conditions);
        if (count($result) > 0) {
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * 获取各个服务商密码最新修改记录，返回记录的id
     * @param type $ids array
     * @return []
     */
    public function newIds($ids) {
        $return_ids = [];
        if (empty($ids) == false) {
            foreach ($ids as $k => $v) {
                $sql = "SELECT id FROM \Shop\Models\BaiyangSmsProviderPasswords WHERE provider_id = {$v} ORDER BY create_time DESC LIMIT 1";
                $result = $this->modelsManager->executeQuery($sql)->getFirst();
                if ($result) {
                    $return_ids[] = $result->id;
                }
            }
        }
        return $return_ids;
    }

}
