<?php
/**
 * Created by PhpStorm.
 */

namespace Shop\Home\Datas;

use Shop\Home\Datas\BaseData;
use Shop\Models\BaiyangOrder;
use Shop\Models\BaiyangOrderDetail;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Shop\Models\BaiyangOrderGoodsReturnReason;
use Shop\Models\BaiyangPromotionEnum;

class BaiyangGroupFightOrderDetailData extends BaseData
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
            'column' => 'd.unit_price,d.id,d.goods_id,d.goods_name,d.price,d.goods_image,i.returned_goods_time,(d.goods_number-d.refund_goods_number) as max_refund_number,g.is_global,g.medicine_type,d.specifications,d.group_id',
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

        $addData = array(
            'table' => '\Shop\Models\BaiyangOrderDetail',
            'bind'  => array(
                'total_sn'          => $param['orderSn'],
                'order_sn'          => $param['orderSn'],
                'goods_id'          => $param['goodsId'],
                'goods_name'        => $param['goodsName'],
                'goods_image'       => $param['goodsImage'],
                'price'             => $param['goodsTotalAmount'],
                'unit_price'        => $param['unitPrice'],
                'stock_type'        => $param['stockType'],
                'market_price'      => $param['marketPrice'],
                'goods_number'      => $param['goodsNumber'],
                'add_time'          => time(),
                'promotion_total'   =>  $param['goodsTotalAmount'],
                'promotion_price'   =>  $param['unitPrice'],
                'specifications'    => '',
                'is_comment'        => 0,
                'is_return'         => 0,
                'goods_type'        => 0,
                'bind_id'           => 0,
                'discount_price'    => "0.00",
                'discount_remark'   => '',
                'invite_code'       => "",
                'code_bu'           => "",
                'code_region'       => "",
                'group_id'          => 0,
                'tag_id'            => 0,
                'treatment_id'      => 0,
            )
        );
        $detail_id = $this->addData($addData, true);
        if (!$detail_id) {
            return false;
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
            'column' => 'd.goods_id,d.unit_price,d.goods_name,d.goods_image,d.price,r.refund_goods_number as now_refund_goods_number ,d.refund_goods_number,d.id,d.specifications,d.goods_type,tag_id',
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