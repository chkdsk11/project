<?php
/**
 * Created by PhpStorm.
 * User: 吴俊华
 * Date: 2016/10/26 1504
 */
namespace Shop\Home\Datas;

class BaiyangGoodsTreatmentData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 根据各个平台获取商品疗程的相关信息
     * @param array $param
     *       -int|string goods_id 商品id(多个以逗号隔开)
     *       -string platform 平台【pc、app、wap】
     *       -int goods_number 商品数量 (可填,验证商品是否符合疗程价时填写)
     * @param bool $returnOne 返回一条或多条数据的开关(true为获取单条，false为获取多条)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getGoodsTreatment($param,$returnOne = true)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangGoodsTreatment',
            'column' => 'id,goods_id,min_goods_number,unit_price as price,promotion_msg,promotion_mutex as mutex',
            'bind' => [],
            'where' => "where platform_".$param['platform']." = 1 and status = 1 and goods_id in({$param['goods_id']})"
        ];
        //求最大件数对应的疗程价
        if($returnOne){
            $condition['bind'] = array_merge($condition['bind'],['min_goods_number' => (int)$param['goods_number']]);
            $condition['where'] .= ' and min_goods_number <= :min_goods_number: order by min_goods_number desc limit 1';
        }
        $data = $this->getData($condition,$returnOne);
        return $data;
    }
}