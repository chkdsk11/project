<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/11/22 0022
 * Time: 下午 3:24
 */

namespace Shop\Home\Services;

use Shop\Home\Services\BaseService;
use Shop\Models\HttpStatus;
use Shop\Home\Datas\BaiyangOrderData;
use Shop\Home\Datas\BaiyangOrderDetailData;
use Shop\Home\Datas\BaiyangSkuData;


class OrderDetailService extends BaseService
{
    protected static $instance=null;

    /**
     * @param array $param
     * @param string $platform
     */
    public function getOneOrderDetail(array $param,string $platform)
    {
        if(empty(intval($param['user_id'])) || empty($param['order_sn']) || empty($platform)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $orderDetail=$this->findOneOrderDetail($param);
        return $this->responseResult(\Shop\Models\HttpStatus::SUCCESS,'',$orderDetail);
    }

    /**
     * @param array $param
     * @return []
     * @notice   'where'=>"user_id=:user_id: and baiy_order.order_sn=:order_sn:"   特别注意
     * @author  康涛
     *
     */
    protected function findOneOrderDetail(array $param)
    {
        //读写行为锁
        $rwKey=\Shop\Models\OrderEnum::USER_ORDER_LOCK_KEY.$param['user_id'];

        $orderData=BaiyangOrderData::getInstance();

        //得到订单及订单物流信息,并设置读写行为
        $order=$orderData->getOneOrder([
            'column'=>'*',
            'where'=>"user_id=:user_id: and baiy_order.order_sn=:order_sn:",
            'bind'=>[
                'user_id'=>$param['user_id'],
                'order_sn'=>$param['order_sn'],
            ]
        ],$this->switchOrderDb($rwKey));

        if(is_array($order) && !empty($order)){

            //订单详情及读写行为
            $orderDetailData=BaiyangOrderDetailData::getInstance();
            $orderDetail=$orderDetailData->getOneOrderDetail([
                'column'=>'*',
                'where'=>'order_sn=:order_sn:',
                'bind'=>[
                    'order_sn'=>$param['order_sn'],
                ],
            ],$this->switchOrderDb($rwKey));

            //订单详情关联商品
            if(is_array($orderDetail) && !empty($orderDetail)){
                $skuId=implode(',',array_column($orderDetail,'goods_id'));
                $skuData=BaiyangSkuData::getInstance();
                $sku=$skuData->getAllSkus([
                    'column'=>'id,medicine_type',
                    'where'=>"id in($skuId)",
                ]);

                //取售后信息
                $orderRefund=[];
                if($order['is_delete']===0 && $order['status']===\Shop\Models\OrderEnum::ORDER_REFUND) {
                    $orderRefund = $orderDetailData->getOrderDetailReturn([
                        'column' => '*',
                        'where' => "order_sn={$param['order_sn']}"
                    ]);
                }
                $orderDetailInfo=$this->aseembleOrderDetail($order,$orderDetail,$sku,$orderRefund);
                unset($order);
                unset($orderDetail);
                unset($sku);
                unset($orderRefund);
                return $orderDetailInfo;
            }
        }
    }

    /**
     * 组装订单，订单详情，订单商品，订单售后信息
     * @param $order    []
     * @param $orderDetail  []
     * @param $orderSku []
     * @param $orderRefund  []
     * @return []
     * @author  康涛
     */
    private function aseembleOrderDetail($order,$orderDetail,$orderSku,$orderRefund)
    {
        $sku=array_column($orderSku,'id');

        //组装订单详情与商品信息
        $orderDetail=array_map(function($item)use($orderSku,$sku){
            $tmpPosition=intval(array_search($item['goods_id'],$sku));
            $item=array_merge($item,$orderSku[$tmpPosition]);
            return $item;
        },$orderDetail);

        //组装订单与订单详情，订单售后
        $order['order_detail']=$orderDetail;
        $order['order_refund']=$orderRefund;
        return $order;
    }
}