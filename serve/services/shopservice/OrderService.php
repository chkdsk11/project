<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/5
 * Time: 17:16
 */

namespace Shop\Services;

use Shop\Datas\BaiyangOrderData;
use Shop\Datas\BaiyangOrderLogData;
use Shop\Datas\BaiyangOrderDetailData;
use Shop\Datas\BaiyAdminData;
use Shop\Datas\BaiyangOrderOperationLogData;
use Shop\Datas\BaiyangProductRuleData;
use Shop\Datas\BaiyangRegionData;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyangOrderGoodsReturnReasonData;
use Shop\Datas\BaiyangParentOrderData;
use Shop\Datas\BaiyangUserData;

use Shop\Home\Listens\{
    ExpressListener
};

use Phalcon\Events\{
    Manager as EventsManager, Event
};

class OrderService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
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
        $eventsManager->attach('express',new ExpressListener());
        /*********************侦听器************************/

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }
    //订单类型
    public $orderType = [
        '普通订单' => 0,
        '处方订单' => 2,
        '拼团订单' => 5,
    ];
    //下单终端
    public $orderTerminal = [
        85 => '微商城',
        89 => 'APP(IOS)',
        90 => 'APP(Android)',
        91 => 'WAP',
        95 => 'PC',
    ];
    //付款方式
    public $orderPayment = [
        1 => '支付宝支付',
        2 => '微信支付',
        3 => '货到付款',
        4 => '红包支付',
        5 => '苹果支付',
        6 => '银联支付',
        7 => '余额支付',
    ];
    //配送方式
    public $orderDelivery = [
        '普通快递' => 0,
        '顾客自提' => 1,
        '两小时达' => 2,
        '当日达' => 3,
    ];
    //订单来源
    //public $orderSource = [1 => '诚仁堂', 2 => '易复诊', 3 => '育学园'];
    public $orderSource = [1 => '诚仁堂'];
    //订单状态
    public $orderStat = [
        'paying' => '待付款',
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '交易完成',
        'refund' => '退款/售后',
        'canceled' => '交易关闭',
        'finished' => '交易完成',
        'draw' => '待抽奖',
        'await' => '待成团',
    ];
    
    public $excel_header = [
        'order_sn'=>['text'=>'订单编号','field'=>'o.order_sn','join'=>''],
        'total_sn'=>['text'=>'订单编号','field'=>'o.total_sn','join'=>''],
        'order_source'=>['text'=>'订单来源','field'=>"if(ISNULL(p.prescription_id),if(o.more_platform_sign = 'yukon','育学院','" . $this->config['company_name'] . "商城'),'易复诊') as order_source",'join'=>'LEFT JOIN baiyang_prescription p ON o.total_sn = p.order_id','table'=>'baiyang_prescription'],
        'shop_id'=>['text'=>'店铺','field'=>"sku_supplier.name as shop_id",'join'=>'LEFT JOIN baiyang_sku_supplier as sku_supplier on sku_supplier.id=o.shop_id','table'=>'baiyang_sku_supplier'],

        'searchType'=>['text'=>'订单状态','field'=>"CASE o.`status`
                        WHEN 'paying' THEN
                            '普待付款通订单'
                        WHEN 'shipping' THEN
                            '待发货'
                        WHEN 'shipped' THEN
                            '已发货'
                        WHEN 'evaluating' THEN
                            '交易完成'
                        WHEN 'refund' THEN
                            '退款/售后'
                        WHEN 'canceled' THEN
                            '交易关闭'
                        WHEN 'finished' THEN
                            '交易完成'
                        END AS `searchType`",
            'join'=>''],

        'channel_subid'=>['text'=>'下单终端','field'=>"o.channel_subid ",'join'=>''],
        'order_type'=>['text'=>'订单类型','field'=>"case order_type when 0 THEN '普通订单' WHEN 5 THEN '拼团订单' END  as order_type",'join'=>''],
        'callback_phone'=>['text'=>'回拨电话','field'=>"o.callback_phone",'join'=>''],
        'ordonnance_photo_picture'=>['text'=>'处方药照片','field'=>"o.ordonnance_photo as ordonnance_photo_picture",'join'=>''],
        'admin_account'=>['text'=>'药师姓名','field'=>"o.admin_account",'join'=>''],
        'doctor_sign_picture'=>['text'=>'药师签名','field'=>"p.doctor_sign as doctor_sign_picture",'join'=>'LEFT JOIN baiyang_prescription p ON o.total_sn = p.order_id','table'=>'baiyang_prescription'],
        'group_fight_head_nickname'=>['text'=>'拼团开团人','field'=>"group_fight_buy.phone as group_fight_head_nickname",'join'=>'left join baiyang_group_fight_buy as group_fight_buy on group_fight_buy.order_sn=o.order_sn and group_fight_buy.is_head=1','table'=>'baiyang_group_fight_buy'],

        'consignee'=>['text'=>'收货人','field'=>"o.consignee",'join'=>''],
        'telephone'=>['text'=>'收货电话','field'=>"o.telephone",'join'=>''],
        'address'=>['text'=>'详细地址','field'=>"o.address",'join'=>''],
        'zipcode'=>['text'=>'邮政编码','field'=>"o.zipcode",'join'=>''],

        'province'=>['text'=>'省','field'=>"o.province",'join'=>''],
        'city'=>['text'=>'市','field'=>"o.city",'join'=>''],
        'county'=>['text'=>'区','field'=>"o.county",'join'=>''],
        'address_tag'=>['text'=>'地址标签','field'=>"CASE user_consignee.tag_id
                        WHEN 1 THEN
                            '我'
                        WHEN 2 THEN
                            '朋友'
                        WHEN 3 THEN
                            '亲人'
                        WHEN 4 THEN
                            '公司'
                        WHEN 5 THEN
                            '其它'
                        END AS `address_tag`",
            'join'=>'left join baiyang_user_consignee as user_consignee on user_consignee.id=o.addr_id','table'=>'baiyang_user_consignee'],

        'express_type'=>['text'=>'配送方式','field'=>"CASE o.express_type
                           WHEN 0 THEN
                                '普通快递'
                           WHEN 1 THEN
                            '顾客自提'
                           WHEN 2 THEN
                                '两小时达'
                           WHEN 3 THEN
                                '当日达'
                          END AS `express_type`",
            'join'=>''],


        'express'=>['text'=>'快递公司','field'=>"o.express",'join'=>''],
        'express_sn'=>['text'=>'快递单号','field'=>"o.express_sn",'join'=>''],
        'carriage'=>['text'=>'快递运费','field'=>"o.carriage",'join'=>''],
        'delivery_time'=>['text'=>'发货时间','field'=>"if(o.delivery_time>0,FROM_UNIXTIME(o.delivery_time) ,'') as delivery_time",'join'=>''],
        'received_time'=>['text'=>'收货时间','field'=>"if(o.received_time>0,FROM_UNIXTIME(o.received_time),'') as received_time",'join'=>''],
        'o2o_remark'=>['text'=>'o2o配送备注','field'=>"o.o2o_remark",'join'=>''],
        'total'=>['text'=>'订单金额','field'=>"o.total",'join'=>''],
        'buyer_message'=>['text'=>'客户留言','field'=>"o.buyer_message",'join'=>''],
        'real_pay'=>['text'=>'实付金额','field'=>"o.real_pay",'join'=>''],
        'add_time'=>['text'=>'下单时间','field'=>"if(o.add_time>0,FROM_UNIXTIME(o.add_time),'') as add_time",'join'=>''],
        'pay_time'=>['text'=>'付款时间','field'=>"if(o.pay_time>0,FROM_UNIXTIME(o.pay_time),'') as pay_time",'join'=>''],
        'is_pay'=>['text'=>'是否已支付','field'=>"if(o.is_pay=1,'是','否') as is_pay",'join'=>''],
        'pay_type'=>['text'=>'支付方式','field'=>"if(o.pay_type=1,'在线支付','货到付款') as pay_type",'join'=>''],
        'payment_name'=>['text'=>'在线支付方式','field'=>"o.payment_name",'join'=>''],
        'youhui_price'=>['text'=>'活动优惠','field'=>"o.youhui_price",'join'=>''],
        'user_coupon_price'=>['text'=>'优惠劵','field'=>"o.user_coupon_price",'join'=>''],
        'balance_price'=>['text'=>'余额','field'=>"o.balance_price",'join'=>''],
        'pay_total'=>['text'=>'应付金额','field'=>"o.pay_total",'join'=>''],

        'is_have_invoice'=>['text'=>'是否开具发票','field'=>"if(o.invoice_type=0,'否','是') as is_have_invoice", 'join'=>''],
        'invoice_type'=>['text'=>'发票类型','field'=>"CASE o.invoice_type
                           WHEN 0 THEN
                                '不需要'
                           WHEN 1 THEN
                                '个人'
                           WHEN 2 THEN
                                '单位'
                           WHEN 3 THEN
                                '电子发票'
                          END AS `invoice_type`",
            'join'=>''],
        'invoice_info'=>['text'=>'发票内容','field'=>"o.invoice_info", 'join'=>''],
        'invoice_rise'=>['text'=>'发票抬头','field'=>"o.invoice_info as invoice_rise", 'join'=>''],
        'invoice_money'=>['text'=>'发票金额','field'=>"o.invoice_money", 'join'=>''],
        'e_invoice_url'=>['text'=>'电子发票地址','field'=>"o.e_invoice_url", 'join'=>''],


        'goods_type'=>['text'=>'商品类型','field'=>"case od.goods_type
                                               WHEN 0 THEN
                                                    '普通商品'
                                               WHEN 1 THEN
                                                    '活动赠品'
                                               WHEN 2 THEN
                                                    '附属赠品'
                                               WHEN 3 THEN
                                                    '换购品'
                                              END AS `goods_type`",
                                            'join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn',
                                            'table'=>'baiyang_order_detail'],

//        'product_code'=>['text'=>'商品编号','field'=>'g.product_code','join'=>'left join baiyang_goods as g on od.goods_id=g.id','table'=>'baiyang_goods'],
        'goods_name'=>['text'=>'商品名称','field'=>'od.goods_name','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],
        'goods_id'=>['text'=>'商品编号','field'=>'od.goods_id','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],
        'specifications'=>['text'=>'规格','field'=>'od.specifications','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],
        'unit_price'=>['text'=>'商品单价','field'=>'od.unit_price','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],
        'goods_number'=>['text'=>'数量','field'=>'od.goods_number','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],
        'promotion_total'=>['text'=>'小计','field'=>'od.promotion_total','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],
        'promotion_price'=>['text'=>'实付价格','field'=>'od.promotion_price','join'=>'left join baiyang_order_detail as od on o.order_sn=od.order_sn','table'=>'baiyang_order_detail'],

    ];

    public $excel_group = [
        'group_1'=>['text'=>'来源','list'=>['order_source']],
        'group_2'=>['text'=>'店铺','list'=>['shop_id']],
        'group_3'=>['text'=>'订单状态','list'=>['searchType']],
        'group_4'=>['text'=>'下单终端','list'=>['channel_subid']],
        'group_5'=>['text'=>'订单类型','list'=>['order_type']],
        'group_6'=>['text'=>'处方药','list'=>['callback_phone','ordonnance_photo_picture','admin_account','doctor_sign_picture']],
        'group_7'=>['text'=>'拼团','list'=>['group_fight_head_nickname']],
        'group_8'=>['text'=>'收货人','list'=>['consignee','telephone','address','zipcode','province','city','county','address_tag']],
        'group_9'=>['text'=>'配送','list'=>['express_type','express','express_sn','carriage']],
        'group_10'=>['text'=>'发货/收货时间','list'=>['delivery_time','received_time']],
        'group_11'=>['text'=>'o2o配送备注','list'=>['o2o_remark']],
        'group_12'=>['text'=>'订单基本','list'=>['total','buyer_message','real_pay','add_time','pay_time','is_pay','pay_type','user_coupon_price', 'balance_price','pay_total']],
        'group_13'=>['text'=>'支付','list'=>['payment_name']],
        'group_14'=>['text'=>'订单商品','list'=>['goods_type','goods_id','goods_name','specifications','unit_price','goods_number','promotion_total','promotion_price']],
        'group_15'=>['text'=>'发票','list'=>['is_have_invoice','invoice_type','invoice_info','invoice_rise','invoice_money','e_invoice_url']],

    ];


    /**
     * 更新订单发货信息
     * @param $orderSn array 订单号
     * @param $express array 物流公司
     * @param $expressSn array 物流单号
     * @return array|bool
     * @author Chensonglu
     */
    public function batchUpdateExpressInfo($orderSn, $express, $expressSn)
    {
        if (!$orderSn) {
            return $this->arrayData('没有正常发货订单','',$orderSn,'error');
        }
        $orderData = BaiyangOrderData::getInstance();
        foreach ($orderSn as $key => $val) {
            if ($express[$key] && $expressSn[$key]) {
                $orderInfo = $orderData->getData([
                    'column' => '*',
                    'table' => 'Shop\Models\BaiyangOrder',
                    'where' => 'WHERE order_sn = :orderSn:',
                    'bind' => [
                        'orderSn' => $val
                    ]
                ], true);
                $this->oneUpdateExpressInfo($orderInfo, $express[$key], $expressSn[$key]);
            }
        }
        return true;
    }

    /**
     * 检验导入文件数据
     * @param $fileName string 文件名
     * @param $fileType string 文件类型
     * @return array
     * @author Chensonglu
     */
    public function checkShipmentsData($fileName, $fileType)
    {
        ini_set("memory_limit","-1");
        $data = $this->excel->importExcel($fileName,$fileType);
        if (!$data) {
            return $this->arrayData('导入的文件没有数据','',$data,'error');
        }
        $data = array_values($data);
        if (!isset($data[0][0]) || !isset($data[0][4]) || !isset($data[0][5])) {
            return $this->arrayData('导入的文件模板错误','',$data,'error');
        }
        $orderSnArr = array_filter(array_column($data, '0'));
        $expressArr = array_filter(array_column($data, '4', '0'));
        $expressSnArr = array_filter(array_column($data, '5', '0'));
        $allOrderInfo = BaiyangOrderData::getInstance()->getOrderInfo($orderSnArr);
        $wrongData = $theData = [];
        if ($allOrderInfo) {
            $orderSnArr = array_diff($orderSnArr, array_column($allOrderInfo, 'order_sn'));
            $regionName = BaiyangRegionData::getInstance()->getRegionAll();
            $goodsRule = BaiyangProductRuleData::getInstance()->getAllGoodsRule();
            $orderDetailData = BaiyangOrderDetailData::getInstance();
            foreach ($allOrderInfo as $order) {
                $order['isNonentity'] = 0;
                //地区匹配转换
                $address = is_numeric($order['province']) && $order['province'] > 0
                    ? $regionName[$order['province']] . ' ' : $order['province']. ' ';
                $address .= is_numeric($order['city']) && $order['city'] > 0
                    ? $regionName[$order['city']] . ' ' : $order['city']  . ' ';
                $address .= is_numeric($order['county']) && $order['county'] > 0
                    ? $regionName[$order['county']] . ' ' : $order['county'] . ' ';
                $order['addressInfo'] = $address . $order['address'];
                $order['productList'] = [];
                //获取商品信息
                $goodsInfo = $orderDetailData->getOrderGoods(['orderSn' => $order['order_sn']]);
                if ($goodsInfo) {
                    foreach ($goodsInfo as $key => $goods) {
                        //商品品规匹配
                        $goods['name_id'] = isset($goodsRule[$goods['name_id']])
                            ? $goodsRule[$goods['name_id']] : "";
                        $goods['name_id2'] = isset($goodsRule[$goods['name_id2']])
                            ? $goodsRule[$goods['name_id2']] : "";
                        $goods['name_id3'] = isset($goodsRule[$goods['name_id3']])
                            ? $goodsRule[$goods['name_id3']] : "";
                        $goodsInfo[$key] = $goods;
                    }
                    $order['productList'] = $goodsInfo;
                }
                if (!isset($expressArr[$order['order_sn']]) || !$expressSnArr[$order['order_sn']]) {
                    $order['msg'] = '物流信息缺失，此单不发货处理';
                    $wrongData[] = $order;
                    continue;
                }
                $order['express'] = $expressArr[$order['order_sn']];
                $order['express_sn'] = $expressSnArr[$order['order_sn']];
                if ($order['audit_state'] != 1) {
                    $order['msg'] = $order['audit_state'] == 2
                        ? '审核不通过，此单不发货处理' : '未审核，此单不发货处理';
                    $wrongData[] = $order;
                    continue;
                }
                if ($order['status'] != 'shipping') {
                    $order['msg'] = '不是待发货状态，此单不发货处理';
                    $wrongData[] = $order;
                    continue;
                }
                $theData[] = $order;
            }
        }
        if ($orderSnArr) {
            foreach ($orderSnArr as $item) {
                $wrongData[] = [
                    'order_sn' => $item,
                    'isNonentity' => 1,
                    'msg' => '该订单不存在'
                ];
            }
        }
        return [
            'valid' => $theData,
            'invalid' => $wrongData,
            'validCount' => count($theData),
            'invalidCount' => count($wrongData),
        ];
    }

    /**
     * 批量发货
     * @param $param
     *              - step int 操作步骤 2 上传文件 3 发货
     *              - path string 文件路径
     *              - type string 文件类型
     *              - orderSn array 订单号
     *              - express array 物流公司
     *              - expressSn array 物流单号
     * @return array|bool
     * @author Chensonglu
     */
    public function batchShipments($param)
    {
        if (!isset($param['step']) || !in_array($param['step'],[1,2,3])) {
            return $this->arrayData('批量退款步骤参数错误','',$param,'error');
        }
        if ($param['step'] == 2) {
            if (!isset($param['path']) || !$param['path']) {
                return $this->arrayData('文件路径参数错误','',$param,'error');
            }
            if (!isset($param['type']) || !$param['type']) {
                return $this->arrayData('文件类型参数错误','',$param,'error');
            }
            return $this->checkShipmentsData($param['path'], $param['type']);
        } elseif ($param['step'] == 3) {
            if (!isset($param['orderSn']) || !$param['orderSn']) {
                return $this->arrayData('没有正常发货的订单','',$param,'error');
            }
            if (!isset($param['express']) || !$param['express']) {
                return $this->arrayData('没有正常发货订单的物流公司','',$param,'error');
            }
            if (!isset($param['expressSn']) || !$param['expressSn']) {
                return $this->arrayData('没有正常发货订单的物流单号','',$param,'error');
            }
            return $this->batchUpdateExpressInfo($param['orderSn'], $param['express'], $param['expressSn']);
        }
        return $this->arrayData('批量发货失败','',$param,'error');
    }

    /**
     * 单个订单发货
     * @param $param
     * @param $isEdit bool 是否修改
     * @return array
     * @author Chensonglu
     */
    public function oneShipments($param, $isEdit = false)
    {
        if (!isset($param['orderSn']) || !$param['orderSn']) {
            return $this->arrayData('请选择发货订单','',$param,'error');
        }
        if (!isset($param['isCheckOrder'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        $orderInfo = BaiyangOrderData::getInstance()->getData([
            'column' => '*',
            'table' => 'Shop\Models\BaiyangOrder',
            'where' => 'WHERE order_sn = :orderSn:',
            'bind' => [
                'orderSn' => $param['orderSn']
            ]
        ],true);
        if (!$orderInfo) {
            return $this->arrayData('该订单不存在','',$param,'error');
        }
        $text = "";
        if (!$isEdit) {
            if ($orderInfo['status'] == 'paying') {
                return $this->arrayData('订单未支付不能发货','',$param,'error');
            } elseif (!$isEdit && $orderInfo['status'] != 'shipping') {
                return $this->arrayData('订单已发货，不能重复发货','',$param,'error');
            }
            if ($orderInfo['audit_state'] != 1) {
                return $this->arrayData('订单未审核或审核不通过','',$param,'error');
            }
            $text .= "发货";
        } else {
            if (!in_array($orderInfo['status'],['shipped','evaluating','finished'])) {
                return $this->arrayData('订单状态不允许修改配送信息','',$orderInfo,'error');
            }
            $text .= "修改";
        }
        if ($param['isCheckOrder']) {
            return $this->arrayData('可以发货操作'.$text,'',$orderInfo);
        }
        if (!isset($param['expressSn']) || !$param['expressSn']) {
            return $this->arrayData('请输入快递单号','',$param,'error');
        }
        if (!isset($param['isSend'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        if (!$param['isSend']) {
            $expressCompany = $this->_eventsManager->fire('express:expressCompany', $this, [
                'postid' => $param['expressSn'],
            ]);
            if ($expressCompany['error']) {
                return $this->arrayData('没有匹配到物流公司','',$expressCompany,'error');
            }
            return $this->arrayData('可以'.$text,'',$expressCompany['data']);
        }
        if (!isset($param['company']) || !trim($param['company'])) {
            return $this->arrayData('请输入物流公司','',$param,'error');
        }
        $upOrderData = $this->oneUpdateExpressInfo($orderInfo, trim($param['company']), $param['expressSn'], $isEdit);
        $upOrderData['info'] = $upOrderData['status'] == 'error' ? $text."失败" : $text."成功";
        return $upOrderData;
    }

    /**
     * 更新一个订单物流信息
     * @param $order array 订单信息
     * @param $company string 物流公司
     * @param $expressSn string 物流单号
     * @param $isEdit bool 是否修改
     * @return array
     * @author Chensonglu
     */
    public function oneUpdateExpressInfo($order, $company, $expressSn, $isEdit = false)
    {
        $time = time();
        $set = "status = :state:,last_status = :lastState:,express = :express:,express_sn = :expressSn:,"
            . "delivery_time = :time:,send_time = :time:";
        $cpsSet = " order_status = :state:";
        $cpsData = [
            'state' => 'shipped',
            'orderSn' => $order['order_sn'],
        ];
        $data = [
            'state' => 'shipped',
            'lastState' => 'shipping',
            'express' => $company,
            'expressSn' => $expressSn,
            'time' => $time,
            'orderSn' => $order['order_sn'],
        ];
        $orderData = BaiyangOrderData::getInstance();
        // 开启事务
        $this->dbWrite->begin();
        //更新订单审核信息
        $orderUp = $orderData->update($set,'Shop\Models\BaiyangOrder',$data,'order_sn = :orderSn:');
        if (!$orderUp) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','订单更新失败','error');
        }
        //更新订单返利状态
        $orderData->update($cpsSet,'Shop\Models\BaiyangCpsOrderLog',$cpsData,'order_sn = :orderSn:');
        //插入操作信息
        $addOperationLog = BaiyangOrderOperationLogData::getInstance()->addOperationLog([
            'belong_sn' => $order['order_sn'],
            'belong_type' => 1,
            'content' => $isEdit ? '修改发货信息':'订单发货',
            'operation_type' => 4,
            'operation_log' => json_encode($order),
        ]);
        if (!$addOperationLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','操作日志更新失败','error');
        }
        //插入订单日志
        $addOrderLog = BaiyangOrderLogData::getInstance()->addOrderLog($order);
        if (!$addOrderLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','orderLog插入失败','error');
        }
        $this->dbWrite->commit();
        $this->cache->delete('express_'.$order['order_sn']);
        return $this->arrayData('修改成功');
    }
    
    /**
     * 获取店铺信息
     * @return array 商家信息
     * @author Chensonglu
     */
    public function getShops()
    {
        $result = BaseData::getInstance()->getData([
            'column' => 'id,name',
            'table' => 'Shop\Models\BaiyangSkuSupplier',
        ]);
        $data = [];
        if ($result) {
            $data = array_column($result, 'name', 'id');
        }
        return $data;
    }

    /**
     * 统计对应状态总数
     * @param $state string 状态
     * @return bool
     * @author Chensonglu
     */
    public function getConditionNum($state)
    {
        if (!$state && $state != 'audit' && !isset($this->orderStat[$state])) {
            return false;
        }
        $where = ($state == 'audit') ? " AND o.audit_state = 0 AND o.status IN ('paying','shipping')" : " AND o.audit_state = 1 AND o.status = '{$state}'";
        return BaiyangOrderData::getInstance()->getOrderNum($where);
    }

    /**
     * 检测订单是否可修改状态
     * @param $param
     *              - orderSn string 订单号
     * @return array
     * @author Chensonglu
     */
    public function checkCanChangeOrder($param)
    {
        if (!isset($param['orderSn']) || !$param['orderSn']) {
            return $this->arrayData('参数错误','',$param,'error');
        }
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getData([
            'column' => '*',
            'table' => 'Shop\Models\BaiyangOrder',
            'where' => 'WHERE order_sn = :orderSn:',
            'bind' => ['orderSn' => $param['orderSn']]
        ],true);
        if (!$orderInfo) {
            return $this->arrayData('该订单不存在','',$param,'error');
        }
        /*if (in_array($orderInfo['status'], ['canceled','paying','draw','await'])) {
            return $this->arrayData("订单 {$this->orderStat[$orderInfo['status']]} 状态，无法修改",'',$orderInfo,'error');
        }
        $service = $this->getOrderServiceInfo($param['orderSn']);
        if($service) {
            foreach ($service as $item) {
                if (!in_array($item['status'], [1,3,6])) {
                    return $this->arrayData('此订单有进行中的服务单，无法修改','',$service,'error');
                    break;
                } elseif ($item['status'] == 3 && $orderInfo['status'] == 'refund') {
                    return $this->arrayData("订单交易关闭，无法修改",'',$orderInfo,'error');
                    break;
                }
            }
        }*/
        return $this->arrayData('可修改订单状态');
    }

    /**
     * 更改订单信息
     * @param $param
     *              - orderSn string 订单号
     *              - updateType int 更改类型 1 订单状态 2 收货信息 3 发票信息
     *              - state string 订单状态
     *              - isUpdate int 是否更新 0 否 1 是
     *              - pid int 上级地区ID
     *              - consignee string 收货人姓名
     *              - telephone int 手机号
     *              - province int 省Id
     *              - city int 市Id
     *              - county int 区Id
     *              - address string 详情地址
     *              - invoiceType int 发票类型
     *              - titleType string 抬头类型
     *              - titleName string 发票抬头
     *              - contentType string 发票内容
     * @return array
     * @author Chensonglu
     */
    public function updateOrder($param)
    {
        if (!isset($param['orderSn']) || !$param['orderSn']) {
            return $this->arrayData('参数错误','',$param,'error');
        }
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getData([
            'column' => '*',
            'table' => 'Shop\Models\BaiyangOrder',
            'where' => 'WHERE order_sn = :orderSn:',
            'bind' => ['orderSn' => $param['orderSn']]
        ],true);
        if (!$orderInfo) {
            return $this->arrayData('该订单不存在','',$param,'error');
        }
        if (!isset($param['updateType']) || !$param['updateType']) {
            return $this->arrayData('更新订单类型参数错误','',$param,'error');
        }
        $set = $operationLog = "";
        $cpsSet = "";
        $data = [];
        $cpsData = [];
        if ($param['updateType'] == 1) {
            $operationLog = "修改了订单状态";
            if (!isset($param['state']) || !$this->orderStat[$param['state']]) {
                return $this->arrayData('订单状态参数错误','',$param,'error');
            }
            $set .= " status = :state:,last_status = :lastState:";
            $cpsSet .= " order_status = :state:";
            $cpsData['state'] = $param['state'];
            $cpsData['orderSn'] = $param['orderSn'];
            $data['state'] = $param['state'];
            $data['lastState'] = $orderInfo['status'];
        } elseif ($param['updateType'] == 2) {
            if (!isset($param['isUpdate'])) {
                return $this->arrayData('参数不完整','',$param,'error');
            }
            if (!$param['isUpdate']) {
                if (!isset($param['pid']) || !$param['pid']) {
                    return $this->arrayData('请选择地址','',$param,'error');
                }
                return $this->arrayData('','',BaiyangRegionData::getInstance()->getChildRegion($param['pid']));
            }
            $operationLog = "修改了收货信息";
            if (!isset($param['consignee']) || !trim($param['consignee'])) {
                return $this->arrayData('请填写收货人','',$param,'error');
            }
            if (!isset($param['telephone']) || !trim($param['telephone'])) {
                return $this->arrayData('请填写手机号','',$param,'error');
            } elseif (!$this->func->isPhone(trim($param['telephone']))) {
                return $this->arrayData('手机号格式错误','',$param,'error');
            }
            if (!isset($param['province']) || !$param['province']) {
                return $this->arrayData('请选择省份','',$param,'error');
            }
            if (!isset($param['city']) || !$param['city']) {
                return $this->arrayData('请选择市','',$param,'error');
            }
            if (!isset($param['county']) || !$param['county']) {
                return $this->arrayData('请选择区','',$param,'error');
            }
            if (!isset($param['address']) || !trim($param['address'])) {
                return $this->arrayData('请填写详细地址','',$param,'error');
            }
            $set .= "consignee=:consignee:,telephone=:telephone:,province=:province:,city=:city:,county=:county:,address=:address:";
            $data['consignee'] = $param['consignee'];
            $data['telephone'] = $param['telephone'];
            $data['province'] = $param['province'];
            $data['city'] = $param['city'];
            $data['county'] = $param['county'];
            $data['address'] = $param['address'];
        } elseif ($param['updateType'] == 3) {
            $operationLog = "修改了发票信息";
            if (!isset($param['invoiceType']) || !in_array($param['invoiceType'],[0,1,2,3])) {
                return $this->arrayData('请选择发票类型','',$param,'error');
            }
            $set .= "invoice_type=:invoiceType:,invoice_info=:info:";
            $data['invoiceType'] = $param['invoiceType'];
            if ($param['invoiceType'] == 0) {
                $data['info'] = '';
            } else {
                if (!isset($param['titleType']) || !$param['titleType']) {
                    return $this->arrayData('请选择发票抬头类型','',$param,'error');
                }
                if (!isset($param['titleName'])) {}
                if ($param['titleType'] == '单位' && !$param['titleName']) {
                    return $this->arrayData('请填写发票抬头','',$param,'error');
                }
                if ($param['titleType'] == '单位' && !$param['taxpayerNumber']) {
                    return $this->arrayData('请填写税号','',$param,'error');
                }
                if (!isset($param['contentType']) || !$param['contentType']) {
                    return $this->arrayData('请选择发票内容','',$param,'error');
                }
                $info = [
                    'title_type' => $param['titleType'],
                    'title_name' => $param['titleName'],
                    'content_type' => $param['contentType'],
                    'type_id' => $param['invoiceType'],
                    'taxpayer_number' => $param['taxpayerNumber'],
                ];
                $data['info'] = json_encode($info);
            }
        }
        $data['orderSn'] = $param['orderSn'];
        // 开启事务
        $this->dbWrite->begin();
        //更新订单审核信息
        $orderUp = $orderData->update($set,'Shop\Models\BaiyangOrder',$data,'order_sn = :orderSn:');
        if (!$orderUp) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','订单更新失败','error');
        }
        //更新订单返利状态
        if ($cpsData) {
            $orderData->update($cpsSet,'Shop\Models\BaiyangCpsOrderLog',$cpsData,'order_sn = :orderSn:');
        }
        //插入操作信息
        $addOperationLog = BaiyangOrderOperationLogData::getInstance()->addOperationLog([
            'belong_sn' => $param['orderSn'],
            'belong_type' => 1,
            'content' => $operationLog,
            'operation_type' => 4,
            'operation_log' => json_encode($orderInfo),
        ]);
        if (!$addOperationLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','操作日志更新失败','error');
        }
        //插入订单日志
        $addOrderLog = BaiyangOrderLogData::getInstance()->addOrderLog($orderInfo);
        if (!$addOrderLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','orderLog插入失败','error');
        }
        $this->dbWrite->commit();
        return $this->arrayData('修改成功','',[
            'username' => $this->session->get('admin_account'),
            'time' => date('Y-m-d H:i:s'),
            'content' => $operationLog
        ]);
    }

    /**
     * 添加订单备注
     * @param $param
     *              - orderSn 子订单号
     *              - remark 备注内容
     *              - type 备注类型（1订单、2服务单）
     * @return array
     * @author Chensonglu
     */
    public function addOrderRemark($param)
    {
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        if (!isset($param['orderSn']) || !$param['orderSn']) {
            return $this->arrayData('请选择订单','',$param,'error');
        }
        if (!isset($param['isAdd'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        if (!$param['isAdd']) {
            $remarkNum = $operationLogData->remarkNum($param['orderSn']);
            return $remarkNum >= 10
                ? $this->arrayData('此订单的备注已超过10条，无法继续添加','',$param,'error')
                : $this->arrayData('可添加备注','',[
                    'account' => $this->session->get('admin_account')
                ]);
        }
        if (!isset($param['remark']) || !trim($param['remark'])) {
            return $this->arrayData('请填写备注（不能全部是空格）','',$param,'error');
        } elseif (mb_strlen(trim($param['remark'])) >= 200) {
            return $this->arrayData('备注内容不能超过200个字符','',$param,'error');
        }
        //插入信息
        $data = [
            'belong_sn' => $param['orderSn'],
            'belong_type' => 1,
            'content' => trim($param['remark']),
            'operation_type' => 1,
        ];
        $addRemark = $operationLogData->addOperationLog($data);
        return $addRemark ? $this->arrayData('备注添加成功') : $this->arrayData('备注添加失败','',$addRemark,'error');
    }

    /**
     * 获取子订单信息
     * @param $orderSn 子订单号
     * @return array
     * @author Chensonglu
     */
    public function getOrderInfo($orderSn)
    {
        //获取订单详情
        $info = $this->getChildOrderInfo($orderSn);
        if (!$info) {
            return false;
        }
        //获取用户手机号
        $user_id = intval($info['user_id']);
        $info['phone'] = BaiyangUserData::getInstance()->findPhoneByUserId($user_id)['phone'];
        $info['orderDue'] = sprintf("%.2f",$info['total']-$info['balance_price']);
        $regionData = BaiyangRegionData::getInstance();
        $info['provinceVal'] = $regionData->getChildRegion();
        if (is_numeric($info['province'])) {
            $info['cityVal'] = $regionData->getChildRegion($info['province']);
            $info['countyVal'] = $regionData->getChildRegion($info['city']);
        }
        $info['orderSource'] = $this->orderSource($orderSn);
        $info['invoice_info'] = isset($info['invoice_info']) && $info['invoice_info']
            ? json_decode($info['invoice_info'], true) : false;
        //获取订单物流
        $info['logistics'] = $this->getOrderLogistics(['orderSn'=>$orderSn,'expressSn'=>$info['express_sn']]);
        //获取订单操作日志
        $info['operationLog'] = BaiyangOrderOperationLogData::getInstance()->getOperationLog([
            'orderSn'=>$orderSn
        ]);
        return $info;
    }

    /**
     * 订单来源
     * @param $orderSn string 订单号
     * @return string
     * @author Chensonglu
     */
    public function orderSource($orderSn)
    {
        if ($orderSn && BaiyangOrderData::getInstance()->isPrescriptionOrder($orderSn)) {
            return '易复诊';
        }
        return $this->config['company_name'] . '商城';
    }

    /**
     * 获取订单物流
     * @param $param
     *              - orderSn 子订单号
     *              - expressSn 订单物流号
     * @param int $sort 排序 默认为顺序
     * @return bool|mixed
     * @author Chensonglu
     */
    public function getOrderLogistics($param, $sort = SORT_ASC)
    {
        $where = " WHERE 1";
        $data = [];
        if (isset($param['orderSn']) && $param['orderSn']) {
            $where .= " AND order_sn = :orderSn:";
            $data['orderSn'] = $param['orderSn'];
        }
        if (isset($param['expressSn']) && $param['expressSn']) {
            $where .= " AND express_sn = :expressSn:";
            $data['expressSn'] = $param['expressSn'];
        }
        if (!$data) {
            return false;
        }

        //获取物流信息
        $result = $this->_eventsManager->fire('express:getLogistics', $this, [
            'postid' => isset($data['expressSn']) ? $data['expressSn'] : '',
        ]);
        if ($result['error']) {
            $result = BaiyangOrderData::getInstance()->getData([
                'column' => 'shipping_detail',
                'table' => 'Shop\Models\BaiyangOrderShipping',
                'where' => $where,
                'bind' => $data
            ],true);
            $result = json_decode($result['shipping_detail'], true);
            $result = isset($result['lastResult']['data']) ? $result['lastResult']['data'] : false;
        } else {
            $result = $result['data']['list'];
        }
        $logistics = [];
        if ($result) {
            array_multisort(array_column($result, 'time'), $sort, $result);
            $weeks =['周日','周一','周二','周三','周四','周五','周六'];
            $date = '';
            foreach ($result as $key => $value) {
                $time = strtotime($value['time']);
                $value['date'] = date('Y-m-d', $time);
                if ($date && $date == $value['date']) {
                    $value['isFirst'] = 0;
                } else {
                    $date = $value['date'];
                    $value['isFirst'] = 1;
                }
                $value['isEnd'] = ($key == (count($result)-1)) ? 1 : 0;
                $value['week'] = $weeks[date('w',$time)];
                $value['hour'] = date('H:i:s',$time);
                $logistics[] = $value;
            }
        }
        return $logistics;
    }

    /**
     * 处方药订单审核
     * @param $param
     *              - totalSn string 处方药待审核母订单号
     *              - isAudit int 操作 0 只获取待审核订单数据 1 审核
     *              - reason string 审核不通过原因
     *              - state int 审核状态
     * @return array
     * @author Chensonglu
     */
    public function auditOrder($param)
    {
        //参数验证
        if (!isset($param['totalSn']) || !$param['totalSn']) {
            return $this->arrayData('请选择审核订单','',$param,'error');
        }
        if (!isset($param['isAudit'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        //获取待审核订单信息
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getUnauditedOrder($param['totalSn']);
        if (!$orderInfo) {
            return $this->arrayData('该订单已审核','','','error');
        }
        //获取待审核订单商品信息
        $orderInfo['products'] = $this->getOrderGoods($param['totalSn'], true);

        if (!$param['isAudit']) {
            return $this->arrayData('可审核','',$orderInfo);
        }
        //审核状态参数验证
        if (!isset($param['state']) || !in_array($param['state'],[1,2])) {
            return $this->arrayData('请选择审核结果','',$param,'error');
        }
        if ($param['state'] == 2 && (!isset($param['reason']) || !trim($param['reason']))) {
            return $this->arrayData('请填写审核不通过原因（不能全部是空格）','',$param,'error');
        } elseif ($param['state'] == 1) {
            $param['reason'] = '';
        }
        $time = time();
        $set = "audit_state = :state:,audit_time = :time:,audit_reason = :reason:";
        $data = [
            'state' => $param['state'],
            'totalSn' => $param['totalSn'],
            'reason' => trim($param['reason']),
            'time' => $time,
        ];
        //获取审核用户信息
        $adminUserId = $this->session->get('admin_id');
        $adminUserInfo = BaiyAdminData::getInstance()->getAdminInfo(['userId'=>$adminUserId]);
        if (isset($adminUserInfo['title']) && $adminUserInfo['title'] == '药师') {
            $set .= ",admin_account = :account:";
            $data['account'] = $adminUserInfo['username'];
        }
        // 开启事务
        $this->dbWrite->begin();
        if ($param['state'] == 1) {
            $operationLogText = "进行了订单审核，审核结果为：通过审核";
            $isError = 0;
            $orderSn = array_unique($orderInfo['orderSn']);
            if ($orderInfo['status'] == 'shipping') {
                foreach ($orderSn as $val) {
                    $erpData = array(
                        'order_sn' => $val,
                        'type_id' => 10,
                        'state' => 0,
                        'add_time' => $time
                    );
                    if (!$orderData->insert('Shop\Models\BaiyangOrderErpLog',$erpData)) {
                        $this->dbWrite->rollback();
                        $isError = 1;
                        break;
                    }
                }
            }
            if ($isError) {
                return $this->arrayData('审核失败','',$orderInfo['products'],'error');
            }
            //获取下单用户手机号
            $userPhone = $orderData->getData([
                'column' => 'phone',
                'table' => 'Shop\Models\BaiyangUser',
                'where' => 'WHERE id = :userId:',
                'bind' => ['userId' => $orderInfo['user_id']]
            ],true);
            //发送审核通过短信
            if (isset($userPhone['phone']) && $userPhone['phone'] && $this->func->isPhone($userPhone['phone'])) {
                $this->func->sendSms($userPhone['phone'],'shop_prescription_audit',['user_id' => $orderInfo['user_id']]);
            }
        } else {
            $operationLogText = "进行了订单审核，审核结果为：不通过审核，原因：{$data['reason']}";
        }
        if ($orderInfo['orderSn'] && is_array($orderInfo['orderSn'])) {
            $addOperationData = BaiyangOrderOperationLogData::getInstance();
            $isRollBack = 0;
            foreach ($orderInfo['orderSn'] as $val) {
                //插入操作信息
                $operationLog = $addOperationData->addOperationLog([
                    'belong_sn' => $val,
                    'belong_type' => 1,
                    'content' => $operationLogText,
                    'operation_type' => 2,
                    'operation_log' => json_encode($orderInfo),
                ]);
                if (!$operationLog) {
                    $isRollBack = 1;
                    break;
                }
            }
            if ($isRollBack) {
                $this->dbWrite->rollback();
                return $this->arrayData('审核失败','','操作日志添加失败','error');
            }
        }
        //更新订单审核信息
        $orderUp = $orderData->update($set,'Shop\Models\BaiyangOrder',$data,'total_sn = :totalSn:');
        if (!$orderUp) {
            $this->dbWrite->rollback();
            return $this->arrayData('审核失败','','更新子订单审核状态失败','error');
        }
        if ($orderInfo['isTotal']) {
            $totalOrderUp = $orderData->update($set,'Shop\Models\BaiyangParentOrder',$data,'total_sn = :totalSn:');
            if (!$totalOrderUp) {
                $this->dbWrite->rollback();
                return $this->arrayData('审核失败','','更新母订单审核状态失败','error');
            }
        }
        $this->dbWrite->commit();
        if ($param['state'] == 2) {
            $this->order->cancel([
                'user_id' => $orderInfo['user_id'],
                'order_sn' => $param['totalSn'],
                'cancel_reason' => '处方药订单审核不通过'
            ]);
        }
        return $this->arrayData('审核成功');
    }

    /**
     * 获取母订单数据且进行处理
     * @param $totalSn array 母订单号
     * @return array 返回母订单数据（母订单号为键 母订单数据为值）
     * @author Chensonglu
     */
    public function getParentOrderAll($totalSn)
    {
        $parentOrder = BaiyangParentOrderData::getInstance()->getParentOrder($totalSn, false);
        if ($parentOrder) {
            $totalSn = array_column($parentOrder, 'total_sn');
            $parentOrder = array_combine($totalSn,$parentOrder);
        }
        unset($totalSn);
        return $parentOrder;
    }

    /**
     * 获取所有订单商品信息
     * @param $totalSn array 订单号
     * @param $isTotal bool 是否母订单号
     * @return array
     * @author Chensonglu
     */
    public function getAllOrderGoods($totalSn, $isTotal = true)
    {
        $result = BaiyangOrderDetailData::getInstance()->getAllOrderGoods($totalSn, $isTotal);
        $orderGoods = [];
        if ($result) {
            //所有商品品规
            $goodsRule = BaiyangProductRuleData::getInstance()->getAllGoodsRule();
            foreach ($result as $goods) {
                //匹配商品品规
                $goods['name_id'] = isset($goodsRule[$goods['name_id']]) ? $goodsRule[$goods['name_id']] : "";
                $goods['name_id2'] = isset($goodsRule[$goods['name_id2']]) ? $goodsRule[$goods['name_id2']] : "";
                $goods['name_id3'] = isset($goodsRule[$goods['name_id3']]) ? $goodsRule[$goods['name_id3']] : "";
                if (!isset($orderGoods[$goods['order_sn']]['goodsNum'])) {
                    $orderGoods[$goods['order_sn']]['goodsNum'] = 0;
                    $orderGoods[$goods['order_sn']]['refundNum'] = 0;
                }
                if ($goods['goods_type'] != 1) {
                    $orderGoods[$goods['order_sn']]['goodsNum'] += isset($goods['goods_number'])
                        ? $goods['goods_number'] : 0;
                    $orderGoods[$goods['order_sn']]['refundNum'] += isset($goods['refund_goods_number'])
                        ? $goods['refund_goods_number'] : 0;
                }
                $orderGoods[$goods['order_sn']]['productList'][] = $goods;
            }
        }
        return $orderGoods;
    }

    /**
     * 获取所有订单服务信息
     * @param $orderSn array 子订单号
     * @return array|bool
     * @author Chensonglu
     */
    public function getOrderService($orderSn)
    {
        if (!$orderSn) {
            return false;
        } elseif (is_array($orderSn)) {
            foreach ($orderSn as $k => $val) {
                $orderSn[$k] = "'{$val}'";
            }
            $orderSnStr = implode(',', $orderSn);
        } else {
            $orderSnStr = "'{$orderSn}'";
        }
        $column = 'ogrr.order_sn,ogrr.service_sn,ogrr.status';
        $result = BaiyangOrderGoodsReturnReasonData::getInstance()->getRefundAll($column,"WHERE ogrr.order_sn IN ({$orderSnStr})");
        $orderService = [];
        if ($result) {
            foreach ($result as $service) {
                $orderService[$service['order_sn']][] = $service;
            }
        }
        return $orderService;
    }

    /**
     * 获取所有子订单信息（含未拆分订单）
     * @param $totalSn array 母订单号
     * @return array
     * @author Chensonglu
     */
    public function getOrderInfoAll($totalSn, $isTotal = true)
    {
        //查询订单信息
        $result = BaiyangOrderData::getInstance()->getOrderInfo($totalSn, $isTotal);
        $orderInfo = [];
        if ($result) {
            //获取子订单商品信息
            $orderGoods = $this->getAllOrderGoods($totalSn, $isTotal);
            //获取子订单号
            $orderSn = array_column($result, 'order_sn');
            //获取所有订单服务单信息
            $orderService = $this->getOrderService($orderSn);
            //获取地区信息
            $regionName = BaiyangRegionData::getInstance()->getRegionAll();
            //查询订单备注总数
            $operationLogData = BaiyangOrderOperationLogData::getInstance();
            $orderRemarkNum = $operationLogData->getAllRemarkNum($orderSn);
            foreach ($result as $info) {
                // 代客下单优惠
                if(($info['user_coupon_price'] + $info['youhui_price']) < $info['order_discount_money']) {
                    $info['youhui_price'] = bcsub($info['order_discount_money'],$info['user_coupon_price'],2);
                }
                //地址匹配
                $address = is_numeric($info['province']) && $info['province'] > 0
                    ? $regionName[$info['province']] . ' ' : $info['province']. ' ';
                $address .= is_numeric($info['city']) && $info['city'] > 0
                    ? $regionName[$info['city']] . ' ' : $info['city']  . ' ';
                $address .= is_numeric($info['county']) && $info['county'] > 0
                    ? $regionName[$info['county']] . ' ' : $info['county'] . ' ';
                $info['addressInfo'] = $address . $info['address'];
                $info['productList'] = [];
                //订单是否退款/退货
                $info['isRefund'] = 0;
                if (isset($orderGoods[$info['order_sn']])) {
                    $info = array_merge($info, $orderGoods[$info['order_sn']]);
                    $info['isRefund'] = isset($info['goodsNum']) && isset($info['refundNum']) && ($info['goodsNum'] == $info['refundNum'])
                        ? 1 : 0;
                }
                $info['remarkCount'] = isset($orderRemarkNum[$info['order_sn']]) ? $orderRemarkNum[$info['order_sn']] : 0;
                //订单备注信息
                $info['remark'] = [];
                if ($info['remarkCount']) {
                    $info['remark'] = $operationLogData->getOperationLog([
                        'orderSn' => $info['order_sn'],
                        'type' => 1
                    ]);
                }
                $info['serviceInfo'] = isset($orderService[$info['order_sn']]) ? $orderService[$info['order_sn']] : [];
                //订单是否关闭
                $info['isClose'] = in_array($info['status'], ['refund','canceled']) ? 1 : 0;
                if (in_array($info['status'], ['shipping','shipped']) && $info['serviceInfo'] && !$info['isClose']) {
                    foreach ($info['serviceInfo'] as $item) {
                        if ($item['status'] == 3) {
                            $childOrderInfo['isClose'] = 1;
                            break 1;
                        }
                    }
                }
                if ($info['total_sn'] != $info['order_sn'] && $isTotal) {
                    $orderInfo[$info['total_sn']][] = $info;
                } else {
                    $orderInfo[$info['order_sn']] = $info;
                }
            }
            unset($orderSn,$orderService,$regionName,$orderRemarkNum);
        }
        return $orderInfo;
    }

    /**
     * 获取普通订单列表数据
     * @param $param
     * @return array
     * @author Chensonglu
     */
    public function getAllOrder($param)
    {
        $result = $this->getTotalOrder($param);
        if (isset($result['count']) && !$result['count']) {
            return $result;
        }
        $totalSn = array_column($result['totalOrder'], 'total_sn');
        //母订单
        $getParentOrder = $this->getParentOrderAll($totalSn);
        //子订单（含未拆分订单
        $getOrderInfo = $this->getOrderInfoAll($totalSn);
        foreach ($result['totalOrder'] as $key => $value) {
            //获取下单用户手机号
            $user_id = 0;
            if (isset($getParentOrder[$value['total_sn']])) {
                $value = $getParentOrder[$value['total_sn']];
                $value['isTotal'] = 1;
                $value['childOrders'] = isset($getOrderInfo[$value['total_sn']]) ? $getOrderInfo[$value['total_sn']] : [];
                $user_id = intval($getParentOrder[$value['total_sn']]['user_id']);
            } else {
                $value = isset($getOrderInfo[$value['order_sn']]) ? $getOrderInfo[$value['order_sn']] : [];
                $value['isTotal'] = 0;
                $value['childOrders'] = [];
                $user_id = isset($getOrderInfo[$value['order_sn']]) ? intval($getOrderInfo[$value['order_sn']]['user_id']) : 0;
            }
            $value['phone'] = BaiyangUserData::getInstance()->findPhoneByUserId($user_id)['phone'];
            $result['totalOrder'][$key] = $value;
        }
        return $result;
    }

    /**
     * 根据子订单号获取服务单信息
     * @param $orderSn 订单号
     * @return bool
     * @author Chensonglu
     */
    public function getOrderServiceInfo($orderSn)
    {
        if (!$orderSn) {
            return false;
        }
        $column = "ogrr.service_sn,ogrr.status";
        return BaiyangOrderGoodsReturnReasonData::getInstance()->getRefundAll($column,"WHERE ogrr.order_sn = '{$orderSn}'");
    }

    /**
     * 根据子订单号获取订单详情
     * @param string $orderSn 子订单号
     * @return array 订单详情
     * @param bool $isBatch 是否批量导入
     * @author Chensonglu
     */
    public function getChildOrderInfo($orderSn, $isBatch = false)
    {
        if (!$orderSn) {
            return false;
        }
        $childOrderInfo = BaiyangOrderData::getInstance()->getOrderInfo($orderSn);
        if ($childOrderInfo) {
            $regionName = BaiyangRegionData::getInstance()->getRegionAll();
            $address = is_numeric($childOrderInfo['province']) && $childOrderInfo['province'] > 0
                ? $regionName[$childOrderInfo['province']] . ' ' : $childOrderInfo['province']. ' ';
            $address .= is_numeric($childOrderInfo['city']) && $childOrderInfo['city'] > 0
                ? $regionName[$childOrderInfo['city']] . ' ' : $childOrderInfo['city']  . ' ';
            $address .= is_numeric($childOrderInfo['county']) && $childOrderInfo['county'] > 0
                ? $regionName[$childOrderInfo['county']] . ' ' : $childOrderInfo['county'] . ' ';
            $childOrderInfo['addressInfo'] = $address . $childOrderInfo['address'];
            if (!$isBatch) {
                if($childOrderInfo['user_coupon_price'] + $childOrderInfo['youhui_price'] < $childOrderInfo['order_discount_money']) $childOrderInfo['youhui_price'] = bcsub($childOrderInfo['order_discount_money'],$childOrderInfo['user_coupon_price'],2); // 代客下单优惠
                $operationLogData = BaiyangOrderOperationLogData::getInstance();
                $childOrderInfo['remarkCount'] = $operationLogData->remarkNum($orderSn);
                $childOrderInfo['remark'] = false;
                if ($childOrderInfo['remarkCount']) {
                    $childOrderInfo['remark'] = $operationLogData->getOperationLog([
                        'orderSn'=>$orderSn,
                        'type'=>1
                    ]);
                }
                $childOrderInfo['serviceInfo'] = $this->getOrderServiceInfo($orderSn);
                $childOrderInfo['isClose'] = in_array($childOrderInfo['status'], ['refund','canceled']) ? 1 : 0;
                if (in_array($childOrderInfo['status'], ['shipping','shipped']) && $childOrderInfo['serviceInfo'] && !$childOrderInfo['isClose']) {
                    foreach ($childOrderInfo['serviceInfo'] as $item) {
                        if ($item['status'] == 3) {
                            $childOrderInfo['isClose'] = 1;
                            continue;
                        }
                    }
                }
            }
            $childOrderInfo = array_merge($childOrderInfo,$this->getOrderGoods($orderSn));
            return $childOrderInfo;
        }
        return false;
    }

    /**
     * 根据子订单号获取订单商品信息
     * @param $orderSn 订单号
     * @param bool $isTotal 是否母订单
     * @return bool
     * @author Chensonglu
     */
    public function getOrderGoods($orderSn, $isTotal = false)
    {
        if (!$orderSn)
        {
            return [
                'productList' => [],
                'isRefund' => 0
            ];
        }
        $detailData = BaiyangOrderDetailData::getInstance();
        $orderGoods = $isTotal ? $detailData->getOrderGoods(['totalSn' => $orderSn])
            : $detailData->getOrderGoods(['orderSn' => $orderSn]);
        $isRefund = 0;
        if ($orderGoods) {
            $goodsRule = BaiyangProductRuleData::getInstance()->getAllGoodsRule();
            $goodsNum = $refundNum = 0;
            foreach ($orderGoods as $key => $val) {
                if ($val['goods_type'] != 1) {
                    $goodsNum += $val['goods_number'];
                    $refundNum += isset($val['refund_goods_number'])?$val['refund_goods_number']:0;
                }
                $val['name_id'] = isset($goodsRule[$val['name_id']]) ? $goodsRule[$val['name_id']] : "";
                $val['name_id2'] = isset($goodsRule[$val['name_id2']]) ? $goodsRule[$val['name_id2']] : "";
                $val['name_id3'] = isset($goodsRule[$val['name_id3']]) ? $goodsRule[$val['name_id3']] : "";
                //商品附属赠品
                $val['appendant'] = [];
                if (!$val['goods_type']) {
                    $val['appendant'] = $detailData->getOrderGoods(['detailId' => $val['id']]);
                }
                $orderGoods[$key] = $val;
            }
            $isRefund = ($goodsNum == $refundNum) ? 1 : 0;
        }
        return [
            'productList' => $orderGoods,
            'isRefund' => $isRefund
        ];
    }

    /**
     * 获取主订单、总记录数
     * @param $param
     *              -string order_sn 订单号
     *              -int start_time 下单时间查询开始时间
     *              -int end_time 下单时间查询结束时间
     *              -int order_type 订单类型
     *              -int shop_id 发货商家
     *              -int channel_subid 下单终端
     *              -string username 用户名
     *              -int phone 手机号
     *              -string goods_name 商品名
     *              -int goods_id 商品id
     *              -int payment_id 支付方式
     *              -string express_type 配送方式
     *              -int order_source 订单来源
     *              -string searchType 订单状态
     *              -int pageNum 每页显示条数
     *              -int page 当前页
     * @return array
     *              -array totalOrder 主订单
     *              -int count 总记录数
     * @author Chensonglu
     */
    public function getTotalOrder($param)
    {
        $where = "";
        $join = "";
        //订单号
        if (isset($param['order_sn']) && $param['order_sn']) {
            $where .= " AND (o.order_sn = '{$param['order_sn']}' OR o.total_sn = '{$param['order_sn']}')";
        }
        //订单时间
        if (isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] && $param['end_time']) {
            $param['start_time'] = strtotime($param['start_time']);
            $param['end_time'] = strtotime($param['end_time']);
            $where .= " AND o.add_time BETWEEN {$param['start_time']} AND {$param['end_time']}";
        } elseif (isset($param['start_time']) && $param['start_time'] && (!isset($param['end_time']) || !$param['end_time'])) {
            $param['start_time'] = strtotime($param['start_time']);
            $where .= " AND o.add_time >= {$param['start_time']}";
        } elseif (isset($param['end_time']) && $param['end_time'] && (!isset($param['start_time']) || !$param['start_time'])) {
            $param['end_time'] = strtotime($param['end_time']);
            $where .= " AND o.add_time <= {$param['end_time']}";
        }
        //订单类型
        if (isset($param['order_type']) && isset($this->orderType[$param['order_type']])) {
            if ($this->orderType[$param['order_type']] != 2) {
                $where .= " AND o.order_type = {$this->orderType[$param['order_type']]} AND o.callback_phone <> ''";
            } else {
                $where .= " AND o.order_type = 0 AND o.callback_phone <> ''";
            }
        }
        //发货商家
        if (isset($param['shop_id']) && $param['shop_id']) {
            $where .= " AND o.shop_id = {$param['shop_id']}";
        }
        //收货人手机
        if (isset($param['telephone']) && $param['telephone']) {
            $where .= " AND o.telephone = '{$param['telephone']}'";
        }
        //下单终端
        if (isset($param['channel_subid']) && isset($this->orderTerminal[$param['channel_subid']])) {
            $where .= " AND o.channel_subid = {$param['channel_subid']}";
        }
        //是否关联user表
        $isJoinUser = 0;
        //用户名
        if (isset($param['username']) && $param['username']) {
            $where .= " AND u.username LIKE '%".$param['username']."%'";
            $isJoinUser = 1;
        }
        //手机号
        if (isset($param['phone']) && $param['phone']) {
            $where .= " AND (u.phone = {$param['phone']} OR u.user_id = {$param['phone']})";
            $isJoinUser = 1;
        }
        $join .= $isJoinUser ? " INNER JOIN baiyang_user u ON o.user_id = u.id" : '';
        //是否关联orderDetail表
        $isJoinOrderDetail = 0;
        //商品ID或商品名称
        if (isset($param['goods_name']) && $param['goods_name'] ) {
            $where .= " AND od.goods_name LIKE '%".$param['goods_name']."%'";
            $isJoinOrderDetail = 1;
        }
        if (isset($param['goods_id']) && is_numeric($param['goods_id']) && $param['goods_id']) {
            $where .= " AND od.goods_id = {$param['goods_id']}";
            $isJoinOrderDetail = 1;
        }
        $join .= $isJoinOrderDetail ? " LEFT JOIN baiyang_order_detail od ON o.order_sn = od.order_sn" : '';
        //支付方式
        if (isset($param['payment_id']) && isset($this->orderPayment[$param['payment_id']])) {
            $where .= " AND o.payment_id = {$param['payment_id']}";
        }
        //配送方式
        if (isset($param['express_type']) && isset($this->orderDelivery[$param['express_type']])) {
            $where .= " AND o.express_type = {$this->orderDelivery[$param['express_type']]}";
        }
        //订单来源
        if (isset($param['order_source']) && isset($this->orderSource[$param['order_source']])) {
            if ($param['order_source'] == 1) {
                $join .= " LEFT JOIN baiyang_prescription p ON o.total_sn = p.order_id";
                $where .= " AND p.order_id IS NULL AND o.more_platform_sign <> 'yukon'";
            } elseif($param['order_source'] == 3){
                $where .= " AND o.more_platform_sign = 'yukon'";
            }else {
                $join .= " INNER JOIN baiyang_prescription p ON o.total_sn = p.order_id";
            }
        }
        //是否关联orderGoodsReturnReason表
        $isJoinOrderReturn = 0;
        //订单状态
        if (isset($param['searchType']) && $param['searchType'] && $param['searchType'] != 'all'){
            if ($param['searchType'] == 'toAudit') {
                $where .= " AND o.audit_state = 0 AND o.status IN ('paying','shipping')";
            } elseif ($param['searchType'] == 'tradingClosed') {
                $where .= " AND o.audit_state = 1 AND (o.status = 'canceled' OR (o.status = 'refund' AND orr.status = 3))";
                $isJoinOrderReturn = 1;
            } elseif ($param['searchType'] == 'aRefundOf') {
                $where .= " AND o.audit_state = 1 AND o.status = 'refund' AND orr.status NOT IN (1,3)";
                $isJoinOrderReturn = 1;
            } elseif ($param['searchType'] == 'finished') {
                $where .= " AND o.audit_state = 1 AND o.status IN ('evaluating','finished')";
            } else {
                $where .= " AND
                 o.audit_state = 1 AND o.status = '{$param['searchType']}'";
            }
        }
        $join .= $isJoinOrderReturn ? " LEFT JOIN baiyang_order_goods_return_reason orr ON o.order_sn = orr.order_sn" : '';
        $orderData = BaiyangOrderData::getInstance();
        //总记录数
        $counts = $orderData->getOrderNum($where,$join);
        //分页
        $pages['psize'] = isset($param['psize']) && $param['psize'] ? (int)$param['psize'] : 15;//每页显示条数
        $pages['page'] = isset($param['page']) && $param['page'] ? (int)$param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $pages['isShow'] = true;
        $page = $this->page->pageDetail($pages);
        //数据
        $totalOrder = $orderData->getTotalOrderAll($where,$join,"GROUP BY o.total_sn","ORDER BY o.add_time DESC","LIMIT {$page['record']},{$page['psize']}");
        return [
            'totalOrder' => $totalOrder,
            'page' => $page['page'],
            'count' => $counts,
        ];
    }

    /**
     * 申请退货/退款/取消
     * @param $param
     *              - orderSn string 订单号
     *              - goodsId array 商品ID
     *              - num array 退货数量
     *              - reason string 退货原因
     *              - explain string 原因说明
     * @return array
     * @author Chensonglu
     */
    public function applyRefund($param)
    {
        if (!isset($param['orderSn']) || !$param['orderSn']) {
            return $this->arrayData('订单不存在，请重新进入申请页面', '', '', 'error');
        }
        //获取数据
        $orderInfo = $this->getChildOrderInfo($param['orderSn']);
        if (!$orderInfo) {
            return $this->arrayData('订单不存在，请重新进入申请页面', '', '', 'error');
        }
        //检验是否已退
        if ($orderInfo['isRefund']) {
            return $this->arrayData('该订单已申请了', '', '', 'error');
        }
        //商品Id
        if (!isset($param['goodsId']) || !$param['goodsId'] || !is_array($param['goodsId'])) {
            return $this->arrayData('商品Id不存在或类型不对', '', '', 'error');
        }
        //退货数量
        if (!isset($param['num']) || !$param['num']|| !is_array($param['num'])) {
            return $this->arrayData('退货数量不存在或类型不对', '', '', 'error');
        } elseif (!array_sum($param['num'])) {
            return $this->arrayData('请填写已选商品的退货数量', '', '', 'error');
        }
        //退货原因
        if (!isset($param['reason']) || !$param['reason']) {
            return $this->arrayData('请选择退货原因', '', '', 'error');
        }
        //原因说明
        if (!isset($param['explain']) || !$param['explain']) {
            return $this->arrayData('请填写原因说明', '', '', 'error');
        }
        if (strlen($param['explain']) > 200) {
            return $this->arrayData('原因说明不能超过200个字符', '', '', 'error');
        }
        $data = [
            'user_id' => $orderInfo['user_id'],
            'order_sn' => $param['orderSn'],
            'reason' => $param['reason'],
            'explain' => $param['explain'],
        ];
        $goods = [];
        foreach ($param['num'] as $key => $item) {
            if ($item) {
                $goods[]= [
                    'goods_id' => $param['goodsId'][$key],
                    'goods_num' => $item,
                ];
            }
        }
        $operationLog = "申请了退款/退货，结果为：";
        $data['goods_content'] = json_encode($goods);
        $result = $this->order->refund($data);
        if ($result['status'] != 200) {
            return $this->arrayData('申请失败', '', '', 'error');
        } else {
            $operationLog .= "申请成功";
        }
        //插入操作信息
        BaiyangOrderOperationLogData::getInstance()->addOperationLog([
            'belong_sn' => $param['orderSn'],
            'belong_type' => 1,
            'content' => $operationLog,
            'operation_type' => 3,
            'operation_log' => json_encode($orderInfo),
        ]);
        $url = "/order/orderDetail?orderSn={$param['orderSn']}";
        return $this->arrayData('申请成功！', $url);
    }

    /**
     * 获取待发货订单数据
     * @param bool $isCheck
     * @param int $row
     * @return array
     * @author Chensonglu
     */
    public function guideInvoices($isCheck = false, $row = 5000)
    {
        if ($isCheck) {
            $count = BaseData::getInstance()->countData([
                'table' => 'Shop\Models\BaiyangOrder',
                'where' => "WHERE audit_state = 1 AND status = 'shipping' AND is_dummy = 0",
            ]);
            if (!$count) {
                return $this->arrayData('没有待发货订单', '', '', 'error');
            }
            return $this->arrayData("有 {$count} 条待发货订单");
        }
        $column = "order_sn,consignee,telephone,province,city,county,address,express,express_sn";
        $data = BaseData::getInstance()->getData([
            'column' => $column,
            'table' => 'Shop\Models\BaiyangOrder',
            'where' => "WHERE audit_state = 1 AND status = 'shipping' AND is_dummy = 0",
            'limit' => "LIMIT 0,{$row}"
        ]);
        $regionName = BaiyangRegionData::getInstance()->getRegionAll();
        foreach ($data as $key => $value) {
            $address = is_numeric($value['province']) && $value['province'] > 0
                ? $regionName[$value['province']] . ' ' : $value['province']. ' ';
            $address .= is_numeric($value['city']) && $value['city'] > 0
                ? $regionName[$value['city']] . ' ' : $value['city']  . ' ';
            $address .= is_numeric($value['county']) && $value['county'] > 0
                ? $regionName[$value['county']] . ' ' : $value['county'] . ' ';
            $address .= $value['address'];
            $value['address'] = $address;
            unset($value['province'], $value['city'], $value['county']);
            $data[$key] = $value;
        }
        return $data;
    }

    /**
     * 根据类型获取退款原因
     * @param int $type
     * @return array|bool
     * @author Chensonglu
     */
    public function getRefundReason($type = 1)
    {
        $result = BaseData::getInstance()->getData([
            'column' => 'reason_desc reason',
            'table' => 'Shop\Models\BaiyangRefundReason',
            'where' => 'WHERE type = :type:',
            'bind' => [
                'type' => $type,
            ],
        ]);
        if ($result){
            return array_column($result, 'reason');
        }
        return false;
    }

    /**
     * excel导出字段组函数
     * @return array
     * @author Zhudan
     */
    public function getExcelField(){
        $data = [];
        foreach($this->excel_group as $key=>$group){
           foreach($group['list'] as $value){
               $data[$key]['list'][$value] = $this->excel_header[$value]['text'];

           }
            $data[$key]['text'] = $group['text'];
        }
        return $data;
    }

    /**
     * 数据查询及整理、excel导出
     * @param $param
     * @param $search int 导出位置 0 普通订单列表导出 1 育学园订单列表导出
     * @return array
     * @author Zhudan
     */
    public function excelOrder($param, $search = 0)
    {
        $where = "";
        $join = "";
        $filed = "";
        $join_table = [];
        //是否导出商品相关字段
        $isExporGoods = 0;
        //整理字段开始
        if($param['export_title']){
            $goodsField = [
                'goods_type',
                'goods_name',
                'goods_id',
                'specifications',
                'unit_price',
                'goods_number',
                'promotion_total',
                'promotion_price'
            ];
            foreach($param['export_title'] as $key){
                if (in_array($key, $goodsField)) {
                    $isExporGoods = 1;
                }
                if(isset($this->excel_header[$key]) && $this->excel_header[$key]['join'] && isset($this->excel_header[$key]['table']) && (empty($join_table) || !in_array($this->excel_header[$key]['table'],$join_table))){
                    $join .= ' '.$this->excel_header[$key]['join'].' ';
                    $filed .= ', '.$this->excel_header[$key]['field'];
                    $join_table[] = $this->excel_header[$key]['table'];
                }else if($this->excel_header[$key]['field']){
                    $filed .= ', '.$this->excel_header[$key]['field'];
                }
            }
        }

        if($param['export_type'] == 'select_check'){
            //订单号
            if (isset($param['order_sn']) && $param['order_sn']) {
                $where .= " AND (o.order_sn = '{$param['order_sn']}' OR o.total_sn = '{$param['order_sn']}')";
            }
            //订单时间
            if (isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] && $param['end_time']) {
                $param['start_time'] = strtotime($param['start_time']);
                $param['end_time'] = strtotime($param['end_time']);
                $where .= " AND o.add_time BETWEEN {$param['start_time']} AND {$param['end_time']}";
            } elseif (isset($param['start_time']) && $param['start_time'] && (!isset($param['end_time']) || !$param['end_time'])) {
                $param['start_time'] = strtotime($param['start_time']);
                $where .= " AND o.add_time >= {$param['start_time']}";
            } elseif (isset($param['end_time']) && $param['end_time'] && (!isset($param['start_time']) || !$param['start_time'])) {
                $param['end_time'] = strtotime($param['end_time']);
                $where .= " AND o.add_time <= {$param['end_time']}";
            }
            //订单类型
            if (isset($param['order_type']) && isset($this->orderType[$param['order_type']])) {
                if ($this->orderType[$param['order_type']] != 2) {
                    $where .= " AND o.order_type = {$this->orderType[$param['order_type']]} AND o.callback_phone <> ''";
                } else {
                    $where .= " AND o.order_type = 0 AND o.callback_phone <> ''";
                }
            }
            //发货商家
            if (isset($param['shop_id']) && $param['shop_id']) {
                $where .= " AND o.shop_id = {$param['shop_id']}";
            }
            //收货人手机
            if (isset($param['telephone']) && $param['telephone']) {
                $where .= " AND o.telephone = '{$param['telephone']}'";
            }
            //下单终端
            if (isset($param['channel_subid']) && isset($this->orderTerminal[$param['channel_subid']])) {
                $where .= " AND o.channel_subid = {$param['channel_subid']}";
            }
            //是否关联user表
            $isJoinUser = 0;
            //用户名
            if (isset($param['username']) && $param['username']) {
                $where .= " AND u.username LIKE '%".$param['username']."%'";
                $isJoinUser = 1;
            }
            //手机号
            if (isset($param['phone']) && $this->func->isPhone($param['phone'])) {
                $where .= " AND (u.phone = {$param['phone']} OR u.user_id = {$param['phone']})";
                $isJoinUser = 1;
            }
            $join .= $isJoinUser && !isset($join_table['baiyang_user'])? " INNER JOIN baiyang_user u ON o.user_id = u.id" : '';
            //是否关联orderDetail表
            $isJoinOrderDetail = 0;
            //商品ID或商品名称
            if (isset($param['goods_name']) && $param['goods_name'] ) {
                $where .= " AND od.goods_name LIKE '%".$param['goods_name']."%'";
                $isJoinOrderDetail = 1;
            }
            if (isset($param['goods_id']) && is_numeric($param['goods_id']) && $param['goods_id']) {
                $where .= " AND od.goods_id = {$param['goods_id']}";
                $isJoinOrderDetail = 1;
            }
            $join .= $isJoinOrderDetail &&  !in_array('baiyang_order_detail',$join_table)? " LEFT JOIN baiyang_order_detail od ON o.order_sn = od.order_sn" : '';
            //支付方式
            if (isset($param['payment_id']) && isset($this->orderPayment[$param['payment_id']])) {
                $where .= " AND o.payment_id = {$param['payment_id']}";
            }
            //配送方式
            if (isset($param['express_type']) && isset($this->orderDelivery[$param['express_type']])) {
                $where .= " AND o.express_type = {$this->orderDelivery[$param['express_type']]}";
            }
            //育学园导出
            if ($search == 1) {
                //是否有搜索条件
                $isSearch = 0;
                //用户类型
                if (isset($param['userType']) && $param['userType']) {
                    $where .= $param['userType'] == 'yukon'
                        ? " AND o.more_platform_sign = 'yukon'" : " AND o.more_platform_sign <> 'yukon'";
                    $isSearch = 1;
                }
                //育学园门店ID / 默认为生产育学园门店ID
                $yukonShopId = isset($this->config->yxy_shop_id)
                    ? $this->config->yxy_shop_id[$this->config->environment] : 100009;
                //商品类型
                if (isset($param['goodsType']) && $param['goodsType']) {
                    $where .= $param['goodsType'] == 'yukon'
                        ? " AND o.shop_id = {$yukonShopId}" : " AND o.shop_id <> {$yukonShopId}";
                    $isSearch = 1;
                }
                if (!$isSearch) {
                    $where .= " AND (o.shop_id = {$yukonShopId} OR o.more_platform_sign = 'yukon')";
                }
            }

            //订单来源
            if (isset($param['order_source']) && isset($this->orderSource[$param['order_source']]) ) {
                if ($param['order_source'] == 1) {
                    if( !in_array('baiyang_prescription',$join_table)){
                        $join .= " LEFT JOIN baiyang_prescription p ON o.total_sn = p.order_id";
                    }
                    $where .= " AND ( p.order_id IS NULL OR p.order_id='0') ";
                } elseif($param['order_source'] == 3) {
                    $where .= " AND o.more_platform_sign = 'yukon'";
                }else {
                    if( !in_array('baiyang_prescription',$join_table)){
                        $join .= " INNER JOIN baiyang_prescription p ON o.total_sn = p.order_id";
                    }
                    $where .= " AND p.prescription_id IS NOT NULL AND  p.order_id<>'0' AND p.order_id<>'' ";
                }
            }

            //是否关联orderGoodsReturnReason表
            $isJoinOrderReturn = 0;
            //订单状态
            if (isset($param['searchType']) && $param['searchType'] && $param['searchType'] != 'all'){
                if ($param['searchType'] == 'toAudit') {
                    $where .= " AND o.audit_state = 0 AND o.status = 'paying'";
                } elseif ($param['searchType'] == 'tradingClosed') {
                    $where .= " AND o.audit_state = 1 AND (o.status = 'canceled' OR (o.status = 'refund' AND orr.status IN (1,3)))";
                    $isJoinOrderReturn = 1;
                } elseif ($param['searchType'] == 'aRefundOf') {
                    $where .= " AND o.audit_state = 1 AND o.status = 'refund' AND orr.status NOT IN (1,3)";
                    $isJoinOrderReturn = 1;
                } elseif ($param['searchType'] == 'finished' && $search == 1) {
                    $where .= " AND o.audit_state = 1 AND o.status IN ('evaluating','finished')";
                } else {
                    $where .= " AND o.audit_state = 1 AND o.status = '{$param['searchType']}'";
                }
            }
            $join .= $isJoinOrderReturn && !in_array('baiyang_order_goods_return_reason',$join_table)? " LEFT JOIN baiyang_order_goods_return_reason orr ON o.order_sn = orr.order_sn" : '';
        }

        //育学园导出全部订单情况
        if($param['export_type'] == 'all' && $search == 1){
            $where .= " AND o.is_dummy = 0";
            //育学园门店ID / 默认为生产育学园门店ID
            $yukonShopId = isset($this->config->yxy_shop_id)
                ? $this->config->yxy_shop_id[$this->config->environment] : 100009;
            $where .= " AND (o.shop_id = {$yukonShopId} OR o.more_platform_sign = 'yukon')";
        }
        $orderData = BaiyangOrderData::getInstance();

        $column = ' o.order_sn '.$filed;
        $group_by = 'group by o.order_sn';
        $group_by = $isExporGoods ? '' : $group_by;
        $order_by = 'ORDER BY o.add_time DESC';
        //数据
        $totalOrder = $orderData->getTotalOrderExcel($column,$where,$join,$group_by,$order_by,"LIMIT 0,2000");
        if($totalOrder){
            $is_have_address = false;
            if(in_array('province',$param['export_title']) ){
                $is_have_address = true;
            }

            if(in_array('city',$param['export_title']) ){
                $is_have_address = true;
            }

            if(in_array('county',$param['export_title']) ){
                $is_have_address = true;
            }

            if($is_have_address){
                $region = BaiyangRegionData::getInstance()->getRegionAll();
            }

            if(in_array('invoice_info',$param['export_title']) || in_array('invoice_type',$param['export_title']) || $is_have_address || in_array('order_type', $param['export_title'])){
                foreach($totalOrder as & $order){
                    if (isset($order['callback_phone']) && $order['callback_phone']) {
                        $order['order_type'] = '处方订单';
                    }

                    if(isset($order['invoice_info'])  && !empty($order['invoice_info'])){
                        $invoice_info = json_decode($order['invoice_info'],true);
                        $order['invoice_info'] = $invoice_info['content_type'];
                        $order['invoice_rise'] = $invoice_info['title_name'];
                    }

                    if(isset($order['province']) && $order['province']){
                        $order['province'] = isset($region[$order['province']]) ? $region[$order['province']] : $order['province'];
                    }
                    if(isset($order['city']) && $order['city']){
                        $order['city'] = isset($region[$order['city']]) ? $region[$order['city']] : $order['city'];
                    }
                    if(isset($order['county']) && $order['county']){
                        $order['county'] = isset($region[$order['county']]) ? $region[$order['county']] : $order['county'];
                    }
                }
            }

            $this->downExcel($totalOrder);
        }else{
            return $totalOrder;
        }

    }

    /**
     * 导出
     * @param $list array 导出的数据
     * @author Zhudan
     */
    public function downExcel($list){

        $head_title = array_keys($list[0]);
        $obj = \Shop\Libs\Excel::getInstance();
        $headArray = [];
        foreach($head_title as $key){
                $headArray[] = $this->excel_header[$key]['text'];
        }
        $obj->exportExcel($headArray,$list,'订单导出列表','订单列表');

    }

    /**
     * 获取普通订单列表数据
     * @param $param
     * @return array
     * @author Chensonglu
     */
    public function getYukonOrder($param)
    {
        $where = "";
        $join = "";
        //订单号
        if (isset($param['order_sn']) && $param['order_sn']) {
            $where .= " AND o.order_sn = '{$param['order_sn']}'";
        }
        //订单时间
        if (isset($param['start_time']) && isset($param['end_time']) && $param['start_time'] && $param['end_time']) {
            $param['start_time'] = strtotime($param['start_time']);
            $param['end_time'] = strtotime($param['end_time']);
            $where .= " AND o.add_time BETWEEN {$param['start_time']} AND {$param['end_time']}";
        } elseif (isset($param['start_time']) && $param['start_time'] && (!isset($param['end_time']) || !$param['end_time'])) {
            $param['start_time'] = strtotime($param['start_time']);
            $where .= " AND o.add_time >= {$param['start_time']}";
        } elseif (isset($param['end_time']) && $param['end_time'] && (!isset($param['start_time']) || !$param['start_time'])) {
            $param['end_time'] = strtotime($param['end_time']);
            $where .= " AND o.add_time <= {$param['end_time']}";
        }
        //是否有搜索条件
        $isSearch = 0;
        //用户类型
        if (isset($param['userType']) && $param['userType']) {
            $where .= $param['userType'] == 'yukon'
                ? " AND o.more_platform_sign = 'yukon'" : " AND o.more_platform_sign <> 'yukon'";
            $isSearch = 1;
        }
        //育学园门店ID / 默认为生产育学园门店ID
        $yukonShopId = isset($this->config->yxy_shop_id)
            ? $this->config->yxy_shop_id[$this->config->environment] : 100009;
        //商品类型
        if (isset($param['goodsType']) && $param['goodsType']) {
            $where .= $param['goodsType'] == 'yukon'
                ? " AND o.shop_id = {$yukonShopId}" : " AND o.shop_id <> {$yukonShopId}";
            $isSearch = 1;
        }
        if (!$isSearch) {
            $where .= " AND (o.shop_id = {$yukonShopId} OR o.more_platform_sign = 'yukon')";
        }
        //是否关联orderGoodsReturnReason表
        $isJoinOrderReturn = 0;
        //订单状态
        if (isset($param['searchType']) && $param['searchType'] && $param['searchType'] != 'all'){
            if ($param['searchType'] == 'tradingClosed') {
                $where .= " AND o.audit_state = 1 AND (o.status = 'canceled' OR (o.status = 'refund' AND orr.status = 3))";
                $isJoinOrderReturn = 1;
            } elseif ($param['searchType'] == 'aRefundOf') {
                $where .= " AND o.audit_state = 1 AND o.status = 'refund' AND orr.status NOT IN (1,3)";
                $isJoinOrderReturn = 1;
            } elseif ($param['searchType'] == 'finished') {
                $where .= " AND o.audit_state = 1 AND o.status IN ('evaluating','finished')";
            } else {
                $where .= " AND
                 o.audit_state = 1 AND o.status = '{$param['searchType']}'";
            }
        }
        $join .= $isJoinOrderReturn ? " LEFT JOIN baiyang_order_goods_return_reason orr ON o.order_sn = orr.order_sn" : '';
        $orderData = BaiyangOrderData::getInstance();
        //总记录数
        $counts = $orderData->getOrderNum($where, $join, false);
        //分页
        $pages['psize'] = isset($param['psize']) && $param['psize'] ? (int)$param['psize'] : 15;//每页显示条数
        $pages['page'] = isset($param['page']) && $param['page'] ? (int)$param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $pages['isShow'] = true;
        $page = $this->page->pageDetail($pages);
        //数据
        $orderSn = $orderData->getTotalOrderAll($where,$join,"", "ORDER BY o.add_time DESC","LIMIT {$page['record']},{$page['psize']}");
        if (!$orderSn){
            return false;
        }
        $orderSn = array_column($orderSn, 'order_sn');
        //订单信息
        $getOrderInfo = $this->getOrderInfoAll($orderSn, false);
        $result['orderList'] = [];
        foreach ($orderSn as $value) {
            if (isset($getOrderInfo[$value])) {
                //获取用户手机号
                $user_id = intval($getOrderInfo[$value]['user_id']);
                $getOrderInfo[$value]['phone'] = BaiyangUserData::getInstance()->findPhoneByUserId($user_id)['phone'];
                $result['orderList'][] = $getOrderInfo[$value];
            }
        }
        $result['page'] = $page['page'];
        return $result;
    }
}