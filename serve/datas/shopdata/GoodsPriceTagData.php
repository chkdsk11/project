<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/10/8
 * Time: 14:05
 */

namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangGoodsPriceTag;
use Shop\Models\BaiyangGoodsPrice;

class GoodsPriceTagData extends BaseData
{
    protected static $instance = null;

    public function selectJoin($selections, $tables, $conditions, $where)
    {
        $phql = "SELECT {$selections} FROM {$tables['tagTable']} LEFT JOIN {$tables['priceTable']} ON t.tag_id = p.tag_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql, $conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    public function selectJoinUser($selections, $tables, $conditions, $where)
    {
        $phql = "SELECT {$selections} FROM {$tables['tagTable']} LEFT JOIN {$tables['priceTable']} ON t.tag_id = g.tag_id LEFT JOIN {$tables['userTable']} ON t.user_id = u.id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql, $conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }
}