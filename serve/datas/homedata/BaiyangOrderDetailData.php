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

class BaiyangOrderDetailData extends BaseData
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
     * @desc 根据子订单编号获取退款信息 
     * @param string $order_sn 子订单编号
     * @param bool/false $returnOne 是否查询多条
     * @return array [] 结果信息
     * @author 秦亮
     */
    public function getOrderDetailReturnData($order_sn, $returnOne = false)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => '*',
            'bind' => [
                'order_sn' => $order_sn,
            ],
            // 此处极易发生无法准确匹配唯一服务单号,海典无法提供唯一服务单号过来,跟产品沟通暂时用子订单加状态匹配
            'where' => "where order_sn = :order_sn: AND (status <> 1) AND (status <> 3) AND (status <> 6) order by add_time desc",
        ];
        return $this->getData($condition, $returnOne);
    }
    
    

    /**
     * @desc 获得订单详情信息
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @param int $global 是否海外购订单 (1:海外购 0:普通订单)
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getOneOrderDetail(array $param, int $global = 0)
    {
        //切换读写
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
     * @desc 根据订单编号获取订单商品信息
     * @param string $orderSn 订单编号
     * @return array [] 结果信息
     * @author 朱丹
     */
    public function getDetailGoodsSkuByOrderSn($orderSn)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderDetail as d ',
            'join'=>'left join \Shop\Models\BaiyangSkuInfo  as i on d.goods_id=i.sku_id left join \Shop\Models\BaiyangGoods as g on d.goods_id=g.id',
            'column' => 'd.unit_price,d.id,d.goods_id,d.goods_name,d.price,d.goods_image,i.returned_goods_time,(d.goods_number-d.refund_goods_number) as max_refund_number,g.is_global,g.medicine_type,d.specifications,d.group_id,d.promotion_total,d.promotion_price',
            'where' => "where d.order_sn = :order_sn:  and d.goods_type=0",
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
        foreach ($param['supplierList'] as $key => $value) {
            // 商品
            foreach ($value['goodsList'] as $k => $v) {
                $addData = array(
                    'table' => '\Shop\Models\BaiyangOrderDetail',
                    'bind'  => array(
                        'total_sn'          => $param['orderSn'],
                        'order_sn'          => $value['orderSn'],
                        'goods_id'          => $v['goods_id'],
                        'goods_name'        => $v['goods_name'],
                        'goods_image'       => $v['goods_image'],
                        'price'             => $v['discount_total'],
                        'unit_price'        => $v['discount_price'],
                        'promotion_total'   => isset($v['promotion_total']) ? $v['promotion_total'] : $v['discount_total'],
                        'promotion_price'   => isset($v['promotion_price']) ? $v['promotion_price'] : $v['discount_price'],
                        'goods_number'      => $v['goods_number'],
                        'specifications'    => $v['specifications'],
                        'is_comment'        => 0,
                        'is_return'         => 0,
                        'add_time'          => time(),
                        'goods_type'        => 0,
                        'bind_id'           => 0,
                        'discount_price'    => "0.00",
                        'discount_remark'   => !empty($v['discountPromotion']) && $v['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::LIMIT_TIME ? $v['discountPromotion']['limit_time_title'] : "",
                        'stock_type'        => $v['stock_type'],
                        'market_price'      => $v['market_price'],
                        'invite_code'       => "",
                        'code_bu'           => "",
                        'code_region'       => "",
                        'group_id'          => $v['group_id'],
                        'tag_id'            => !empty($v['discountPromotion']) && $v['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::MEMBER_PRICE ? $v['discountPromotion']['id'] : 0,
                        'treatment_id'      => !empty($v['discountPromotion']) && $v['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::TREATMENT ? $v['discountPromotion']['id'] : 0,
                        'is_refund'         => isset($v['returned_goods_time']) && $v['returned_goods_time'] == 0 ? 1 : 0,
                    )
                );
                $detail_id = $this->addData($addData, true);
                if (!$detail_id) {
                    return false;
                }
                // 附属赠品
                if (isset($v['bind_gift']) && !empty($v['bind_gift'])) {
                    foreach ($v['bind_gift'] as $_k => $_v) {
                        if ($v['goods_number'] < 1) {
                            continue;
                        }
                        $addData = array(
                            'table' => '\Shop\Models\BaiyangOrderDetail',
                            'bind'  => array(
                                'total_sn'          => $param['orderSn'],
                                'order_sn'          => $value['orderSn'],
                                'goods_id'          => $_v['goods_id'],
                                'goods_name'        => $_v['goods_name'],
                                'goods_image'       => $_v['goods_image'],
                                'price'             => 0,
                                'unit_price'        => 0,
                                'goods_number'      => $_v['goods_number'],
                                'specifications'    => $_v['specifications'],
                                'is_comment'        => 0,
                                'is_return'         => 0,
                                'add_time'          => time(),
                                'goods_type'        => 2,
                                'bind_id'           => $detail_id,
                                'discount_price'    => 0,
                                'discount_remark'   => "",
                                'stock_type'        => $v['stock_type'],
                                'market_price'      => 0,
                                'promotion_origin'  => 1,
                                'promotion_code'    => "",
                                'invite_code'       => "",
                                'code_bu'           => "",
                                'code_region'       => "",
                                'group_id'          => 0,
                                'tag_id'            => 0,
                                'treatment_id'      => 0,
                                'is_refund'         => isset($_v['returned_goods_time']) && $_v['returned_goods_time'] == 0 ? 1 : 0,
                            )
                        );
                        if (!$this->addData($addData)) {
                            return false;
                        }
                    }
                }
            }
            // 活动赠品
            foreach ($value['giftList'] as $k => $v) {
                if ($v['goods_number'] < 1) {
                    continue;
                }
                $addData = array(
                    'table' => '\Shop\Models\BaiyangOrderDetail',
                    'bind'  => array(
                        'total_sn'          => $param['orderSn'],
                        'order_sn'          => $value['orderSn'],
                        'goods_id'          => $v['goods_id'],
                        'goods_name'        => $v['goods_name'],
                        'goods_image'       => $v['goods_image'],
                        'price'             => 0,
                        'unit_price'        => 0,
                        'goods_number'      => $v['goods_number'],
                        'specifications'    => $v['specifications'],
                        'is_comment'        => 0,
                        'is_return'         => 0,
                        'add_time'          => time(),
                        'goods_type'        => 1,
                        'bind_id'           => 0,
                        'discount_price'    => 0,
                        'discount_remark'   => "",
                        'stock_type'        => $v['stock_type'],
                        'market_price'      => 0,
                        'promotion_origin'  => 1,
                        'promotion_code'    => "",
                        'invite_code'       => "",
                        'code_bu'           => "",
                        'code_region'       => "",
                        'group_id'          => 0,
                        'tag_id'            => 0,
                        'treatment_id'      => 0,
                        'is_refund'         => 1,
                    )
                );
                if (!$this->addData($addData)) {
                    return false;
                }
            }
        }
        return true;
    }


    /**
     * 获取退款的订单详情
     * @param $reason_id
     * @return array|bool
     */
    public function getOrderDetailByService($reason_id){
        $condition = [
            'table' => 'Shop\Models\BaiyangOrderGoodsReturn as r',
            'join'  => 'left join \Shop\Models\BaiyangOrderDetail as d on r.order_goods_id=d.id',
            'column' => 'd.goods_id,d.unit_price,d.goods_name,d.goods_image,d.price,r.refund_goods_number as now_refund_goods_number,d.refund_goods_number,r.return_type,r.status,r.update_time,r.add_time,d.id,d.specifications,d.goods_type,tag_id,r.order_goods_id,d.promotion_price,promotion_total',
            'where' => 'where r.reason_id = :reason_id:' ,
            'bind' => [
                'reason_id'=>$reason_id
            ],
        ];
        $orderDetailList =  $this->getData($condition);

        foreach($orderDetailList as &$detail){
            if($detail['tag_id']){
                $row = $this->getDetailGoodsTag([
                    'column'=>'tag_name',
                    'where'=>'tag_id=:tag_id:',
                    'bind' => [
                        'tag_id'=>$detail['tag_id']
                    ],
                ]);
                $detail['tag_name'] = $row['tag_name'];

            }else{
                $detail['tag_name'] = '';
            }
        }
        return $orderDetailList;
    }



    /**
     * 修改订单详情数据
     * @param $param
     * @return bool
     */
    public function updateOrderDetail($param){
        $condition = [
            'table' => 'Shop\Models\BaiyangOrderDetail',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->updateData($condition);
    }


    /**
     * 修改订单详情数据
     * @param $param
     * @return bool
     */
    public function getGiftOrderDetail($param){
        $condition = [
            'table' => 'Shop\Models\BaiyangOrderDetail',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition);
    }


    /**
     * 获取
     * @param $param
     * @return array|bool
     */
    public function getDetailGoodsTag($param){
        $condition = [
            'table' => 'Shop\Models\BaiyangGoodsPriceTag ',
            'column' =>$param['column'] ,
            'where' => 'where '. $param['where'],
            'bind' => $param['bind'] ,
        ];
        return $this->getData($condition,true);
    }
}