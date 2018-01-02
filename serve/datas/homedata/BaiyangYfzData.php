<?php
/**
 * Created by PhpStorm.
 * User: Sary
 * Date: 2016/12/26
 * Time: 18:36
 */
namespace Shop\Home\Datas;

use Shop\Home\Datas\BaseData;

class BaiyangYfzData extends BaseData
{
    protected static $instance = null;

    /**
     * 获取处方药商品列表
     *
     * @param array $param
     * @return array|bool
     */
    public function getPrescriptionGoodsList($param)
    {
        $params['table'] = 'Shop\Models\BaiyangPrescription AS p';
        $params['column'] = 'p.prescription_id,p.yfz_prescription_id,p.create_time,pg.good_id,pg.good_number';
        $params['join'] = 'INNER JOIN Shop\Models\BaiyangPrescriptionGoods AS pg ON p.prescription_id = pg.prescription_id';
        $params['where'] = ' WHERE p.union_user_id= :union_user_id: ';
        $params['bind']['union_user_id'] = $param['union_user_id'];
        if (isset($param['goods_id_list'])) {
            #$params['where'] .= ' AND pg.good_id IN(:goods_id:)';
            #$params['bind']['goods_id'] = $param['goods_id_list'];

            $goods = explode(',',$param['goods_id_list']);
            $goods_val = explode(',',$param['goods_id_list_val']);
            $new_goods = array();
            foreach($goods as $k=>$row){
                $arr = array('good_id'=>$row,'good_number'=>$goods_val[$k]);
                $new_goods[] = $arr;
            }
            return $new_goods;
        } else {
            $params['where'] .= ' AND p.yfz_prescription_id = :yfz_prescription_id: ';
            $params['bind']['yfz_prescription_id'] = $param['yfz_prescription_id'];
        }
        $params['where'] .= ' AND p.status=1 AND p.exp_time >' . time();
        $ret = $this->getData($params);
        return $ret;
    }

    /**
     * @desc 获得易复诊处方单信息
     * @param array $param
     *      -string column  字段
     *      -string where  条件
     *      -array bind  绑定参数
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getPrescriptionInfo(array $param)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangPrescription',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition, true);
    }

    /**
     * @desc 获得易复诊处方单信息
     * @param array $param
     * @return array [] 结果信息
     * @author  柯琼远
     */
    public function pushYfz($param) {
        if (!empty($param['unionUserId'])) {
            $status = $param['status'] == "paying" ? "paying" : 'paid';
            $result = $this->func->prescriptionMatchOrder($param['unionUserId'], $param['orderSn'], $status, 1);
            if ($result['code'] == 31091) {
                $this->log->error("ERROR:提交订单易复诊同步状态失败[ORDER_SN:{$param['orderSn']}]" . print_r($result,1));
                return false;
            } elseif ($result['code'] == 200) {
                foreach ($param['goodsList'] as $k => $v) {
                    if (isset($result['data'][$v['goods_id']])) {
                        $tableName = $param['isGlobal'] ? 'BaiyangKjOrderDetail' : 'BaiyangOrderDetail';
                        $this->updateData([
                            'table' => "\\Shop\\Models\\{$tableName}",
                            'column' => "promotion_origin = '{$result['data'][$v['goods_id']]}', promotion_origin = '3'",
                            'where' => "where order_sn = '{$param['orderSn']}' and goods_id = '{$v['goods_id']}'"
                        ]);
                    }
                }
            }
        }
        return true;
    }
}