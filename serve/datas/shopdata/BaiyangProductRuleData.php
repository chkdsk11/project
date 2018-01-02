<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangProductRule;
use Shop\Models\CacheKey;

class BaiyangProductRuleData extends BaseData
{
    protected static $instance=null;

    /**
     * 获取商品品规
     * @param $ruleId 品规ID
     * @return array|bool
     * @author Chensonglu
     */
    public function getAllGoodsRule()
    {
        $this->cache->selectDb(0);
        $allRule = $this->cache->getValue(CacheKey::ALL_PRODUCT_RULE);
        if (!$allRule) {
            $result = $this->getData([
                'column' => 'id,name',
                'table' => 'Shop\Models\BaiyangProductRule',
            ]);
            $allRule = array_column($result, 'name', 'id');
            $this->cache->setValue(CacheKey::ALL_PRODUCT_RULE,$allRule);
        }
        return $allRule;
    }
}