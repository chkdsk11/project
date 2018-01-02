<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/27 0027
 * Time: 10:07
 */

namespace Shop\Home\Services;

use Phalcon\Http\Client\Exception;
use Shop\Home\Datas\{
    BaiyangGroupFightBData, BaiyangSkuData, BaseData, BaiyangOrderData, BaiyangOrderDetailData, BaiyangUserConsigneeData
};

use Shop\Home\Listens\GroupfightOrderConfirm;
use Shop\Home\Listens\GroupfightOrderSubmit;
use Shop\Home\Listens\{
    PromotionLimitBuy, PromotionShopping,
    PromotionCalculate, FreightListener, BalanceListener, MomListener, StockListener, PromotionGoodsDetail
};

use Shop\Models\{
    BaiyangGroupFight, HttpStatus, OrderEnum
};

use Phalcon\Events\{
    Manager as EventsManager, Event
};

use Shop\Home\Services\{
    BaseService, AuthService
};


class GroupFightOrderDetailService extends BaseService
{
    /**
     * @var GroupService
     */
    protected static $instance = null;

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new GroupFightOrderDetailService();
        }

        //实例化事件管理器
        $eventsManager = new EventsManager();

        //开启事件结果回收
        $eventsManager->collectResponses(true);

        /********************************侦听器*********************************/
        $eventsManager->attach('promotion', new PromotionCalculate());
        $eventsManager->attach('promotion', new PromotionLimitBuy());
        $eventsManager->attach('promotion', new PromotionShopping());
        $eventsManager->attach('promotion', new MomListener());
        $eventsManager->attach('order', new FreightListener());
        $eventsManager->attach('order', new BalanceListener());
        $eventsManager->attach('order', new StockListener());
        $eventsManager->attach('promotionInfo', new PromotionGoodsDetail());
        /*********************侦听器************************/

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    /**
     * @desc 订单详情
     * @param array $param
     *       -int      user_id    用户id
     *       -string   order_sn   订单编号
     *       -string   prescription_id  易复诊处方id
     *       -string   platform   平台
     *       -int      channel_subid  渠道号
     * @return array  []   结果信息
     * @author  吴俊华
     */
    public function getOrderDetail(array $param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';

        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($orderSn) || empty($userId) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        // 读写key
        $rwKey = OrderEnum::USER_ORDER_LOCK_KEY . $userId;
        $global = 0;
        // 获取订单信息
        $orderData = BaiyangOrderData::getInstance();
        $order = $orderData->getOneOrder([
            'column' => '*',
            'where' => 'order_sn = :order_sn: and user_id = :user_id: and is_delete = 0',
            'bind' => [
                'order_sn' => $orderSn,
                'user_id' => $userId,
            ]
        ], $this->switchOrderDb($rwKey), $global);
        if (!$order) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

        $orderDetailData = BaiyangOrderDetailData::getInstance();

        // 相同的订单数据
        $orderInfo = [
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'status' => $order['status'],
            'is_global' => 0,
            'province' => $order['province'],
            'city' => $order['city'],
            'county' => $order['county'],
            'address' => $order['address'],
            'telephone' => $order['telephone'],
            'add_time' => $order['add_time'],
            'pay_time' => $order['pay_time'],
            'audit_time'=>$order['audit_time'] ? :$order['add_time'],
            'consignee' => $order['consignee'],
            'consignee_id' => isset($order['consignee_id']) ? $order['consignee_id'] : '',
            'invoice_type' => $order['invoice_type'],
            'invoice_info' => $order['invoice_info'],
            'e_invoice_url' => isset($order['e_invoice_url']) ? $order['e_invoice_url'] : '',
        ];

        $orderInfo['express_sn'] = $order['express_sn'];
        $orderInfo['express_type'] = $order['express_type'];
        $orderInfo['express'] = $order['express'];
        // 显示英文物流名称
        $orderInfo['express_en'] = '';
        if($orderInfo['express']) {
            foreach (OrderEnum::$LogisticsNo as $kk => $val) {
                if((strtoupper($orderInfo['express']) == strtoupper($val)) || (strtoupper($orderInfo['express']) == strtoupper($kk))) {
                    $orderInfo['express_en'] = $kk;
                    break;
                }
            }
        }
        //$orderInfo['predict_time'] = isset($order['o2o_remark']) ? $order['o2o_remark'] : '';
        // 预计到达时间 (处理两小时达)
//        if ($global == 0 && $orderInfo['express_type'] == 2 && !empty($orderInfo['predict_time'])) {
//            $predictTimeArr = explode("—",$orderInfo['predict_time']);
//            $endTimeArr = explode(" ",$predictTimeArr[1]);
//            $orderInfo['predict_time'] = $predictTimeArr[0].'-'.$endTimeArr[1];
//        }

        //$orderInfo['audit_state'] = $order['audit_state'];
        //$orderInfo['callback_phone'] = $order['callback_phone']; // 处方单回拨号码
        // 处方单照片
        //$orderInfo['ordonnance_photo'] = isset($order['ordonnance_photo']) ? preg_replace("/http:\/\/[^\/]+\//", '', $order['ordonnance_photo']) : '' ;
        $orderInfo['order_total'] = $order['total'];
        $orderInfo['real_pay'] = $order['real_pay'];
        $orderInfo['goods_amount'] = $order['goods_price']; //商品总额
        $orderInfo['invoice_info'] = json_decode($orderInfo['invoice_info'],true);
        $orderInfo['balance_price'] = $order['balance_price']; // 余额支付的金额

        // 剩余未支付金额
        $orderInfo['left_unpaid'] = bcsub($orderInfo['order_total'], $orderInfo['balance_price'], 2);
        $orderInfo['carriage'] = sprintf("%.2f", $order['carriage']);  //运费
        $orderInfo['payment_id'] = $order['payment_id'];
        // 支付名称
        if (!isset(OrderEnum::$PaymentName[$orderInfo['payment_id']])) {
            $orderInfo['payment_name'] = '在线支付';
        } else {
            $orderInfo['payment_name'] = OrderEnum::$PaymentName[$orderInfo['payment_id']];
        }
        $orderInfo['pay_link'] = '';
        $cancel = 60*30;
        $orderTime = $orderInfo['audit_time'] ? :$orderInfo['add_time'] ;
        $cancelTime = $orderInfo['status'] == OrderEnum::ORDER_PAYING ? (int) $orderTime + $cancel - time() : 0;
        $orderInfo['cancel_reason'] = $order['cancel_reason'];
        $orderInfo['cancel_time'] = $cancelTime < 0 ? 0 : $cancelTime;
        $orderInfo['buyer_message'] = $order['buyer_message'];

        $orderInfo['is_show_logisticsbutton'] = false;

        $rxExist = 0; // 是否处方单
        $detailList = [];

        // 普通订单
        if ($global === 0) {
            // 获取订单详细
            $detailList = $orderDetailData->getOneOrderDetail([
                'column' => '*',
                'where' => 'order_sn = :order_sn: order by goods_type asc',
                'bind' => [
                    'order_sn' => $orderSn
                ]
            ], $global);
            if (empty($detailList)) {
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            foreach ($detailList as $key => $val) {
                // 赠品(普通赠品、附属赠品)
                if($val['goods_type'] > 0){
                    $giftsList[] = $val;
                    unset($detailList[$key]);
                    continue;
                }
                // 商品是否上下架
                $saleArr = $this->filterData('sale',$this->getGoodsDetail(['goods_id' => $val['goods_id'], 'platform' => $platform]));
                $detailList[$key]['is_sale'] = $saleArr[0]['sale'];
                $detailList[$key]['is_global'] = $global;
                // 获取物流
                $shippingDetail = null;
                if(!empty($orderInfo['express_sn']) && strtolower($orderInfo['express']) != 'zps'){
                    $shippingDetail = $this->func->getLogisticsData($orderSn);
                }

            }
            // 判断订单是否处方单
            $goodsIdsStr = implode(',', array_column($detailList, 'goods_id'));
            $drugTypeArr = $this->filterData('drug_type', $this->getGoodsDetail(['goods_id' => $goodsIdsStr, 'platform' => $platform]));
            if (in_array(1, array_column($drugTypeArr, 'drug_type'))) {
                $rxExist = 1;
            }

            $goodsList[] = [
                'goods_id' => $detailList[0]['goods_id'],
                'goods_name' => $detailList[0]['goods_name'],
                'goods_number' => $detailList[0]['goods_number'],
                'price' => $detailList[0]['price'],
                'market_price' => $detailList[0]['market_price'],
                "is_sale"=> $detailList[0]['is_sale'],
                "is_global" => 0,
                "stock_type"=> $detailList[0]['stock_type'],
             "goods_type"=> $detailList[0]['goods_type'],
            "is_comment"=> $detailList[0]['is_comment'],
              "goods_image"=> $detailList[0]['goods_image'],
                'first_image' => $detailList[0]['goods_image'],
            ];
        }

        // 处方单
        if ($rxExist == 1) {
            $orderInfo['check_time'] = $orderInfo['add_time'] + $this->func->getConfigValue(BaiyangConfigEnum::ORDER_AUTO_AUDIT_PASS_TIME);
        }
        // 省市区
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $orderInfo['province'] = $userConsigneeData->getRegionName($orderInfo['province']);
        $orderInfo['city'] = $userConsigneeData->getRegionName($orderInfo['city']);
        $orderInfo['county'] = $userConsigneeData->getRegionName($orderInfo['county']);

        // 初始化key
        $orderInfo['end_time'] = 0; // 待付款的剩余时间戳
        $orderInfo['format_rest_time'] = ''; // 待付款的剩余时间(格式化)
        $orderInfo['refund_status'] = -1; // 退款审核状态
        $orderInfo['refund_type'] = -1; // 退款类型
        $orderInfo['reason'] = ''; // 退款缘由
        $orderInfo['explain'] = ''; // 退换货说明
        $orderInfo['images'] = ''; // 退款图片数组json格式
        $orderInfo['status_name'] = ''; // 退款状态

        // 待付款
        if($orderInfo['status'] == OrderEnum::ORDER_PAYING ){

            $orderInfo['end_time'] = 0;
            $orderInfo['format_rest_time'] = '';//floor($restTime/86400) . '天' . floor($restTime%86400/3600) . '小时' . floor($restTime%3600/60) . "分" . $restTime%60 . '秒';
        }elseif ($orderInfo['status'] == OrderEnum::ORDER_REFUND){
            // 退货/售后
            $reasonRet = BaseData::getInstance()->getData([
                'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
                'column' => 'status refund_status,return_type refund_type,reason,explain,images',
                'where' => 'where order_sn = :order_sn:',
                'bind' => [
                    'order_sn' => $orderSn
                ],
            ],true);
            if($reasonRet){
                $orderInfo['refund_status'] = $reasonRet['refund_status'];
                $orderInfo['refund_type'] = $reasonRet['refund_type'];
                $orderInfo['reason'] = $reasonRet['reason'];
                $orderInfo['explain'] = $reasonRet['explain'];
                $orderInfo['images'] = $reasonRet['images'];
                $orderInfo['status_name'] = OrderEnum::$RefundStatus[$reasonRet['refund_status']];
            }
        }



        $fightUserList = $this->getfightUserList($orderSn, $userId);

        if(empty($fightUserList['goods_slide_images']) === false){
            $goods_slide_images = @json_decode($fightUserList['goods_slide_images'], true);
            if($goods_slide_images and isset($goods_slide_images[0])){
                $goodsList[0]['first_image'] = $goods_slide_images[0];
            }
            unset($fightUserList['goods_slide_images']);
        }
        $data = [
            'is_global' => $global,
            'rx_exist' => $rxExist,
            'orderInfo' => $orderInfo,
            'fight' => $fightUserList,
            'goodsList' => $goodsList,
            'shippingDetail' => $shippingDetail
            //'product'=>$detailList

        ];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }


    private function getfightgroupuser($userId, $orderSn)
    {
        $data = BaiyangGroupFightBData::getInstance()->getGroupFightBuyByOrderSn($orderSn, $userId);
        if (empty($data)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        return $data[0];
    }


    private function getfightUserList($orderSn,$userId)
    {
        $fightGroupAct = $this->getfightgroupuser($userId, $orderSn);
        $data = BaiyangGroupFightBData::getInstance()->getGroupFightAndUserList($fightGroupAct['gf_id']);

        unset( $data['goods_image'], $data['send_notice'], $data['gfa_cycle'], $data['user_id'], $data['nickname']);

        $data['gfa_user_type'] = $fightGroupAct['gfa_user_type'];
        $data['gfa_type'] = $fightGroupAct['gfa_type'];
        $data['gfa_is_draw'] = $fightGroupAct['gfa_is_draw'];
        //$data['gfa_state'] = $fightGroupAct['gfa_state'];
        $data['gfa_draw_num'] = $fightGroupAct['gfa_draw_num'];
        $data['goods_slide_images'] = $fightGroupAct['goods_slide_images'];
        if(isset($data['user_list']) and is_array($data['user_list'])){
            foreach($data['user_list'] as $v){
                if($v['user_id'] == $userId){
                    $data['gfu_state'] = $v['gfu_state'];
                    $data['is_win'] = $v['is_win'];
                    $data['is_head'] = $v['is_head'];
                }
            }
        }
        $data['goods_number'] = 1;
        return $data;

    }

    /**
     * @desc 处理物流数据
     * @param array $logistics 物流信息[二维数组]
     * @param int $global 是否海外购(1:海外购 0:普通订单)
     * @return array [] 处理后的物流信息
     */
    private function handleLogisticsData(array $logistics, $global = 0)
    {
        $newLogistics = [];
        $lastLogistics = [];
        foreach ($logistics as $key => $value){
            $timeArr = explode(' ', $value['time']);
            // 海外购订单
            if($global){
                $newLogistics[$timeArr[0]][] = [
                    'year' => !isset($newLogistics[$timeArr[0]]) ? $timeArr[0] : '',
                    'week' => !isset($newLogistics[$timeArr[0]]) ? $this->getWeekday(strtotime($value['time'])) : '',
                    'time' => isset($timeArr[1]) ? $timeArr[1] : '',
                    'recPlace' => isset($value['recPlace']) ? $value['recPlace'] : '',
                    'status' => isset($value['status']) ? $value['status'] : '',
                ];
            }else{
                // 普通订单
                $newLogistics[$timeArr[0]][] = [
                    'year' => !isset($newLogistics[$timeArr[0]]) ? $timeArr[0] : '',
                    'week' => !isset($newLogistics[$timeArr[0]]) ? $this->getWeekday(strtotime($value['time'])) : '',
                    'time' => isset($timeArr[1]) ? $timeArr[1] : '',
                    'context' => isset($value['context']) ? $value['context'] : '',
                    'areaCode' => isset($value['areaCode']) ? $value['areaCode'] : '',
                    'areaName' => isset($value['areaName']) ? $value['areaName'] : '',
                    'status' => isset($value['status']) ? $value['status'] : '',
                ];
            }
        }
        foreach ($newLogistics as $key => $value){
            foreach ($value as $val){
                $lastLogistics[] = $val;
            }
        }
        return $lastLogistics;
    }
}































