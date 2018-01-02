<?php
/**
 * Created by PhpStorm.
 * User: 杨永坚
 * Date: 2016/10/8
 * Time: 14:05
 */

namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangGoods;
use Shop\Models\BaiyangGoodsPriceTag;
use Shop\Models\BaiyangGoodsPrice;

class GoodsPriceData extends BaseData
{
    protected static $instance = null;

    public function selectJoin($selections, $tables, $conditions, $where)
    {
        $phql = "SELECT {$selections} FROM {$tables['priceTable']} LEFT JOIN {$tables['skuTable']} ON s.id = p.goods_id LEFT JOIN {$tables['tagTable']} ON p.tag_id = t.tag_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql, $conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    public function joinSku($selections, $tables, $conditions, $where)
    {
        $phql = "SELECT {$selections} FROM {$tables['priceTable']} INNER JOIN {$tables['skuTable']} ON s.id = p.goods_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql, $conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }
}