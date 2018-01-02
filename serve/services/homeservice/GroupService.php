<?php
/**
 * Created by PhpStorm.
 * User: lifeilin
 * Date: 2017/4/27 0027
 * Time: 10:07
 */

namespace Shop\Home\Services;

use Shop\Home\Datas\{
    BaiyangGroupFightData, BaiyangSkuData, BaseData, BaiyangOrderData, BaiyangOrderDetailData, BaiyangUserConsigneeData
};

use Shop\Home\Listens\{
    PromotionLimitBuy, PromotionShopping,
    PromotionCalculate, FreightListener, BalanceListener, MomListener,StockListener,PromotionGoodsDetail
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

class GroupService extends BaseService
{
    /**
     * @var GroupService
     */
    protected static $instance=null;

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance = new GroupService();
        }

        //实例化事件管理器
        $eventsManager=new EventsManager();

        //开启事件结果回收
        $eventsManager->collectResponses(true);

        /********************************侦听器*********************************/
        $eventsManager->attach('promotion',new PromotionCalculate());
        $eventsManager->attach('promotion',new PromotionLimitBuy());
        $eventsManager->attach('promotion',new PromotionShopping());
        $eventsManager->attach('promotion',new MomListener());
        $eventsManager->attach('order',new FreightListener());
        $eventsManager->attach('order',new BalanceListener());
        $eventsManager->attach('order',new StockListener());
        $eventsManager->attach('promotionInfo',new PromotionGoodsDetail());
        /*********************侦听器************************/

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    protected function getGroupOrderList($param)
    {
        // 读写锁
        $lockKey = OrderEnum::USER_ORDER_LOCK_KEY . $param['user_id'];
        $platform = $param['platform'];
        $param['column'] = 'order_sn,add_time';
        $param['where'] = "user_id = {$param['user_id']} and is_delete = 0 and order_type = 5";
        // 根据订单状态拼接对应条件
        if (isset($param['condition']) && !empty($param['condition'])) {
            $param['where'] .= " and {$param['condition']}";
        }
        $baseData = BaseData::getInstance();
        $param['order'] = 'order by add_time desc';
        $start = ($param['pageStart'] - 1) * $param['pageSize'];
        $param['limit'] = $start . ',' . $param['pageSize'];
        // 订单信息
        $orderData = BaiyangOrderData::getInstance();
        $order = $orderData->getOrderList($param, $this->switchOrderDb($lockKey));
        $orderCounts = $kjOrderCounts = 0;

        // 计算订单总数
        if($param['status'] == OrderEnum::ORDER_REFUND){
            // 退款/售后订单(需要特殊处理)
            $stmt = $this->dbRead->prepare("SELECT count(order_sn) AS counts FROM (SELECT od.order_sn FROM baiyang_order AS od INNER JOIN baiyang_order_goods_return_reason AS odrr ON od.order_sn = odrr.order_sn AND odrr.`status` IN (0,2) AND odrr.return_type = 2 WHERE od.user_id = {$param['user_id']} AND od.`status` ='refund' AND od.is_delete = 0 AND od.order_type != 5 GROUP BY od.order_sn) AS order_refund");
            $stmt->execute();
            $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
            if(!empty($ret)){
                $orderCounts = $ret['counts'];
            }
        }else{
            $orderCounts = $baseData->countData([
                'table' => '\Shop\Models\BaiyangOrder',
                'where' => "where {$param['where']}",
            ]);
            $kjOrderCounts = $baseData->countData([
                'table' => '\Shop\Models\BaiyangKjOrder',
                'where' => "where {$param['where']}",
            ]);
        }
        $totalItem = $orderCounts + $kjOrderCounts;
        if (empty($order)) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, [
                'orderList' => [],
                'pageNum' => $totalItem,
                'pageCount' => ceil($totalItem / $param['pageSize']),
                'pageStart' => $param['pageStart'],
                'pageSize' => $param['pageSize'],
            ]);
        }

        // 保留需要字段
        $order['data'] = $this->filterData('order_sn,add_time', $order['data']);
        // 所有配置信息
        $config = $this->config;
        $env = $config->environment;

        // 订单详情信息
        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $orderColumn = 'status,last_status,express_sn,express_type,audit_state,audit_time,carriage,balance_price,allow_comment,payment_id,express,consignee,telephone,province,city,county,address';
        foreach ($order['data'] as $key => $value) {
            // 区分跨境订单和普通订单
            $global = strstr($value['order_sn'], OrderEnum::KJ) ? 1 : 0;
            $column = $global ? $orderColumn.',order_total_amount order_total' : $orderColumn.',total order_total';
            $orderInfo = $orderData->getOneOrder([
                'column' => $column,
                'where' => 'order_sn = :order_sn:',
                'bind' => ['order_sn' => $value['order_sn']]
            ], $this->switchOrderDb($lockKey), $global);
            if(empty($orderInfo)){
//                return ['status' => 10, 'explain' => json_encode($value), 'data' => ""];
                return $this->uniteReturnResult(HttpStatus::NO_DATA,[$value]);
            }
            $order['data'][$key]['status'] = $orderInfo['status'];
            $order['data'][$key]['last_status'] = $orderInfo['last_status'];
            $order['data'][$key]['goods_total'] = 0;
            $order['data'][$key]['order_total'] = $orderInfo['order_total'];
            $order['data'][$key]['express_sn'] = $orderInfo['express_sn'];
            $order['data'][$key]['express_type'] = $orderInfo['express_type'];
            $order['data'][$key]['express'] = $orderInfo['express'];
            // 显示英文物流名称
            $order['data'][$key]['express_en'] = '';
            if($orderInfo['express']) {
                foreach (OrderEnum::$LogisticsNo as $kk => $val) {
                    if((strtoupper($orderInfo['express']) == strtoupper($val)) || (strtoupper($orderInfo['express']) == strtoupper($kk))) {
                        $order['data'][$key]['express_en'] = $kk;
                        break;
                    }
                }
            }
            $order['data'][$key]['consignee'] = $orderInfo['consignee'];
            $order['data'][$key]['telephone'] = $orderInfo['telephone'];
            // 省市区
            $order['data'][$key]['province'] = $userConsigneeData->getRegionName($orderInfo['province']);
            $order['data'][$key]['city'] = $userConsigneeData->getRegionName($orderInfo['city']);
            $order['data'][$key]['county'] = $userConsigneeData->getRegionName($orderInfo['county']);
            $order['data'][$key]['address'] = $orderInfo['address'];

            $order['data'][$key]['audit_state'] = $orderInfo['audit_state'];
            $order['data'][$key]['audit_time'] = $orderInfo['audit_time'];
            $order['data'][$key]['carriage'] = sprintf("%.2f", $orderInfo['carriage']);
            $order['data'][$key]['is_global'] = $global;
            $order['data'][$key]['is_show_logisticsbutton'] = false;
            // 剩余未支付金额
            $order['data'][$key]['left_unpaid'] = bcsub($orderInfo['order_total'], $orderInfo['balance_price'], 2);
            $order['data'][$key]['balance_price'] = $orderInfo['balance_price'];
            $order['data'][$key]['payment_id'] = $orderInfo['payment_id'];
            // 支付名称
            if (!isset(OrderEnum::$PaymentName[$orderInfo['payment_id']])) {
                $order['data'][$key]['payment_name'] = '在线支付';
            } else {
                $order['data'][$key]['payment_name'] = OrderEnum::$PaymentName[$orderInfo['payment_id']];
            }
            $order['data'][$key]['allow_comment'] = $orderInfo['allow_comment'];
            $order['data'][$key]['pay_link'] = '';
            $order['data'][$key]['callback_link'] = '';
            $order['data'][$key]['used_logistics_wap_api'] = $config->used_logistics_wap_api;

//            $cancel = $config->order_effective_time * 3600;
//            if($orderInfo['express_type'] == 2 || $orderInfo['express_type'] == 3 && $global == 0){
//                $cancel = $config->o2o_order_effective_time * 3600;
//            }
            $cancel = 1800;

            $cancelTime = $orderInfo['status'] == OrderEnum::ORDER_PAYING ? (int) $value['add_time'] + $cancel - time() : 0;
            $order['data'][$key]['cancel_time'] = $cancelTime < 0 ? 0 : $cancelTime;
            $order['data'][$key]['valid_time'] = $cancel;
            $order['data'][$key]['refund_status'] = 0;
            if (!in_array($orderInfo['express_type'],[2,3]) && (in_array($orderInfo['status'], [OrderEnum::ORDER_SHIPPED, OrderEnum::ORDER_EVALUATING, OrderEnum::ORDER_FINISHED]
                    ) || $orderInfo['express_sn'])) {
                $order['data'][$key]['is_show_logisticsbutton'] = true;
            }

            $order['data'][$key]['giftsList'] = $order['data'][$key]['goodsList']  = [];
            $orderDetailColumn = 'a.id,a.total_sn,a.order_sn,a.goods_id,a.goods_name,a.goods_image,a.price,a.unit_price,
                a.goods_number,a.specifications,a.is_comment,a.is_return,a.add_time,a.goods_type,a.discount_price,
                a.discount_remark,a.stock_type,a.market_price,a.original_price,a.promotion_origin,a.promotion_code,a.invite_code,a.code_bu,a.code_region,a.code_office';
            $orderDetailColumn .= $global ?  ',a.push_host,a.business_id' : ',a.group_id,a.tag_id,a.treatment_id';
            // 获取订单详细
            $orderDetail = $orderDetailData->getOrderDetail([
                'column' => $orderDetailColumn.',c.drug_type',
                'where' => 'a.order_sn = :order_sn: order by a.goods_type asc',
                'bind' => [
                    'order_sn' => $value['order_sn']
                ]
            ], $this->switchOrderDb($lockKey), $global);
            if(empty($orderDetail)){
//                return ['status' => 101, 'explain' => json_encode($orderDetail), 'data' => ""];
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            foreach ($orderDetail as $k => $v){
                // 普通商品
                if($v['goods_type'] == 0){
                    $v['is_global'] = $global;
                    $order['data'][$key]['goodsList'][] = $v;
                }else{
                    // 赠品
                    $order['data'][$key]['giftsList'][] = $v;
                }
            }
            if($global){
                $goodsIdStr = implode(',',array_column($orderDetail,'goods_id'));
                // 海外购订单需验证速愈素
                $isQuicksinOrder = $orderDetailData->isQuicksinOrder($goodsIdStr);
                if($isQuicksinOrder){
                    $order['data'][$key]['pay_link'] = $config->wap_base_url[$env].'order-pay.html?order_id='.$order['data'][$key]['order_sn'].'&is_global='.$global;
                    $order['data'][$key]['callback_link'] = $config->wap_base_url[$env].'order-submit-successfully.html';
                }
            }

            // 退货/售后
            $order['data'][$key]['returnInfo'] = null;
            if ($global === 0) {
                $reasonRet = $baseData->getData([
                    'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
                    'column' => '*',
                    'where' => 'where order_sn = :order_sn:',
                    'bind' => [
                        'order_sn' => $value['order_sn']
                    ],
                ], true);
                if(!empty($reasonRet)){
                    $order['data'][$key]['returnInfo'] = $reasonRet;
                    $order['data'][$key]['refund_status'] = $reasonRet['status'];
                }
            } else {
                $order['data'][$key]['returnInfo'] = null;
            }
//
//            foreach ($order['data'][$key]['goodsList'] as $kk => $vv) {
//                // 判断是否会员标签价
//                $order['data'][$key]['goodsList'][$kk]['memberTagName'] = '';
//                $memberTagName = '';
//                if (isset($vv['tag_id'])) {
//                    if ($platform == OrderEnum::PLATFORM_APP) {
//                        $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $vv['tag_id']);
//                    } else {
//                        // 非app端要排除辣妈
//                        if ($vv['tag_id'] != 0) {
//                            $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $vv['tag_id']);
//                        }
//                    }
//                    if ($memberTagName) {
//                        $order['data'][$key]['goodsList'][$kk]['memberTagName'] = $memberTagName['tag_name'].'价';
//                    }
//                }
//            }
            // 判断订单是否处方单
            $goodsIdsStr = implode(',',array_column($orderDetail,'goods_id'));
            $drugTypeArr = $this->filterData('drug_type',$this->getGoodsDetail(['goods_id' => $goodsIdsStr, 'platform' => $platform]));
            $order['data'][$key]['rx_exist'] = (in_array(1,array_column($drugTypeArr,'drug_type'))) ? 1 : 0;

            if ($orderInfo['express_type'] > 1) {
                $order['data'][$key]['audit_time'] = $orderInfo['audit_time'] + $config->o2o_order_effective_time * 3600 - time();
            } else {
                $order['data'][$key]['audit_time'] = $orderInfo['audit_time'] + $config->order_effective_time * 3600 - time();
            }
        }
        // 计算订单商品总数
        if(!empty($order['data'])){
            foreach ($order['data'] as $key => $value){
                if(!empty($value['goodsList'])){
                    $total = 0;
                    foreach ($value['goodsList'] as $kk => $vv){
                        $total += $vv['goods_number'];
                    }
                    $order['data'][$key]['goods_total'] = $total;
                }
                $group = BaiyangGroupFightData::getInstance()->getGroupFightBuyByOrderSn($value['order_sn']);
                if(empty($group) === false){
                    $fight_list = $group->toArray();
                    if(count($fight_list) > 0){
                        $order['data'][$key]['fight'] = array_shift($fight_list);
                    }
                }
            }
        }


        $data = [
            'order_list' => $order['data'],
            'order_count' => $totalItem,
            'total_pages' => ceil($totalItem / $param['pageSize']),
            'page_index' => $param['pageStart'],
            'page_size' => $param['pageSize'],
        ];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * 获取订单列表
     * @param $param
     *      -int user_id 用户id
     *      -string status 订单状态
     *      -int pageStart 当前页码 (默认为1)
     *      -int pageSize 每页条数 (默认为5)
     *      -string platform  平台
     * @return \array[]
     */
    public function getOrderList($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['status'] = isset($param['status']) ? (string)$param['status'] : '';
        $param['pageStart'] = isset($param['pageStart']) && (int)$param['pageStart'] > 0 ? (int)$param['pageStart'] : 1;
        $param['pageSize'] = isset($param['pageSize']) && (int)$param['pageSize'] > 0 ? (int)$param['pageSize'] : 5;
        if (empty($param['user_id']) || empty($param['status']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 根据不同状态获取订单列表信息
        switch ($param['status']) {
            case "":
                // 所有订单列表
            case OrderEnum::ORDER_ALL:
                $param['condition'] = "";
                break;
            // 待支付订单列表
            case OrderEnum::ORDER_PAYING:
                $param['condition'] = "(status = 'paying' or (status = 'shipping' and audit_state = 0))";
                break;
            // 待发货订单列表
            case OrderEnum::ORDER_SHIPPING:
                $param['condition'] = "status = 'shipping' and audit_state = 1";
                break;
            // 待收货订单列表
            case OrderEnum::ORDER_SHIPPED:
                $param['condition'] = "status = 'shipped'";
                break;
            // 待评价订单列表
            case OrderEnum::ORDER_EVALUATING:
                $time = time() - 90*24*60*60;
                $param['condition'] = "status = 'evaluating' and express_time > {$time}";
                break;
            //待成团
            case "await":
                $param['condition'] = "status = 'await'";
                break;
        }
        return $this->getGroupOrderList($param);
    }

    /**
     * 获取拼团活动详情
     * @param $param
     * @return \array[]
     */
    public function getGroupActivityDetailed($param)
    {
        $user_id = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $act_id = isset($param['act_id']) ? (int)$param['act_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';

        // 判断参数是否合法
        if ( $act_id <= 0 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }

        $data = BaiyangGroupFightData::getInstance()->getGroupFightActivityDetailed($act_id);

        if(empty($data)){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $data = $data->toArray();

        $data['is_join'] = 0;
        $data['is_new_user'] = 1;
        $data['join_number'] = 0;
        $data['first_image'] = '';
        if(empty($data['goods_slide_images']) === false && count( $data['goods_slide_images']) > 0){
            $data['first_image'] = $data['goods_slide_images'][0];
        }

        if($user_id > 0){
            $number = BaiyangGroupFightData::getInstance()->getGroupJoinNumber($act_id,$user_id);

            $fight_list = BaiyangGroupFightData::getInstance()->getGroupFightListByUserId($act_id,$user_id);

//            if(empty($fight_list) === false){
//                foreach ($fight_list as $item){
//                    if(($item['gf_state'] == 1 and $item['gf_end_time'] > time()) or $item['gf_state'] == 2 ){
//                        $data['is_join'] = 1;
//                        break;
//                    }
//                }
//            }
            $data['is_new_user'] = intval(BaiyangGroupFightData::getInstance()->isNewUser($act_id)) > 0 ? 0 : 1;

            $data['fight'] = $fight_list;
            $data['join_number'] = $number;
            $data['is_join'] =  $number > 0;
        }

        // 格式化参数
//        $param['goods_id'] = intval($data['goods_id']);
//        $param['user_id'] = $user_id;
//        $param['is_temp'] =  0;
//        $param['platform'] = $platform;

        //       $promotion = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionInfoById',$this,$param);

//        if(isset($promotion['data']['discountInfo']) ) {
//            $data['goods_price'] = $promotion['data']['discountInfo']['goods_price'];
//            $data['market_price'] = $promotion['data']['discountInfo']['market_price'];
//            $data['stock'] = $promotion['data']['discountInfo']['stock'];
//        }
//        $good = BaiyangSkuData::getInstance()->getSkuInfo($param['goods_id'],$platform);
//
//        if($good !== false && empty($good) === false){
//            $data['original_price'] = $good['sku_price'];
//            $data['body'] = $good['sku_desc'];
//        }
        $data['gfa_join_num'] = $data['gfa_join_num'] + intval($data['gfa_num_init']);
       // unset($data['gfa_num_init']);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * 获取指定拼团的参与详情
     * @param $param
     *      - int fight_id 参团ID
     *      - int user_id 用户ID
     * @return \array[]
     */
    public function getGroupFight($param)
    {
        $fight_id = isset($param['fight_id']) ? intval($param['fight_id']) : 0;
        $user_id = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';

        if($fight_id <= 0  || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }

        //查询当前拼团的详情
        $fight = BaiyangGroupFightData::getInstance()->getGroupFightAndUserList($fight_id);

        if(empty($fight)){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

        //查询当前活动详情
        $act_result = BaiyangGroupFightData::getInstance()->getGroupFightActivityDetailed($fight['gfa_id']);

        if(empty($act_result) === false){
            $data = $act_result->toArray();
            $data['first_image'] = '';
            if(empty($data['goods_slide_images']) === false){
                $data['first_image'] = $data['goods_slide_images'][0];
            }
            if($user_id > 0){
                //查询当前用户拼团次数
                $data['join_number'] = BaiyangGroupFightData::getInstance()->getGroupJoinNumber($data['gfa_id'],$user_id);
                $data['user']['join_number'] = $data['join_number'];
            }else{
                $data['join_number'] = 0;
                $data['user']['join_number'] = 0;
            }
        }else{
            $data = [];
            $data['user']['join_number'] = 0;
            $data['join_number'] = 0;
        }
        $fight['is_win'] = 0;
        $fight['join_time'] = 0;
        $fight['join_number'] = 0;
        $fight['end_time'] = $fight['gf_end_time'] - time();

        $data['user']['is_win'] = 0;
        $data['user']['join_time'] = 0;
        $data['user']['is_join'] = intval($data['join_number'] > 0);
        $data['user']['is_head'] = 0;
        $data['user']['user_id'] = $user_id;
        $data['user']['is_join_fight'] = false;


        if($user_id > 0 && empty($fight['user_list']) === false){
            foreach ($fight['user_list'] as &$item){
                if($item['user_id'] == $fight['user_id']){
                    $fight['is_win'] = $item['is_win'];
                    $fight['join_time'] = $item['join_time'];
                    $fight['join_number'] = BaiyangGroupFightData::getInstance()->getGroupJoinNumber($data['gfa_id'],$user_id);

                }

                if($item['user_id'] == $user_id){
                    $data['user']['is_win'] = $item['is_win'];
                    $data['user']['join_time'] = $item['join_time'];
                    $data['user']['is_join'] = 1;
                    $data['user']['is_head'] = $item['is_head'];
                }
            }
        }

        if($data['user']['join_time'] > 0){
            $data['user']['is_join_fight'] = true;
        }
        if(empty($act_result) === false){

            if($fight['gf_end_time'] > time()){
                $data['end_time'] = $data['gfa_endtime'] - time();
            }else{
                $data['end_time'] = 0;
            }
            $data['fight'] = $fight;

            if($user_id){
                $data['is_new_user'] = intval(BaiyangGroupFightData::getInstance()->isNewUser($user_id)) > 0 ? 0 : 1;
            }else{
                $data['is_new_user'] = 1;
                $data['is_head'] = 0;
            }

            return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
        }
        return $this->uniteReturnResult(HttpStatus::NO_DATA);
    }

    /**
     * 获取拼团列表
     * @param $param
     *      - int page 分页索引
     *      - int size 每页记录数量
     *      - int user_id 用户ID
     *      - string platform 客户端类型
     *      - string not_in 排除的活动ID数组
     * @return \array[]
     */
    public function getGroupList($param)
    {
        $page = isset($param['page']) ? intval($param['page']) : 1;
        $size = isset($param['size']) ? intval($param['size']) : 10;
        $user_id = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';

        if(!$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }


        $params = [];
        if(isset($param['not_in']) && is_array($param['not_in'])){
            $params['not_in'] = $param['not_in'];
        }

        $group_result = BaiyangGroupFightData::getInstance()->getGroupList($params,$page,$size);

//        if($group_result !== null ){
//            foreach ($group_result['lists'] as &$item){
//                // 格式化参数
//                $param['goods_id'] = isset($item['goods_id']) ? (int)$item['goods_id'] : 0;
//                $param['user_id'] = $user_id;
//                $param['is_temp'] =  0;
//                $param['platform'] = $platform;
//                if($param['goods_id'] <= 0){
//                    break;
//                }
//
//                $promotion = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionInfoById',$this,$param);
//
//                if(isset($promotion['data']['discountInfo']) ) {
//                    $item['goods_price'] = $promotion['data']['discountInfo']['goods_price'];
//                    $item['market_price'] = $promotion['data']['discountInfo']['market_price'];
//                    $item['stock'] = $promotion['data']['discountInfo']['stock'];
//                }
//                $good = BaiyangSkuData::getInstance()->getSkuInfoLess($param['goods_id'],$platform);
//
//                if($good !== false && empty($good) === false){
//                    $item['original_price'] = $good['sku_price'];
//                }
//            }
//        }

        return $this->uniteReturnResult(HttpStatus::SUCCESS, $group_result);
    }

    /**
     * 获取我参与的拼团列表
     * @param $param
     * @return \array[]
     */
    public function getGroupFightList($param)
    {
        $page = isset($param['page']) ? intval($param['page']) : 1;
        $size = isset($param['size']) ? intval($param['size']) : 10;
        $user_id = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';

        if($user_id <=0 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }

        $lists = BaiyangGroupFightData::getInstance()->getFightListByUserId($user_id,$page,$size);

//        if(empty($lists['lists']) === false){
//            foreach ($lists['lists'] as &$item){
//                // 格式化参数
//                $param['goods_id'] = isset($item['goods_id']) ? (int)$item['goods_id'] : 0;
//                $param['user_id'] = $user_id;
//                $param['is_temp'] =  0;
//                $param['platform'] = $platform;
//                if($param['goods_id'] <= 0){
//                    break;
//                }
//
//                $promotion = $this->_eventsManager->fire('promotionInfo:getGoodsPromotionInfoById',$this,$param);
//
//                if(isset($promotion['data']['discountInfo']) ) {
//                    $item['goods_price'] = $promotion['data']['discountInfo']['goods_price'];
//                    $item['market_price'] = $promotion['data']['discountInfo']['market_price'];
//                    $item['stock'] = $promotion['data']['discountInfo']['stock'];
//                }
//                $good = BaiyangSkuData::getInstance()->getSkuInfoLess($param['goods_id'],$platform);
//
//                if($good !== false && empty($good) === false){
//                    $item['original_price'] = $good['sku_price'];
//                }
//            }
//        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $lists);
    }
}
