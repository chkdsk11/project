<?php

/**
 * 警戒值设置
 * @author yanbo
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;

class SmsAlarmService extends BaseService {

    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;

    /**
     *  警戒值设置列表
     * @return array
     */
    public function getList() {
        $param = array(
            'column' => '*',
            'table' => '\Shop\Models\BaiyangSmsAlarm'
        );
        $result = BaseData::getInstance()->getData($param);
        $list = [];
        if ($result) {
            foreach ($result as $k => $v) {
                $list[$v['alarm_group_code']]['name'] = $v['alarm_group_name'];
                $list[$v['alarm_group_code']]['list'][] = $v;
            }
        }
        return $list;
    }

    /**
     * 修改警戒值
     * @param type $param
     * @return type array
     */
    public function editAlarm($param) {
        if (is_array($param) && empty($param) == false) {
            foreach ($param as $k => $v) {
                $arr = [];
                $arr['alarm_id'] = (int) $v->name;
                $arr['alarm_value'] = (int) $v->value;
                $columStr = $this->jointString($arr, array('alarm_id'));
                $where = "alarm_id = :alarm_id:";
                BaseData::getInstance()->update($columStr, '\Shop\Models\BaiyangSmsAlarm', $arr, $where);
            }
            return $this->arrayData('修改成功！');
        }
        return $this->arrayData('修改失败', '', '', 'err');
    }

}
