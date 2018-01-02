<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/11/18 0018
 * Time: 上午 9:42
 */

namespace Shop\Home\Datas;

use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangOrder;
use Shop\Models\BaiyangOrderDetail;
use Shop\Models\BaiyangKjOrderDetail;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Shop\Models\BaiyangOrderGoodsReturnReason;
use Shop\Models\BaiyangPromotionEnum;

class BaiyangO2OrderDetailData extends BaseData
{
    protected static $instance=null;

    /**
     * @desc 根据条件获得订单列表的订单详情数据 (需要连表查：药物类型)
     * @param $param array
     *      -string column  字段
     *      -string where   条件
     *      -string order   排序
     * @param string $rw  读写行为
     * @param int $global  是否海外购订单 (1:海外购 0:普通订单)
     * @return array [] 结果信息
     * @author 吴俊华
     *
     */
    public function getOrderDetail($param, $rw = 'read', $global = 0)
    {
        //读写切换
        $db = $this->switchRwDb($rw);
        $table = $global ? 'Shop\Models\BaiyangKjOrderDetail a' : 'Shop\Models\BaiyangOrderDetail a';
        $condition = [
            'table' => $table,
            'join' => 'left join Shop\Models\BaiyangGoods b on b.id = a.goods_id left join Shop\Models\BaiyangSpu c on c.spu_id = b.spu_id',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition);
    }

    /**
     * 得到订单的售后信息
     * @param array $param
     *      -column string
     *      -where string
     * @return []
     * @author 康涛
     */
    public function getOrderDetailReturn(array $param)
    {
       $phql="select {$param['column']} from Shop\Models\BaiyangOrderGoodsReturnReason";
       if(isset($param['where']) && !empty($param['where'])){
           $phql.=" where {$param['where']}";
       }
        $ret=$this->modelsManager->executeQuery($phql);
        if(count($ret)){
            return $ret->toArray();
        }
        return [];
    }

    /**
     * @desc 获得订单详情信息
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @param string $rw 读写行为
     * @param int $global 是否海外购订单 (1:海外购 0:普通订单)
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getOneOrderDetail(array $param, string $rw = 'read', int $global = 0)
    {
        //切换读写
        $this->switchRwDb($rw);
        $table = $global ? 'Shop\Models\BaiyangKjOrderDetail' : 'Shop\Models\BaiyangOrderDetail';
        $condition = [
            'table' => $table,
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition);
    }

    /**
     * @desc 根据订单编号获取订单商品信息
     * @param string $orderSn 订单编号
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getOrderDetailByOrderSn($orderSn)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderDetail',
            'column' => 'id',
            'where' => "where order_sn = :order_sn:",
            'bind' => [
                'order_sn' => $orderSn
            ],
        ];
        return $this->getData($condition);
    }

    /**
     * @desc 获取订单的商品评价信息
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getOrderGoodsCommentInfo($param)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangGoodsComment',
            'column' => $param['column'],
            'where' => 'where '.$param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition,true);
    }

    /**
     * @desc 验证是否速愈素订单
     * @param string $goodsIdStr 订单商品id，多个用逗号分开
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function isQuicksinOrder($goodsIdStr)
    {
        if (empty($goodsIdStr)) {
            return false;
        }
        // 通过验证保税区来辨别是否是 “速愈素”
        $bonded = $this->getData([
            'table'  => 'Shop\Models\BaiyangGoodsStockBonded',
            'column' => 'bonded_id',
            'where'  => "where bonded_id = 2 and goods_id in({$goodsIdStr})",
        ], true);
        if (!empty($bonded)){
            return true;
        }
        return false;
    }

    /**
     * @desc 插入订单详情
     * @param $param
     * @return bool
     * @author 柯琼远
     */
    public function insertOrderDetail($param) {
        foreach ($param['goodsList'] as $value) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrderDetail',
                'bind'  => array(
                    'total_sn'          => $param['orderSn'],
                    'order_sn'          => $param['orderSn'],
                    'goods_id'          => $value['goods_id'],
                    'goods_name'        => $value['goods_name'],
                    'goods_image'       => $value['goods_image'],
                    'price'             => $value['discount_total'],
                    'unit_price'        => $value['discount_price'],
                    'promotion_total'   => isset($value['promotion_total']) ? $value['promotion_total'] : 0,
                    'promotion_price'   => isset($value['promotion_price']) ? $value['promotion_price'] : 0,
                    'goods_number'      => $value['goods_number'], 
                    'specifications'    => $value['specifications'],
                    'is_comment'        => 0,
                    'is_return'         => 0,
                    'add_time'          => time(),
                    'goods_type'        => 0,
                    'discount_price'    => "0.00",
                    'discount_remark'   => !empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME ? $value['discountPromotion']['limit_time_title'] : "",
                    'stock_type'        => $value['stock_type'],
                    'market_price'      => $value['market_price'],
                    'invite_code'       => "",
                    'code_bu'           => "",
                    'code_region'       => "",
                    'group_id'          => $value['group_id'],
                    'tag_id'            => !empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::MEMBER_PRICE ? $value['discountPromotion']['id'] : 0,
                    'treatment_id'      => !empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::TREATMENT ? $value['discountPromotion']['id'] : 0,
                )
            );
            $ret = $this->addData($addData);
            if (!$ret) {
                return false;
            }
        }
        foreach ($param['giftList'] as $value) {
            if ($value['goods_number'] < 1) {
                continue;
            }
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrderDetail',
                'bind'  => array(
                    'total_sn'          => $param['orderSn'],
                    'order_sn'          => $param['orderSn'],
                    'goods_id'          => $value['goods_id'],
                    'goods_name'        => $value['goods_name'],
                    'goods_image'       => $value['goods_image'],
                    'price'             => 0,
                    'unit_price'        => 0,
                    'goods_number'      => $value['goods_number'],
                    'specifications'    => $value['specifications'],
                    'is_comment'        => 0,
                    'is_return'         => 0,
                    'add_time'          => time(),
                    'goods_type'        => isset($value['product_type']) ? $value['product_type'] : 1,
                    'discount_price'    => 0,
                    'discount_remark'   => "",
                    'stock_type'        => $value['stock_type'],
                    'market_price'      => 0,
                    'promotion_origin'  => 1,
                    'promotion_code'    => "",
                    'invite_code'       => "",
                    'code_bu'           => "",
                    'code_region'       => "",
                    'group_id'          => 0,
                    'tag_id'            => 0,
                    'treatment_id'      => 0,
                )
            );
            $ret = $this->addData($addData);
            if (!$ret) {
                return false;
            }
        }
        return true;
    }
}