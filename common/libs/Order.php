<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/16
 * Time: 17:39
 */

namespace Shop\Libs;

use Shop\Libs\LibraryBase;
use Shop\Home\Services\OrderService;

class Order extends LibraryBase
{
    private $defined = [
        'platform' => 'pc', //平台
        'channel_subid' => 95, //渠道号
    ];

    /**
     * 取消订单
     * @param $param
     *              - user_id 用户id
     *              - order_sn 订单号
     *              - cancel_reason 取消订单原因
     * @return mixed
     * @author Chensonglu
     */
    public function cancel($param)
    {
        $param = array_merge($this->defined, $param);
        return OrderService::getInstance()->cancelOrder($param);
    }

    /**
     * @param $param
     *             - user_id 用户id
     *             - order_sn 子订单号
     *             - reason 退款原因
     *             - explain 退款说明
     *             - images 图片
     *             - goods_content 需退款的商品内容
     * @return mixed
     * @author Chensonglu
     */
    public function refund($param)
    {
        $param = array_merge($this->defined, $param);
        return OrderService::getInstance()->orderApplyRefund($param);
    }
}