<?php
/**
 * O2O的DATA类
 */

namespace Shop\Home\Datas;

class BaiyangO2oData extends BaseData {

    protected static $instance = null;

    private $templateTable = '\Shop\Models\BaiyangO2oFreightTemplate';
    private $regoinTable = '\Shop\Models\BaiyangO2oRegion';
    private $timeTable = '\Shop\Models\BaiyangO2oTime';
    private $baiyangRegoinTable = '\Shop\Models\BaiyangRegion';

    /**
     * @DESC   获取O2O地区的配送方式
     * @param  int $region_id
     * @return int
     * @author 柯琼远
     */
    public function getO2OType($region_id) {
        $where = array(
            'column' => 'type',
            'table' => $this->regoinTable,
            'where' => 'where county = :region_id:',
            'bind' => ['region_id' => $region_id]
        );

        $result = $this->getData($where, true);
        return empty($result) ? 0 : (int)$result['type'];
    }

    /**
     * @DESC   获取O2O时间信息
     * @return array
     * @author 柯琼远
     */
    public function getO2OTime() {
        $where = array(
            'column' => 'type,begin_time,end_time',
            'table' => $this->timeTable,
        );
        return $this->getData($where);
    }

    /**
     * @DESC   获取O2O运费模板信息
     * @param  int $region_id
     * @param  int $type
     * @return array
     * @author 柯琼远
     */
    public function getO2OTemplate($region_id, $type) {
        $where = array(
            'column' => 'free_price,col_0,col_1,col_2,col_3,col_4',
            'table' => $this->templateTable,
            'where' => 'where county = :region_id: and type = :type: and is_default = 0',
            'bind'  => ['region_id'=> $region_id, 'type'=> $type]
        );
        $result = $this->getData($where, true);
        if (empty($result)) {
            $where = array(
                'column' => 'free_price,col_0,col_1,col_2,col_3,col_4',
                'table' => $this->templateTable,
                'where' => 'where type = :type: and is_default = 1',
                'bind'  => ['type'=> $type]
            );
            $result = $this->getData($where, true);
        }
        return $result;
    }

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


    /** 根据 地区名字获取相关记录
     * @param $name
     * @return array|bool
     * @author 陶铎云
     */
    public function getRegionByName($name) {
        $where = array(
            'column' => 'id,region_name,true_name',
            'table' => $this->baiyangRegoinTable,
            'where' => "where region_name = '" . trim($name) . "'",
        );

        return $this->getData($where);
    }

}