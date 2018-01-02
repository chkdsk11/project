<?php
/**
 * 配送公告类
 */

namespace Shop\Home\Datas;

class BaiyangAnnouncementData extends BaseData {

    protected static $instance = null;

    /**
     * @DESC   获取配送公告
     * @param $consigneeInfo []
     * @return string
     * @author 柯琼远
     */
    public function getAnnouncement($consigneeInfo) {
        $result = "";
        if (empty($consigneeInfo)) {
            return $result;
        }
        $where = array(
            'column' => 'content,region_str_id',
            'table' => '\Shop\Models\BaiyangAnnouncement',
            'order' => 'order by add_time DESC',
        );
        $announcementList = $this->getData($where);
        foreach ($announcementList as $key => $value) {
            $region_str_id = ',' . $value['region_str_id']. ',';
            if (strpos($region_str_id, ',all,')                                !== false
                ||  strpos($region_str_id, ',' . $consigneeInfo['province'] . ',') !== false
                ||  strpos($region_str_id, ',' . $consigneeInfo['city'] . ',')     !== false
                ||  strpos($region_str_id, ',' . $consigneeInfo['county'] . ',')   !== false ) {
                $result = $value['content'];break;
            }
        }
        return $result;
    }

}