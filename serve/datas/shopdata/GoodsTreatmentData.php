<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/9/29
 * Time: 17:16
 */

namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangGoodsTreatment;

class GoodsTreatmentData extends BaseData
{
    protected static $instance = null;

    public function selectJoin($selections, $tables, $conditions, $where)
    {
        $phql = "SELECT {$selections} FROM {$tables['skuTable']} INNER JOIN {$tables['goodsTreatmentTable']} ON g.id = t.goods_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql, $conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }
}