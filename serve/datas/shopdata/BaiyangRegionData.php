<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/22
 * Time: 10:21
 */

namespace Shop\Datas;

use Shop\Models\BaiyangRegion;
use Shop\Models\CacheKey;

class BaiyangRegionData extends BaseData
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 根据地区ID获取地区名
     * @param $regionId 地区ID
     * @return bool
     * @author Chensonglu
     */
    public function getRegionName($regionId)
    {
        if (!$regionId || !is_numeric($regionId)) {
            return false;
        }
        $result = $this->getData([
            'column' => 'region_name',
            'table' => 'Shop\Models\BaiyangRegion',
            'where' => 'WHERE id = :regionId:',
            'bind' => [
                'regionId' => $regionId
            ],
        ], true);
        return isset($result['region_name']) ? $result['region_name'] : false;
    }

    /**
     * 根据父ID获取子所有地区信息
     * @param int $pid 父ID
     * @return array|bool
     * @author Chensonglu
     */
    public function getChildRegion($pid = 1)
    {
        if (!$pid || !is_numeric($pid)) {
            return false;
        }
        $result = $this->getData([
            'column' => 'id,region_name',
            'table' => 'Shop\Models\BaiyangRegion',
            'where' => 'WHERE pid = :pid:',
            'bind' => [
                'pid' => $pid
            ],
        ]);
        $childRegion = [];
        if ($result) {
            foreach ($result as $value) {
                $childRegion[$value['id']] = $value['region_name'];
            }
        }
        return $childRegion;
    }

    /**
     * 获取所有地区
     * @return mixed
     * @author Chensonglu
     */
    public function getRegionAll()
    {
        $this->cache->selectDb(0);
        $allRegion = $this->cache->getValue(CacheKey::ALL_Region);
        if (!$allRegion) {
            $result = $this->getData([
                'column' => 'id,region_name',
                'table' => 'Shop\Models\BaiyangRegion',
            ]);
            $allRegion = array_column($result, "region_name", "id");
            $this->cache->setValue(CacheKey::ALL_Region,$allRegion);
        }
        return $allRegion;
    }
}