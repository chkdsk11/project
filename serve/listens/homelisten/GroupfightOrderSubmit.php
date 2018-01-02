<?php
/**
 * @author: 文和
 * @copyright: 2017/5/22 10:21
 * @link chenxudaren.com
 * @internal
 * @license
 */

namespace Shop\Home\Listens;


use Phalcon\Http\Client\Exception;
use Shop\Home\Datas\BaiyangAnnouncementData;
use Shop\Home\Datas\BaiyangConfigData;
use Shop\Home\Datas\BaiyangGroupFightBData;
use Shop\Home\Datas\BaiyangGroupFightGoodsStockChangeLogData;
use Shop\Home\Datas\BaiyangGroupFightOrderData;
use Shop\Home\Datas\BaiyangGroupFightOrderDetailData;
use Shop\Home\Datas\BaiyangOrderLogData;
use Shop\Home\Datas\BaiyangTouchMachineOrderData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaiyangUserInvoiceData;
use Shop\Models\HttpStatus;
use Shop\Models\OrderEnum;

use Phalcon\Events\{
    Manager as EventsManager, Event
};

class GroupfightOrderSubmit extends BaseGroupfight
{
    protected static $instance = null;

    public function __construct()
    {
        $this->requiredParam = [
            'user_id'            => [
                'require' => 1,
                'filter'  => 'number'
            ],
            'is_dummy'           => [
                'require' => 1,
                'filter'  => 'number'
            ],
            'is_open'            => [
                'require' => 1,
                'filter'  => 'number'
            ],
            'group_id'           => [
                'require' => 1,
                'filter'  => 'number'
            ],
            'goods_num'          => [
                'require' => 1,
                'filter'  => 'number',
            ],
            'channel_subid'      => [
                'require' => 1,
                'value'   => [85, 91]//89, 90, , 95
            ],
            'platform'           => [
                'require' => 1,
                'value'   => ['wap', 'wechat']//'pc', 'app',
            ],
            'is_online'          => [
                'require' => 0,
            ],
            'invoiceHeader'      => [
                'require' => 0,
                'filter'  => 'string'
            ],
            'invoiceContentType' => [
                'require' => 0,
                //'value' => [10, 16]  //10 药品 , 16 明细
            ],
            'taxpayerNumber' => [ //纳税人号 ， 开企业户头的发票的时候用
                'require' => 0,
            ],
            'invoiceType'        => [
                'require' => 1,
            ],
            'orderType'          => [
                'require' => 0,
            ],
            'goods_id'           => [
                'require' => 0,
            ],
            'use_credit'         => [
                'require' => 1,
                'filter'  => 'number'
            ],
            'leaveWord'          => [
                'require' => 0,
                'filter'  => 'string'
            ],
            'address_id'         => [
                'require' => 1,
                'filter'  => 'number'
            ],
            'pay_password'       => [
                'require' => 0,
            ],
            'sn'                 => [
                'require' => 0,
            ],
            'udid'               => [
                'require' => 0,
            ],
            'machine_type'       => [
                'require' => 1,
                'value'   => [1, 2, 3] //1 触屏机  , 2 微信 , 3 wap
            ],
            'machine_sn'         => [
                'require' => 0,
            ]
        ];
        if (empty($this->data)) {
            $this->data = [
                'param'   => [],
                'order'   => [],
                'user'    => [],
                'group'   => [],
                'product' => [],
                'address' => [],
                'invoice' => []
            ];
        }
    }

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if (empty(static::$instance)) {
            static::$instance = new static();
        }

        //实例化事件管理器
        $eventsManager = new EventsManager();

        //开启事件结果回收
        $eventsManager->collectResponses(true);

        /********************************侦听器*********************************/
        $eventsManager->attach('order', new BalanceListener());
        $eventsManager->attach('order', new StockListener());
        /*********************侦听器************************/

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    public function confirm(array $param)
    {
        $this->stepGet01($param);

        $this->step02();

        //计算订单所用的数据
        $this->stepCalculateData03();

        //生产订单 和 余额支付
        $this->stepMakeOrder04();

        //生成订单后的后续的一些处理
        $this->stepSubsequent05();

        //处理返回的数据
        return $this->step06();
    }

    //初始化 数据
    private function stepGet01(array $param)
    {

        //检测必填参数
        if (($this->data['param'] = $this->verifyRequiredParam($param)) === false) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }

        $this->data['param']['goods_num']            = 1;
        $this->data['param']['user_id']              = intval($param['user_id']);
        $this->data['param']['is_online']            = 1; //只支持在线支付
        $this->data['invoice']                       = [];
        $this->data['invoice']['invoiceType']        = $this->data['param']['invoiceType'];
        $this->data['invoice']['invoiceContentType'] = $this->data['param']['invoiceContentType'];
        $this->data['invoice']['invoiceHeader']      = $this->data['param']['invoiceHeader'];
        $this->data['invoice']['invoiceInfo']        = '';
        $this->data['invoice']['taxpayerNumber'] = isset($this->data['param']['taxpayerNumber']) ? $this->data['param']['taxpayerNumber'] : '';

//        if ($this->data['invoice']['invoiceType'] > 0) {
//            $this->data['invoice']['ifReceipt'] = 1;
//        } else {
//            $this->data['invoice']['ifReceipt'] = 0;
//        }

        //如果 开发票 并且 发票抬头 和 发票内容为空 则 抛出错误
        if (
            $this->data['invoice']['invoiceType'] > 0
            and empty($this->data['invoice']['invoiceHeader'])
            and empty($this->data['invoice']['invoiceContentType'])
        ) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }

        if ($this->data['param']['use_credit'] and empty($this->data['param']['pay_password'])) {
            throw new Exception('', HttpStatus::PAY_PASSWORD_IS_EMPTY);
        }

        if ($this->data['invoice']['invoiceType'] > 0 and in_array($this->data['param']['invoiceContentType'], [10, 16]) === false) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }
        if (
            $this->data['invoice']['invoiceType'] == 2
            and empty($this->data['invoice']['taxpayerNumber'])
        ) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }

    }

    //检测数据
    private function step02()
    {

        if ($this->data['param']['goods_num'] > 1) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }

        //判断是否虚拟用户
        if ($this->data['param']['is_dummy']) {
            throw new Exception('', HttpStatus::USER_DUMMY_ERROR);
        }

        if (empty($this->data['param']['user_id'])) {
            throw new Exception('', HttpStatus::PARAM_ERROR);
        }

        $this->data['user'] = $this->getUserInfo($this->data['param']['user_id'], ['id', 'balance', 'phone', 'nickname', 'pay_password']);

        if (empty($this->data['user'])) {
            throw new Exception('', HttpStatus::USER_NOT_EXIST);
        }

        if ($this->chkSn() === false) {
            throw new Exception('', HttpStatus::GROUP_PARAM_ERROR);
        }

        //获取收货地址信息
        $this->data['address'] = $this->getConsigneeInfo(['user_id' => $this->data['param']['user_id'], 'address_id' => $this->data['param']['address_id']]);

        //检测地址不能为空
        if (empty($this->data['address'])) {
            throw new Exception('', HttpStatus::NO_THIS_ADDRESS);
        }


        //获取活动 或 开团 数据
        $this->getGroupData($this->data['param']['group_id']);
        //检测活动
        $this->chkGroup();

        //获取商品数据
        $this->data['product'] = $this->getPorduct(['is_use_stock', 'sku_market_price', 'sku_price', 'sale', 'supplier_id']);

        //检测商品不能为空
        if (empty($this->data['product'])) {
            throw new Exception('', HttpStatus::GROUP_NOT_GOODS);
        }

        //检测库存
        $this->chkStock();


    }

    //计算订单所用的数据
    private function stepCalculateData03()
    {

        //获取订单号
        $this->data['order']['orderSn']        = $this->makeOrderSn();
        $this->data['order']['user_id']        = $this->data['user']['id'];
        $this->data['order']['channel_subid']  = $this->data['param']['channel_subid'];
        $this->data['order']['channelName']    = $this->getChannelName();
        $this->data['order']['leaveWord']      = $this->data['param']['leaveWord'];
        $this->data['order']['orderType']      = $this->data['param']['orderType'];
        $this->data['order']['isPay']          = 0; //是否已付款
        $this->data['order']['orderType']      = 5; //是否已付款
        $this->data['order']['carriage']       = 0; //运费, 包邮
        $this->data['order']['status']         = 'paying'; //默认的订单状态
        $this->data['order']['paymentCode']    = '';
        $this->data['order']['payType']        = 1; //在线支付
        $this->data['order']['payTime']        = '';
        $this->data['order']['payRemark']      = '';
        $this->data['order']['expendSn']       = '';
        $this->data['order']['paymentId']      = '';
        $this->data['order']['paymentName']    = '';
        $this->data['order']['balancePayable'] = 0; //初始化 应付余额
        $this->data['order']['balancePaid']    = 0; //初始化 实付余额
        $this->data['order']['realPrice']      = 0;
        $this->data['order']['addTime']        = time();
        $this->data['order']['balance']        = 0;  //订单使用的余额初始化
        $this->data['order']['invoiceInfo']    = $this->getInvoiceInfo();
        $this->data['order']['shop_id']    = $this->data['product']['supplier_id'];

        $this->data['order']['goodsTotalPrice'] = bcmul($this->data['group']['gfa_price'], $this->data['param']['goods_num'], 2);
        $this->data['order']['invoiceMoney']    = $this->data['invoice']['invoiceType'] ? $this->data['order']['goodsTotalPrice'] : 0;

        if ($this->data['order']['goodsTotalPrice'] <= 0) {
            throw new Exception('', HttpStatus::GROUP_GOODS_PRICE_ERROR);
        }

        $this->data['order']['orderAmount'] = $this->data['order']['goodsTotalPrice'] + $this->data['order']['carriage'];

        $this->data['order']['amountPayable'] = $this->data['order']['orderAmount']; //应付总金额
        $this->data['order']['left_amount']   = $this->data['order']['amountPayable']; // 这里等会看看怎么处理

        if ($this->data['param']['use_credit'] and $this->data['user']['balance'] > 0) {
            if ($this->data['user']['balance'] >= $this->data['order']['goodsTotalPrice']) {
                $this->data['order']['balancePayable'] = $this->data['order']['goodsTotalPrice'];
            } else {
                $this->data['order']['balancePayable'] = $this->data['user']['balance'];
            }
        }

        $this->data['goods'] = $this->getGoodsData();

        $gfEndTime                   = $this->gfEndTime($this->data['order']['addTime']);
        $this->data['groupFightBuy'] = $this->getGroupFightBuyData($gfEndTime);

        if ($this->data['param']['is_open']) {
            $this->data['groupFight']               = $this->getGroupFightData($gfEndTime);
            $this->data['groupFightBuy']['is_head'] = 1;
        }

        $this->data['invoice']['userId'] = $this->data['order']['user_id'];

        //unset($this->data['product']);
    }

    //生产订单 和 余额支付
    private function stepMakeOrder04()
    {

        if ($this->data['order']['balancePayable'] > 0) {
            $this->payBalance();
        }

        // 入库
        $this->dbWrite->begin();
        $success = true;

        if (!BaiyangUserInvoiceData::getInstance()->insertUserInvoice($this->data['invoice'])) $success = false;//插入发票信息

        if (!BaiyangGroupFightOrderDetailData::getInstance()->insertOrderDetail($this->data['goods'])) $success = false;//插入订单详情

        if (!BaiyangGroupFightGoodsStockChangeLogData::getInstance()->insertGoodsStockChange([
            'orderSn'     => $this->data['order']['orderSn'],
            'goodsId'     => $this->data['group']['goods_id'],
            'goodsNumber' => $this->data['param']['goods_num'],
            'stockType'   => $this->data['goods']['stockType']
        ])
        ) {
            $success = false;//库存变化
        }

        if (!BaiyangGroupFightOrderData::getInstance()->insertOrderPayDetail($this->getPayDetailData())) $success = false;//插入支付信息

        if (!BaiyangGroupFightOrderData::getInstance()->insertOrder($this->data['order'], $this->data['address'], $this->data['invoice'])) $success = false;//插入订单

        if (isset($this->data['param']['machine_sn'])) {
            if (!BaiyangTouchMachineOrderData::getInstance()->insertMachineSn(['orderSn'   => $this->data['order']['orderSn'],
                                                                               'isGlobal'  => 0,
                                                                               'machineSn' => $this->data['param']['machine_sn']
            ])
            ) $success = false;//插入触屏机设备号
        }


        if ($this->data['param']['is_open']) {
            if (($gfId = BaiyangGroupFightBData::getInstance()->insertGroupFight($this->data['groupFight'])) == false) $success = false;
            $this->data['groupFightBuy']['gf_id'] = $gfId;
        }
        if (!BaiyangGroupFightBData::getInstance()->insertGroupFightBuy($this->data['groupFightBuy'])) $success = false;
        if (!BaiyangOrderLogData::getInstance()->addOrderLog($this->getOrderLogData())) $success = false;
        // 生成订单失败退款  这里还得看看拼团 表的变化
        if (!$success) {
            if ($this->data['order']['balancePaid'] > 0) {
                $ret = $this->_eventsManager->fire('order:external_refund_order', $this, [
                    'order_sn'     => $this->data['order']['orderSn'],
                    'refund_money' => $this->data['order']['balancePaid'],
                ]);
                $this->log->error("ERROR:提交订单失败退还余额" . print_r($ret, 1));
            }
            $this->dbWrite->rollback();
            throw new Exception('', HttpStatus::OPERATE_ERROR);
        }
        $this->dbWrite->commit();


    }

    private function stepSubsequent05()
    {
        // 余额支付 需要同步库存
        if ($this->data['order']['paymentId'] == 7) {
            //库存同步在wap端进行
            //$this->_eventsManager->fire('order:syncStockAndSaleNumber', $this, ['order_sn' => $this->data['order']['orderSn']]);
        }
    }

    private function step06()
    {
        return [
            'order_id'      => $this->data['order']['orderSn'],
            'is_open'       => $this->data['param']['is_open'],
            'group_id'      => $this->data['param']['group_id'],
            'gfa_id'        => $this->data['group']['gfa_id'],
            'gfa_endtime'   => $this->data['group']['gfa_endtime'],
            'gfa_starttime' => $this->data['group']['gfa_starttime'],
            'gfa_type'      => $this->data['group']['gfa_type'],
            'user_id'       => $this->data['order']['user_id'],
            'real_price'    => $this->data['order']['realPrice'],
            'is_pay'        => $this->data['order']['isPay'],
            'pay_time'      => $this->data['order']['payTime'],
            'pay_remark'    => $this->data['order']['payRemark'],
            'payment_id'    => $this->data['order']['paymentId'],
            'payment_name'  => $this->data['order']['paymentName'],
            'payment_code'  => $this->data['order']['paymentCode'],
            'left_amount'   => $this->data['order']['left_amount'],
            'is_online_pay'     => $this->data['param']['is_online'],
            'balance_price' => $this->data['order']['balance'],
            'is_global'     => 0
        ];
    }


    // 支付余额
    private function payBalance()
    {
        if ($this->data['order']['amountPayable'] > 0) {

            if ($this->func->getConfigValue('min_amount_for_password') < $this->data['order']['balancePayable']) {
                if (empty($this->data['param']['pay_password'])) {
                    throw  new Exception('', HttpStatus::PAY_PASSWORD_IS_EMPTY);
                }
            }
            $balanceResult = $this->_eventsManager->fire('order:add_user_expend', $this, [
                'phone'        => $this->data['user']['phone'],
                'amount'       => $this->data['order']['balancePayable'],
                'pay_password' => $this->data['param']['pay_password'],
                'order_sn'     => $this->data['order']['orderSn'],
            ]);
            if ($balanceResult['status'] != HttpStatus::SUCCESS) {
                $message = @json_encode($balanceResult, JSON_UNESCAPED_UNICODE);
                throw new Exception($message, HttpStatus::PAY_FAIL_YUE);
            }


            $this->data['order']['balance']     = $balanceResult['data']['expend_amount'];
            $this->data['order']['balancePaid'] = $balanceResult['data']['expend_amount'];
            $this->data['order']['expendSn']    = $balanceResult['data']['expend_sn'];
            $this->data['order']['payRemark']   = "用户使用余额支付{$this->data['order']['balancePaid']}元";
            $this->data['order']['realPrice']   = $this->data['order']['balancePaid'];
            $this->data['order']['left_amount'] = bccomp($this->data['order']['amountPayable'], $this->data['order']['balancePaid'], 2);


            //如果是余额全额付款 , 填写 支付 方式为余额支付
            if (bcsub($this->data['order']['amountPayable'], $this->data['order']['balancePaid'], 2) == 0) {
                $this->data['order']['isPay']         = 1;
                $this->data['order']['paymentId']     = 7;
                $this->data['order']['paymentName']   = '余额支付';
                $this->data['order']['payTime']       = time();
                $this->data['invoice']['invoiceType'] = 0; //余额全部支付 不开发票
                $this->data['order']['invoiceMoney']  = 0;
                $this->data['order']['paymentCode']   = 'balance';

                if ($this->data['param']['is_open']) { //开团的话支付完成改成  已开团
                    $this->data['groupFight']['gf_state'] = 1;
                }
                $this->data['groupFightBuy']['gfu_state'] = 1;


                /*
                 * 如果已经余额支付则  把订单状态改为待成团,
                 * 如果支付完了该单  已经成团 , 这里也是改成   await , 等返回客户端后 , 再查看是否已成团 , 再改订单状态为 shipping(已成团) ,
                 */
                $this->data['order']['status'] = 'await';


            }

            if ($this->data['invoice']['invoiceType'] > 0) {
                $this->data['order']['invoiceMoney'] = $this->data['order']['left_amount'];
                $this->data['order']['invoiceMoney'] < 0 and $this->data['order']['invoiceMoney'] = 0;
            }
        }
    }

    private function getOrderLogData()
    {
        $log_content = [
            'group_id'     => $this->data['param']['group_id'],
            'is_open'      => $this->data['param']['is_open'],
            'status'       => $this->data['order']['status'],
            'goods_price'  => $this->data['group']['gfa_price'],
            'real_pay'     => $this->data['order']['realPrice'],
            'payment_id'   => $this->data['order']['paymentId'],
            'invoice_type' => $this->data['invoice']['invoiceType'],
        ];
        if ($log_content['invoice_type'] > 0) {
            $log_content['invoice_header'] = $this->data['invoice']['invoiceHeader'];
            $log_content['invoice_type']   = $this->data['invoice']['invoiceContentType'];
            $log_content['invoice_money']  = $this->data['order']['invoiceMoney'];
            $log_content['taxpayerNumber']  = isset($this->data['invoice']['taxpayerNumber']) ? $this->data['invoice']['taxpayerNumber'] : '';

        }
        $log_content = serialize($log_content);

        return [
            'order_sn'    => $this->data['order']['orderSn'],
            'log_time'    => $this->data['order']['addTime'],
            'log_content' => $log_content,
            'user_id'     => $this->data['order']['user_id']
        ];
    }

    private function getGroupFightData($gfEndTime)
    {
        return [

            'add_time'      => $this->data['order']['addTime'],
            'gf_start_time' => $this->data['order']['addTime'],
            'user_id'       => $this->data['order']['user_id'],

            'gfa_id'         => $this->data['group']['gfa_id'],
            'gfa_name'       => $this->data['group']['gfa_name'],
            'gfa_user_count' => $this->data['group']['gfa_user_count'],
            'gfa_cycle'      => $this->data['group']['gfa_cycle'],
            'goods_id'       => $this->data['group']['goods_id'],
            'goods_name'     => $this->data['group']['goods_name'],
            'goods_image'    => $this->data['group']['goods_image'],
            'gfa_price'      => $this->data['group']['gfa_price'],
            'nickname'       => $this->data['user']['nickname'],
            'phone'          => $this->data['user']['phone'],
            'gf_end_time'    => $gfEndTime,
            'gf_join_num'    => 1,
            'gf_state'       => 0, // 0 未开团
        ];
    }

    private function getGroupFightBuyData($gfEndTime)
    {
        return [
            'gfa_id'        => $this->data['group']['gfa_id'],
            'add_time'      => $this->data['order']['addTime'],
            'user_id'       => $this->data['order']['user_id'],
            'nickname'      => $this->data['user']['nickname'],
            'order_sn'      => $this->data['order']['orderSn'],
            'edit_time'     => $this->data['order']['addTime'],
            'phone'         => $this->data['user']['phone'],
            'gf_start_time' => $this->data['order']['addTime'],
            'gf_end_time'   => $gfEndTime,
            'gf_id'         => $this->data['param']['group_id'],
            'is_head'       => 0,
            'gfu_state'     => 0,
        ];
    }

    private function getGoodsData()
    {
        return [
            'orderSn'          => $this->data['order']['orderSn'],
            'goodsId'          => $this->data['group']['goods_id'],
            'goodsName'        => $this->data['group']['goods_name'],
            'goodsImage'       => $this->data['group']['goods_image'],
            'goodsTotalAmount' => $this->data['order']['goodsTotalPrice'],
            'unitPrice'        => $this->data['group']['gfa_price'],
            'stockType'        => $this->data['product']['is_use_stock'],
            'marketPrice'      => $this->data['product']['sku_market_price'],
            'goodsNumber'      => $this->data['param']['goods_num']
        ];
    }

    private function getPayDetailData()
    {
        return [
            'orderSn'     => $this->data['order']['orderSn'],
            'balancePaid' => $this->data['order']['balancePaid'],
            'expendSn'    => $this->data['order']['expendSn'],
            'payRemark'   => $this->data['order']['payRemark']
        ];
    }

    private function getInvoiceInfo()
    {
        if ($this->data['invoice']['invoiceType'] > 0) {
            return json_encode([
                'title_type'   => ($this->data['invoice']['invoiceType'] == 1) ? '个人' : '单位',
                'title_name'   => $this->data['invoice']['invoiceContentType'],
                'content_type' => OrderEnum::$receiptContent[$this->data['invoice']['invoiceContentType']],
                'taxpayerNumber' => isset($this->data['invoice']['taxpayerNumber']) ? $this->data['invoice']['taxpayerNumber'] : '',
                'type_id'      => 3,//电子发票
            ], JSON_UNESCAPED_UNICODE);
        }
        return '';
    }

    private function getChannelName()
    {
        switch ($this->data['param']['channel_subid']) {
            case '95':
                return 'pc';
            case '91':
                return 'wap';
            case '90':
                return 'android';
            case '89':
                return 'ios';
            case '85':
                return 'wechat';
            default :
                return 'pc';
        }
    }
}