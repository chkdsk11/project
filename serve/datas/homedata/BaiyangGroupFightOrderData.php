<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/28 0028
 * Time: 9:01
 */

namespace Shop\Home\Datas;
use Phalcon\Http\Client\Exception;
use Shop\Models\BaiyangGroupFight;
use Shop\Models\BaiyangGroupFightActivity;
use Shop\Models\BaiyangOrder;
use Phalcon\Paginator\Adapter\QueryBuilder as PaginatorQueryBuilder;
use Shop\Models\HttpStatus;

/**
 * 拼团方法集合
 * @package Shop\Home\Datas
 */
class BaiyangGroupFightOrderData extends BaseData
{
    /**
     * @var BaiyangGroupFightData
     */
    protected static $instance=null;

    /**
     * @desc 插入订单
     * @param array $param
     * @return bool true|false 结果信息
     * @author 柯琼远
     */
    public function insertOrder($param, $address, $invoice) {

        $addData = array(
            'table' => '\Shop\Models\BaiyangOrder',
            'bind'  => array(
                'agent_id' => 1,
                'user_id' => $param['user_id'],
                'total_sn' => $param['orderSn'],
                'order_sn' => $param['orderSn'],

                'consignee' => $address['consignee'],
                'telephone' => $address['telphone'],
                'zipcode' => $address['zipcode'],
                'province' => $address['province'],
                'city' => $address['city'],
                'county' => $address['county'],
                'address' => $address['address'],
                'addr_id' => $address['id'],
                'express_type' => $address['expressType'],
                'order_type' => $param['orderType'],
                'total' => $param['orderAmount'],
                'pay_remark' => $param['payRemark'],
                'real_pay' => $param['realPrice'],
                'carriage' => $param['carriage'],
                'is_pay' => $param['isPay'],
                'pay_time' => $param['payTime'],

                'status' => $param['status'],

                'last_status' => 'paying',
                'pay_type' => $param['payType'],

                'invoice_type' => $invoice['invoiceType'] > 0 ? 3 : 0,
                'invoice_info' => $invoice['invoiceInfo'],
                'invoice_money' => $param['invoiceMoney'],
                'buyer_message' => $param['leaveWord'],
                'add_time' => $param['addTime'],
                'audit_time' => $param['addTime'],
                'goods_price' => $param['goodsTotalPrice'],
                'balance_price' => $param['balancePaid'],
                'payment_name' => $param['paymentName'],
                'payment_id' => $param['paymentId'],
                'payment_code' => $param['paymentCode'],
                'channel_subid' => $this->config->channel_subid,
                'channel_name' => $param['channelName'],
                'trade_no' => '',
                'express_status' => 0,
                'express_time' => 0,
                'allow_comment' =>0,
                //'audit_state' =>  0 ,
                'callback_phone' =>  '',
                'ordonnance_photo' =>  '',
                'user_coupon_id' =>  '',
                'user_coupon_price' => 0,
                'order_discount_money' => 0,
                'detail_discount_money' => "0.00",
                'youhui_price' => 0,
                'is_comment' => 0,
                'is_return' => 0,
                'o2o_remark' =>  '',
                'shop_id' => $param['shop_id'],
                'express' => '',
                'express_sn' => '',
                'delivery_status' => 0,
                'received' => 0,
                'discount_remark' => '',
            )
        );
        if (!$this->addData($addData)) {
            return false;
        }

        return true;
    }


    /**
     * @desc 插入订单支付详情
     * @param array $param
     * @return bool true|false 结果信息
     * @author 柯琼远
     */
    public function insertOrderPayDetail($param) {

        if ($param['balancePaid'] > 0) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrderPayDetail',
                'bind'  => array(
                    'order_sn'        => $param['orderSn'],
                    'order_channel'   => $this->config->channel_subid,
                    'payid'           => '905_20',
                    'pay_name'        => '商城余额支付',
                    'pay_money'       => $param['balancePaid'],
                    'pay_time'        => date('Y-m-d H:i:s'),
                    'trade_no'        => $param['expendSn'],
                    'pay_remark'      => $param['payRemark'],
                    'create_time'     => date('Y-m-d H:i:s'),
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        return true;
    }

}

























