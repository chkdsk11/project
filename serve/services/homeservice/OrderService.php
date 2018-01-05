<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/11/16 0016
 * Time: 上午 9:46
 */

namespace Shop\Home\Services;

use Shop\Models\CacheKey;
use Shop\Libs\CacheRedis;
use Shop\Libs\ExpressText;

use Shop\Home\Datas\{
	BaiyangUserGoodsPriceTagData, BaiyangUserData, BaseData,
	BaiyangOrderData, BaiyangOrderLogData, BaiyangOrderDetailData,
	BaiyangAnnouncementData, BaiyangSkuData, BaiyangUserConsigneeData,
	BaiyangUserSinceShopData, BaiyangYfzData, BaiyangUserInvoiceData,
    BaiyangGoodsStockChangeLogData, BaiyangPromotionData, BaiyangShoppingCartData,
	BaiyangKjOrderData, BaiyangKjOrderDetailData,BaiyangCpsData,BaiyangCouponRecordData,
    BaiyangTouchMachineOrderData, BaiyangOrderPromotionData,BaiyangGoodsComment,BaiyangOrderGoodsReturnReasonData,
    BaiyangPaymentData
	};

use Shop\Home\Listens\{
	PromotionCoupon, PromotionGetGoodsDiscountPrice, PromotionGoodsDetail,
	PromotionGoodset, PromotionLimitBuy, PromotionShopping,
	PromotionCalculate, FreightListener, BalanceListener, MomListener,StockListener,OrderListener
	};

use Shop\Models\{
	BaiyangOrder, BaiyangKjOrder, BaiyangOrderDetail,
	HttpStatus, OrderEnum, BaiyangConfigEnum, BaiyangPromotionEnum
	};

use Phalcon\Events\{
	Manager as EventsManager, Event
	};

use Shop\Home\Services\{
	 AuthService
	};

class OrderService extends BaseService
{
    protected static $instance=null;

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new OrderService();
        }

        //实例化事件管理器
        $eventsManager= new EventsManager();

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
        $eventsManager->attach('order',new OrderListener());
        /*********************侦听器************************/

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }
	
	/**
	 * @desc 提交跨境订单
	 * @param array $param
	 *       -user_id int 用户ID（*）
	 *       -address_id int 地址ID（*）
     *       -insure_amount int 保费
	 *       -buyer_message string 买家留言
	 *       -pay_type int 支付方式：1-在线支付
	 *       -express_type int 配送方式:0-普通快递
	 *       -channel_name string 渠道名称
	 *       -platform string 平台：pc,wap,app（*）
	 *       -channel_subid string 渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
	 * @return array
	 * @author sarcasme
	 */
	public function commitGlobalOrder (array $param)
	{
		// 获取参数
		// 验证购物车和促销信息
		$ret = $this->globalOrderPromotionInfo(array_merge($param, ['action'=> 'commitOrder']));
		if ($ret['status'] != HttpStatus::SUCCESS) return $ret;
		$result = $ret['data'];
		// 获取订单号
		$result['orderSn'] = $this->makeOrderSn(1);
		// 获取参数
		$result['userId'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
		$result['addressId'] = isset($param['address_id']) ? (int)$param['address_id'] : 0;
		$result['paymentId'] = isset($param['payment_id']) ? (int)$param['payment_id'] : 0;
		$result['expressType'] = isset($param['express_type']) ? (int)$param['express_type'] : 0;
		$result['shopId'] = isset($result['shopId']) ? (int)$result['shopId'] : 1;
		$result['buyerMessage'] = isset($param['buyer_message']) ? htmlspecialchars($param['buyer_message']) : '';
		$result['insure_amount'] = isset($param['insure_amount']) ? (int)($param['insure_amount']) : 0;
        $result['channelName'] = isset($param['channel_name']) ? htmlspecialchars($param['channel_name']) : '';
        $result['machineSn'] = isset($param['machine_sn']) ? htmlspecialchars($param['machine_sn']) : '';
		// 验证参数
		if ($result['userId'] < 1 || $result['addressId'] < 1 || !in_array($result['paymentId'],[0,3]) || !in_array($result['expressType'],[0,1,2,3]) )
		{
			return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
		}
		// 验证用户信息
		$userInfo = BaiyangUserData::getInstance()->getUserInfo($result['userId'], '*');
		if (empty($userInfo)) { return $this->uniteReturnResult(HttpStatus::USER_NOT_EXIST, $result); }
		$result['userId'] = $userInfo['id'];
		$result['unionUserId'] = $userInfo['union_user_id'];
		$result['phone'] = !empty($userInfo['phone']) ? $userInfo['phone'] : $userInfo['user_id'];
        $result['inviteCode'] = $userInfo['invite_code'];//用于推送CPS
		// 验证地址信息
		$consigneeData = BaiyangUserConsigneeData::getInstance();
		$consigneeParam = [
			'column' => 'id, consignee, consignee_id, address, province, city, county, telphone, zipcode,identity_confirmed',
			'where' => 'id = :id: and user_id = :user_id:',
			'bind' => ['id' => $param['address_id'],'user_id' => $result['userId']]
		];
		$result['consigneeInfo'] = $consigneeData->getConsigneeInfo($consigneeParam);
        if (empty($result['consigneeInfo'])) { return $this->uniteReturnResult(HttpStatus::ADDRESS_NOT_EXIST, $result); }
        if ($result['consigneeInfo']['identity_confirmed'] == 0) {
            return $this->uniteReturnResult(HttpStatus::IDCARD_NAME_ERROR, $result);
        }
        // 计算运费

        $result['paymentId'] = 0;
        $result['expressType'] = 0;
        $result['freight'] = $this->_eventsManager->fire('order:getFreightFee', $this, [
            'goods_ids' => $result['goodsIds'],
            'region_id' => $result['consigneeInfo']['province'],
            'type' => 0,
            'total' => $result['costPrice'],
        ])['data']['freight'];
        $result['costPrice'] = bcadd($result['costPrice'], $result['freight'], 2);

		// 入库
		$this->dbWrite->begin();
		$success = true;
		if (!BaiyangKjOrderData::getInstance()->insertOrder($result)) $success = false;//插入订单
		if (!BaiyangKjOrderDetailData::getInstance()->insertOrderDetail($result)) $success = false;//插入订单详情
        if (!BaiyangGoodsStockChangeLogData::getInstance()->insertGoodsStockChange($result)) $success = false;//库存变化
        if (!BaiyangTouchMachineOrderData::getInstance()->insertMachineSn($result)) $success = false;//插入触屏机设备号
        //清空购物车
        if ($result['goodsId'] == 0) {
            if (!BaiyangShoppingCartData::getInstance()->deleteShoppingCartAftercommitOrder($result)) $success = false;
        }
		if (!$success) {
			$this->dbWrite->rollback();
			return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $result);
		}
		$this->dbWrite->commit();
        BaiyangYfzData::getInstance()->pushYfz($result);//推送易复诊
        BaiyangCpsData::getInstance()->pushCps($result);//推送CPS
        // 海外购订单需验证速愈素
        $wap_base_url = $this->config->wap_base_url[$this->config->environment];
        $pay_link = $wap_base_url.'order-pay.html?order_id='.$result['orderSn'].'&is_global=1';
        $callback_link = $wap_base_url.'order-submit-successfully.html';

		return $this->uniteReturnResult(HttpStatus::SUCCESS, [
			'orderSn'      => $result['orderSn'],
			'leftAmount'   => $result['costPrice'],
			'isOnlinePay'  => 1,
            'paymentId'    => 0,
			'allowComment' => 1,
			'balancePrice' => "0.00",
			'rxExist'      => 0,
			'validTime'    => time() + $this->config->order_effective_time * 3600,
			'status'       => "paying",
			'isGlobal'     => 1,
			'payLink'      => $pay_link,
			'callbackLink' => $callback_link,
		]);
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
	 *       -is_balance int 是否启用余额（1:开启 0:关闭），默认开启
	 *       -is_first int 是否初访问，取值：0，1，默认：1
	 *       -platform string 平台：pc,wap,app
	 * @return array
	 * @author sarcasme
	 */
	public function confirmGlobalOrder(array $param)
	{
		// 购物车和促销信息
		$param['is_first'] = isset($param['is_first']) ? (int)$param['is_first'] : 1;
		$param['action'] = "orderInfo";
		$ret = $this->globalOrderPromotionInfo($param);
		if ($ret['status'] != HttpStatus::SUCCESS) { return $ret; }
		$result = $ret['data'];
		// 验证用户信息
		$userInfo = BaiyangUserData::getInstance()->getUserInfo($param['user_id'], 'default_consignee');
		if (empty($userInfo)) { return $this->uniteReturnResult(HttpStatus::USER_NOT_EXIST, ['param' => $param]); }
		// 收货地址
		$result['consigneeList'] = BaiyangUserConsigneeData::getInstance()->getUserConsigneeList($param['user_id']);
		$param['address_id'] = isset($param['address_id']) ? (int)$param['address_id'] : 0;
		$ret = $this->func->arrayFieldSelected($result['consigneeList'], $param['address_id']);
		$result['consigneeList'] = $ret['list'];
		$param['address_id'] = $ret['value'];
		$consigneeInfo = $ret['info'];
		if (!empty($result['consigneeList'])) {
			$isSelected = array_column($result['consigneeList'], 'selected');
			$index  = array_search(1, $isSelected);
			if ($index !== false) {
				$result['identityNumber'] = $result['consigneeList'][$index]['consignee_id'];
			}
		}
		// 配送信息
        $result = $this->_eventsManager->fire('order:remakeExpress', $this, $result);
        if (!empty($consigneeInfo)) {
            $result['freightInfo'] = $this->_eventsManager->fire('order:getFreightFee', $this, [
                'goods_ids' => $result['goodsIds'],
                'region_id' => $consigneeInfo['province'],
                'type' => 0,
                'total' => $result['costPrice'],
            ])['data'];
        }
        $result['announcement'] = BaiyangAnnouncementData::getInstance()->getAnnouncement($consigneeInfo);
        $result['costPrice'] = bcadd($result['costPrice'], $result['freightInfo']['freight'], 2);
		// 返回
		return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
	}
	
    /**
     * @desc 确认订单页面
     * @param array $param
     *       -user_id int 用户ID（*）
     *       -address_id int 地址ID（*）
     *       -record_id string 优惠券领取id
     *       -payment_id int 支付方式：0-在线支付，3 : 货到付款，默认：0
     *       -express_type int 配送方式:0-普通快递,1-顾客自提,2-两小时达,3-当日达
     *       -o2o_time int O2O配送时间
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
        // 验证用户信息
        $userInfo = BaiyangUserData::getInstance()->getUserInfo($param['user_id'], 'balance,pay_password,default_consignee');
        if (empty($userInfo)) {
            return $this->uniteReturnResult(HttpStatus::USER_NOT_EXIST, ['param' => $param]);
        }
        // 用户余额 && 是否已设置支付密码
        $result['balance'] = $userInfo['balance'];
        $result['isSetPwd'] = !empty($userInfo['pay_password']) ? 1 : 0;
        // 收货地址
        $result['consigneeList'] = BaiyangUserConsigneeData::getInstance()->getUserConsigneeList($param['user_id']);
        $param['address_id'] = isset($param['address_id']) ? (int)$param['address_id'] : 0;
        $ret= $this->func->arrayFieldSelected($result['consigneeList'], $param['address_id']);
        $result['consigneeList'] = $ret['list'];
        $param['address_id'] = $ret['value'];
        $consigneeInfo = $ret['info'];
        //判断是否开发票
        $isInvoice = BaiyangOrderData::getInstance()->getLastOrderInvoice($param);
        // 发票
        if ($isInvoice) {
            $invoiceInfo = BaiyangUserInvoiceData::getInstance()->getUserInvoice($param['user_id']);
            $content_type = $result['rxExist'] == 1 ? 10 : 16;
            if (!empty($invoiceInfo)) {
                $invoiceInfo['content_type'] = $content_type;
                $result['invoiceInfo'] = array_merge(["if_receipt"=>1], $invoiceInfo);
            }/* elseif (!empty($consigneeInfo)) {
                $result['invoiceInfo'] = ["if_receipt"=>1,'invoice_type'=>1,'title_name'=>$consigneeInfo['consignee'],'content_type'=>$content_type];
            }*/
        } else {
            unset($result['invoiceInfo']);
        }
	    // 配送信息
        $result = $this->getExpressInfo($result, $param, $consigneeInfo);
        // 余额支付
        if (!$param['is_first'] && isset($param['is_balance']) && $param['is_balance'] == 0) $result['isBalance'] = 0;
        if ($result['balance'] == 0) $result['isBalance'] = 0;
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
     * @desc 配送方式、支付方式信息
     * @param array $result
     * @param array $param
     * @param array $consigneeInfo
     * @return array
     * @author 柯琼远
     */
    private  function getExpressInfo ($result, $param, $consigneeInfo) {
        // 货到付款款支持O2O
        $result['facePayIfO2o'] = 1;
        // 支付方式
        $result['paymentId'] = !$result['hasSupplier'] && isset($param['payment_id']) && $param['payment_id'] == 3 ? 3 : 0;
        if (!empty($consigneeInfo) && !$result['isDummy']) {
            if (!$result['hasSupplier']) {
                // 自提地址只能【在线支付】【自提】
                $ziTiAddressId = $this->func->isZitiAddress($consigneeInfo);
                if ($ziTiAddressId) {
                    $result['expressType'] = 1;
                    $result['paymentId'] = 0;
                }else {
                    //货到付款控制
                    $result['ifFacePay'] = BaiyangPaymentData::getInstance()->checkCashOnDelivery([
                        'channel_subid' => isset($param['channel_subid']) ? $param['channel_subid'] : 95,
                        'goodsList' => isset($result['goodsList']) ? $result['goodsList'] : [],
                    ]) ? 1 : 0;
                    // 极速配送
                    $o2oInfo = $this->_eventsManager->fire('order:getO2OExpressInfo', $this, ['consigneeInfo'=>$consigneeInfo]);
                    if ($o2oInfo['status'] == HttpStatus::SUCCESS) {
                        $result['expressType'] = $o2oInfo['data']['type'] == 1 ? 2 : 3;
                        $result['o2oInfo'] = $o2oInfo['data'];
                        $o2oTime = isset($param['o2o_time']) ? (int)$param['o2o_time'] : 0;
                        $ret = $this->func->arrayFieldSelected($o2oInfo['data']['list'], $o2oTime, 'time');
                        $result['o2oInfo']['list'] = $ret['list'];
                        foreach ($result['o2oInfo']['list'] as $k => $v) {
                            if ($result['o2oInfo']['free_price'] <= $result['costPrice']) {
                                $result['o2oInfo']['list'][$k]['fee'] =  "0.00";
                            }
                        }
                        // 极速配送运费返回格式
                        $freePrice = $result['o2oInfo']['free_price'];
                        $freight = $freePrice > $result['costPrice'] ? $ret['info']['fee'] : "0.00";
                        $lack_price = $freight > 0 ? bcsub($freePrice, $result['costPrice'], 2) : "0.00";
                        $result['freightInfo'] = [
                            'freight' => $freight,
                            'tips' => [
                                'free_price' => $freePrice,
                                'not_free_fee' => $ret['info']['fee'],
                                'lack_price' => $lack_price,
                                'promote_text' => $freight > 0 ? "再购买".$lack_price."元免邮" : '',
                            ]
                        ];
                    }
                    // 配送方式
                    if (!$param['is_first'] && $param['express_type'] == 0) {
                        $result['expressType'] = 0;
                    }
                    if ($result['paymentId'] == 3 && $result['facePayIfO2o'] == 0) {
                        $result['expressType'] = 0;
                    }
                    // 到付提示信息
                    $result['facePayTips'] = $this->_eventsManager->fire('order:getFreightFee', $this, [
                        'goods_ids' => $result['goodsIds'],
                        'region_id' => $consigneeInfo['province'],
                        'type' => 1,
                        'total' => $result['costPrice'],
                    ])['data']['tips'];
                }
            }
            // 包邮活动
            if ($result['isExpressFree'] && $result['expressType'] == 0 && $result['paymentId'] == 0) {
                $result['freightInfo'] = ['freight'=>"0.00",'tips'=>['free_price'=>"0.00",'not_free_fee'=>"0.00",'lack_price'=>'0.00','promote_text'=>'']];
            } elseif ($result['expressType'] <= 1) {
                // 普通运费模板
                $result['freightInfo'] = $this->_eventsManager->fire('order:getFreightFee', $this, [
                    'goods_ids' => $result['goodsIds'],
                    'region_id' => $result['expressType'] == 1 ? $ziTiAddressId : $consigneeInfo['province'],
                    'type'      => $result['expressType'] == 1 ? 2 : ($result['paymentId'] == 0 ? 0 : 1),
                    'total'     => $result['costPrice'],
                ])['data'];
            }
        }
        // 整理配送方式输出结构
        $result = $this->_eventsManager->fire('order:remakeExpress', $this, $result);
        if ($result['expressType'] == 1) $result['expressType'] = 0;
        // 配送公告
        $result['announcement'] = BaiyangAnnouncementData::getInstance()->getAnnouncement($consigneeInfo);
        // 计算应付金额
        $result['costPrice'] = bcadd($result['costPrice'], $result['freightInfo']['freight'], 2);
        return $result;
    }

    /**
    * @desc 提交订单
    * @param array $param
    *       -user_id int 用户ID（*）
    *       -address_id int 地址ID（*）
    *       -buyer_message string 买家留言
    *       -record_id string 优惠券领取id
    *       -payment_id int 支付方式：0-在线支付，3-货到付款
    *       -express_type int 配送方式:0-普通快递,2-两小时达,3-当日达（*）
    *       -o2o_time int O2O配送时间
    *       -invoice_type int 发票类型 0不需要 1个人 2单位
    *       -invoice_header string 发票抬头
    *       -taxpayer_number string 税号
    *       -is_balance int 是否使用余额支付：0-不使用，1-使用
    *       -pay_password string 支付密码
    *       -callback_phone string 回拨电话
    *       -ordonnance_photo string 处方单图片
    *       -machine_sn string 触屏机设备号
    *       -channel_name string 渠道名称
    *       -platform string 平台：pc,wap,app（*）
    *       -channel_subid string 渠道号，微商场：85 IOS：89 安卓：90 WAP：91 PC：95（*）
    * @return array
    * @author 柯琼远
    */
    public function commitOrder($param) {
        // 验证购物车和促销信息
        $ret = $this->orderPromotionInfo(array_merge($param, ['action'=> 'commitOrder']));
        if ($ret['status'] != HttpStatus::SUCCESS) return $ret;
        $result = $ret['data'];
        // 获取订单号
        $result['orderSn'] = $this->makeOrderSn();
        if (count($result['supplierList']) > 1) {
            foreach ($result['supplierList'] as $key => $value) {
                $result['supplierList'][$key]['orderSn'] = $this->makeChildOrderSn($result['orderSn']);
            }
        } else {
            $result['supplierList'][0]['orderSn'] = $result['orderSn'];
        }
        // 获取参数
        $result['userId'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $result['addressId'] = isset($param['address_id']) ? (int)$param['address_id'] : 0;
        $result['paymentId'] = isset($param['payment_id']) ? (int)$param['payment_id'] : 0;
        $result['expressType'] = isset($param['express_type']) ? (int)$param['express_type'] : 0;
        $result['o2oTime'] = isset($param['o2o_time']) ? (int)$param['o2o_time'] : 0;
        $result['isBalance'] = isset($param['is_balance']) ? (int)$param['is_balance'] : 0;
        $result['payPassword'] = isset($param['pay_password']) ? (string)$param['pay_password'] : '';
        $result['callbackPhone'] = isset($param['callback_phone']) ? (string)$param['callback_phone'] : '';
        $result['ordonnancePhoto'] = isset($param['ordonnance_photo']) ? (string)$param['ordonnance_photo'] : '';
        $result['invoiceType'] = isset($param['invoice_type']) ? (int)$param['invoice_type'] : 0;
        $result['invoiceHeader'] = isset($param['invoice_header']) ? htmlspecialchars($param['invoice_header']) : '';
        $result['taxpayerNumber'] = isset($param['taxpayer_number']) ? (string)$param['taxpayer_number'] : '';
        $result['taxpayerNumber'] = str_replace(' ', '', $result['taxpayerNumber']);
        $result['buyerMessage'] = isset($param['buyer_message']) ? htmlspecialchars($param['buyer_message']) : '';
        $result['channelName'] = isset($param['channel_name']) ? htmlspecialchars($param['channel_name']) : '';
        $result['machineSn'] = isset($param['machine_sn']) ? htmlspecialchars($param['machine_sn']) : '';
        // 验证参数
        if ($result['userId'] < 1 || $result['addressId'] < 1 || !in_array($result['paymentId'],[0,3]) || !in_array($result['expressType'],[0,2,3]) || !in_array($result['isBalance'],[0,1])) {
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
        $result['more_platform_sign'] = $userInfo['more_platform_sign'];
        // 验证地址信息
        $consigneeData = BaiyangUserConsigneeData::getInstance();
        $consigneeParam = [
            'column' => 'id,consignee,address,province,city,county,telphone,zipcode',
            'where' => 'id = :id: and user_id = :user_id:',
            'bind' => ['id' => $param['address_id'],'user_id' => $result['userId']]
        ];
        $result['consigneeInfo'] = $consigneeData->getConsigneeInfo($consigneeParam);
        if (empty($result['consigneeInfo'])) {
            return $this->uniteReturnResult(HttpStatus::ADDRESS_NOT_EXIST, $result);
        }
        // 处理处方单
        if ($result['rxExist'] == 1) {
            if (empty($result['callbackPhone'])) {
                return $this->uniteReturnResult(HttpStatus::CALLBACKPHONE_IS_EMPTY, $result);
            }
            if (!empty($result['ordonnancePhoto'])) {
                if (!preg_match('/^http/', $result['ordonnancePhoto'])) {
                    $ordonnancePhoto = $this->moveImg($result['ordonnancePhoto']);
                    if (!empty($ordonnancePhoto) && !is_array($ordonnancePhoto)) {
                        $result['ordonnancePhoto'] = $ordonnancePhoto;
                    }
                }
            }
        }
        // 验证发票信息
        if ($result['invoiceType'] > 0) {
            if ($result['invoiceType'] == 2) {
                if (empty($result['invoiceHeader']) || empty($result['taxpayerNumber'])) {
                    return $this->uniteReturnResult(HttpStatus::INVOICE_IS_EMPTY, $result);
                }
            } else {
                $result['taxpayerNumber'] = '';// 个人发票的税号为空
            }
        }
        // 计算运费
        $ret = $this->getExpressInfoForCommit($result);
        if ($ret['status'] != HttpStatus::SUCCESS) return $ret;
        $result = $ret['data'];
        // 余额支付
        $ret = $this->payBalance($result);
        if ($ret['status'] != HttpStatus::SUCCESS) {
            $this->log->error("ERROR:提交订单余额支付失败" . print_r($ret,1));
            return $ret;
        }
        $result = $ret['data'];
        // 入库
        $this->dbWrite->begin();
        $success = true;
        if (!$this->changeMomGiftStatus($result)) $success = false;//更新辣妈礼包状态
        if (!BaiyangUserInvoiceData::getInstance()->insertUserInvoice($result)) $success = false;//插入发票信息
        if (!BaiyangOrderDetailData::getInstance()->insertOrderDetail($result)) $success = false;//插入订单详情
        if (!BaiyangGoodsStockChangeLogData::getInstance()->insertGoodsStockChange($result)) $success = false;//库存变化
        if (!BaiyangPromotionData::getInstance()->insertPromotionDetailLog($result)) $success = false;//插入促销日志
        if (!BaiyangOrderData::getInstance()->insertOrderPayDetail($result)) $success = false;//插入支付信息
        if (!BaiyangOrderData::getInstance()->insertOrder($result)) $success = false;//插入订单
        if (!BaiyangTouchMachineOrderData::getInstance()->insertMachineSn($result)) $success = false;//插入触屏机设备号
        // 更新使用优惠券状态
        if (!empty($result['couponInfo'])) {
            $ret = BaiyangCouponRecordData::getInstance()->TradeToUpdateCoupon($result['orderSn'], $result['userId'], $result['couponInfo']['coupon_sn']);
            if ($ret['code'] != 200) $success = false;
        }
        //清空购物车
        if ($result['goodsId'] == 0) {
            if (!BaiyangShoppingCartData::getInstance()->deleteShoppingCartAftercommitOrder($result)) $success = false;
        }
        // 生成订单失败退款
        if (!$success) {
            if ($result['costBalance'] > 0) {
                $ret = $this->_eventsManager->fire('order:external_refund_order', $this, [
                    'order_sn'     => $result['orderSn'],
                    'refund_money' => $result['costBalance'],
                ]);
                $this->log->error("ERROR:提交订单失败退还余额" . print_r($ret,1));
            }
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR, $result);
        }
        $this->dbWrite->commit();
        // 解绑会员标签
        $this->_eventsManager->fire('promotion:unbindUserTag', $this, $result);
        // 余额支付或货到付款，需要同步库存
        if ($result['paymentId'] == 3 || $result['paymentId'] == 7) {
            $this->_eventsManager->fire('order:syncStockAndSaleNumber', $this, ['order_sn'=>$result['orderSn']]);
            $supplierCount = count($result['supplierList']);
            if ($supplierCount > 1) {
                $phone = !empty($result['phone']) ? $result['phone'] : $result['consigneeInfo']['telphone'];
                $this->func->sendSms($phone, 'shop_split_order', [], 'pc', ['number'=>$supplierCount]);
            }
        }
        //推送易复诊
        BaiyangYfzData::getInstance()->pushYfz($result);
        BaiyangCpsData::getInstance()->pushCps($result);// 推送CPS
        // 删除购物车缓存
        $redis = $this->cache;
        $redis->selectDb(2);
        $redis->delete(CacheKey::MAKE_ORDER_PROMOTION."0_".$result['userId']);
        $redis->delete(CacheKey::CART_LIMIT_BUY_KEY."0_".$result['userId']);
        $redis->delete(CacheKey::ALL_CHANGE_PROMOTION.'0_'.$result['userId']);
        // 返回信息
        $validTime = $result['expressType'] > 1 ? time() + $this->config->o2o_order_effective_time * 3600 : time() + $this->config->order_effective_time * 3600;
        return $this->uniteReturnResult(HttpStatus::SUCCESS, [
            'orderSn'      => $result['orderSn'],
            'leftAmount'   => bcsub($result['costPrice'], $result['costBalance'], 2),
            'isOnlinePay'  => $result['paymentId'] == 3 ? 0 : 1,
            'paymentId'    => $result['paymentId'],
            'allowComment' => (($result['allRx'] == 1) || ($result['isDummy'] == 1)) ? 0 : 1,
            'balancePrice' => $result['costBalance'],
            'rxExist'      => $result['needAudit'],
            'validTime'    => $validTime,
            'status'       => $result['status'],
            'audit_state'  => $result['needAudit'] ? 0 : 1,
            'isGlobal'     => 0,
            'payLink'      => '',
            'callbackLink' => '',
        ]);
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
            $balanceResult = $this->_eventsManager->fire('order:add_user_expend', $this, [
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
                $result['paymentName'] = '余额支付';
            }
            // 分摊余额
            $result['supplierList'] = $this->func->childAllotParent($result['supplierList'], $result['costBalance'], 'costBalance');
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    // 支付方式，运费
    private function getExpressInfoForCommit($result) {
        if ($result['isDummy']) {
            // 虚拟订单没运费
            $result['freight'] = "0.00";
            $result['expressType'] = 0;
            $result['paymentId'] = 0;
        } else {
            if ($result['hasSupplier']) {
                $result['expressType'] = 0;
                $result['paymentId'] = 0;
            } else {
                // 自提
                $ziTiAddressId = $this->func->isZitiAddress($result['consigneeInfo']);
                if ($ziTiAddressId) {
                    $result['expressType'] = 1;
                    $result['paymentId'] = 0;
                } else {
                    // 极速配送
                    if ($result['expressType'] > 1) {
                        $o2oInfo = $this->_eventsManager->fire('order:getO2OExpressFee', $this, [
                            'consigneeInfo' => $result['consigneeInfo'],
                            'time' => $result['o2oTime'],
                            'total' => $result['costPrice'],
                        ]);
                        if ($o2oInfo['status'] != HttpStatus::SUCCESS) {
                            return $o2oInfo;
                        }
                        $result['o2oInfo'] = $o2oInfo['data'];
                        $result['freight'] = $o2oInfo['data']['fee'];
                    }
                }
            }
            // 包邮只支持在线支付和普通配送
            if ($result['isExpressFree'] && $result['expressType'] == 0 && $result['paymentId'] == 0) {
                $result['freight'] = "0.00";
            } elseif ($result['expressType'] <= 1) {
                // 普通运费模板
                $result['freight'] = $this->_eventsManager->fire('order:getFreightFee', $this, [
                    'goods_ids' => $result['goodsIds'],
                    'region_id' => $result['expressType'] == 1 ? $ziTiAddressId : $result['consigneeInfo']['province'],
                    'type' => $result['expressType'] == 1 ? 2 : ($result['paymentId'] == 0 ? 0 : 1),
                    'total' => $result['costPrice'],
                ])['data']['freight'];
            }
            $result['costPrice'] = bcadd($result['costPrice'], $result['freight'], 2);
            // 分摊运费
            $result['supplierList'] = $this->func->childAllotParent($result['supplierList'], $result['freight'], 'freight');
            foreach ($result['supplierList'] as $key => &$value) {
                $value['costPrice'] = bcadd($value['costPrice'], $value['freight'], 2);
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    // 更新辣妈礼包状态
    private function changeMomGiftStatus($result) {
        $goodsList = array();
        foreach ($result['goodsList'] as $key => $value) {
            if ($value['group_id'] == 0 && !empty($value['discountPromotion']) && $value['discountPromotion']['promotion_type'] == BaiyangPromotionEnum::MOM_PRICE) {
                $goodsList[] = [
                    'goods_id' => $value['goods_id'],
                    'gift_id'  => $value['discountPromotion']['gift_id'],
                    'tag_id'   => $value['discountPromotion']['tag_id'],
                    'price'    => $value['discountPromotion']['price'],
                ];
            }
        }
        if (!empty($goodsList)) {
            $ret = $this->_eventsManager->fire('promotion:checkOrderMomGoods', $this, [
                'user_id'            => $result['userId'],
                'goods_group_list'   => [0 => $goodsList],
            ]);
            if ($ret['code'] == HttpStatus::SUCCESS && !empty($ret['data']['where'])) {
                return BaseData::getInstance()->updateData([
                    'table' => "\\Shop\\Models\\BaiyangMomGetGift",
                    'column' => 'ascription = 2',
                    'where' => "where " . $ret['data']['where']
                ]);
            }
        }
        return true;
    }

    /**
     * @desc 支付完成同步库存和销量
     * @param array $param
     *       -order_sn int 订单ID（*）
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
     * @desc 退款恢复库存和销量
     * @param array $param
     *       -order_sn int 订单ID（*）
     * @return array
     * @author 柯琼远
     */
    public function recoverStockAndSaleNumber($param) {
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        if (empty($orderSn) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $this->_eventsManager->fire('order:recoverStockAndSaleNumber', $this, ['order_sn'=>$orderSn]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }

    /**
     * @desc 推送订单CPS
     * @param array $param
     *       -order_sn strin 订单号
     *       -invite_code int 邀请码（baiyang_user的invite_code字段）
     * @return array
     * @author 柯琼远
     */
    public function pushCps($param) {
        $order_sn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $invite_code = isset($param['invite_code']) ? (string)$param['invite_code'] : '';
        if (empty($order_sn) || empty($invite_code)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        BaiyangCpsData::getInstance()->pushCps([
            'orderSn'=> $order_sn,
            'inviteCode'=> $invite_code,
        ]);
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
                $time = time() - 90*24*60*60;
                $param['condition'] = "status = 'evaluating' and express_time > {$time} and is_refund = 0";
                break;
            // 退款/售后列表
            case OrderEnum::ORDER_REFUND:
                $param['condition'] = "";
                break;
            default:
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
                break;
        }
        return $this->getAllOrderList($param);
    }

    /**
     * @desc 得到所有订单或根据订单状态得到订单
     * @param array $param
     *      -int user_id  用户ID
     *      -string platform  平台
     *      -string status  订单状态
     *      -int pageStart  当前页码
     *      -int pageSize  每页条数
     *      -string condition  条件 (可填)
     * @return array|bool
     * @author 吴俊华
     */
    protected function getAllOrderList($param)
    {
        // 读写锁
        $lockKey = OrderEnum::USER_ORDER_LOCK_KEY . $param['user_id'];
        $platform = $param['platform'];
        $param['column'] = 'id,order_sn,total_sn,status,payment_id,add_time';
        $param['where'] = "user_id = {$param['user_id']} and is_delete = 0 and order_type != 5";
        // 根据订单状态拼接对应条件
        if (isset($param['condition']) && !empty($param['condition'])) {
            $param['where'] .= " and {$param['condition']}";
        }
        $baseData = BaseData::getInstance();
        $param['order'] = 'order by add_time desc,id desc';
        $start = ($param['pageStart'] - 1) * $param['pageSize'];
        $param['limit'] = $start . ',' . $param['pageSize'];
        // 订单信息
        $orderData = BaiyangOrderData::getInstance();
        $order = $orderData->getOrderList($param, $this->switchOrderDb($lockKey));
        $orderCounts = $kjOrderCounts = $orderCounts1 = $orderCounts2 = 0;
        $parentWhere = $sonWhere = $globalWhere = $param['where'];
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
            if($param['status'] == OrderEnum::ORDER_ALL){
                $sonWhere .= " and (order_sn = total_sn or (payment_id > 0 and audit_state = 1))";
            }elseif($param['status'] == OrderEnum::ORDER_PAYING){
                $sonWhere .= " and order_sn = total_sn and (payment_id = 0 or (payment_id > 0 and audit_state != 1))";
            }
            $orderCounts1 = $baseData->countData([
                'table' => '\Shop\Models\BaiyangOrder',
                'where' => "where {$sonWhere}",
            ]);
            if($param['status'] == OrderEnum::ORDER_ALL || $param['status'] == OrderEnum::ORDER_PAYING) {
                $orderCounts2 = $baseData->countData([
                    'table' => '\Shop\Models\BaiyangParentOrder',
                    'where' => "where {$parentWhere} and (payment_id = 0 or (payment_id > 0 and audit_state != 1))",
                ]);
            }
            $kjOrderCounts = $baseData->countData([
                'table' => '\Shop\Models\BaiyangKjOrder',
                'where' => "where {$globalWhere}",
            ]);
            $orderCounts = $orderCounts1 + $orderCounts2;
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
        // 所有配置信息
        $config = $this->config;
        $env = $config->environment;

        // 订单详情信息
        $skuData = BaiyangSkuData::getInstance();
        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $orderColumn = 'status,last_status,express_sn,express_type,audit_state,audit_time,carriage,balance_price,allow_comment,payment_id,express,consignee,telephone,province,city,county,address,shop_id';
        foreach ($order['data'] as $key => $value) {
            // 区分跨境订单和普通订单
            $global = strstr($value['order_sn'], OrderEnum::KJ) ? 1 : 0;
            $column = $global ? $orderColumn.',order_total_amount order_total,order_bond' : $orderColumn.',total order_total';
            $orderWhere = 'order_sn = :order_sn:';
            $orderBind = ['order_sn' => $value['order_sn']];
            if($value['sign'] == 3){
                // 母订单
                $orderInfo = $orderData->getParentOrder([
                    'column' => $column,
                    'where' => $orderWhere,
                    'bind' => $orderBind
                ]);
            }else{
                // 子订单
                $orderInfo = $orderData->getOneOrder([
                    'column' => $column,
                    'where' => $orderWhere,
                    'bind' => $orderBind
                ], $this->switchOrderDb($lockKey), $global);
            }
            if(empty($orderInfo)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
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
            $order['data'][$key]['is_oral_imapact'] = 0;
            
            $cancel = $config->order_effective_time * 3600;
            if($orderInfo['express_type'] == 2 || $orderInfo['express_type'] == 3 && $global == 0){
                $cancel = $config->o2o_order_effective_time * 3600;
            }
            $cancelTime = $orderInfo['status'] == OrderEnum::ORDER_PAYING ? (int) $orderInfo['audit_time'] + $cancel - time() : 0;
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
            if($value['sign'] == 3){
                $detailWhere = 'a.total_sn = :total_sn:';
                $detailBind = ['total_sn' => $value['total_sn']];
            }else{
                $detailWhere = 'a.order_sn = :order_sn:';
                $detailBind = ['order_sn' => $value['order_sn']];
            }
            // 获取订单详细
            $orderDetail = $orderDetailData->getOrderDetail([
                'column' => $orderDetailColumn.',c.drug_type',
                'where' => $detailWhere.' order by a.goods_type asc',
                'bind' => $detailBind
            ], $this->switchOrderDb($lockKey), $global);
            if(empty($orderDetail)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            foreach ($orderDetail as $k => $v){
                // 赠品
                if($v['goods_type'] == 1 || $v['goods_type'] == 2){
                    $order['data'][$key]['giftsList'][] = $v;
                }else{
                    // 普通商品
                    $v['is_global'] = $global;
                    $order['data'][$key]['goodsList'][] = $v;
                }
            }
            if($global){
                // 海外购订单需验证速愈素
                //if($orderInfo['order_bond'] == 2){
                    $order['data'][$key]['pay_link'] = $config->wap_base_url[$env].'order-pay.html?order_id='.$order['data'][$key]['order_sn'].'&is_global='.$global;
                    $order['data'][$key]['callback_link'] = $config->wap_base_url[$env].'order-submit-successfully.html';
                    $order['data'][$key]['is_oral_imapact'] = 1;
                //}
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

            foreach ($order['data'][$key]['goodsList'] as $kk => $vv) {
                // 判断是否会员标签价
                $order['data'][$key]['goodsList'][$kk]['memberTagName'] = '';
                $memberTagName = '';
                if (isset($vv['tag_id'])) {
                    if ($platform == OrderEnum::PLATFORM_APP) {
                        $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $vv['tag_id']);
                    } else {
                        // 非app端要排除辣妈
                        if ($vv['tag_id'] != 0) {
                            $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $vv['tag_id']);
                        }
                    }
                    if ($memberTagName) {
                        $order['data'][$key]['goodsList'][$kk]['memberTagName'] = $memberTagName['tag_name'].'价';
                    }
                }
            }
            // 判断订单是否处方单
            $goodsIdsStr = implode(',',array_column($orderDetail,'goods_id'));
            $order['data'][$key]['rx_exist'] = 0;
            $drugTypeArr = $this->filterData('drug_type',$this->getGoodsDetail(['goods_id' => $goodsIdsStr, 'platform' => $platform]));
            if($global == 0){
                $noAudits = $this->func->getConfigValue('order_no_audit_goods_type').',5';
                foreach ($drugTypeArr as $k => $v){
                    if (strpos($noAudits, (string)$v['drug_type']) === false) $order['data'][$key]['rx_exist'] = 1;
                }
            }

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
            }
        }
        $data = [
            'orderList' => $order['data'],
            'pageNum' => $totalItem,
            'pageCount' => ceil($totalItem / $param['pageSize']),
            'pageStart' => $param['pageStart'],
            'pageSize' => $param['pageSize'],
        ];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * @desc 我的订单列表 (V2版本)
     * @param array $param
     *      -int user_id 用户id
     *      -string status 订单状态
     *      -int pageStart 当前页码 (默认为1)
     *      -int pageSize 每页条数 (默认为5)
     *      -string platform  平台
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getOrderListByStatusV2($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['order_sn'] = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
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
                $param['condition'] = "(status = 'shipping' or (status = 'refund' and last_status = 'shipping')) and audit_state = 1 and is_refund = 0";
                break;
            // 待收货订单列表
            case OrderEnum::ORDER_SHIPPED:
                $param['condition'] = "(status = 'shipped' or (status = 'refund' and last_status = 'shipped')) and is_refund = 0";
                break;
            // 待评价订单列表
            case OrderEnum::ORDER_EVALUATING:
                $time = time() - 90*24*60*60;
                $param['condition'] = "status = 'evaluating' and express_time > {$time} and is_refund = 0";
                break;
            // 退款/售后订单
            case OrderEnum::ORDER_REFUND:
                $param['condition'] = "";
                break;
            default:
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
                break;
        }
        if($param['status'] != OrderEnum::ORDER_REFUND){
            if(!empty($param['order_sn'])){
                $param['find'] = $condition = "order_sn = '{$param['order_sn']}'";
                $param['condition'] = empty($param['condition']) ? $condition : $condition." and ".$param['condition'];
            }
            return $this->getOrderListV2($param);
        }else{
            // 退款/售后订单
            return $this->getServiceList($param);
        }
    }

    /**
     * @desc 得到所有订单或根据订单状态得到订单 (V2版本)
     * @param array $param
     *      -int user_id  用户ID
     *      -string platform  平台
     *      -string status  订单状态
     *      -int pageStart  当前页码
     *      -int pageSize  每页条数
     *      -string condition  条件 (可填)
     * @return array|bool
     * @author 吴俊华
     */
    protected function getOrderListV2($param)
    {
        // 读写锁
        $lockKey = OrderEnum::USER_ORDER_LOCK_KEY . $param['user_id'];
        $platform = $param['platform'];
        $param['column'] = 'id,order_sn,total_sn,status,payment_id,add_time,express_time';
        $param['where'] = "user_id = {$param['user_id']} and is_delete = 0 and order_type != 5";
        // 根据订单状态拼接对应条件
        if (isset($param['condition']) && !empty($param['condition'])) {
            $param['where'] .= " and {$param['condition']}";
        }
        $baseData = BaseData::getInstance();
        $param['order'] = 'order by add_time desc,id desc';
        $start = ($param['pageStart'] - 1) * $param['pageSize'];
        $param['limit'] = $start . ',' . $param['pageSize'];
        // 订单信息
        $orderData = BaiyangOrderData::getInstance();
        $order = $orderData->getOrderListV2($param, $this->switchOrderDb($lockKey));

        $orderCounts2 = 0;
        $parentWhere = $sonWhere = $globalWhere = $param['where'];
        if($param['status'] == OrderEnum::ORDER_ALL){
            $sonWhere .= " and (order_sn = total_sn or (payment_id > 0 and audit_state = 1))";
        }elseif($param['status'] == OrderEnum::ORDER_PAYING){
            $sonWhere .= " and order_sn = total_sn and (payment_id = 0 or (payment_id > 0 and audit_state != 1))";
        }
        $orderCounts1 = $baseData->countData([
            'table' => '\Shop\Models\BaiyangOrder',
            'where' => "where {$sonWhere}",
        ]);
        if($param['status'] == OrderEnum::ORDER_ALL || $param['status'] == OrderEnum::ORDER_PAYING){
            $orderCounts2 = $baseData->countData([
                'table' => '\Shop\Models\BaiyangParentOrder',
                'where' => "where {$parentWhere} and (payment_id = 0 or (payment_id > 0 and audit_state != 1))",
            ]);
        }
        $orderCounts = $orderCounts1 + $orderCounts2;
        if($param['status'] == OrderEnum::ORDER_SHIPPING || $param['status'] == OrderEnum::ORDER_SHIPPED){
            $globalWhere .= " and status != 'refund'";
        }
        $kjOrderCounts = $baseData->countData([
            'table' => '\Shop\Models\BaiyangKjOrder',
            'where' => "where {$globalWhere}",
        ]);
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
        // 所有配置信息
        $config = $this->config;
        $env = $config->environment;

        // 订单详情信息
        $skuData = BaiyangSkuData::getInstance();
        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $userConsigneeData = BaiyangUserConsigneeData::getInstance();
        $orderColumn = 'status,last_status,express_sn,express_type,audit_state,audit_time,carriage,balance_price,allow_comment,payment_id,express,consignee,telephone,province,city,county,address,shop_id,express_status';
        foreach ($order['data'] as $key => $value) {
            // 区分跨境订单和普通订单
            $global = strstr($value['order_sn'], OrderEnum::KJ) ? 1 : 0;
            $column = $global ? $orderColumn.',order_total_amount order_total,order_bond,is_declare' : $orderColumn.',total order_total,audit_reason';
            $orderWhere = 'order_sn = :order_sn:';
            $orderBind = ['order_sn' => $value['order_sn']];
            if($value['sign'] == 3){
                // 母订单
                $orderInfo = $orderData->getParentOrder([
                    'column' => $column,
                    'where' => $orderWhere,
                    'bind' => $orderBind
                ]);
            }else{
                // 子订单
                $orderInfo = $orderData->getOneOrder([
                    'column' => $column,
                    'where' => $orderWhere,
                    'bind' => $orderBind
                ], $this->switchOrderDb($lockKey), $global);
            }
            if(empty($orderInfo)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }

            $order['data'][$key]['shop_name'] = $global ? '海外优选' : '诚仁堂自营';
            if($global == 0 && $value['sign'] == 1){
                $shopNameArr = $skuData->getShopNameByShopId([
                    'column' => 'name',
                    'where' => 'id = :id:',
                    'bind' => ['id' => $orderInfo['shop_id']],
                ]);
                if(!empty($shopNameArr)) $order['data'][$key]['shop_name'] = $shopNameArr['name'];
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

            $cancel = $config->order_effective_time * 3600;
            if($orderInfo['express_type'] == 2 || $orderInfo['express_type'] == 3 && $global == 0){
                $cancel = $config->o2o_order_effective_time * 3600;
            }
            $cancelTime = $orderInfo['status'] == OrderEnum::ORDER_PAYING ? (int) $orderInfo['audit_time'] + $cancel - time() : 0;
            $order['data'][$key]['cancel_time'] = $cancelTime < 0 ? 0 : $cancelTime;
            $order['data'][$key]['valid_time'] = $cancel;
            $order['data'][$key]['refund_status'] = 0;

            // 订单新的状态(V2)
            $order['data'][$key]['order_status'] = $this->_eventsManager->fire('order:getOrderStatus',$this,['orderInfo' => $orderInfo,'is_global' => $global]);
            $order['data'][$key]['last_service'] = null;
            $order['data'][$key]['comment_id'] = '';
            $order['data'][$key]['comment_status'] = '';
            $order['data'][$key]['allow_comment_number'] = '';
            $order['data'][$key]['is_oral_imapact'] = 0; // 是否速愈素
            $order['data'][$key]['is_apply_return'] = 0; // 是否能申请售后
            if($global){
                $order['data'][$key]['clearance_status'] = $this->getClearanceStatus($orderInfo['is_declare']); // 清关状态
            }else{
                // 最新的服务单信息
                $lastService = $orderData->getOrderServiceInfo([
                    'column' => 'b.id,b.service_sn,b.status,b.operator_id,b.log_content,b.add_time',
                    'where' => 'a.order_sn = :order_sn: order by b.id desc',
                    'bind' => [ 'order_sn' => $value['order_sn'] ],
                ],$orderInfo['shop_id']);
                if(!empty($lastService)) $order['data'][$key]['last_service'] = $lastService;
                $order['data'][$key]['audit_reason'] = $orderInfo['audit_reason']; // 审核不通过原因
            }
            if (!in_array($orderInfo['express_type'],[2,3]) && (in_array($orderInfo['status'], [OrderEnum::ORDER_SHIPPED, OrderEnum::ORDER_EVALUATING, OrderEnum::ORDER_FINISHED]
                    ) || $orderInfo['express_sn'])) {
                $order['data'][$key]['is_show_logisticsbutton'] = true;
            }

            $order['data'][$key]['giftsList'] = $order['data'][$key]['goodsList']  = [];
            $orderDetailColumn = 'a.id,a.total_sn,a.order_sn,a.goods_id,a.goods_name,a.goods_image,a.price,a.unit_price,
                a.goods_number,a.specifications,a.is_comment,a.is_return,a.add_time,a.goods_type,a.discount_price,
                a.discount_remark,a.stock_type,a.market_price,a.original_price,a.promotion_origin,a.promotion_code,a.invite_code,a.code_bu,a.code_region,a.code_office';
            $orderDetailColumn .= $global ?  ',a.push_host,a.business_id' : ',a.group_id,a.tag_id,a.treatment_id,a.refund_goods_number,a.is_refund';

            if($value['sign'] == 3){
                $detailWhere = 'a.total_sn = :total_sn:';
                $detailBind = ['total_sn' => $value['total_sn']];
            }else{
                $detailWhere = 'a.order_sn = :order_sn:';
                $detailBind = ['order_sn' => $value['order_sn']];
            }
            // 获取订单详细
            $orderDetail = $orderDetailData->getOrderDetail([
                'column' => $orderDetailColumn.',c.drug_type',
                'where' => $detailWhere.' order by a.goods_type asc',
                'bind' => $detailBind
            ], $this->switchOrderDb($lockKey), $global);
            if(empty($orderDetail)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }

            $commentGoodsList = []; // 允许评价的商品(排除处方商品、虚拟商品)
            $returnGoodsList = []; // 允许售后的商品(排除活动赠品)
            foreach ($orderDetail as $k => $v){
                if($v['goods_type'] != 1){
                    $returnGoodsList[] = $v;
                }
                // 赠品、附属赠品
                if($v['goods_type'] == 1 || $v['goods_type'] == 2){
                    $order['data'][$key]['giftsList'][] = $v;
                }else{
                    // 普通商品、换购品
                    $v['is_global'] = $global;
                    $order['data'][$key]['goodsList'][] = $v;
                    // 排除处方商品
                    if($v['drug_type'] != 1 && $v['drug_type'] != 5){
                        $commentGoodsList[] = $v;
                    }
                }
            }
            if($global == 0 && $orderInfo['express_status'] == 1 && ($value['status'] == OrderEnum::ORDER_EVALUATING || $value['status'] == OrderEnum::ORDER_FINISHED)){
                // 判断是否能申请售后
                $isRefundArr = array_unique(array_column($returnGoodsList,'is_refund'));
                if(in_array(0,$isRefundArr)){
                    $order['data'][$key]['is_apply_return'] = 1;
                }
            }

            // 待评价、已完成的订单(即交易完成状态)
            if($orderInfo['status'] == OrderEnum::ORDER_EVALUATING || $orderInfo['status'] == OrderEnum::ORDER_FINISHED){
                $order['data'][$key] = $this->_eventsManager->fire('order:getOrderCommentInfo',$this,['orderInfo' => $order['data'][$key],'commentGoodsList' => $commentGoodsList]);
            }
            if($global){
                // 海外购订单需验证速愈素
                //if($orderInfo['order_bond'] == 2){
                    $order['data'][$key]['pay_link'] = $config->wap_base_url[$env].'order-pay.html?order_id='.$order['data'][$key]['order_sn'].'&is_global='.$global;
                    $order['data'][$key]['callback_link'] = $config->wap_base_url[$env].'order-submit-successfully.html';
                    $order['data'][$key]['is_oral_imapact'] = 1;
                //}
            }
            
            // 退货/售后
            $order['data'][$key]['returnInfo'] = null;
            $order['data'][$key]['notice_has_service'] = 0;
            if ($global === 0) {
                $reasonRet = $baseData->getData([
                    'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
                    'column' => '*',
                    'where' => 'where order_sn = :order_sn:',
                    'order' =>'order by id desc',
                    'bind' => [
                        'order_sn' => $value['order_sn']
                    ],
                ], true);
                if(!empty($reasonRet)){
                    $order['data'][$key]['returnInfo'] = $reasonRet;
                    $order['data'][$key]['refund_status'] = $reasonRet['status'];
                    if($value['status'] == OrderEnum::ORDER_REFUND || $value['status'] ==  OrderEnum::ORDER_SHIPPING || $value['status'] ==  OrderEnum::ORDER_SHIPPED){
                        // 订单新的状态(V2) [交易关闭/待发货/待收货]
                        if($reasonRet['status'] == 3){
                            $order['data'][$key]['order_status'] = OrderEnum::ORDER_CLOSED;
                        }else{
                            $order['data'][$key]['order_status'] = ($value['status'] ==  OrderEnum::ORDER_REFUND) ? $orderInfo['last_status'] : $value['status'];
                        }

                        if($value['status'] ==  OrderEnum::ORDER_SHIPPED && in_array($reasonRet['status'],[0,4,5,2])){
                            $order['data'][$key]['notice_has_service'] = 1;
                        }
                    }
                    //全部商品已完成退货则不可以评论
                    if(!empty($commentGoodsList)){
                        if(!in_array(0,array_unique(array_column($commentGoodsList,'is_return')))){
                            $order['data'][$key]['comment_status'] = 3;
                        }
                    }
                }
            }

            foreach ($order['data'][$key]['goodsList'] as $kk => $vv) {
                // 判断是否会员标签价
                $order['data'][$key]['goodsList'][$kk]['memberTagName'] = '';
                $memberTagName = '';
                if (isset($vv['tag_id'])) {
                    if ($platform == OrderEnum::PLATFORM_APP) {
                        $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $vv['tag_id']);
                    } else {
                        // 非app端要排除辣妈
                        if ($vv['tag_id'] != 0) {
                            $memberTagName = BaiyangUserGoodsPriceTagData::getInstance()->getPriceTagName($platform, $vv['tag_id']);
                        }
                    }
                    if ($memberTagName) {
                        $order['data'][$key]['goodsList'][$kk]['memberTagName'] = $memberTagName['tag_name'].'价';
                    }
                }
            }
            // 判断订单是否处方单
            $goodsIdsStr = implode(',',array_column($orderDetail,'goods_id'));
            $order['data'][$key]['rx_exist'] = 0;
            $drugTypeArr = $this->filterData('drug_type',$this->getGoodsDetail(['goods_id' => $goodsIdsStr, 'platform' => $platform]));
            if($global == 0){
                $noAudits = $this->func->getConfigValue('order_no_audit_goods_type').',5';
                foreach ($drugTypeArr as $k => $v){
                    if (strpos($noAudits, (string)$v['drug_type']) === false) $order['data'][$key]['rx_exist'] = 1;
                }
            }

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
            }
        }
        $data = [
            'orderList' => $order['data'],
            'pageNum' => $totalItem,
            'pageCount' => ceil($totalItem / $param['pageSize']),
            'pageStart' => $param['pageStart'],
            'pageSize' => $param['pageSize'],
        ];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * @desc 订单搜索 (V2)
     * @param array $param
     *      -int user_id 用户id
     *      -string order_sn 订单编号(可填，搜索时用)
     *      -string platform  平台
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function searchOrder($param)
    {
        // 格式化参数
        $param['user_id'] = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['order_sn'] = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $param['pageStart'] = isset($param['pageStart']) && (int)$param['pageStart'] > 0 ? (int)$param['pageStart'] : 1;
        $param['pageSize'] = isset($param['pageSize']) && (int)$param['pageSize'] > 0 ? (int)$param['pageSize'] : 5;
        $param['status'] = isset($param['status']) ? (string)$param['status'] : '';
        if (empty($param['user_id']) || empty($param['status']) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 订单搜索
        if(!empty($param['order_sn'])) $param['find'] = "order_sn = '{$param['order_sn']}'";
        return $this->getOrderListByStatusV2($param);
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
        $redis = $this->cache;
        $redis->selectDb(2);
        $ret = $redis->getValue($cacheKey);
        if ($ret) {
            return $this->makeOrderSn($is_global);
        }
        // 设置缓存
        $redis->setValue($cacheKey, 1, 30);
        return $order_sn;
    }

    /**
     * 利用redis生成子订单号
     * @param  $parent_order_sn string  主订单号
     * @author  柯琼远
     * @return bool|string
     */
    protected function makeChildOrderSn($parent_order_sn) {
        $order_sn = substr($parent_order_sn, 0, -5);
        //生成订单号
        $order_sn = $order_sn . substr(rand(100000, 999999), 1, 5);
        $cacheKey = CacheKey::ORDER_SN . $order_sn;
        $redis = $this->cache;
        $redis->selectDb(2);
        if ($redis->getValue($cacheKey)) {
            return $this->makeChildOrderSn($parent_order_sn);
        }
        // 设置缓存
        $redis->setValue($cacheKey, 1, 30);
        return $order_sn;
    }
    
    /**
     * @desc 获取不同状态的订单数量
     * @param array $param
     *      -int user_id 用户id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getOrderNumberByStatus(array $param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['channel_subid'] = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $param['udid'] = isset($param['udid']) ? (string)$param['udid'] : '';
        if(empty($userId) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $orderData = BaiyangOrderData::getInstance();
        // 根据状态得到统计数据
        $data[OrderEnum::ORDER_ALL] = $orderData->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_ALL]);
        $data[OrderEnum::ORDER_PAYING] = $orderData->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_PAYING]);
        $data[OrderEnum::ORDER_SHIPPING] = $orderData->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_SHIPPING]);
        $data[OrderEnum::ORDER_SHIPPED] = $orderData->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_SHIPPED]);
        $data[OrderEnum::ORDER_EVALUATING] = $orderData->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_EVALUATING]);
        $data[OrderEnum::ORDER_REFUND] = $orderData->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_REFUND]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * @desc 获取不同状态的订单数量 (V2版本)
     * @param array $param
     *      -int user_id 用户id
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getOrderNumberByStatusV2(array $param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['channel_subid'] = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $param['udid'] = isset($param['udid']) ? (string)$param['udid'] : '';
        if(empty($userId) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $data = $this->_eventsManager->fire('order:getOrderNumberByStatus',$this,['user_id' => $userId]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
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
        if(empty($orderSn) && !empty($prescriptionId)){
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
        }
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
        $orderSign = 1;
        if (!$order) {
            // 获取母订单
            $order = $orderData->getParentOrder([
                'column' => '*',
                'where' => 'order_sn = :order_sn: and user_id = :user_id: and is_delete = 0',
                'bind' => [
                    'order_sn' => $orderSn,
                    'user_id' => $userId,
                ]
            ]);
            $orderSign = 3; // 母订单标识
            if(!$order){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
        }
        $config = $this->config;
        $env = $config->environment;
        $orderDetailData = BaiyangOrderDetailData::getInstance();

        // 相同的订单数据
        $orderInfo = [
            'order_sn' => $order['order_sn'],
            'user_id' => $order['user_id'],
            'status' => $order['status'],
            'last_status' => $order['last_status'],
            'is_global' => $global,
            'global_class' => $global ? 'hwg-btn' : '',
            'province' => $order['province'],
            'city' => $order['city'],
            'county' => $order['county'],
            'address' => $order['address'],
            'telephone' => $order['telephone'],
            'add_time' => $order['add_time'],
            'pay_time' => $order['payment_id'] > 0 && $order['audit_state'] == 1 ? $order['pay_time'] < $order['audit_time'] ? $order['audit_time'] : $order['pay_time'] : 0,
            'delivery_time' => $order['delivery_time'],
            'express_time' => $order['status'] == OrderEnum::ORDER_SHIPPED ? 0 : $order['express_time'],
            'audit_time' => $order['audit_time'],
            'allow_comment' => $order['allow_comment'],
            'consignee' => $order['consignee'],
            'consignee_id' => isset($order['consignee_id']) ? $order['consignee_id'] : '',
            'invoice_type' => $order['invoice_type'],
            'invoice_info' => $order['invoice_info'],
            'e_invoice_url' => isset($order['e_invoice_url']) ? $order['e_invoice_url'] : '',
            'is_parent' => $orderSign == 3 ? 1 : 0, // 是否拆单
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
        $orderInfo['ordonnance_photo'] = isset($order['ordonnance_photo']) && $order['ordonnance_photo'] != '无' ? $order['ordonnance_photo'] : '' ;
        $orderInfo['order_total'] = $global ? $order['order_total_amount'] : $order['total'];
        $orderInfo['invoice_info'] = json_decode($orderInfo['invoice_info'],true);
        $orderInfo['goods_amount'] = $order['goods_price']; //商品总额

        $orderInfo['coupon_amount'] = $order['user_coupon_price']; // 优惠券金额
        $orderInfo['full_reduce'] = $order['youhui_price']; // 优惠满减
        $orderInfo['balance_price'] = $order['balance_price']; // 余额支付的金额
        $orderInfo['order_discount_money'] = $order['order_discount_money']; // 满减+满折+优惠券
        if($orderInfo['coupon_amount'] + $orderInfo['full_reduce'] < $orderInfo['order_discount_money']) $orderInfo['full_reduce'] = bcsub($orderInfo['order_discount_money'],$orderInfo['coupon_amount'],2); // 代客下单优惠
        $orderInfo['detail_discount_money'] = $order['detail_discount_money']; // 订单优惠了xxx(套餐+商品)
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

        $cancel = $config->order_effective_time * 3600;
        // 极速达的剩余支付时间较少(极速达 = 当日达和两小时达)
        if($orderInfo['express_type'] == 2 || $orderInfo['express_type'] == 3  && $global == 0){
            $cancel = $config->o2o_order_effective_time * 3600;
        }
        $cancelTime = $orderInfo['status'] == OrderEnum::ORDER_PAYING ? (int) $orderInfo['audit_time'] + $cancel - time() : 0;
        $orderInfo['cancel_reason'] = $order['cancel_reason'];
        $orderInfo['cancel_time'] = $cancelTime < 0 ? 0 : $cancelTime;
        $orderInfo['valid_time'] = $cancel;
        $orderInfo['buyer_message'] = $order['buyer_message'];
        $orderInfo['is_show_logisticsbutton'] = false;
        // 订单新的状态(V2)
        $orderInfo['order_status'] = $this->_eventsManager->fire('order:getOrderStatus',$this,['orderInfo' => $order,'is_global' => $global]);
        $orderInfo['last_logistics'] = null;
        $orderInfo['last_service'] = null;
        $orderInfo['service_number'] = '';
        $orderInfo['comment_id'] = '';
        $orderInfo['comment_status'] = '';
        $orderInfo['allow_comment_number'] = '';
        $orderInfo['is_oral_imapact'] = 0; // 是否速愈素
        $orderInfo['is_apply_return'] = 0; // 是否能申请售后

        if (!in_array($orderInfo['express_type'],[2,3]) && (in_array($orderInfo['status'], [OrderEnum::ORDER_SHIPPED, OrderEnum::ORDER_EVALUATING, OrderEnum::ORDER_FINISHED]
                ) || (!empty($orderInfo['express_sn']) && $orderInfo['express_sn'] != '无'))) {
            $orderInfo['is_show_logisticsbutton'] = true;
        }
        $rxExist = 0; // 是否处方单
        $giftsList = $detailList  = [];
        $orderDetailColumn = 'a.id,a.total_sn,a.order_sn,a.goods_id,a.goods_name,a.goods_image,a.price,a.unit_price,a.goods_number,a.specifications,a.is_comment,a.is_return,a.add_time,a.goods_type,a.discount_price,a.discount_remark,a.promotion_price,a.promotion_total,a.stock_type,a.market_price,a.original_price,a.promotion_origin,a.promotion_code,a.invite_code,a.code_bu,a.code_region,a.code_office';
        $orderDetailColumn .= $global ?  ',a.push_host,a.business_id,a.goods_tax_amount,a.tax_rate' : ',a.group_id,a.tag_id,a.treatment_id,a.refund_goods_number,a.is_refund';
        $commentGoodsList = []; // 允许评价的商品(排除处方商品)

        // 普通订单
        if ($global === 0) {
            if($orderSign == 1){
                // 子订单
                $detailWhere = 'a.order_sn = :order_sn:';
                $detailBind = ['order_sn' => $orderSn];
            }else{
                // 母订单
                $detailWhere = 'a.total_sn = :total_sn:';
                $detailBind = ['total_sn' => $order['total_sn']];
            }
            // 获取订单详细
            $detailList = $orderDetailData->getOrderDetail([
                'column' => $orderDetailColumn.',c.drug_type',
                'where' => $detailWhere.' order by a.goods_type asc',
                'bind' => $detailBind
            ], $this->switchOrderDb($rwKey), $global);
            if(empty($detailList)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }

            $returnGoodsList = []; // 允许售后的商品(排除活动赠品)
            foreach ($detailList as $key => $val) {
                if($val['goods_type'] != 1){
                    $returnGoodsList[] = $val;
                }
                // 赠品(普通赠品、附属赠品)
                if($val['goods_type'] == 1 || $val['goods_type'] == 2){
                    $giftsList[] = $val;
                    unset($detailList[$key]);
                    continue;
                }
                // 排除处方商品、虚拟商品
                if($val['drug_type'] != 1 && $val['drug_type'] != 5){
                    $commentGoodsList[] = $val;
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
                        $detailList[$key]['memberTagName'] = $memberTagName['tag_name'].'价';
                    }
                }
            }
            if($global == 0 && $order['express_status'] == 1 && ($order['status'] == OrderEnum::ORDER_EVALUATING || $order['status'] == OrderEnum::ORDER_FINISHED)){
                // 判断是否能申请售后
                $isRefundArr = array_unique(array_column($returnGoodsList,'is_refund'));
                if(in_array(0,$isRefundArr)){
                    $orderInfo['is_apply_return'] = 1;
                }
            }

            // 判断订单是否处方单
            $goodsIdsStr = implode(',',array_column($detailList,'goods_id'));
            $drugTypeArr = $this->filterData('drug_type',$this->getGoodsDetail(['goods_id' => $goodsIdsStr, 'platform' => $platform]));
            $noAudits = $this->func->getConfigValue('order_no_audit_goods_type').',5';
            foreach ($drugTypeArr as $k => $v){
                if (strpos($noAudits, (string)$v['drug_type']) === false) $rxExist = 1;
            }

            // 获取物流
            $shippingDetail = null;
            if(!empty($orderInfo['express_sn']) && strtolower($orderInfo['express']) != 'zps'){
                $shippingDetail = $this->func->getLogisticsData($orderSn);
            }
            if(!empty($shippingDetail)){
                // 最新的物流信息
                if(isset($shippingDetail['context_list']) && !empty($shippingDetail['context_list'])){
                    $contextList = reset($shippingDetail['context_list']);
                    $orderInfo['last_logistics']['time'] = $contextList['time'];
                    $orderInfo['last_logistics']['context'] = $contextList['context'];
                }
                if($platform == OrderEnum::PLATFORM_PC){
                    $sort1 = []; // pc端普通物流信息要顺序 (最新时间在最下面)
                    if(isset($shippingDetail['context_list']) && !empty($shippingDetail['context_list'])){
                        foreach ($shippingDetail['context_list'] as $key => $value) {
                            $sort1[] = $key;
                        }
                        array_multisort($sort1, SORT_DESC,$shippingDetail['context_list']);
                    }
                }
            }
            // 最新的服务单信息
             $lastService = $orderData->getOrderServiceInfo([
                'column' => 'b.id,b.service_sn,b.status,b.operator_id,b.log_content,b.add_time',
                'where' => 'a.order_sn = :order_sn: order by b.id desc',
                'bind' => [ 'order_sn' => $orderSn ],
            ],$order['shop_id']);
            if(!empty($lastService)) $orderInfo['last_service'] = $lastService;
            // 订单的服务单数量
            $orderInfo['service_number'] = $orderData->getOrderServiceNumber(['order_sn' => $orderSn]);
        } else {
            // 跨境订单
            $detailList = $orderDetailData->getOrderDetail([
                'column' => $orderDetailColumn.',c.drug_type',
                'where' => 'a.order_sn = :order_sn: and a.goods_type = 0 order by a.goods_type asc',
                'bind' => [
                    'order_sn' => $orderSn,
                ]
            ], $this->switchOrderDb($rwKey), $global);
            if(empty($detailList)){
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
            // 海外购订单需验证速愈素
            //if($order['order_bond'] == 2){
                $orderInfo['pay_link'] = $config->wap_base_url[$env].'order-pay.html?order_id='.$orderInfo['order_sn'].'&is_global='.$global;
                $orderInfo['callback_link'] = $config->wap_base_url[$env].'order-submit-successfully.html';
                $orderInfo['is_oral_imapact'] = 1;
            //}

            foreach ($detailList as $key => $value){
                // 商品是否上下架
                $saleArr = $this->filterData('sale',$this->getGoodsDetail(['goods_id' => $value['goods_id'], 'platform' => $platform]));
                $detailList[$key]['is_sale'] = $saleArr[0]['sale'];
                $detailList[$key]['is_global'] = $global;
                $detailList[$key]['memberTagName'] = '';
                $commentGoodsList[] = $value;
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

            if(!empty($kjShippingDetail)){
                // 最新的物流信息
                $contextList = end($kjShippingDetail);
                $orderInfo['last_logistics']['time'] = $contextList['time'];
                $orderInfo['last_logistics']['context'] = $contextList['status'];
                if($platform == OrderEnum::PLATFORM_APP || $platform == OrderEnum::PLATFORM_WAP){
                    $sort1 = []; // 移动端跨境物流信息要倒叙 (最新时间在最上面)
                    foreach ($kjShippingDetail as $key => $value) {
                        $sort1[] = $key;
                    }
                    array_multisort($sort1, SORT_DESC,$kjShippingDetail);
                }
            }
        }
        // 处方单
        if ($rxExist == 1) {
            $orderInfo['check_time'] = $orderInfo['add_time'] + $this->func->getConfigValue(BaiyangConfigEnum::ORDER_AUTO_AUDIT_PASS_TIME);
        }
        // 待评价、已完成的订单(即交易完成状态)
        if($orderInfo['status'] == OrderEnum::ORDER_EVALUATING || $orderInfo['status'] == OrderEnum::ORDER_FINISHED){
            $orderInfo = $this->_eventsManager->fire('order:getOrderCommentInfo',$this,['orderInfo' => $orderInfo,'commentGoodsList' => $commentGoodsList]);
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

        $notice_has_service = 0;//收货操作是否需要提示有服务单正在处理
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
        }else{
            // 退货/售后
            $reasonRet = BaseData::getInstance()->getData([
                'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
                'column' => 'status refund_status,return_type refund_type,reason,explain,images',
                'where' => 'where order_sn = :order_sn:',
                'order' =>'order by id desc',
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
                $orderInfo['status_name'] = $reasonRet['refund_status'] >= 0 ? OrderEnum::$RefundStatus[$reasonRet['refund_status']] : '';
                // 订单新的状态(V2) [交易关闭/待发货/待收货]
                if($orderInfo['status'] == OrderEnum::ORDER_REFUND || $orderInfo['status'] ==  OrderEnum::ORDER_SHIPPING || $orderInfo['status'] ==  OrderEnum::ORDER_SHIPPED){
                    if($reasonRet['refund_status'] == 3){
                        $orderInfo['order_status'] = OrderEnum::ORDER_CLOSED;
                    }else{
                        $orderInfo['order_status'] = ($orderInfo['status'] ==  OrderEnum::ORDER_REFUND) ? $orderInfo['last_status'] : $orderInfo['status'];
                    }
                }

                if($orderInfo['status'] ==  OrderEnum::ORDER_SHIPPED && in_array($reasonRet['refund_status'],[0,4,5,2])){
                    $notice_has_service = 1;//收货操作是否需要提示有服务单正在处理
                }
                //全部商品已完成退货则不可以评论
                if($global == 0 && !empty($commentGoodsList)){
                    if(!in_array(0,array_unique(array_column($commentGoodsList,'is_return')))){
                        $orderInfo['comment_status'] = 3;
                    }
                }
            }
        }
        
        $orderInfo['images'] = json_decode($orderInfo['images'],true);
        // pc端需要处理物流数据
        if($platform == OrderEnum::PLATFORM_PC && (!empty($shippingDetail) || !empty($kjShippingDetail))){
            $tempShippingDetail = $global ? $kjShippingDetail : $shippingDetail;
            if($global){
                // 海外购订单
                $contextList = $this->handleLogisticsData($tempShippingDetail,$global);
                unset($kjShippingDetail);
                $kjShippingDetail['logistics_id'] = $orderInfo['express_sn'];
                $kjShippingDetail['logistics_com'] = $orderInfo['express'];
                $kjShippingDetail['clearance_status'] = $this->getClearanceStatus($order['is_declare']); // 清关状态
                $kjShippingDetail['context_list'] = $contextList;
            }else{
                // 普通订单
                $tempShippingDetail = $shippingDetail['context_list'];
                $shippingDetail['context_list'] = !empty($tempShippingDetail) ? $this->handleLogisticsData($tempShippingDetail,$global) : [];
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
            'notice_has_service'=>$notice_has_service,
        ];
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $data);
    }

    /**
     * 删除订单
     * @param array $param
     *      -order_sn string
     *      -user_id int
     * @return array
     * @author  陈松炉
     */
    public function deleteOrder($param) {
        //接收参数
        $orderSn = isset($param['order_sn']) ? $param['order_sn']: '';
        $userId = isset($param['user_id']) ? $param['user_id']: '';
        if (empty($orderSn) || empty($userId) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $baseData=BaseData::getInstance();
        // 获取订单信息
        $orderInfo = BaiyangOrderData::getInstance()->getOrderInfo($orderSn);
        // (order_sign:1-海外购，2-母订单，3-没有母订单的子订单，4-有母订单的子订单)
        if(empty($orderInfo) || $orderInfo['user_id'] != $userId){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        if ($orderInfo['status'] == 'refund') {
            // 退款完成的订单可以删除
            $returnReason = $baseData->getData([
                'table' => 'Shop\Models\BaiyangOrderGoodsReturnReason',
                'column' => 'status',
                'where' => "where order_sn=:order_sn:",
                'order' => "order by id desc",
                'bind' => ['order_sn'=>$orderSn],
            ], true);
            if (!empty($returnReason) && $returnReason['status'] != 3) {
                return $this->uniteReturnResult(HttpStatus::NO_DATA);
            }
        }
        // 删除订单
        $updateData = [
            'table' => 'Shop\Models\BaiyangOrder',
            'column' => "is_delete = 1",
            'where' => "where order_sn=:order_sn:",
            'bind' => ['order_sn'=>$orderSn]
        ];
        //查询订单信息
        if ($orderInfo['order_sign'] == 1) {
            // 海外购订单
            $updateData['table'] = 'Shop\Models\BaiyangKjOrder';
            if (!$baseData->updateData($updateData)) {
                return $this->uniteReturnResult(HttpStatus::DELETE_ERROR);
            }
        } elseif ($orderInfo['order_sign'] == 2) {
            // 母订单
            $updateData['table'] = 'Shop\Models\BaiyangParentOrder';
            if (!$baseData->updateData($updateData)) {
                return $this->uniteReturnResult(HttpStatus::DELETE_ERROR);
            }
            $updateData['table'] = 'Shop\Models\BaiyangOrder';
            $updateData['where'] = "where total_sn=:order_sn:";
            if (!$baseData->updateData($updateData)) {
                return $this->uniteReturnResult(HttpStatus::DELETE_ERROR);
            }
        } else {
            // 子订单
            $updateData['table'] = 'Shop\Models\BaiyangOrder';
            if (!$baseData->updateData($updateData)) {
                return $this->uniteReturnResult(HttpStatus::DELETE_ERROR);
            }
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }

    /**
     * 取消订单
     * @param array $param
     *      -order_sn string
     *      -user_id int
     * @return array
     * @author  陈松炉
     */
    public function cancelOrder($param)
    {
        // 接收参数
        $orderSn = isset($param['order_sn']) ? $param['order_sn']: '';
        $userId = isset($param['user_id']) ? $param['user_id']: '';
        $cancelReason = isset($param['cancel_reason']) ? $param['cancel_reason']: '';
        $platform = $param['platform'];
        if (empty($orderSn) || empty($userId) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        //PC端取消订单必须选择取消原因
        if($platform === \Shop\Models\OrderEnum::PLATFORM_PC && empty($cancelReason)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $global = strstr($orderSn, OrderEnum::KJ) ? 1 : 0;
        $this->dbWrite->begin();
        $baseData = BaseData::getInstance();
        // 获取订单信息
        $orderInfo = BaiyangOrderData::getInstance()->getOrderInfo($orderSn);
        if (empty($orderInfo) || $orderInfo['order_sign'] == 4) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        if (!($orderInfo['status'] == 'paying' || ($orderInfo['status'] == 'shipping' && $orderInfo['audit_state'] != 1))) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        // 更新参数
        $updateData = [
            'table' => 'Shop\Models\BaiyangOrder',
            'column' => "status='canceled',last_status='paying',cancel_reason=:cancel_reason:",
            'where' => "where order_sn=:order_sn:",
            'bind' => ['order_sn'=>$orderSn,'cancel_reason'=>$cancelReason]
        ];
        //查询订单信息
        if ($orderInfo['order_sign'] == 1) {
            // 海外购订单
            $updateData['table'] = 'Shop\Models\BaiyangKjOrder';
            if (!$baseData->updateData($updateData)) {
                $this->dbWrite->rollback();
                return $this->uniteReturnResult(HttpStatus::CANCEL_ERROR);
            }
        } else {
            // 取消母订单
            $updateData['table'] = 'Shop\Models\BaiyangParentOrder';
            if (!$baseData->updateData($updateData)) {
                $this->dbWrite->rollback();
                return $this->uniteReturnResult(HttpStatus::CANCEL_ERROR);
            }
            // 取消子订单
            $updateData['table'] = 'Shop\Models\BaiyangOrder';
            $updateData['where'] = "where total_sn=:order_sn:";
            if (!$baseData->updateData($updateData)) {
                $this->dbWrite->rollback();
                return $this->uniteReturnResult(HttpStatus::CANCEL_ERROR);
            }
        }
        //退余额
        if ($orderInfo['balance_price'] > 0) {
            $ret = $this->_eventsManager->fire('order:external_refund_order', $this, [
                'order_sn'     => $orderSn,
                'refund_money' => $orderInfo['balance_price'],
            ]);
            if ($ret['status'] != 200) {
                $this->log->error("REFUND BALANCE:" . print_r($ret,1));
            }
        }
        //添加订单变更记录
        BaiyangOrderData::getInstance()->addOrderLog($userId, $orderSn, $orderInfo);
        //删除限购记录
        $baseData->updateData([
            'column' => 'is_delete = :is_delete:',
            'table' => 'Shop\Models\BaiyangLimitedLog',
            'where' => 'where order_sn=:order_sn: and user_id=:user_id: ',
            'bind' => [
                'order_sn'=>$orderSn,
                'user_id'=>$userId,
                'is_delete'=>1
            ]
        ]);
        //删除促销活动记录
        $baseData->updateData([
            'column' => 'is_delete = 1',
            'table' => 'Shop\Models\BaiyangOrderPromotionDetail',
            'where' => 'where order_sn = :order_sn: and user_id = :user_id: ',
            'bind' => ['order_sn' => $orderSn,'user_id' => $userId]
        ]);
        //cps订单状态改为取消订单
        $baseData->updateData([
            'column' => 'order_status = :status:',
            'table' => 'Shop\Models\BaiyangCpsOrderLog',
            'where' => 'where order_sn=:order_sn: ',
            'bind' => [
                'order_sn'=>$orderSn,
                'status'=>'canceled'
            ]
        ]);
        //退优惠券
        $baseData->updateData([
            'column' => 'is_used = :is_used:,used_time = :used_time:,order_sn = :order_id:',
            'table' => 'Shop\Models\BaiyangCouponRecord',
            'where' => 'where order_sn=:order_sn: and user_id=:user_id: ',
            'bind' => [
                'order_sn'=>$orderSn,
                'user_id'=>$userId,
                'is_used'=>0,
                'used_time'=>0,
                'order_id'=>''
            ]
        ]);
        //改库存变化记录
        $baseData->updateData([
            'column' => 'change_reason = :change_reason:,sync = :sync:,change_time = :change_time:,sync_time = :sync_time:',
            'table' => 'Shop\Models\BaiyangGoodsStockChangeLog',
            'where' => 'where order_id=:order_sn: ',
            'bind' => [
                'order_sn'=>$orderSn,
                'change_reason'=>2,
                'sync'=>1,
                'change_time'=>date('Y-m-d H:i:s'),
                'sync_time'=> date('Y-m-d H:i:s')
            ]
        ]);

        //易复诊处方状态修改
        $unionUserId = BaiyangUserData::getInstance()->getUserUnion_id($userId);
        $result = $this->func->prescriptionMatchOrder($unionUserId['union_user_id'], $orderSn, 'cancel');
        if ($result['code'] != 200 && $result['code'] != 31092) {
            //记录同步易复诊处方状态失败数据
            $this->log->error("YFZ ORDER SYNC FAULT !");
            $this->log->error("ORDER_SN : " . $orderSn);
            $this->log->error("RETURNED VALUE : " . print_r($result,1));
        }
        $this->dbWrite->commit();

        // 根据订单号来获取平台名
        $channelSubid = $orderSn[0] == OrderEnum::KJ ? substr($orderSn, 1, 2) : substr($orderSn, 0, 2);
        $platform = array_search($channelSubid, array('pc' => 95, 'wap' => 91, 'wechat' => 85));
        if($channelSubid == 90 || $channelSubid == 89){
            $platform = 'app';
        }

        // 同步商品库存到es
        $detailList = BaiyangOrderDetailData::getInstance()->getOneOrderDetail([
            'column' => 'goods_id',
            'where' => 'total_sn = :order_sn:',
            'bind' => ['order_sn' => $orderSn]
        ], $global);
        $redis = $this->cache;
        $redis->selectDb(6);
        foreach ($detailList as $key => $value) {
            $redis->rPush(CacheKey::ES_STOCK_KEY, [
                'goodsId' => $value['goods_id'],
                'platform' => $platform
            ]);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }

    /**
     * 支付完成
     * @param array $param
     *      -order_sn string(*)
     *      -payment_id int 支付方式id 1: 支付宝支付  2 : 微信支付 5:苹果支付 6:银联支付(*)
     *      -pay_money float 支付金额(*)
     *      -trade_no string 交易号
     *      -buyer_id string 买家帐号（支付宝支付）
     *      -bank_type string 付款银行（微信支付）
     * @return array
     * @author  柯琼远
     */
    public function payFinished($param) {
        // 接收参数
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn']: '';
        $paymentId = isset($param['payment_id']) ? (int)$param['payment_id']: 0;
        $payMoney = isset($param['pay_money']) ? (float)$param['pay_money']: 0;
        $tradeNo = isset($param['trade_no']) ? (string)$param['trade_no']: '';
        $buyerId = isset($param['buyer_id']) ? (string)$param['buyer_id']: '';
        $bank_type = isset($param['bank_type']) ? (string)$param['bank_type']: '';
        // 判断参数是否合法
        if (empty($orderSn) || !in_array($paymentId, [1,2,5,6]) || $payMoney <= 0 || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $global = $orderSn[0] == OrderEnum::KJ ? 1 : 0;
        $nowTime = time();
        // 获取订单信息
        $orderInfo = BaiyangOrderData::getInstance()->getOrderInfo($orderSn);
        if (empty($orderInfo) || $orderInfo['status'] != 'paying' || $orderInfo['audit_state'] == 0 || $orderInfo['order_sign'] == 4) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA, $orderInfo);
        }
        if ($orderInfo['order_type'] == 5) {
            return $this->uniteReturnResult(HttpStatus::SUCCESS, [$orderInfo]);
        }
        $orderInfo['payment_time'] = $nowTime;
        $result = [$orderInfo];
        // 查检支付金额是否符合
        $real_pay = $global ? $orderInfo['order_total_amount'] : $orderInfo['total'];
        $paid = bcsub($real_pay, $orderInfo['balance_price'], 2);
        if (bccomp($paid, $payMoney, 2) != 0) {
            $this->log->error("ERROR：支付金额不符" . print_r($param,1));
            return $this->uniteReturnResult(HttpStatus::PAY_MONEY_ERROR);
        }
        $pay_remark = $payment_name = $payment_code = $pay_company_code = $payid = '';
        // 支付方式
        switch ($paymentId) {
            case 1:
                $pay_remark = "支付宝支付??,买家支付宝账号:{$buyerId}";
                $payment_name = "支付宝";
                $payment_code = "alipay";
                $payid = '5';
                $pay_company_code = $this->config->kj_custom_id['alipay'];
                break;
            case 2:
                $pay_remark = "微信支付??,付款银行:{$bank_type}";
                $payment_name = "微信支付";
                $payment_code = "wechat";
                $payid = '905_18';
                $pay_company_code = $this->config->kj_custom_id['wechat'];
                break;
            case 5:
                $pay_remark = "苹果支付:??元,连连支付单号:{$tradeNo}";
                $payment_name = "苹果支付";
                $payid = '905_19';
                $payment_code = "alipay";
                break;
            case 6:
                $pay_remark = "银联支付??元";
                $payment_name = "银联支付";
                $payment_code = "chinapay";
                $payid = '13';
                break;
            case 8:
                $pay_remark = "昊合支付??元";
                $payment_name = "昊合支付";
                $payment_code = "haohepay";
                $payid = '910_602';
                break;
        }
        // 添加支付日志
        $baseData = BaseData::getInstance();
        $addData = [
            'table' => 'Shop\Models\BaiyangOrderPayDetail',
            'bind' => [
                'order_sn' => $orderSn,
                'order_channel' => $this->config->channel_subid,
                'payid' => $payid,
                'pay_name' => $payment_name,
                'pay_money' => $payMoney,
                'pay_time' => date('Y-m-d H:i:s', $nowTime),
                'trade_no' => $tradeNo,
                'pay_remark' => str_replace('??', $paid, $pay_remark),
                'create_time' => date('Y-m-d H:i:s',$nowTime),
            ]
        ];
        $baseData->addData($addData);
        //添加订单变更记录
        BaiyangOrderData::getInstance()->addOrderLog($orderInfo['user_id'], $orderSn, $orderInfo);
        // 更新参数
        $temp_pay_remark = !empty($orderInfo['pay_remark']) ? $orderInfo['pay_remark'].';' : '';
        $updateData = [
            'is_pay' => 1,
            'pay_time' => $nowTime,
            'real_pay' => $real_pay,
            'pay_remark' => $temp_pay_remark.str_replace('??', $paid, $pay_remark),
            'payment_name' => $payment_name,
            'payment_id' => $paymentId,
            'payment_code' => $payment_code,
            'status' => 'shipping',
            'last_status' => 'paying',
            'trade_no' => $tradeNo,
        ];
        if ($orderInfo['order_sign'] == 1) {
            // 海外购
            $updateData['pay_company_code'] = $pay_company_code;
            $updateData['pay_number'] = $tradeNo;
            $baseData->updateDataV2('Shop\Models\BaiyangKjOrder',$updateData,['order_sn'=> $orderSn]);
            //更新返利表的支付状态
            $cps['order_status'] ='shipping';
            $cps['pay_time'] =$nowTime;
            $cps['pay_id'] =$paymentId;
            $baseData->updateDataV2('Shop\Models\BaiyangCpsOrderLog',$cps,['order_sn'=> $orderSn]);
        } else {
            // 普通订单
            if ($orderInfo['order_sign'] == 2) {
                // 母订单
                $baseData->updateDataV2('Shop\Models\BaiyangParentOrder',$updateData,['order_sn'=> $orderSn]);
                $childOrderList =  $baseData->getData([
                    'table' => 'Shop\Models\BaiyangOrder',
                    'column' => '*',
                    'where' => 'where total_sn = :order_sn:',
                    'bind' => ['order_sn' => $orderSn]
                ]);
                foreach ($childOrderList as $key => $value) {
                    // 添加子订单支付日志
                    $paid = bcsub($value['total'], $value['balance_price'], 2);
                    $addData['bind']['order_sn'] = $value['order_sn'];
                    $addData['bind']['pay_money'] = $paid;
                    $addData['bind']['pay_remark'] = str_replace('??', $paid, $pay_remark);
                    $baseData->addData($addData);
                    // 更新子订单状态
                    $updateData['real_pay'] = $value['total'];
                    $temp_pay_remark = !empty($value['pay_remark']) ? $value['pay_remark'].';' : '';
                    $updateData['pay_remark'] = $temp_pay_remark.$addData['bind']['pay_remark'];
                    $baseData->updateDataV2('Shop\Models\BaiyangOrder',$updateData,['order_sn'=> $value['order_sn']]);
                    $childOrderList[$key]['payment_time'] = $nowTime;
                    $childOrderList[$key]['order_id'] = $value['order_sn'];
                    $childOrderList[$key]['paid'] = $value['total'];
                    //更新返利表的支付状态
                    $cps['order_status'] ='shipping';
                    $cps['pay_time'] =$nowTime;
                    $cps['pay_id'] =$paymentId;
                    $baseData->updateDataV2('Shop\Models\BaiyangCpsOrderLog',$cps,['order_sn'=> $value['order_sn']]);
                }
                $result = $childOrderList;
                // 发短信
                $userInfo = BaiyangUserData::getInstance()->getUserInfo($orderInfo['user_id'], 'phone,user_id');
                $phone = !empty($userInfo['phone']) ? $userInfo['phone'] : $orderInfo['telephone'];
                $this->func->sendSms($phone, 'shop_split_order', [], 'pc', ['number'=>count($childOrderList)]);
            } else {
                // 没有母订单
                $baseData->updateDataV2('Shop\Models\BaiyangOrder',$updateData,['order_sn'=> $orderSn]);
                //更新返利表的支付状态
                $cps['order_status'] ='shipping';
                $cps['pay_time'] =$nowTime;
                $cps['pay_id'] =$paymentId;
                $baseData->updateDataV2('Shop\Models\BaiyangCpsOrderLog',$cps,['order_sn'=> $orderSn]);
            }
        }
        // 支付完成同步库存
        $this->_eventsManager->fire('order:syncStockAndSaleNumber', $this, ['order_sn'=>$orderSn]);
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * 获取待支付订单信息
     * @param array $param
     *      -user_id string
     *      -order_sn string
     * @return array
     * @author  柯琼远
     */
    public function getOrderInfo($param) {
        // 接收参数
        $orderSn = isset($param['order_sn']) ? $param['order_sn']: '';
        $userId = isset($param['user_id']) ? $param['user_id']: '';
        if (empty($orderSn) || empty($userId) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $result = BaiyangOrderData::getInstance()->getOrderInfo($orderSn);
        if (empty($result) || $result['user_id'] != $userId) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $result);
    }

    /**
     * @desc 订单申请退款
     * @param array $param
     *       -int      user_id    用户id
     *       -int      order_sn   订单编号
     *       -string   reason     退款原因
     *       -string   explain    退款说明
     *       -array    images     图片(最多5张)
     *       -string   platform   平台
     *       -int     channel_subid  渠道号
     * @return array  []   结果信息
     * @author 吴俊华
     */
    public function orderApplyRefund($param)
    {

        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $reason = isset($param['reason']) ? (string)$param['reason'] : '';
        $explain = isset($param['explain']) ? (string)$param['explain'] : '';
        $images = isset($param['images']) ? $param['images'] : '';

        if(!isset($param['goods_content'])){
            return $this->uniteReturnResult(HttpStatus::REFUND_VESION_UPGRADE);
        }
        $goods_content = isset($param['goods_content']) ? json_decode($param['goods_content'],true) : '';

        
        if (empty($orderSn) || !$userId  || empty($explain) || !$this->verifyRequiredParam($param) || empty($goods_content)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        $goods_arr =  [];
        foreach($goods_content as $goods){
            if (!isset($goods['detail_id']) || !isset($goods['goods_id']) || !isset($goods['goods_num'])) {
                return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
            }
            $goodsKey = $goods['detail_id'] . '_' . $goods['goods_id'];
            $goods_arr[$goodsKey] = $goods['goods_num'];
        }
//        $maxUpload = $this->config->max_upload_img;
        $maxUpload = 5;

        if (count($images) > $maxUpload) {
            return $this->uniteReturnResult(HttpStatus::OVER_MAX_UPLOAD, [], [$maxUpload]);
        }

        // 验证订单是否能够退款
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->newVerifyOrderIsCanRefund($userId ,$orderSn);

        if (!$orderInfo) {
            $error = $orderInfo === false?HttpStatus::HAVE_REFUND_SERVICE_HANDLE:HttpStatus::NO_DATA;
            return $this->uniteReturnResult($error);
        }


        $orderDetailData = BaiyangOrderDetailData::getInstance();

        $is_all_refund = true;//是否整单退款

        //售后
        if($orderInfo['express_status']==1){
            // 获取订单详细
            $detailList = $orderDetailData->getOneOrderDetail([
                'column' => '*',
                'where' => 'order_sn = :order_sn: and goods_type = 0 ',
                'bind' => [
                    'order_sn' => $param['order_sn'],
                ]
            ]);

            $is_allow = true;
            if($detailList){
                $refund_data = [];
                foreach($detailList as $key=>$goods){
                    $goodsKey = $goods['id'] . '_' . $goods['goods_id'];
                    //判断非整单退  活动赠品不参与退款但是不影响退运费
                    if($goods['goods_type']!==1 && (!isset($goods_arr[$goodsKey]) || $goods_arr[$goodsKey] !=$goods['goods_number'] )){
                        $is_all_refund = false;
                    }

//                    if(isset($goods_arr[$goodsKey]) && $goods_arr[$goodsKey]>0){
                    if(isset($goods_arr[$goodsKey])){
                        //判断规则
                        if($goods_arr[$goodsKey]<=0 || $goods_arr[$goodsKey]>$goods['goods_number']){
                            $is_allow = false;
                            break;
                        }
                        $goods['refund_goods_number'] = $detailList[$key]['refund_goods_number']+$goods_arr[$goodsKey];
                        $goods['now_refund_goods_number'] = $goods_arr[$goodsKey];
                        if($goods_arr[$goodsKey] == $goods['goods_number']){
                            $goods['refund_amount'] = $goods['promotion_total'];
                        }else{
                            $goods['refund_amount'] = bcmul(bcdiv($goods_arr[$goodsKey],$goods['goods_number'],4),$goods['promotion_total'],2);
//                            $goods['refund_amount'] = bcmul($goods_arr[$goodsKey],$goods['promotion_price'],2);
                        }
                        $refund_data[] = $goods;
                    }

                }
                $detailList = $refund_data;

            }else{
                $is_allow = false;
            }

            if(!$is_allow){
                return $this->uniteReturnResult(HttpStatus::NOT_REFUND_RULES);
            }
            //处理赠品
                $detail_ids = implode(',', array_column($detailList,'id'));
                $detailList_gift = $orderDetailData->getOneOrderDetail([
                    'column' => '*',
                    'where' => 'bind_id in('.$detail_ids.') and goods_number>refund_goods_number',
                    'bind'=>[]
                ]);

                if($detailList_gift){
                    $detailList = array_merge($detailList,$detailList_gift);
                }

        }else{
            //售前
            $detailList = $orderDetailData->getOneOrderDetail([
                'column' => '*',
                'where' => 'order_sn = :order_sn: ',
                'bind' => [
                    'order_sn' => $param['order_sn'],
                ]
            ]);

            foreach($detailList as $key=>$goods){
                $detailList[$key]['now_refund_goods_number'] = $detailList[$key]['refund_goods_number'] = $detailList[$key]['goods_number'];
                $detailList[$key]['refund_amount'] = $detailList[$key]['promotion_total'];
            }
        }


        $service_sn = substr(date('Ymds'),2).substr(mt_rand(100000,999999),1);

        // 开启事务
        $this->dbWrite->begin();
        $baseData = BaseData::getInstance();



        //整单退要退运费
        if($is_all_refund){
            $refund_amount = $orderInfo['total'];
        }else{
            $refund_amount = array_sum(array_column($detailList,'refund_amount'));
        }


        $return_type = 2;//默认退款方式为退货退款

        if(!$orderInfo['pay_type']){  //货到付款

            if($orderInfo['express_status'] != 1 ){

                $refund_amount = $orderInfo['balance_price'];
                $return_type = 0;
            }

        }else{
            if($orderInfo['express_status'] !== 1 ){
                $return_type = 1;
            }
        }

        $shop_name = '';
        if($orderInfo['shop_id']){
            $skuData = BaiyangSkuData::getInstance();
            $row = $skuData->getShopNameByShopId([
                'column' =>'name',
                'where' => 'id=:id:',
                'bind' => [
                    'id'=>$orderInfo['shop_id']
                ],
            ]);
            $shop_name = $row['name'];
        }

        $service_img = [];
        if ($images) {
            foreach($images as $v){

                if ($this->func->isBase64($v)) {
                    //把数据下载到本地处理
                    $url = $this->moveImg($v);
                    if($url && !is_array($url)){
                        $service_img[] = $url;
                    }
                }
            }
        }
        $service_img =  $service_img?json_encode($service_img):json_encode($images);

        $status = 0;
        // 退款原因
        $reasonRet = $baseData->addData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'bind' => [
                'order_sn' => $orderSn,
                'service_sn' =>$service_sn,
                'reason' => $reason,
                'explain' => $explain,
                'images' => $service_img,
                'status' => $status,
                'add_time' => time(),
                'update_time' => 0,
                'user_id'=>$userId,
                'refund_amount'=>$refund_amount,
                'return_type'=>$return_type,
                'shop_name'=>$shop_name,
                'shop_id'=>$orderInfo['shop_id'],
            ]
        ],true);


        $returnData['table'] = '\Shop\Models\BaiyangOrderGoodsReturn';
        foreach ($detailList as $key => $value) {
            //赠品直接全部退完
            if($value['goods_type']){
                $value['refund_goods_number'] = $value['now_refund_goods_number'] = $value['goods_number'];
            }
            $returnData['bind'] = [
                'user_id' => $userId,
                'order_goods_id' => $value['id'],
                'refund_goods_number' => $value['now_refund_goods_number'],
                'reason_id' =>$reasonRet,
                'return_type' => $return_type,
                'status' => $status,
                'add_time' => time(),
                'order_sn' => $orderSn
            ];
            $ret = $baseData->addData($returnData);

            if (!$ret) {
                $this->dbWrite->rollback();
                return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
            }else{
                $is_refund = $value['refund_goods_number'] == $value['goods_number']?1:0;
              $orderData->newUpdateReturnGoodsStatus($value['id'],$value['refund_goods_number'], $is_refund);
            }
        }


        // 退款订单
//        $orderRet = $orderData->updateReturnOrderStatus($orderSn, OrderEnum::ORDER_REFUND, $orderInfo['status']);
        if ($reasonRet) {
            $log_content = $orderData->getServiceStatusText($status);
            $userData = \Shop\Home\Datas\BaiyangUserData::getInstance();

            $user_info = $userData->getUserInfo($userId);
            $orderData->addOrderServiceLog($service_sn,$log_content,$status,$user_info['username']);
            $this->dbWrite->commit();

            //添加服务单日志

            // 订单日志
//            $logData = [
//                'user_id' => $userId,
//                'order_sn' => $orderSn,
//                'log_content' => serialize($orderInfo),
//            ];
//            LogService::getInstance()->save(['prefix' => 'order','data' => $logData]);
             $orderData->addOrderLog($userId, $orderSn, $orderInfo);

            //未发货 则可以根据海典截单 兼容了宝岛
            if(($orderInfo['shop_id']==1 || $orderInfo['shop_id']==0) && $orderInfo['status'] == 'shipping'  &&  isset($this->config->erp_url[$this->config->environment]) && !empty($this->config->erp_url[$this->config->environment])){
                $api = \Shop\Home\Services\ApiService::getInstance();
                $api->erpApplyREfundNotice(['service_sn'=>$service_sn]);
            }


            // cps为中民则推送
            if ($orderInfo['ad_source_id'] == 4) {
                $this->func->sendZmCps($orderSn);
            }
            return $this->uniteReturnResult(HttpStatus::SUCCESS);
        } else {
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
        }
    }

    /**
     * @desc 撤销退款申请
     * @param array $param
     *       -int      user_id    用户id
     *       -int      order_sn   订单编号
     *       -string   platform   平台
     *       -int      channel_subid  渠道号
     * @return array  []   结果信息
     * @author 吴俊华
     */
    public function oldcancelRefundApply($param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        if (empty($orderSn) || empty($userId) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        $orderData = BaiyangOrderData::getInstance();
        //查询订单信息
        $orderInfo = $orderData->getTheOrder([
            'column' => '*',
            'where' => 'order_sn = :order_sn: and user_id=:user_id:',
            'bind' => [
                'order_sn' => $orderSn,
                'user_id' => $userId,
            ]
        ]);
        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $baseData = BaseData::getInstance();
        $returnInfo = $orderData->getReturnOrderInfo($userId, $orderSn);
        if(!$returnInfo){
            return $this->uniteReturnResult(HttpStatus::ORDER_NOT_REFUND_RECORD);
        }
        // 获取订单详细
        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $detailList = $orderDetailData->getOneOrderDetail([
            'column' => '*',
            'where' => 'order_sn = :order_sn: and goods_type = 0',
            'bind' => [
                'order_sn' => $param['order_sn'],
            ]
        ]);

        // 开启事务
        $this->dbWrite->begin();
        $returnData['table'] = '\Shop\Models\BaiyangOrderGoodsReturn';
        $returnData['where'] = 'where order_goods_id = :order_goods_id:';
        foreach ($detailList as $key => $value) {
            $returnData['bind'] = [
                'order_goods_id' => $value['id'],
            ];
            $ret = $baseData->deleteData($returnData);
            if (!$ret) {
                $this->dbWrite->rollback();
                return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
            }
        }
        // 删除退款原因
        $reasonRet = $baseData->deleteData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'where' => 'where order_sn = :order_sn:',
            'bind' => [
                'order_sn' => $orderSn,
            ]
        ]);
        // 退款商品
        $detailRet = $orderData->updateReturnGoodsStatus($orderSn, 0);
        // 退款订单
        $orderRet = $orderData->updateReturnOrderStatus($orderSn, $returnInfo['last_status'], OrderEnum::ORDER_REFUND);
        if ($reasonRet && $detailRet && $orderRet) {
            $this->dbWrite->commit();
            // 订单日志
//            $logData = [
//                'user_id' => $userId,
//                'order_sn' => $orderSn,
//                'log_content' => serialize($orderInfo),
//            ];
//            LogService::getInstance()->save(['prefix' => 'order','data' => $logData]);
            $orderData->addOrderLog($userId, $orderSn, $orderInfo);
            return $this->uniteReturnResult(HttpStatus::SUCCESS);
        } else {
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
        }
    }


    /**
     * 服务单请求撤销申请退款
     */
    public function cancelRefundApply($param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $service_sn = isset($param['service_sn']) ? (string)$param['service_sn'] : '';
        $caller = isset($param['caller']) ? (string)$param['caller'] : '';

        if (empty($orderSn) || empty($userId) || empty($service_sn) || !$this->verifyRequiredParam($param)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        $orderData = BaiyangOrderData::getInstance();
        //查询订单信息
        $orderInfo = $orderData->getTheOrder([
            'column' => '*',
            'where' => 'order_sn = :order_sn: and user_id=:user_id:',
            'bind' => [
                'order_sn' => $orderSn,
                'user_id' => $userId,
            ]
        ]);
        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }


        $serviceInfo = $orderData->getReturnService([
            'column' => '*',
            'where' => 'service_sn = :service_sn: and status not in (3,6) ',
            'bind' => [
                'service_sn' => $service_sn,
            ]
        ]);
        if (!$serviceInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }


        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $orderDetailList = $orderDetailData->getOrderDetailByService($serviceInfo['id']);

        if (!$orderDetailList) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $status = 6;
        // 开启事务
        $this->dbWrite->begin();

        //屏蔽 退款申请审核通过后 还可以撤销申请的 功能  demo：文和  1708041136 start
        /*if(
            empty($caller)
            and isset($serviceInfo['status']) and $serviceInfo['status'] >= 0
            and isset($serviceInfo['return_type']) and $serviceInfo['return_type'] == 1
            and isset($orderInfo['status']) and $orderInfo['status'] == 'shipping'
        ){
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::REFUND_AUDITED);
        }*/
        if (isset($serviceInfo['status']) && $serviceInfo['status'] != 0 && $serviceInfo['status'] != 4) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::REFUND_AUDITED);
        }
        //end

        $update_service = $orderData->updateReturnService([
            'column' => 'status =:status:',
            'where' => 'id = :id:' ,
            'bind' => [
                'status'=>$status,
                'id'=>$serviceInfo['id']
            ],
        ]);

        foreach ($orderDetailList as $detail) {
            $refund_goods_number = $detail['refund_goods_number'] - $detail['now_refund_goods_number'];
            $is_refund = $detail['goods_type'] == 1?1:0;//活动商品不参与退款
            $result = $orderData->newUpdateReturnGoodsStatus($detail['id'],$refund_goods_number,$is_refund);
            if(!$result){
                $this->dbWrite->rollback();
            }
        }

        $userData = \Shop\Home\Datas\BaiyangUserData::getInstance();
        $user_info = $userData->getUserInfo($userId);

        $log_content = $orderData->getServiceStatusText($status);
        $add_log = $orderData->addOrderServiceLog($service_sn,$log_content,$status,$user_info['username']);
        if($update_service && $add_log){
            $this->dbWrite->commit();
            return $this->uniteReturnResult(HttpStatus::SUCCESS);
        }else{
            $this->dbWrite->rollback();
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
        }

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
        $global = strstr($param['order_sn'], OrderEnum::KJ) ? 1 : 0;
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

        //处理存在服务单情况
        if($updateResult && $param['status']== OrderEnum::ORDER_SHIPPED){
            $serviceData = $orderData->getOrderCanCancelService($param['order_sn']);

            //存在则关闭服务单
            if($serviceData){
                $param['service_sn'] = $serviceData['service_sn'];
                $param['caller'] = 1; //为了区分是前台调好用还是其他函数调用
                $res = $this->cancelRefundApply($param);
                return $res;
            }
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
//        $logData = [
//            'user_id' => $param['user_id'],
//            'order_sn' => $param['order_sn'],
//            'log_content' => serialize($orderInfo),
//        ];
//        LogService::getInstance()->save(['prefix' => 'order','data' => $logData]);
        $orderData->addOrderLog($param['user_id'], $param['order_sn'], $orderInfo);
        // 易复诊和积分
        if($param['status'] == OrderEnum::ORDER_SHIPPED) {
            // 同步到易复诊，可能不存在
            $redis = $this->cache;
            $redis->selectDb(6);
            $redis->rPush(OrderEnum::ORDER_FINISHED, $param['order_sn']);
            // 更新用户积分
            $wapUrl = $this->config->wap_url[$this->config->environment].'/wap/integral/add_order_list_integral';
            $this->curl->sendPost($wapUrl, http_build_query(['order_sn' => $param['order_sn']]));
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS);
    }

    /**
     * @desc 查看物流信息
     * @param array $param
     *      -int user_id 用户id
     *      -string order_sn 订单编号
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getOrderLogistics(array $param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['channel_subid'] = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $param['udid'] = isset($param['udid']) ? (string)$param['udid'] : '';
        if(empty($userId) || empty($orderSn) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 区分跨境订单和普通订单
        $global = strstr($orderSn, OrderEnum::KJ) ? 1 : 0;
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getOneOrder([
            'column' => 'express_sn,express',
            'where' => 'order_sn = :order_sn: and user_id = :user_id: and is_delete = 0',
            'bind' => [
                'order_sn' => $orderSn,
                'user_id' => $userId,
            ]
        ], 'read', $global);
        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $shippingDetail = null; // 物流信息
        if($global){
            // 跨境的物流信息
            $logisticsInfo = $orderData->getLogisticsInfo([
                'column' => 'show_logistics',
                'where' => 'express_sn = :express_sn:',
                'bind' => [
                    'express_sn' => $orderInfo['express_sn'],
                ]
            ], $global);
            $shippingDetail = !empty($logisticsInfo) ? json_decode($logisticsInfo['show_logistics'],true) : null;
            if(!empty($shippingDetail) && ($param['platform'] == OrderEnum::PLATFORM_APP || $param['platform'] == OrderEnum::PLATFORM_WAP)){
                $sort1 = []; // 移动端跨境物流信息要倒叙 (最新时间在最上面)
                foreach ($shippingDetail as $key => $value) {
                    $sort1[] = $key;
                }
                array_multisort($sort1, SORT_DESC,$shippingDetail);
                $contextList = [];
                foreach ($shippingDetail as $key => $value){
                    $contextList[$key] = [
                        'time' => $value['time'],
                        'context' => $value['status'],
                        'ftime' => $value['time'],
                        'areaCode' => '',
                        'areaName' => '',
                        'status' => $value['recPlace'],
                    ];
                }
                unset($shippingDetail);
                $shippingDetail['logistics_id'] = $orderInfo['express_sn'];
                $shippingDetail['logistics_com'] = $orderInfo['express'];
                $shippingDetail['context_list'] = $contextList;
            }
        }else{
            // 普通订单的获取物流
            if(!empty($orderInfo['express_sn']) && strtolower($orderInfo['express']) != 'zps'){
                $shippingDetail = $this->func->getLogisticsData($orderSn);
                if(!empty($shippingDetail) && ($param['platform'] == OrderEnum::PLATFORM_PC)){
                    $sort1 = []; // pc端普通物流信息要顺序 (最新时间在最下面)
                    if(isset($shippingDetail['context_list']) && !empty($shippingDetail['context_list'])){
                        foreach ($shippingDetail['context_list'] as $key => $value) {
                            $sort1[] = $key;
                        }
                        array_multisort($sort1, SORT_DESC,$shippingDetail['context_list']);
                    }
                }
            }
        }
        if(empty($shippingDetail)){
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $shippingDetail);
    }

    /**
     * @desc 提醒发货
     * @param array $param
     *      -int user_id 用户id
     *      -string order_sn 订单编号
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function remindDeliveryOrder(array $param)
    {
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['channel_subid'] = isset($param['channel_subid']) ? (int)$param['channel_subid'] : 0;
        $param['udid'] = isset($param['udid']) ? (string)$param['udid'] : '';
        if(empty($userId) || empty($orderSn) || !$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        // 区分跨境订单和普通订单
        $global = strstr($orderSn, OrderEnum::KJ) ? 1 : 0;
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getOneOrder([
            'column' => 'order_sn',
            'where' => 'order_sn = :order_sn: and user_id = :user_id: and is_delete = 0 and status = :status:',
            'bind' => [
                'order_sn' => $orderSn,
                'user_id' => $userId,
                'status' => OrderEnum::ORDER_SHIPPING,
            ]
        ], 'read', $global);
        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $result = $orderData->updateOrderInfo([
            'column' => 'is_remind = 1',
            'where' => 'order_sn = :order_sn: and user_id = :user_id:',
            'bind' => [
                'order_sn' => $orderSn,
                'user_id' => $userId,
            ],
        ], $global);
        if(!$result){
            return $this->uniteReturnResult(HttpStatus::OPERATE_ERROR);
        }
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

    /**
     * @remark 移动端评价晒单
     * @param array $param
     *      -int userId 用户id
     *      -string orderId 订单编号
     *      -int isGlobal 是否为海外购
     *      -string platform  平台
     *      -int channel_subid  渠道号
     *      -string udid  手机唯一id(app端必填)
     * @return mixed
     * @author 杨永坚
     */
    public function getBaskComemnt($param = array())
    {
        $param['platform'] = isset($param['platform']) ? (string)$param['platform'] : '';
        $param['userId'] = isset($param['userId']) ? (int)$param['userId'] : '';
        $param['orderId'] = isset($param['orderId']) ? (string)$param['orderId'] : '';
        $param['isGlobal'] = isset($param['isGlobal']) ? (int)$param['isGlobal'] : '';
        //参数错误
        if(!$this->verifyRequiredParam($param) || empty($param['userId']) || empty($param['orderId']) || !in_array($param['isGlobal'], [0, 1])){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $baseData = BaseData::getInstance();
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        if($param['isGlobal'] == 1){
            $orderTable = '\Shop\Models\BaiyangKjOrder';
            $orderDetailTable = '\Shop\Models\BaiyangKjOrderDetail';
        }else{
            $orderTable = '\Shop\Models\BaiyangOrder';
            $orderDetailTable = '\Shop\Models\BaiyangOrderDetail';
        }
        $orderInfo = $baseData->getData(array(
            'table' => $orderTable,
            'column' => 'order_sn,status,express_status,express_time',
            'where' => 'where user_id = :user_id: and order_sn = :order_sn:',
            'bind' => array(
                'user_id' => $param['userId'],
                'order_sn' => $param['orderId']
            )
        ), true);
        if(empty($orderInfo)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::NOT_FOUND);
        }
        if($orderInfo['express_status'] != 1){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::NOT_SIGNIN_COMMENT);
        }
        $where = 'where order_sn = :order_sn: and goods_type in (0,3)';
        $orderGoods = $baseData->getData(array(
            'table' => $orderDetailTable,
            'column' => 'order_sn,goods_id,goods_name,goods_image,is_comment as comment_status,is_return',
            'where' => $where,
            'bind' => array(
                'order_sn' => $param['orderId'],
            )
        ));
        $time = time() - 90*86400;
        $goodsCommentData = BaiyangGoodsComment::getInstance();

        $canCommentGoodsList = [];
        foreach ($orderGoods as $key => $value){
            $dataInfo = $BaiyangSkuData->getSkuInfo($value['goods_id'], $param['platform']);
            //处方药、虚拟商品不可评论
            if($dataInfo['drug_type'] == 1 || $dataInfo['drug_type'] == 5){
                continue;
            }
            // 全退的商品不可评论 (已完成退款)
            if($value['is_return'] == 1){
                continue;
            }
            $value['notice'] = null;
            $value['price'] = $dataInfo['sku_price'];
            $value['market_price'] = $dataInfo['sku_market_price'];
            $canCommentGoodsList[] = $value;
        }

        foreach ($canCommentGoodsList as $k => $v){
            $goodsComment = $goodsCommentData->getGoodsComment([
                'order_sn' => $v['order_sn'],
                'goods_id' => $v['goods_id'],
            ],true);
            if(!empty($goodsComment)){
                $canCommentGoodsList[$k]['comment_status'] = OrderEnum::APPEND_EVALUATED;
                $goodsCommentImageNumber = count($goodsCommentData->getGoodsCommentImageNumber(['comment_id' => $goodsComment['id']])); // 已上传图片的商品数
                if($goodsCommentImageNumber > 0){
                    $canCommentGoodsList[$k]['comment_status'] = OrderEnum::EVALUATED;
                }
            }
            if($orderInfo['express_time'] < $time){
                if($canCommentGoodsList[$k]['comment_status'] == OrderEnum::EVALUATING){
                    unset($canCommentGoodsList[$k]);
                    continue;
                }else{
                    $canCommentGoodsList[$k]['comment_status'] = OrderEnum::EVALUATED;
                }
            }
        }
        $canCommentGoodsList = array_values($canCommentGoodsList);
        return $this->uniteReturnResult(\Shop\Models\HttpStatus::SUCCESS, $canCommentGoodsList);
    }

    /**
     * @desc 获取跨境订单物流的清关状态
     * @param int $isDeclare 订单的推送状态
     * @return string $clearanceStatus 结果信息
     * @author 吴俊华
     */
    private function getClearanceStatus($isDeclare)
    {
        switch ($isDeclare) {
            case 0  :   $clearanceStatus = '未推送';break;
            case 1  :   $clearanceStatus = '推送成功';break;
            case 2  :   $clearanceStatus = '推送失败';break;
            case 3  :   $clearanceStatus = '申报成功';break;
            case 4  :   $clearanceStatus = '申报失败';break;
            case 5  :   $clearanceStatus = '订单拣货成功';break;
            case 6  :   $clearanceStatus = '订单拣货失败';break;
            case 7  :   $clearanceStatus = '订单出库成功';break;
            case 8  :   $clearanceStatus = '订单出库失败';break;
            case 9  :   $clearanceStatus = '订单运送过程中';break;
            case 10 :   $clearanceStatus = '订单尚未开始运送';break;
            default:    $clearanceStatus = '';break;
        }
        return $clearanceStatus;
    }

    /**
     * 获取退款理由
     * @return mixed
     */
    public function getOrderRefundReason($param){
        // 格式化参数
        $userId = isset($param['user_id']) ? (int)$param['user_id'] : 0;
        $orderSn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';


        if (empty($orderSn) || !$userId  ||  !$this->verifyRequiredParam($param) ) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }

        // 验证订单是否能够退款
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getOneOrder([
            'column' =>'status',
            'where' => 'order_sn=:order_sn:',
            'bind' => [
                'order_sn'=>$orderSn
            ],
        ]);

        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $type = 1;
        if(in_array($orderInfo['status'],array(OrderEnum::ORDER_EVALUATING,OrderEnum::ORDER_FINISHED))){
            $type = 0;
        }
        $reason = $orderData->RefundReason($type);
        return $this->uniteReturnResult(HttpStatus::SUCCESS,$reason);
    }

    /**
     * 根据快递单号查快递公司
     * @param $postid 快递单号
     * @return string
     */
    public function noticeLogistics($param){
        $postid =  isset($param['postid']) ? (string)$param['postid'] : '';
        if(!$this->verifyRequiredParam($param) || empty($postid)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $url = "http://www.kuaidi100.com/autonumber/autoComNum?text=".$postid;
        $result = @file_get_contents($url);
        $result = json_decode($result,true);
        if($result['auto']){
            $text = ExpressText::getInstance()->expressCode();
            $list =array_slice( $result['auto'],0,5);
            foreach($list as &$row){
                if(isset($text[$row['comCode']])){
                    $row['comCode'] = $text[$row['comCode']];
                }
            }

            return $this->uniteReturnResult(HttpStatus::SUCCESS,array_column($list,'comCode'));

        }else{
            return  $this->uniteReturnResult(HttpStatus::ERROR);
        }
    }

    /**
     * 查询快递进度
     * @param $param
     * @return mixed
     */
    public function getExpress($param){
        $postid =  isset($param['postid']) ? (string)$param['postid'] : '';
        if(!$this->verifyRequiredParam($param) || empty($postid)){
                return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
            }
            $url = "http://www.kuaidi100.com/autonumber/autoComNum?text=".$postid;

            $result = @file_get_contents($url);
            $result = json_decode($result,true);


            $data = [];
            if($result['auto']){
            	$text = ExpressText::getInstance()->expressCode();
                $company_code = trim($result['auto'][0]['comCode']);
                $url = "http://www.kuaidi100.com/query?type={$company_code}&postid={$postid}&id=1&valicode=&temp=0.".time();

                $res = @file_get_contents($url);

                $res = json_decode($res,true);

                if($res['data']){
                $data['express_company'] = isset($text[$result['auto'][0]['comCode']])
                    ? $text[$result['auto'][0]['comCode']] : '';
                    $data['list'] = $res['data'];

                }
        }
        return  $this->uniteReturnResult(HttpStatus::SUCCESS,$data);
    }


    /**
     * 提交申请退款物流
     * @param array $param
     * @return mixed
     */
    public function addServiceExpress($param = array()){

        $service_sn = isset($param['service_sn']) ? (string)$param['service_sn'] : '';
        $express_no = isset($param['express_no']) ? (string)$param['express_no'] : '';
        $express_company = isset($param['express_company']) ? (string)$param['express_company'] : '';

        //参数错误
        if(!$this->verifyRequiredParam($param) ||  empty($service_sn)  ||  empty($express_no) ||  empty($express_company)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $status = 5;
        $yxyShopId = isset($this->config->yxy_shop_id[$this->config->environment]) ? (int)$this->config->yxy_shop_id[$this->config->environment] : 0;
        $orderData = BaiyangOrderData::getInstance();
        $update_service = $orderData->updateReturnService([
            'column' => 'express_no =:express_no:,express_company=:express_company:,status=:status:',
            //'where' => 'service_sn = :service_sn:' ,
            'where' => 'service_sn = :service_sn: AND shop_id <> :shop_id:' ,
            'bind' => [
                'express_no'=>$express_no,
                'express_company'=>$express_company,
                'service_sn'=>$service_sn,
                'status'=>$status,
                'shop_id'=> $yxyShopId, //育学园订单不能添加物流信息  秦亮 #7529
            ],
        ]);


        if($update_service){
            $log_content = $orderData->getServiceStatusText($status);
            $log_content[] = '快递公司：'.$express_company;
            $log_content[] = '快递单号：'.$express_no;
            $service_info = $orderData->getReturnService([
                'column' => 'user_id',
                'where' => 'service_sn=:service_sn: ',
                'bind' => [
                    'service_sn'=>$service_sn
                ],
            ]);
            $userData = \Shop\Home\Datas\BaiyangUserData::getInstance();
            $user_info = $userData->getUserInfo($service_info['user_id']);
            $orderData->addOrderServiceLog($service_sn,$log_content,$status,$user_info['username']);
            return  $this->uniteReturnResult(HttpStatus::SUCCESS);
        }else{
            return  $this->uniteReturnResult(HttpStatus::ERROR);
        }
    }

    /**
     * 可申请列表
     * @param $param
     * @return mixed
     */
    public function canServiceGoodsList($param){

        $order_sn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $userId= isset($param['user_id']) ? (string)$param['user_id'] : '';

        //参数错误
        if(!$this->verifyRequiredParam($param) ||  empty($order_sn) || empty($userId)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }

        $orderData = BaiyangOrderData::getInstance();

        //查询订单信息
        $orderInfo = $orderData->getTheOrder([
            'column' => 'express_time,status,total',
            'where' => "order_sn = :order_sn: and user_id=:user_id: and status in ('shipping','finished','shipped','evaluating')",
            'bind' => [
                'order_sn' => $order_sn,
                'user_id' => $userId,
            ]
        ]);

        if(empty($orderInfo)){
            $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $goodsList = $orderDetailData->getDetailGoodsSkuByOrderSn($order_sn);


        //交易已完成才能申请售后

            $time = time();
            foreach($goodsList as $k=>&$goods){
                //还未收货则不设置为过期
                if(in_array($orderInfo['status'],['finished','evaluating'])) {

                    if(!$goods['returned_goods_time']){

                        unset($goodsList[$k]);//不支持退换货的直接删掉
                        continue;
                    }
                    $returned_time = $goods['returned_goods_time'] > 0 ? $goods['returned_goods_time'] * 24 * 3600 + $orderInfo['express_time'] : 0;
                    $returned_time < $time ? $goods['is_expire'] = 1 : $goods['is_expire'] = 0;

                }else{
                    $goods['is_expire'] = 0;
                    $goods['total'] = $orderInfo['total'];

                }
                $goods['gift_list'] = $orderDetailData->getGiftOrderDetail([
                    'column' => 'goods_id,goods_name,goods_image,goods_number,price,unit_price,specifications',
                    'where' => 'bind_id = :bind_id:',
                    'bind' => [
                        'bind_id'=>$goods['id']
                    ], 
                ]);
            }


//        }
        return  $this->uniteReturnResult(HttpStatus::SUCCESS,array_values($goodsList));

    }



    public function getServiceList($param){
        $order_sn = isset($param['order_sn']) ? (string)$param['order_sn'] : '';
        $userId= isset($param['user_id']) ? (string)$param['user_id'] : '';
        $pageSize = isset($param['pageSize']) &&  $param['pageSize'] ? (string)$param['pageSize'] : 10;
        $pageStart = isset($param['pageStart'])&& $param['pageStart']? (string)$param['pageStart'] : 1;

        //参数错误
        if(!$this->verifyRequiredParam($param)  || empty($userId)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $orderData = BaiyangOrderData::getInstance();
        $serviceData = $orderData->getReturnServiceList([
            'order_sn'=>$order_sn,
            'user_id'=>$userId,
            'column'=>'*',
            'order'=>'order by id desc ',
            'pageSize'=>$pageSize,
            'pageStart'=>$pageStart,
        ]);

        if($serviceData){
            $orderDetailData = BaiyangOrderDetailData::getInstance();
            $return_type_text = ['取消','仅退款','退货退款'];
            $yxyShopId = isset($this->config->yxy_shop_id[$this->config->environment]) ? $this->config->yxy_shop_id[$this->config->environment] : -1;
            foreach($serviceData['list'] as &$row){
                $row['return_type_text'] = $row['return_type']>=0?$return_type_text[$row['return_type']]:'';
                $row['goodsList'] =  $orderDetailData->getOrderDetailByService($row['id']);
                $row['is_show'] = $yxyShopId == $row['shop_id'] ? 0 : 1; // 育学园店铺不显示物流按钮
            }
            return  $this->uniteReturnResult(HttpStatus::SUCCESS,$serviceData);
        }else{
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

    }

    /**
     * 获取服务详情
     * @param $param
     * @return mixed
     */
    public function getServiceDetail($param){
        $service_sn = isset($param['service_sn']) ? (string)$param['service_sn'] : '';
        $userId= isset($param['user_id']) ? (string)$param['user_id'] : '';

        //参数错误
        if(!$this->verifyRequiredParam($param)  || empty($service_sn) || empty($userId)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $orderData = BaiyangOrderData::getInstance();
        $serviceRow = $orderData->getReturnService([
            'column' => '*',
            'where' => 'service_sn= :service_sn:',
            'bind' => [
                'service_sn'=>$service_sn
            ],
        ]);

        if(!$serviceRow){
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $serviceRow['images'] = json_decode($serviceRow['images'],true);
        $return_type_text = ['取消','仅退款','退货退款'];

        $serviceRow['return_type_text'] = $serviceRow['return_type']>=0?$return_type_text[$serviceRow['return_type']]:'';

        $serviceRow['status_text'] = $orderData->serviceStatusText($serviceRow['status']);

        $serviceRow['add_time'] = date('Y-m-d H:i:s',$serviceRow['add_time']);
        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $serviceRow['goodsList'] = $orderDetailData->getOrderDetailByService($serviceRow['id']);
        $yxyShopId = isset($this->config->yxy_shop_id[$this->config->environment]) ? $this->config->yxy_shop_id[$this->config->environment] : -1;
        $serviceRow['is_show'] = $yxyShopId == $serviceRow['shop_id'] ? 0 : 1; // 育学园店铺不显示物流按钮

        return  $this->uniteReturnResult(HttpStatus::SUCCESS,$serviceRow);
    }


    /**
     * 服务单进度x
     * @param $param
     * @return mixed
     */
    public  function getServiceStatusList($param){

        $service_sn = isset($param['service_sn']) ? (string)$param['service_sn'] : '';
        $userId= isset($param['user_id']) ? (string)$param['user_id'] : '';

        //参数错误
        if(!$this->verifyRequiredParam($param)  || empty($service_sn) || empty($userId)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }

        $orderData = BaiyangOrderData::getInstance();
        $serviceRow = $orderData->getReturnService([
            'column' => 'order_sn,service_sn,add_time,status,shop_id',
            'where' => 'service_sn= :service_sn:',
            'bind' => [
                'service_sn'=>$service_sn
            ],
        ]);

        if(!$serviceRow){
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

        if(in_array($serviceRow['status'],[0,4])){
            $serviceRow['is_can_cancel'] = 1;
        }else{
            $serviceRow['is_can_cancel'] = 0;
        }
        
        $orderBy = $param['platform'] == 'pc'?'asc':'desc';
        $serviceLog = $orderData->getOrderServiceLog([
            'column' => 'log_content,operator_name,status',
            'where' => 'service_sn= :service_sn:',
            'order'=>'order by id '.$orderBy,
            'bind' => [
                'service_sn'=>$service_sn
            ],
        ]);
        if(!$serviceLog){
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        
//        $yxyShopId = isset($this->config->yxy_shop_id[$this->config->environment]) ? $this->config->yxy_shop_id[$this->config->environment] : -1;
//        $serviceRow['is_show'] = $yxyShopId == $serviceRow['shop_id'] ? 0 : 1; // 育学园店铺不显示物流按钮
        $serviceRow['is_show'] = 1;//显示物流按钮

        foreach($serviceLog as &$log)
        {
            $log['log_content'] = json_decode( $log['log_content'],true);
            if ($log['status'] == 4 && $serviceRow['is_show'] == 0) {
                unset($log['log_content']['2']);
                unset($log['log_content']['3']);
                unset($log['log_content']['4']);
            }
        }
        
        $serviceRow['add_time'] = date('Y-m-d H:i:s',$serviceRow['add_time']);
        if($param['platform'] == 'pc'){
            $serviceRow['list'] = $serviceLog;
        }else{
            $serviceRow['list'] = array_column($serviceLog,'log_content');
        }
        return  $this->uniteReturnResult(HttpStatus::SUCCESS,$serviceRow);
    }

    /**
     * 根据订单获取店铺信息
     * @param $param
     * @return \array[]
     */
    public function getShopDetail($param){
        $service_sn = isset($param['service_sn']) ? (string)$param['service_sn'] : '';
        $user_id= isset($param['user_id']) ? (string)$param['user_id'] : '';

        //参数错误
        if(!$this->verifyRequiredParam($param)  || empty($service_sn) || empty($user_id)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $orderData = BaiyangOrderData::getInstance();

        $serviceRow = $orderData->getReturnService([
            'column' => '*',
            'where' => 'service_sn= :service_sn:',
            'bind' => [
                'service_sn'=>$service_sn
            ],
        ]);
        if(!isset($serviceRow['order_sn']) && !$serviceRow['order_sn']){
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $order_sn = $serviceRow['order_sn'];


        //查询订单信息
        $orderInfo = $orderData->getTheOrder([
            'column' => 'shop_id',
            'where' => 'order_sn = :order_sn: and user_id=:user_id:',
            'bind' => [
                'order_sn' => $order_sn,
                'user_id' => $user_id,
            ]
        ]);
        if(!$orderInfo){
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }

        $orderInfo['shop_id'] = isset($orderInfo['shop_id']) && $orderInfo['shop_id']?$orderInfo['shop_id']:1;
        $skuData = BaiyangSkuData::getInstance();
        $row = $skuData->getShopNameByShopId([
                'column' =>'*',
                'where' => 'id=:id:',
                'bind' => [
                    'id'=>$orderInfo['shop_id']
                ],
            ]);

        return  $this->uniteReturnResult(HttpStatus::SUCCESS,$row);
    }

    /**
     * 获取在线支付方式
     * @param $param
     * @return \array[]
     * @author CSL
     * @date 2018-01-02
     */
    public function getOnlinePayment($param){
        //参数错误
        if(!$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $onlinePayment = BaiyangPaymentData::getInstance()->getOnlinePayment($param);
        if(!$onlinePayment){
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        return  $this->uniteReturnResult(HttpStatus::SUCCESS, $onlinePayment);
    }

    /**
     * 获取自提门店列表
     * @param $param
     * @return \array[]
     * @author CSL
     * @date 2018-01-03
     */
    public function getSinceShop($param)
    {
        //参数错误
        if(!$this->verifyRequiredParam($param)){
            return $this->uniteReturnResult(\Shop\Models\HttpStatus::PARAM_ERROR, ['param' => $param]);
        }
        $shopList = BaiyangUserSinceShopData::getInstance()->getSinceShopList();
        if (!$shopList) {
            return  $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        $region = BaiyangUserConsigneeData::getInstance()->getAllRegionList(true);
        foreach ($shopList as $key => $shop) {
            $shop['provinceName'] = isset($region[$shop['province']]) ? $region[$shop['province']] : $shop['province'];
            $shop['cityName'] = isset($region[$shop['city']]) ? $region[$shop['city']] : $shop['city'];
            $shop['countyName'] = isset($region[$shop['county']]) ? $region[$shop['county']] : $shop['county'];
            $shopList[$key] = $shop;
        }
        return $this->uniteReturnResult(HttpStatus::SUCCESS, $shopList);
    }
}