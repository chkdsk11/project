<?php
/**
 * 运费模块的DATA类
 */

namespace Shop\Home\Datas;


class BaiyangFreghtTemplate extends BaseData {
    // 单例
    protected static $instance = null;

    // 表名
    private $groupTable = '\Shop\Models\BaiyangFreightTemplateGroup';
    private $detailTable = '\Shop\Models\BaiyangFreightTemplateDetail';

    /**
     * @DESC   获取运费模板信息
     * @param  int $template_id   如果为0获取默认运费模板
     * @param  int $is_global     是否海外购模板
     * @return int
     * @author 柯琼远
     */
    public function getFreightTemplate($template_id, $is_global) {
        $whereArr = array('state = 1 and value_type = 1');
        $whereArr[] = "is_global = " . (int)$is_global;
        $template_id = (int)$template_id;
        $whereArr[] = $template_id == 0 ? "is_default = 1" : "id = " . $template_id;
        $where = array(
            'column' => 'id,value_type',
            'table'  => $this->groupTable,
            'where'  => 'where ' . implode(' and ', $whereArr),
        );
        return $this->getData($where, true);
    }

    /**
     * @DESC   获取一个运费模板的明细
     * @param  int $template_id   如果为0获取默认运费模板
     * @param  int $type     类型(0-在线支付；1-货到付款；2-顾客自提)
     * @return int
     * @author 柯琼远
     */
    public function getTemplateDetail($template_id, $type) {
        $template_id = (int)$template_id;
        $type = (int)$type;
        $where = array(
            'column' => 'template_id,default_value,default_fee,region_list,is_default',
            'table'  => $this->detailTable,
            'where'  => "where template_id = {$template_id} and type = {$type}",
        );
        return $this->getData($where);
    }

    /**
     * @desc 获取默认包邮价格
     * @author 柯琼远
     */
    public function getFreightFreePrice() {
        $where = array(
            'column' => 'd.default_value,g.is_global',
            'table'  => $this->groupTable . " as g",
            'join'   => 'left join ' . $this->detailTable . ' as d on g.id = d.template_id',
            'where'  => "where g.is_default = 1 and d.type = 0 and d.is_default = 1",
        );
        $ret = $this->getData($where);
        $result = array();
        foreach ($ret as $key => $value) {
            if ($value['is_global'] == 0) {
                $result[0] = $value['default_value'];
            } else {
                $result[1] = $value['default_value'];
            }
        }
        return $result;
    }
}