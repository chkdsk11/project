<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/11/16 0016
 * Time: 上午 9:46
 */

namespace Shop\Home\Services;

use Phalcon\Exception;
use Shop\Models\CacheKey;
use Shop\Libs\CacheRedis;

use Shop\Home\Datas\{
	BaiyangUserGoodsPriceTagData, BaiyangUserData, BaseData,
    BaiyangO2OrderData, BaiyangOrderLogData, BaiyangO2OrderDetailData,
	BaiyangAnnouncementData, BaiyangSkuData, BaiyangUserConsigneeData,
	BaiyangUserSinceShopData, BaiyangYfzData, BaiyangUserInvoiceData,
    BaiyangO2OGoodsStockChangeLogData, BaiyangO2OPromotionData, BaiyangGoodsShoppingOfflineCart,
	BaiyangKjOrderData, BaiyangKjOrderDetailData,BaiyangCpsData,BaiyangCouponRecordData,BaiyangO2oData
	};

use Shop\Home\Listens\{
	OrderFigureCoupon, OrderCheckExpress, OrderCheckPrefix,
	PromotionCoupon, PromotionGetGoodsDiscountPrice, PromotionGoodsDetail,
	PromotionGoodset, PromotionLimitBuy, O2OPromotionShopping,
    O2OPromotionCalculate, O2OFreightListener, BalanceListener, MomListener,StockListener
	};//,O2OPromotionCoupon

use Shop\Models\{
	BaiyangOrder, BaiyangKjOrder, BaiyangOrderDetail,
	HttpStatus, OrderEnum, BaiyangConfigEnum, BaiyangPromotionEnum
	};

use Phalcon\Events\{
	Manager as EventsManager, Event
	};

use Shop\Home\Services\{
	BaseService, AuthService
	};

class O2OrderService extends O2OBaseService
{
    protected static $instance=null;

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new O2OrderService();
        }

        //实例化事件管理器
        $eventsManager=new EventsManager();

        //开启事件结果回收
        $eventsManager->collectResponses(true);

       /********************订单提交前的信息校验------添加对应侦听器********/
        //$eventsManager->attach('order_before_listen',new OrderCheckExpress());   //物流配送选择校验
        //$eventsManager->attach('order_before_listen',new OrderCheckPrefix()); //订单前辍校验
        /*******************订单提交前的信息校验------添加对应侦听器*******/

        /********************************促销侦听器*********************************/
        $eventsManager->attach('promotion',new O2OPromotionCalculate());
        //$eventsManager->attach('promotion',new PromotionLimitBuy());
        $eventsManager->attach('promotion',new O2OPromotionShopping());
       // $eventsManager->attach('promotion',new MomListener());
        /********************************促销侦听器************************/

        /********************优惠券计算**************************/
       // $eventsManager->attach('coupon',new OrderFigureCoupon());
        //$eventsManager->attach('coupon',new O2OPromotionCoupon());
        /*********************优惠券计算************************/

        /********************运费计算**************************/
        $eventsManager->attach('freight',new O2OFreightListener());
        /*********************优惠券计算************************/

        /********************余额支付**************************/
        $eventsManager->attach('balance',new BalanceListener());
        /*********************优惠券计算************************/

        $eventsManager->attach('order',new StockListener());

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }
	

	
    /**
     * @desc 确认订单页面
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -address_id int 地址ID（*）
     *       -coupon_sn string 优惠券编号
     *       -payment_id int 支付方式：0-在线支付，3 : 货到付款，默认：0
     *       -express_type int 配送方式:0-普通快递,1-顾客自提,2-两小时达,3-当日达
     *       -o2o_time int O2O配送时间
     *       -shop_id int 门店ID
     *       -is_balance int 是否启用余额（1:开启 0:关闭），默认开启
     *       -is_first int 是否初访问，取值：0，1，默认：0
     *       -platform string 平台：pc,wap,app
     * @return array
     * @author 柯琼远
     */
    public function confirmOrder($param) {
        // 购物车和促销信息
        $param['is_first'] = isset($param['is_first']) ? (int)$param['is_first'] : 0;
        $param['action'] = $param['is_first'] == 0 ? "coupon" : "orderInfo";
        $ret = $this->orderPromotionInfo($param);
        if ($ret['status'] != HttpStatus::SUCCESS) {
            return $ret;
        }
        $result = $ret['data'];
        $result['expressType'] = $param['express_type'];

        // 验证用户信息
        $userInfo = BaiyangUserData::getInstance()->getUserInfo($param['user_id'], 'balance,pay_password,default_consignee');
        if (empty($userInfo)) {
            return $this->uniteReturnResult(HttpStatus::USER_NOT_EXIST, ['param' => $param]);
        }
        // 用户余额 && 是否已设置支付密码
        $result['balance'] = $userInfo['balance'];
        $result['isSetPwd'] = !empty($userInfo['pay_password']) ? 1 : 0;
        $result['consigneeListOther'] = [];
        // 收货地址
        $result['deliveryAddress'] = BaiyangUserConsigneeData::getInstance()->getUserConsigneeList($param['user_id']);

        //获取o2o 的配送范围
        $range = $this->getOtoDeliveryArea();
        $result['consigneeList'] = [
            'oto'=>[],
            'other'=>[]
        ];

        if($result['deliveryAddress'] and is_array($result['deliveryAddress'])){
            foreach($result['deliveryAddress'] as $k=>$v){
                if($range){
                    if(in_array($v['county'], $range)
                        and
                        !(
                            $v['city'] == 284
                            and
                            (
                                strpos($v['address'] , '开封路88号') !== false
                                or strpos($v['address'] , '百洋科技园') !== false
                                or strpos($v['address'] , '百洋健康科技园') !== false
                            )
                        )
                    ){
                        $result['consigneeList']['oto'][] = $v;
                    }else{
                        $result['consigneeList']['other'][] = $v;
                    }
                }else{
                    $result['consigneeList']['other'][] = $v;
                }
            }
        }
        unset($rangeRs);
        unset($result['deliveryAddress']);

        $param['address_id'] = isset($param['address_id']) ? (int)$param['address_id'] : 0;
        $ret= $this->func->arrayFieldSelected($result['consigneeList']['oto'], $param['address_id']);
        $result['consigneeList']['oto'] = $ret['list'];
        $param['address_id'] = $ret['value'];
        $consigneeInfo = $ret['info'];

        // 门店
//        $result['sinceShopList'] = BaiyangUserSinceShopData::getInstance()->getSinceShopList();
//        $param['shop_id'] = isset($param['shop_id']) ? (int)$param['shop_id'] : 0;
//        $ret = $this->func->arrayFieldSelected($result['sinceShopList'], $param['shop_id']);
//        $result['sinceShopList'] = $ret['list'];
//        $param['shop_id'] = $ret['value'];
        // 发票
        if ($param['is_first'] == 1) {
            $invoiceInfo = BaiyangUserInvoiceData::getInstance()->getUserInvoice($param['user_id']);
            if (!empty($invoiceInfo)) $result['invoiceInfo'] = array_merge(["if_receipt"=>1], $invoiceInfo);
        } else {
            unset($result['invoiceInfo']);
        }
	    // 配送信息
        $result['facePayIfO2o'] = 1;
        $result = $this->getExpressInfo($result, $param, $consigneeInfo);
        // 余额支付
        $result['isBalance'] = isset($param['is_balance']) && empty($param['is_balance']) ? 0 : 1;
        if ($result['isBalance'] == 1) {
            $result['costBalance'] = $result['balance'] > $result['costPrice'] ? $result['costPrice'] : $result['balance'];
            $min_amount_for_password = $this->func->getConfigValue('min_amount_for_password');
            $result['isNeedPwd'] = $min_amount_for_password < $result['costBalance'] ? 1 : 0;
            $result['costPrice'] = bcsub($result['costPrice'], $result['costBalance'], 2);
        }
        // 返回
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }


    /**
     * @desc 提交订单
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -address_id int 地址ID（*）
     *       -insure_amount int 保费
     *       -buyer_message string 买家留言
     *       -coupon_sn string 优惠券编号
     *       -payment_id int 支付方式：0-在线支付，3-货到付款
     *       -express_type int 配送方式:0-普通快递,1-顾客自提,2-两小时达,3-当日达（*）
     *       -o2o_time int O2O配送时间
     *       -shop_id string 门店ID
     *       -invoice_type int 发票类型 0不需要 1个人 2单位
     *       -invoice_header string 发票抬头
     *       -invoice_content_type int 发票内容：10-药品,11-生活用品，12-医疗用品，13-医疗器械，14-计生用品，15-食品，16-明细
     *       -is_balance int 是否使用余额支付：0-不使用，1-使用
     *       -pay_password string 支付密码
     *       -callback_phone string 回拨电话
     *       -ordonnance_photo string 处方单图片
     *       -platform string 平台：pc,wap,app（*）
     *       -channel_subid string 渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
     * @return array
     * @author 柯琼远
     */
    public function commitOrder($param) {
        // 验证购物车和促销信息
        $ret = $this->orderPromotionInfo(array_merge($param, ['action'=> 'commitOrder']));
        //$this->log->error("\$param：" . print_r($param, 1) );
        if ($ret['status'] != HttpStatus::SUCCESS) return $ret;
        $result = $ret['data'];
        //return ['status'=>2,'data'=>1,'explain'=>1];
        // return $result;
        // 获取订单号
        $result['orderSn'] = $this->makeOrderSn();
        // 获取参数
        $result['cart_type'] = isset($param['cart_type']) ? intval($param['cart_type']) : 1;
        $result['userId'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $result['addressId'] = isset($param['address_id']) ? (int)$param['address_id'] : 0;
        $result['paymentId'] = isset($param['payment_id']) ? (int)$param['payment_id'] : 0;
        $result['expressType'] = isset($param['express_type']) ? (int)$param['express_type'] : 0;
        $result['o2oTime'] = isset($param['o2o_time']) ? (int)$param['o2o_time'] : 0;
        $result['shopId'] = isset($param['shop_id']) ? (int)$param['shop_id'] : 0;
        $result['isBalance'] = isset($param['is_balance']) ? (int)$param['is_balance'] : 0;
        $result['payPassword'] = isset($param['pay_password']) ? (string)$param['pay_password'] : '';
        $result['callbackPhone'] = isset($param['callback_phone']) ? (string)$param['callback_phone'] : '';
        $result['ordonnancePhoto'] = isset($param['ordonnance_photo']) ? (string)$param['ordonnance_photo'] : '';
        $result['invoiceType'] = isset($param['invoice_type']) ? (int)$param['invoice_type'] : 0;
        $result['invoiceHeader'] = isset($param['invoice_header']) ? htmlspecialchars($param['invoice_header']) : '';
        $result['invoiceContentType'] = isset($param['invoice_content_type']) ? (int)$param['invoice_content_type'] : 0;
        $result['buyerMessage'] = isset($param['buyer_message']) ? htmlspecialchars($param['buyer_message']) : '';
        $result['taxpayerNumber'] = isset($param['taxpayer_number']) ? htmlspecialchars($param['taxpayer_number']) : '';


        // 验证参数
        if ($result['userId'] < 1 || $result['addressId'] < 1 || !in_array($result['paymentId'],[0,3]) || !in_array($result['expressType'],[0,1,2,3]) || !in_array($result['isBalance'],[0,1])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        // 验证用户信息
        $userInfo = BaiyangUserData::getInstance()->getUserInfo($result['userId'], '*');
        if (empty($userInfo)) {
            return $this->uniteReturnResult(HttpStatus::USER_NOT_EXIST, $result);
        }
        $result['userId'] = $userInfo['id'];
        $result['unionUserId'] = $userInfo['union_user_id'];
        $result['phone'] = !empty($userInfo['phone']) ? $userInfo['phone'] : $userInfo['user_id'];
        $result['balance'] = $userInfo['balance'];
        $result['inviteCode'] = $userInfo['invite_code'];

        // 验证地址信息 , 验证收货地址在不在 OTO 送货区域内
        $ret = $this->chkShippingAddress($param['address_id'], $result['userId']);

        $result['consigneeInfo'] = $ret['data'];
        if ($ret['status'] != HttpStatus::SUCCESS) {
            return $this->uniteReturnResult($ret['status'], $result);
        }
        // 验证处方单
        if ($result['rxExist'] == 1 && empty($result['callbackPhone'])) {
            return $this->uniteReturnResult(HttpStatus::CALLBACKPHONE_IS_EMPTY, $result);
        }
        // 验证发票信息
        if ($result['invoiceType'] > 0) {
            if ((empty($result['invoiceHeader']) && $result['invoiceType'] == 2) || !isset(OrderEnum::$receiptContent[$result['invoiceContentType']])) {
                return $this->uniteReturnResult(HttpStatus::INVOICE_IS_EMPTY, $result);
            }
        }
        // 计算运费
        $ret = $this->getExpressInfoForCommit($result);
        if ($ret['status'] != HttpStatus::SUCCESS) return $ret;
        $result = $ret['data'];
        // 余额支付
        $ret = $this->payBalance($result);
        if ($ret['status'] != HttpStatus::SUCCESS) return $ret;
        $result = $ret['data'];
        //$this->log->error("\$result：" . print_r($result, 1) );
        // 入库
        $this->dbWrite->begin();
        $success = true;


        if (!BaiyangCpsData::getInstance()->pushCps($result)) $success = false;//异步推送CPS

        if (!BaiyangUserInvoiceData::getInstance()->insertUserInvoice($result)) $success = false;//插入发票信息
        if (!BaiyangO2OrderDetailData::getInstance()->insertOrderDetail($result)) $success = false;//插入订单详情

        if (!BaiyangO2OGoodsStockChangeLogData::getInstance()->insertGoodsStockChange($result)) $success = false;//库存变化
        if (!BaiyangO2OPromotionData::getInstance()->insertPromotionDetailLog($result)) $success = false;//插入促销日志
        if (!BaiyangO2OrderData::getInstance()->insertOrderPayDetail($result)) $success = false;//插入支付信息

        if (!BaiyangO2OrderData::getInstance()->insertOrder($result)) $success = false;//插入订单

        // 更新使用优惠券状态
        if (!empty($result['couponInfo'])) {
            $ret = BaiyangCouponRecordData::getInstance()->TradeToUpdateCoupon($result['orderSn'], $result['userId'], $result['couponInfo']['coupon_sn']);
            if ($ret['code'] != 200) $success = false;
        }
        //清空购物车
        if (!BaiyangGoodsShoppingOfflineCart::getInstance()->deleteShoppingCartAftercommitOrder($result)) $success = false;
        // 生成订单失败后

        if (!$success) {
            // 退款
            if ($result['costBalance'] > 0) {
                $this->_eventsManager->fire('balance:external_refund_order', $this, [
                    'order_sn'     => $result['orderSn'],
                    'refund_money' => $result['costBalance'],
                ]);
            }
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $result);
        }
        $this->dbWrite->commit();

        // 如果是余额支付或者货到付款，需要同步库存
        if ($result['paymentId'] == 3 || $result['paymentId'] == 7) {
            $this->_eventsManager->fire('order:syncStockAndSaleNumber', $this, ['order_sn'=>$result['orderSn']]);
            //$this->func->syncStockAndSaleNumber($result['orderSn']);
        }
        // 删除购物车缓存
        $this->RedisCache->delete(CacheKey::MAKE_ORDER_PROMOTION."0_".$result['userId']);
        $this->RedisCache->delete(CacheKey::ALL_CHANGE_PROMOTION.'0_'.$result['userId']);
        // 返回信息
        $validTime = $result['expressType'] > 1 ? time() + $this->config->o2o_order_effective_time * 3600 : time() + $this->config->order_effective_time * 3600;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, [
            'orderSn'      => $result['orderSn'],
            'leftAmount'   => bcsub($result['costPrice'], $result['costBalance'], 2),
            'isOnlinePay'  => $result['paymentId'] == 3 ? 0 : 1,
            'paymentId'    => $result['paymentId'],
            'allowComment' => $result['allRx'] == 1 ? 0 : 1,
            'balancePrice' => $result['costBalance'],
            'rxExist'      => $result['rxExist'],
            'validTime'    => $validTime,
            'status'       =>  $result['status'],
            'audit_state' => $result['rxExist'] == 1 ? 0 : 1,
            'isGlobal'     => 0,
            'payLink'      => '',
            'callbackLink' => '',
        ]);
    }

    ####################################################################################################
    /**
     * @desc 确认订单/提交订单
     * @param array $param
     * @author 柯琼远
     * @return array
     */
    //todo: change the function;
    protected function orderPromotionInfo($param) {

        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $isTemp = 0;
        $coupon_sn = isset($param['coupon_sn']) ? (string)$param['coupon_sn'] : '';
        $platform = isset($param['platform']) ? (string)$param['platform'] : "";
        $action = $param['action'];
        // 判断参数是否合法
        if ($userId < 1 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        // 获取购物车商品列表
        $cartList = BaiyangGoodsShoppingOfflineCart::getInstance()->getShoppingCart([
            'user_id'   => $userId,
            'is_temp'   => $isTemp,
            'is_global' => 0,
            'selected'  =>1
        ]);
        //$result['goodsList'] = $list;
        //$cartList = $list;  // 普通商品列表
        //unset($list);
//        $increaseBuyList = array();// 加价购商品列表
//        foreach ($list as $value) {
//            if ($value['increase_buy'] == 0) $cartList[] = $value;
//            else $increaseBuyList[] = $value;
//        }

        // 套餐/限时优惠会员价
        $result = $this->_eventsManager->fire('promotion:getGoodsDiscountInfo',$this,[
            'cartGoodsList'=> $cartList,
            'platform'=> $platform,
            'userId'=> $userId,
            'isTemp'=> $isTemp
        ]);

        $result['goodsList'] = $this->_eventsManager->fire('promotion:delNotSelectGoods',$this,$result['goodsList']);
        if (empty($result['goodsList'])) {
            return $this->uniteReturnResult(HttpStatus::NOT_SELECT_GOODS, ['param'=> $param]);
        }
        // 加价购的商品特殊处理
        //$result['increaseBuyList'] = $increaseBuyList;
        // 显示促销活动
//        $result = $this->_eventsManager->fire('promotion:getCartPromotion',$this,[
//            'shoppingCartInfo'=> $result,
//            'userId'=> $userId,
//            'isTemp'=> $isTemp
//        ]);
        // 验证限购
        //$listenParam = array_merge($this->_eventsManager->fire('promotion:getLimitBuyParam',$this,$result), ['platform'=>$platform, 'user_id'=>$userId, 'is_temp'=>0]);
        //$ret = $this->_eventsManager->fire('promotion:limitBuy',$this,$listenParam);
        //if ($ret['error'] == 1) {
         //   return $this->uniteReturnResult($ret['code'], ['param'=>$param], $ret['data']);
        //}

        // 计算活动门槛
        $result = $this->getGoodsPromotionInfo($result, ['platform' => $platform, 'user_id' => $userId, 'is_temp' => $isTemp], $action, $coupon_sn);
        // 去除因各种原因没选中的商品
        $result['goodsList'] = $this->_eventsManager->fire('promotion:delNotSelectGoods',$this,$result['goodsList']);
        if (empty($result['goodsList'])) {
            return $this->uniteReturnResult(HttpStatus::NOT_SELECT_GOODS, ['param'=> $param]);
        }
        // 验证库存
        $ret = $this->_eventsManager->fire('promotion:verifyOrderGoods', $this, $result);
        if ($ret['status'] != HttpStatus::SUCCESS) {
            return $ret;
        }
        // 获取到达门槛的活动列表
        //$result['availPromotionList'] = $this->_eventsManager->fire('promotion:getCanUsePromotion',$this,$result);
        if ($action == "orderInfo" || $action == "coupon") {
            // 显示换购品和赠品
            $result = $this->_eventsManager->fire('promotion:getIncreaseBuyGiftShow',$this,$result);
            // 梳理结算页面所需求的字段
            $result = $this->_eventsManager->fire('promotion:getOrderInfoField',$this,$result);
        } else {
            // 梳理提交订单所需求的字段
            $result = $this->_eventsManager->fire('promotion:getCommitOrderField',$this,$result);
            if (!empty($coupon_sn) && empty($result['couponInfo'])) {
                return $this->uniteReturnResult(HttpStatus::INVALID_COUPON, ['param'=> $param]);
            }
        }
        // 判断处方药能不能加到购物车
//        if ($result['rxExist'] == 1 && $this->func->getDisplayAddCart($platform) == 0) {
//            return $this->uniteReturnResult(HttpStatus::RX_CANNOT_ADD_TO_CART, ['param'=> $param]);
//        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }
    // 配送方式、支付方式信息
    private  function getExpressInfo ($result, $param, $consigneeInfo) {
        //$result['expressType'] = 0;
        // O2O信息
        if (!empty($consigneeInfo)) {
            $o2oInfo = $this->_eventsManager->fire('freight:getO2OExpressInfo', $this, ['consigneeInfo'=>$consigneeInfo]);
            if ($o2oInfo['status'] == HttpStatus::SUCCESS) {

                $result['expressType'] = $o2oInfo['data']['type'] == 1 ? 2 : 3;
                $o2oTime = isset($param['o2o_time']) ? (int)$param['o2o_time'] : 0;

                $ret = $this->func->arrayFieldSelected($o2oInfo['data']['list'], $o2oTime, 'time');

                $o2oInfo['data']['list'] = $ret['list'];
                $o2oResult = $ret['info'];
                $result['o2oInfo'] = $o2oInfo['data'];

                foreach ($result['o2oInfo']['list'] as $key => $val) {
                    $result['o2oInfo']['list'][$key]['fee'] = ($result['costPrice'] >= $result['o2oInfo']['free_price']) ?  "0.00" : $val['fee'];
                }

            }
        }
//        if (!$param['is_first'] && isset($param['express_type']) && in_array($param['express_type'], [0, 1])) {
//            // 非PC不支持自提
//            $result['expressType'] = $this->config->platform != 'pc' ? 0 : (int)$param['express_type'];
//        }
        // 支付方式
        $result['paymentId'] = isset($param['payment_id']) && in_array($param['payment_id'], [0, 3]) ? (int)$param['payment_id'] : 0;
        // 配送方式重构
        $result = $this->_eventsManager->fire('freight:remakeExpress', $this, $result);
        if($result['expressList'] and is_array($result['expressList'])){
            foreach($result['expressList'] as $k=>$v){
                if($v['express_type'] == 1 or $v['express_type'] == 0) unset($result['expressList'][$k]);
            }
        }
        // 计算运费
//        if ($result['isDummy'] == 1 || $result['isExpressFree'] == 1 || empty($consigneeInfo)) {
//            // 包邮或者虚拟订单或者没有地址
//            $tips = ['free_price' => "0.00", 'not_free_fee' => "0.00", 'lack_price' => "0.00", 'promote_text'=>''];
//            $result['freightInfo'] = ['freight' => "0.00", 'tips' => $tips];
//            $result['facePayTips'] = $tips;
//        } else {
            //if ($result['expressType'] > 1) {

            // o2o运费
            $freePrice = isset($result['o2oInfo']['free_price']) ? $result['o2oInfo']['free_price'] : 0;

            //应付款 > 包邮价格 ? 包邮 : 模板运费
            $freight = $result['costPrice'] >= $freePrice ?  "0.00" : $o2oResult['fee'];
            $lack_price = $freight > 0 ? bcsub($freePrice, $result['costPrice'], 2) : "0.00";
            $result['freightInfo'] = [
                'freight' => $freight,
                'tips' => [
                    'free_price' => $freePrice,
                    'not_free_fee' => isset($o2oResult['fee']) ? $o2oResult['fee'] : '',
                    'lack_price' => $lack_price,
                    'promote_text' => $freight > 0 ? "再购买".$lack_price."元免邮" : '',
                ]
            ];

            //}
//            else {
//                $freightParam = [
//                    'goods_ids' => $result['goodsIds'],
//                    'region_id' => $consigneeInfo['province'],
//                    'type' => 0,
//                    'total' => $result['costPrice'],
//                ];
//                if ($result['expressType'] == 0) {
//                    // 普通快递
//                    //$freightParam['type'] = $result['paymentId'] == 3 ? 1 : 0;
//                } else {
//                    // 自提
//                    //$freightParam['type'] = 2;
//                   // $freightParam['region_id'] = $param['shop_id'];
//                }
//                $result['freightInfo'] = $this->_eventsManager->fire('freight:getFreightFee', $this, $freightParam)['data'];
//            }
	        // 付到付款运费提示
	        if ( $result['paymentId'] == 3 ) {
		        $result['facePayTips'] = $result['freightInfo']['tips'];
	        }
// else{
//		        $result['facePayTips'] = $this->_eventsManager->fire('freight:getFreightFee', $this, [
//			        'goods_ids' => $result['goodsIds'],
//			        'region_id' => $consigneeInfo['province'],
//			        'type' => 1,
//			        'total' => $result['costPrice'],
//		        ])['data']['tips'];
//	        }
        //}
        unset($result['o2oInfo']);
        // 配送公告
        $result['announcement'] = BaiyangAnnouncementData::getInstance()->getAnnouncement($consigneeInfo);
        // 计算应付金额
        $result['costPrice'] = bcadd($result['costPrice'], $result['freightInfo']['freight'], 2);
        return $result;
    }



    // 支付余额
    private function payBalance($result) {
        if ($result['isBalance'] == 1) {
            $result['costBalance'] = $result['costPrice'] > $result['balance'] ? $result['balance'] : $result['costPrice'];
            if ($this->func->getConfigValue('min_amount_for_password') < $result['costBalance']) {
                if (!isset($result['payPassword']) || empty($result['payPassword'])){
                    return $this->uniteReturnResult(HttpStatus::PAY_PASSWORD_IS_EMPTY, $result);
                }
            }
            $balanceResult = $this->_eventsManager->fire('balance:add_user_expend', $this, [
                'phone'        => $result['phone'],
                'amount'       => $result['costBalance'],
                'pay_password' => $result['payPassword'],
                'order_sn'     => $result['orderSn'],
            ]);
            if ($balanceResult['status'] != HttpStatus::SUCCESS) return $balanceResult;
            $result['costBalance'] = $balanceResult['data']['expend_amount'];
            $result['expendSn'] = $balanceResult['data']['expend_sn'];
            if (bcsub($result['costPrice'], $result['costBalance'], 2) == 0) {
                $result['paymentId'] = 7;
                $result['status'] = 'shipping';
                $result['paymentName'] = '余额支付';
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }



    /**提交订单时检测收货地址是不是OTO的地址
     * @param $address_id
     * @param $userId
     * @return mixed
     */
    private function chkShippingAddress($address_id, $userId){
        $consigneeData = BaiyangUserConsigneeData::getInstance();
        $consigneeParam = [
            'column' => 'id,consignee,address,province,city,county,telphone,zipcode',
            'where' => 'id = :id: and user_id = :user_id:',
            'bind' => ['id' => $address_id,'user_id' => $userId]
        ];
        $consigneeInfo = $consigneeData->getConsigneeInfo($consigneeParam);
        if(empty($consigneeInfo)){
            return $this->uniteReturnResult(HttpStatus::SHIP_ADDRESS_NUM_ERROR, $consigneeInfo);
        }
        //获取o2o 的配送范围
        $range = $this->getOtoDeliveryArea();
        if(empty($range)){
            return $this->uniteReturnResult(HttpStatus::SHIP_ADDRESS_NUM_ERROR);
        }
        if(in_array($consigneeInfo['county'], $range) === false){
            return $this->uniteReturnResult(HttpStatus::SHIP_ADDRESS_NUM_ERROR);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $consigneeInfo);

    }
    // 支付方式，运费
    private function getExpressInfoForCommit($result) {
        //if ($result['expressType'] > 1) {

            $o2oInfo = $this->_eventsManager->fire('freight:getO2OExpressFee', $this, [
                'consigneeInfo'=>$result['consigneeInfo'],
                'time'=>$result['o2oTime'],
                'total'=>$result['costPrice'],
            ]);
            if ($o2oInfo['status'] != HttpStatus::SUCCESS) {
                return $o2oInfo;
            }
            $result['o2oInfo'] = $o2oInfo['data'];
            $result['freight'] = $o2oInfo['data']['fee'];
             //expressType: 2-两小时达,3-当日达
            //type : 1 两小时达,2-当日达
            if (($result['expressType'] == 2 && $o2oInfo['data']['type'] != 1)
                || ($result['expressType'] == 3 && $o2oInfo['data']['type'] != 2)) {

                return $this->uniteReturnResult(HttpStatus::O2O_EXPRESS_TIME_INVALID, $result);
            }
        //}
//        else {
//            if ($result['isDummy'] == 0 && $result['isExpressFree'] == 0) {
//                $freightParam = [
//                    'goods_ids' => $result['goodsIds'],
//                    'region_id' => $result['consigneeInfo']['province'],
//                    'type' => 0,
//                    'total' => $result['costPrice'],
//                ];
//                if ($result['expressType'] == 0) {
//                    // 普通快递
//                    if ($result['paymentId'] == 3) {
//                        // 货到付款
//                        $freightParam['type'] = 1;
//                        $result['status'] = 'shipping';
//                    } else {
//                        // 在线支付
//                        $freightParam['type'] = 0;
//                    }
//                } else {
//                    // 自提
//                    $shopInfo = BaiyangUserSinceShopData::getInstance()->getSinceShopInfo($result['shopId']);
//                    if (empty($shopInfo)) {
//                        return $this->uniteReturnResult(HttpStatus::SINCE_SHOP_NOT_EXIST, $result);
//                    }
//                    $freightParam['type'] = 2;
//                    $freightParam['region_id'] = $result['shopId'];
//                    $result['consigneeInfo'] = array_merge($result['consigneeInfo'], $shopInfo);
//                }
//                $result['freight'] = $this->_eventsManager->fire('freight:getFreightFee', $this, $freightParam)['data']['freight'];
//            } else {
//                $result['freight'] = "0.00";
//            }
//        }
        if ($result['expressType'] != 1) {
            $result['shopId'] = 0;
        }
        if ($result['paymentId'] == 3) {
            $result['status'] = 'shipping';
            $result['paymentName'] = OrderEnum::$PaymentName[$result['paymentId']];
        }
        $result['costPrice'] = bcadd($result['costPrice'], $result['freight'], 2);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 支付完成同步库存和销量
     * @param array $param
     *       -order_sn int 用户ID（*）
     * @return array
     * @author 柯琼远
     */
    public function syncStockAndSaleNumber($param) {
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        if (empty($orderSn) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $this->_eventsManager->fire('order:syncStockAndSaleNumber', $this, ['order_sn'=>$orderSn]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }

    /**
     * @desc 我的订单列表 (根据状态返回)
     * @param array $param
     *      -int user_id 用户id
     *      -string status 订单状态
     *      -int pageStart 当前页码 (默认为1)
     *      -int pageSize 每页条数 (默认为5)
     *      -string platform  平台
     * @return array [] 结果信息
     * @author 吴俊华
     *
     */
    public function getOrderListByStatus($param)
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
                $param['condition'] = "status = 'evaluating'";
                break;
            // 售后订单列表
            case OrderEnum::ORDER_REFUND:
                $param['condition'] = "status = 'refund'";
                break;
            default:
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
                break;
        }
        return $this->getAllOrderList($param);
    }




    /**
     * 利用redis生成订单号
     * @param  $is_global int  是否海外购，默认：0
     * @author  柯琼远
     * @return bool|string
     */
    protected function makeOrderSn($is_global = 0) {
        // 前缀
        $prefix = $is_global == 0 ? "" : "G";
        //生成订单号
        $order_sn = $prefix . $this->config->channel_subid . date('YmdHis') . substr(microtime(), 2, 5);
        $cacheKey = CacheKey::ORDER_SN . $order_sn;
        $ret = $this->RedisCache->getValue($cacheKey);
        if ($ret) {
            return $this->makeOrderSn($is_global);
        }
        // 设置5秒缓存有效期
        $this->RedisCache->setValue($cacheKey, 1, 5);
        return $order_sn;
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
        $prescriptionId = isset($param['prescription_id']) ? (string)$param['prescription_id'] : '';
        $platform = isset($param['platform']) ? (string)$param['platform'] : '';
        if (empty($orderSn) && empty($prescriptionId) || empty($userId) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 通过易复诊id去查询订单编号
        /*if(empty($orderSn) && !empty($prescriptionId)){
            $prescriptionInfo = BaiyangYfzData::getInstance()->getPrescriptionInfo([
                'column' => 'order_id',
                'where' => 'yfz_prescription_id = :yfz_prescription_id:',
                'bind' => [
                    'yfz_prescription_id' => $prescriptionId
                ],
            ]);
            if(empty($prescriptionInfo)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            $orderSn = $prescriptionInfo['order_id'];
        }*/
        // 读写key
        $rwKey = OrderEnum::USER_ORDER_LOCK_KEY . $userId;
        // 区分跨境订单和普通订单
        $global = strstr($orderSn, OrderEnum::KJ) ? 1 : 0;
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
        $config = $this->config;
        $env = $config->environment;
        $orderDetailData = BaiyangOrderDetailData::getInstance();

        // 相同的订单数据
        $orderInfo = [
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'status' => $order['status'],
            'is_global' => $global,
            'global_class' => $global ? 'hwg-btn' : '',
            'province' => $order['province'],
            'city' => $order['city'],
            'county' => $order['county'],
            'address' => $order['address'],
            'telephone' => $order['telephone'],
            'add_time' => $order['add_time'],
            'pay_time' => $order['pay_time'],
            'delivery_time' => $order['delivery_time'],
            'express_time' => $order['express_time'],
            'audit_time' => $order['audit_time'],
            'allow_comment' => $order['allow_comment'],
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
        $orderInfo['predict_time'] = isset($order['o2o_remark']) ? $order['o2o_remark'] : '';
        // 预计到达时间 (处理两小时达)
        if ($global == 0 && $orderInfo['express_type'] == 2 && !empty($orderInfo['predict_time'])) {
            $predictTimeArr = explode("—",$orderInfo['predict_time']);
            $endTimeArr = explode(" ",$predictTimeArr[1]);
            $orderInfo['predict_time'] = $predictTimeArr[0].'-'.$endTimeArr[1];
        }

        $orderInfo['audit_state'] = $order['audit_state'];
        $orderInfo['callback_phone'] = $order['callback_phone']; // 处方单回拨号码
        // 处方单照片
        $orderInfo['ordonnance_photo'] = isset($order['ordonnance_photo']) ? preg_replace("/http:\/\/[^\/]+\//", '', $order['ordonnance_photo']) : '' ;
        $orderInfo['order_total'] = $global ? $order['order_total_amount'] : $order['total'];
        $orderInfo['order_total'] = sprintf("%.2f", $orderInfo['order_total']);
        $orderInfo['invoice_info'] = json_decode($orderInfo['invoice_info'],true);
        $orderInfo['goods_amount'] = $order['goods_price']; //商品总额

        $orderInfo['coupon_amount'] = $order['user_coupon_price']; // 优惠券金额
        $orderInfo['full_reduce'] = $order['youhui_price']; // 优惠满减
        $orderInfo['balance_price'] = $order['balance_price']; // 余额支付的金额
        $orderInfo['order_tax_amount'] = isset($order['order_tax_amount']) ? $order['order_tax_amount'] : 0; // 进口税
        $orderInfo['order_tax_amount'] = sprintf("%.2f", $orderInfo['order_tax_amount']);
        // 剩余未支付金额
        $orderInfo['left_unpaid'] = bcsub($orderInfo['order_total'], $orderInfo['balance_price'], 2);
        $orderInfo['carriage'] = sprintf("%.2f", $order['carriage']);
        $orderInfo['payment_id'] = $order['payment_id'];
        // 支付名称
        if (!isset(OrderEnum::$PaymentName[$orderInfo['payment_id']])) {
            $orderInfo['payment_name'] = '在线支付';
        } else {
            $orderInfo['payment_name'] = OrderEnum::$PaymentName[$orderInfo['payment_id']];
        }
        $orderInfo['pay_link'] = '';
        $orderInfo['callback_link'] = '';
        $orderInfo['used_logistics_wap_api'] = $config->used_logistics_wap_api;
        // 海外购订单需验证速愈素
        /*if($global){
            $isQuicksinOrder = $orderDetailData->isQuicksinOrder($orderInfo['order_sn']);
            if($isQuicksinOrder){
                $orderInfo['pay_link'] = $config->wap_base_url[$env].'order-pay.html?order_id='.$orderInfo['order_sn'].'&is_global='.$global;
                $orderInfo['callback_link'] = $config->wap_base_url[$env].'order-submit-successfully.html';
            }
        }*/
        $cancel = OrderEnum::CANCEL_TIME;
        // 极速达的剩余支付时间较少(极速达 = 当日达和两小时达)
        if($orderInfo['express_type'] == 2 || $orderInfo['express_type'] == 3  && $global == 0){
            $cancel = 1800;
        }
        $cancelTime = $orderInfo['status'] == OrderEnum::ORDER_PAYING ? (int) $orderInfo['audit_time'] + $cancel - time() : 0;
        $orderInfo['cancel_reason'] = $order['cancel_reason'];
        $orderInfo['cancel_time'] = $cancelTime < 0 ? 0 : $cancelTime;
        $orderInfo['valid_time'] = $cancel;
        $orderInfo['buyer_message'] = $order['buyer_message'];
        $orderInfo['is_show_logisticsbutton'] = false;
        if (!in_array($orderInfo['express_type'],[2,3]) && (in_array($orderInfo['status'], [OrderEnum::ORDER_SHIPPED, OrderEnum::ORDER_EVALUATING, OrderEnum::ORDER_FINISHED]
                ) || $orderInfo['express_sn'])) {
            $orderInfo['is_show_logisticsbutton'] = true;
        }
        $rxExist = 0; // 是否处方单
        $giftsList = $detailList  = [];

        // 普通订单
        //if ($global === 0) {
            // 获取订单详细
            $detailList = $orderDetailData->getOneOrderDetail([
                'column' => '*',
                'where' => 'order_sn = :order_sn: order by goods_type asc',
                'bind' => [
                    'order_sn' => $orderSn
                ]
            ], $this->switchOrderDb($rwKey), $global);
            if(empty($detailList)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }

            foreach ($detailList as $key => $val) {
                // 赠品
                if($val['goods_type'] == 1){
                    $giftsList[] = $val;
                    unset($detailList[$key]);
                    continue;
                }
                // 商品是否上下架
                $saleArr = $this->filterData('sale',$this->getGoodsDetail(['goods_id' => $val['goods_id'], 'platform' => $platform]));
                $detailList[$key]['is_sale'] = $saleArr[0]['sale'];
                $detailList[$key]['is_global'] = $global;
                $memberTagName = '';
                //判断是否会员标签价
                $detailList[$key]['memberTagName'] = '';
                if(isset($val['tag_id'])){
                    if($platform == OrderEnum::PLATFORM_APP){
                        $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $val['tag_id']);
                    }else{
                        // 非app端要排除辣妈
                        if($val['tag_id'] != 0) {
                            $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $val['tag_id']);
                        }
                    }
                    if($memberTagName){
                        $detailList[$key]['memberTagName'] = $memberTagName['tag_name'];
                    }
                }
            }
            // 判断订单是否处方单
            $goodsIdsStr = implode(',',array_column($detailList,'goods_id'));
            $drugTypeArr = $this->filterData('drug_type',$this->getGoodsDetail(['goods_id' => $goodsIdsStr, 'platform' => $platform]));
            if(in_array(1,array_column($drugTypeArr,'drug_type'))){
                $rxExist = 1;
            }
            // 获取物流
            $shippingDetail = null;
            if(!empty($orderInfo['express_sn']) && strtolower($orderInfo['express']) != 'zps'){
                $shippingDetail = $this->func->getLogisticsData($orderSn);
            }
       // }
        /*else {
            // 跨境订单
            $detailList = $orderDetailData->getOneOrderDetail([
                'column' => '*',
                'where' => 'order_sn = :order_sn: and goods_type = :goods_type:',
                'bind' => [
                    'order_sn' => $orderSn,
                    'goods_type' => 0,
                ]
            ], $this->switchOrderDb($rwKey), $global);
            if(empty($detailList)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            foreach ($detailList as $key => $value){
                $detailList[$key]['is_global'] = $global;
            }
            // 获取物流
            $logisticsInfo = $orderData->getLogisticsInfo([
                'column' => 'show_logistics',
                'where' => 'express_sn = :express_sn:',
                'bind' => [
                    'express_sn' => $order['express_sn'],
                ]
            ], $global);
            $kjShippingDetail = !empty($logisticsInfo) ? json_decode($logisticsInfo['show_logistics'],true) : null;
        }*/
        // 处方单
        if ($rxExist == 1) {
            $orderInfo['check_time'] = $orderInfo['add_time'] + $this->func->getConfigValue(BaiyangConfigEnum::ORDER_AUTO_AUDIT_PASS_TIME);
        }

        // 省市区
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $orderInfo['province'] = $userConsigneeData->getRegionName($orderInfo['province']);
        $orderInfo['city'] = $userConsigneeData->getRegionName($orderInfo['city']);
        $orderInfo['county'] = $userConsigneeData->getRegionName($orderInfo['county']);
        $orderInfo['shopInfo'] = null;
        // 获取门店
        if ($orderInfo['express_type'] == 1) {
            $shopInfo = BaseData::getInstance()->getData([
                'table' => '\Shop\Models\BaiyangUserSinceShop',
                'column' => '*',
                'where' => 'where id = :id:',
                'bind' => [
                    'id' => $order['shop_id']
                ],
            ],true);
            $orderInfo['shopInfo'] = !empty($shopInfo) ? $shopInfo : null;
        }

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
        if($orderInfo['status'] == OrderEnum::ORDER_PAYING || $orderInfo['audit_state'] == 0){
            $restTime = $orderInfo['audit_time'];
            if ($orderInfo['express_type'] > 1) {
                $restTime = $restTime + $config->o2o_order_effective_time * 3600;
            } else {
                $restTime = $restTime + $config->order_effective_time * 3600;
            }
            $orderInfo['end_time'] = $restTime;
            $orderInfo['format_rest_time'] = floor($restTime/86400) . '天' . floor($restTime%86400/3600) . '小时' . floor($restTime%3600/60) . "分" . $restTime%60 . '秒';
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
        $orderInfo['images'] = json_decode($orderInfo['images'],true);
        // pc端需要处理物流数据
        if($platform == OrderEnum::PLATFORM_PC && (!empty($shippingDetail) || !empty($kjShippingDetail))){
            $tempShippingDetail = $global ? $kjShippingDetail : $shippingDetail;
            if($global){
                // 海外购订单
                $kjShippingDetail = $this->handleLogisticsData($tempShippingDetail,$global);
            }else{
                // 普通订单
                $tempShippingDetail = $shippingDetail['context_list'];
                $shippingDetail['context_list'] = $this->handleLogisticsData($tempShippingDetail,$global);
            }
        }
        $data = [
            'is_global' => $global,
            'rx_exist' => $rxExist,
            'shippingDetail' => isset($shippingDetail) ? $shippingDetail : null,
            'kjShippingDetail' => isset($kjShippingDetail) ? $kjShippingDetail : null,
            'orderInfo' => $orderInfo,
            'goodsList' => $detailList,
            'giftsList' => $giftsList,
        ];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }



    /**
     * @desc 改变订单状态
     * @param array $param
     *      -int user_id 用户id
     *      -string order_sn 订单编号
     *      -string status 订单当前状态(shipped或evaluating)
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function changeOrderStatus(array $param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['order_sn'] = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $param['status'] = isset($param['status']) ? (string)$param['status'] : '';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['channel_subid'] = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $param['udid'] = isset($param['udid']) ? (string)$param['udid'] : '';
        if(empty($param['user_id']) || empty($param['order_sn']) || !in_array($param['status'],[OrderEnum::ORDER_SHIPPED, OrderEnum::ORDER_EVALUATING]) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 区分跨境订单和普通订单
        $global =  0;
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getOneOrder([
            'column' => '*',
            'where' => 'order_sn = :order_sn: and user_id = :user_id: and is_delete = 0',
            'bind' => [
                'order_sn' => $param['order_sn'],
                'user_id' => $param['user_id'],
            ]
        ], 'read', $global);
        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

        // 目前只支持两种订单状态改变
        $column = 'status = :status:,last_status = :last_status:';
        switch ($param['status']) {
            // 待收货改为待评价状态
            case OrderEnum::ORDER_SHIPPED:
                $status = $orderInfo['allow_comment'] ? OrderEnum::ORDER_EVALUATING : OrderEnum::ORDER_FINISHED;
                $column .= ',express_status = 1,express_time = '.time();
                break;
            // 待评价改为已完成状态
            case OrderEnum::ORDER_EVALUATING:
                $status = OrderEnum::ORDER_FINISHED;
                break;
            default:
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
                break;
        }
        // 更新订单状态
        $updateResult = $orderData->updateOrderInfo([
            'column' => $column,
            'where' => 'order_sn = :order_sn: and is_delete = 0',
            'bind' => [
                'order_sn' => $param['order_sn'],
                'last_status' => $param['status'],
                'status' => $status,
            ],
        ], $global);
        if(!$updateResult){
            return $this->uniteReturnResult(HttpStatus::EDIT_ERROR);
        }
        // 更新cps订单状态
        $cpsResult = $orderData->updateCpsOrderInfo([
            'column' => 'order_status = :order_status:',
            'where' => 'order_sn = :order_sn:',
            'bind' => [
                'order_sn' => $param['order_sn'],
                'order_status' => $status,
            ]
        ]);
        // 订单日志
        $logData = [
            'user_id' => $param['user_id'],
            'order_sn' => $param['order_sn'],
            'log_content' => serialize($orderInfo),
        ];
        LogService::getInstance()->save(['prefix' => 'order','data' => $logData]);
        // 易复诊和积分
        /*if($param['status'] == OrderEnum::ORDER_SHIPPED) {
            //同步到易复诊，可能不存在
            $redis = $this->cache;
            $redis->selectDb(6);
            $redis->rPush(OrderEnum::ORDER_FINISHED, $param['order_sn']);
            //todo 更新用户积分
//            $url = $this->config->item('wap_api_url')."/wap/integral/add_order_integral";
//            api_curl($url, ['order_sn' => $order_id], 'POST');
        }*/
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }



    /**
     * @desc 处理物流数据
     * @param array $logistics 物流信息[二维数组]
     * @param int $global 是否海外购(1:海外购 0:普通订单)
     * @return array [] 处理后的物流信息
     * @author 吴俊华
     */
    private function handleLogisticsData(array $logistics, int $global = 0)
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
                    'time' => $timeArr[1],
                    'recPlace' => $value['recPlace'],
                    'status' => $value['status'],
                ];
            }else{
                // 普通订单
                $newLogistics[$timeArr[0]][] = [
                    'year' => !isset($newLogistics[$timeArr[0]]) ? $timeArr[0] : '',
                    'week' => !isset($newLogistics[$timeArr[0]]) ? $this->getWeekday(strtotime($value['time'])) : '',
                    'time' => $timeArr[1],
                    'context' => $value['context'],
                    'areaCode' => $value['areaCode'],
                    'areaName' => $value['areaName'],
                    'status' => $value['status'],
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



    /**
     * @desc 获取商品满足或不满足促销活动信息 [相关信息有：优惠价、赠品、换购品等]
     * @param array  $goodsPromotionList  商品参加促销活动信息
     * @param array  $basicParam  基本参数[一维数组]
     *   [platform] -string  平台【pc、app、wap】
     *   [user_id]  -int     用户id (临时用户或真实用户id)
     *   [is_temp]  -int     是否为临时用户 (1:临时用户 0:真实用户)
     * @param string $action 动作类型(shoppingCart:购物车列表展示 orderInfo:购物车结算 commitOrder:提交订单 coupon:切换优惠券)
     * @param string $couponSn 提交订单时，用户选中的优惠券编号
     * @return array [] 处理后的信息
     * @author 吴俊华
     */
    protected function getGoodsPromotionInfo2($goodsPromotionList,$basicParam,$action = 'shoppingCart',$couponSn = '')
    {
        $shoppingCartGoodsPromotionList = $goodsPromotionList;
        $shoppingCartGoodsInfo = $goodsPromotionList['goodsList']; //购物车商品、活动信息
        $joinList = $goodsPromotionList['joinList'];  //商品参加活动列表
        $mutexList = $goodsPromotionList['mutexList']; //商品参加活动后的互斥列表

        $usableCouponsList = $this->getUsableCouponsList($shoppingCartGoodsInfo,$basicParam,$mutexList,$joinList,$couponSn,$action);

        if(isset($usableCouponsList['couponList']) && !empty($usableCouponsList['couponList'])){
            $coupon = [];
            foreach($usableCouponsList['couponList'] as $v){
                if($coupon){
                    if(in_array($v['coupon_sn'], array_column($coupon,'coupon_sn')) === false){
                        $coupon[] = $v;
                    }
                }else{
                    $coupon[] = $v;
                }

            }
            $shoppingCartGoodsPromotionList['couponList'] = $coupon;

        }

        return $shoppingCartGoodsPromotionList;
    }



    /**
     * @desc 获取可用优惠券列表
     * @param array $shoppingCartGoodsInfo 购物车信息
     * @param array $basicParam 基础信息
     * @param array $mutexList 商品的互斥数组  ['7000800'=>[15,20]]
     * @param array $joinList 商品参加过的活动数组  ['7000800'=>[15,20]]
     * @param string $couponSn 用户选中的优惠券编码
     * @param string $action 动作类型(orderInfo:购物车结算 commitOrder:提交订单 coupon:切换优惠券)
     * @return array $usableCouponsList 可用优惠券列表
     * @author 吴俊华
     */
    protected function getUsableCouponsList($shoppingCartGoodsInfo,$basicParam,$mutexList,$joinList,$couponSn ='',$action = 'orderInfo')
    {
        $shoppingCartGoodsList = []; //购物车所有商品信息
        //组装优惠券需要的商品信息(若商品参加过满减/满折，优惠价已改变)
        foreach($shoppingCartGoodsInfo as $value){
            if($value['group_id'] == 0){
                //商品
                $shoppingCartGoodsList[] = [
                    'goods_id' => $value['goods_id'],
                    'group_id' => $value['group_id'],
                    'goods_number' => $value['goods_number'],
                    'brand_id' => $value['brand_id'],
                    'category_id' => $value['category_id'],
                    'drug_type' => $value['drug_type'],
                    'promotion_price' => $value['promotion_price'],
                    'discount_price' => $value['discount_price'],
                    'promotion_total' => $value['promotion_total'],
                    'discount_total' => $value['discount_total'],
                ];
            }else{
                //套餐
                $shoppingCartGoodsList[] = [
                    'goods_id' => 0,
                    'group_id' => $value['group_id'],
                    'goods_number' => $value['goods_number'],
                    'promotion_total' => $value['promotion_total'],
                    'discount_total' => $value['discount_total'],
                    'groupGoodsList' =>  $value['groupGoodsList'],
                ];
            }
        }
        $usableParam = [
            'basicParam' => $basicParam,
            'shoppingCartGoodsList' => $shoppingCartGoodsList,
            'joinList' => $joinList,
            'mutexList' => $mutexList,
            'couponSn' => $couponSn,
            'action' => $action,
        ];
        $usableCouponsList = $this->_eventsManager->fire('coupon:getCouponList',$this,$usableParam);
        $couponList = [];
        if(isset($usableCouponsList['couponList']) && !empty($usableCouponsList['couponList'])){
            foreach($usableCouponsList['couponList'] as $value){
                $couponList[] = [
                    'coupon_sn' => $value['coupon_sn'],
                    'coupon_name' => $value['coupon_name'],
                    'coupon_type' => $value['coupon_type'],
                    'coupon_range' => $value['use_range'],
                    'coupon_price' => $value['discount'],
                    'coupon_value' => $value['coupon_value'],
                    'expiration' => $value['expiration'],
                    'selected' => $value['is_selected'],
                ];
            }
            $usableCouponsList['couponList'] = $couponList;
        }
        return $usableCouponsList;
    }





    /**
     * @desc 根据时间戳返回周几
     * @param int $time 时间戳
     * @return string '' 结果信息
     * @author 吴俊华
     */
    private function getWeekday(int $time)
    {
        $weekday = ['日', '一', '二', '三', '四', '五', '六'];
        return '周' . $weekday[date('w', $time)];
    }


    /**获取o2o 的配送范围
     * @return array 返回一维数组
     *      county  配送的区域 , 如市北区, 市南区 等
     */
    public function getOtoDeliveryArea(){

        $rangeRs = BaiyangO2oData::getInstance()->getO2ORegionAll();
        $range = [];
        if($rangeRs and is_array($rangeRs)){
            $range = array_column($rangeRs,'county');
        }
        return $range;
    }


    /** 判断 定位的地址 在不在 OTO 配送范围之内
     * @param $name
     * @return \array[]  返回该  区  是不是OTO的地址
     */
    public function chkOtoDeliveryArea(array $param){

        try{
            $result = BaiyangO2oData::getInstance()->getRegionByName($param['county']);
            if(empty($result)){
                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }

            //根据  省 市 区 筛选出符合条件的 地址
            $deliveryArea = [];
            foreach($result as $v){
                if(stripos($v['true_name'], $param['city']) !== false
                    and (
                        stripos($v['true_name'], $param['province']) !== false
                        or stripos($v['true_name'], trim($param['province'],'省')) !== false
                    )
                ){
                    $deliveryArea = $v;
                }
            }
            if(empty($deliveryArea)){
                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }

            //判断筛选出的地址 在不在OTO配送范围内
            $range = $this->getOtoDeliveryArea();
            if(empty($range)){
                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }

            if(in_array($deliveryArea['id'], $range) === false){

                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }

        }catch (\Exception $e){
            return $this->uniteReturnResult(HttpStatus::O2O_REGION_NOT_EXIST, ['status'=>0]);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, ['status'=>1]);
    }


    /**获取OTO的地址列表
     * @param array $param
     * @return \array[]
     */
    public function getOtoAddress(array $param){
        try{
            $deliveryAddress = BaiyangUserConsigneeData::getInstance()->getUserConsigneeList($param['user_id']);
            if(empty($deliveryAddress)){
                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }
            //获取o2o 的配送范围
            $range = $this->getOtoDeliveryArea();
            if(empty($range)){
                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }
            $result = [];
            if($deliveryAddress and is_array($deliveryAddress)){
                foreach($deliveryAddress as $k=>$v){
                    if(in_array($v['county'], $range)){
                            $result[] = $v;
                    }
                }
            }
            unset($deliveryAddress);
            if(empty($result)){
                throw new \Exception(HttpStatus::O2O_REGION_NOT_EXIST);
            }
        }catch (\Exception $e){
            return $this->uniteReturnResult(HttpStatus::O2O_REGION_NOT_EXIST, ['status'=>0]);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }
}