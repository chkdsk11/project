<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangGoods;

class BaiyangGoodsData extends BaseData
{
    protected static $instance=null;
    public function selectJoin($selections, $tables, $conditions, $where)
    {
        $phql = "SELECT {$selections} FROM {$tables['goodsTable']} LEFT JOIN {$tables['skuInfoTable']} ON g.id = s.sku_id WHERE {$where}";
        $result = $this->modelsManager->executeQuery($phql, $conditions);
        if(count($result) > 0){
            $result = $result->toArray();
            return $result;
        }
        return false;
    }

    /**
     * 获取商品信息
     * @param int $where 查询条件
     * @param string $selections 查询字段
     * @return mixed
     * @author Chensonglu
     */
    public function getGoods($where = 1, $selections = '*')
    {
        $sql = "SELECT {$selections} FROM baiyang_goods WHERE {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }
}