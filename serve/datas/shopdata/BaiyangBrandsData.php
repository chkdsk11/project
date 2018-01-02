<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/6/27
 * Time: 9:30
 */

namespace Shop\Datas;

use Shop\Datas\BaseData;
use Shop\Models\BaiyangBrands;

class BaiyangBrandsData extends BaseData
{
    protected static $instance=null;

    /**
     * 获取品牌信息
     * @param int $where 查询条件
     * @param string $selections 查询字段
     * @return mixed
     * @author Chensonglu
     */
    public function getBrand($where = 1, $selections = '*')
    {
        $sql = "SELECT {$selections} FROM baiyang_brands WHERE {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        return $stmt->fetchall(\PDO::FETCH_ASSOC);
    }

}