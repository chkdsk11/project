<?php
/**
 * 店铺信息
 * User: 秦亮
 */
namespace Shop\Home\Datas;

use Shop\Home\Datas\BaseData;

class BaiyangSkuSupplierData extends BaseData
{
    protected static $instance = null;
    
    /**
     * 获取用户信息
     *
     * @param int $userId  用户id
     * @return array
     */
    public function getSupplier($shop_id, $returnOne = false)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangSkuSupplier',
            'column' => 'user_name,address',
            'where' => 'where id = :id:' ,
            'bind' => [
                'id'=> (int)$shop_id
            ],
        ];
        return  $this->getData($condition, $returnOne);
    }
}