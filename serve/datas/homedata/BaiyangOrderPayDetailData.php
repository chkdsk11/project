<?php
/**
 * O2O的DATA类
 */

namespace Shop\Home\Datas;


class BaiyangOrderPayDetailData extends BaseData {

    protected static $instance = null;

    
    /**
     * @DESC   获取O2O 可配送范围列表
     * @return array
     * @author 陶铎云
     */
    public function getO2ORegionAll() {
        $where = array(
            'column' => 'id,county,city,province,type',
            'table' => $this->regoinTable,
        );

        return $this->getData($where);
    }

}