<?php
/**
 * 广告.
 * User: ZHQ
 */
namespace Shop\Home\Datas;

use Shop\Home\Datas\BaseData;

class BaiyangAdData extends BaseData
{
    protected static $instance = null;

    /**
     * 按广告名获取广告列表
     *
     * @param string $adName 广告名称
     * @param $limitNumber 限制数量
     * @return array|bool
     */
    public function getAdByNameList($adName, $limitNumber)
    {
        $nowTime = time();
        $param = array(
            'table' => 'Shop\Models\AdPosition AS ap',
            'column' => 'ad.advertisement_id ad_id,ad.advertisement title,ad.image_url,ad.location,ad.advertisement_desc content',
            'join' => 'LEFT JOIN Shop\Models\Advertisements AS ad ON ad.adp_id=ap.id',
            'limit' => 'LIMIT ' . $limitNumber,
            'order' => 'ORDER BY ad.update_time DESC',
            'where' => "WHERE ap.adpositionid_name like :ad_name: and  ap.status=1 and ad.end_time > {$nowTime}",
            'bind' => array('ad_name' => "%{$adName}%")
        );
        $ret = $this->getData($param);
        return $ret;
    }
}