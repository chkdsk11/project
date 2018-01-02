<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/22
 * Time: 15:00
 */

namespace Shop\Services;

use Shop\Datas\BaiyangOrderData;
use Shop\Datas\BaiyangOrderDetailData;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyangOrderGoodsReturnReasonData;
use Shop\Datas\BaiyangOrderOperationLogData;
use Shop\Datas\BaiyangProductRuleData;

use Shop\Home\Listens\{
    BalanceListener,StockListener,ExpressListener
};

use Phalcon\Events\{
    Manager as EventsManager, Event
};

class RefundService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new RefundService();
        }

        //实例化事件管理器
        $eventsManager= new EventsManager();

        //开启事件结果回收
        $eventsManager->collectResponses(true);

        /********************************侦听器*********************************/
        $eventsManager->attach('balance',new BalanceListener());
        $eventsManager->attach('stock',new StockListener());
        $eventsManager->attach('express',new ExpressListener());
        /*********************侦听器************************/

        //给当前服务配置事件侦听
        static::$instance->setEventsManager($eventsManager);
        return static::$instance;
    }

    //退款状态
    public $refundState = [
        '申请待处理' => 0,
        '已拒绝退款' => 1,
        '待卖家退款' => 2,
        '退款成功' => 3,
        '待买家发货' => 4,
        '待卖家收货' => 5,
        '已撤销申请' => 6,
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
    //订单状态
    public $orderStat = [
        'paying' => '待付款',
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '交易完成',
        'refund' => '退款/售后',
        'canceled' => '交易关闭',
        'finished' => '交易完成'
    ];

    public $excel_header = [
        'service_sn'=>['text'=>'服务单号','field'=>'ogrr.service_sn','join'=>''],

        'order_sn'=>['text'=>'订单编号','field'=>'o.order_sn','join'=>''],
        'shop_name'=>['text'=>'店铺','field'=>'ogrr.shop_name','join'=>''],
//        'shop_id'=>['text'=>'店铺','field'=>"sku_supplier.name as shop_id",'join'=>'LEFT JOIN baiyang_sku_supplier as sku_supplier on sku_supplier.id=o.shop_id','table'=>'baiyang_sku_supplier'],
        'return_type'=>['text'=>'退款类型','field'=>"CASE ogrr.return_type
                    WHEN 0 THEN
                            '取消'
                        WHEN 1 THEN
                            '仅退款'
                        WHEN 2 THEN
                            '退货退款'
                        END AS `return_type`",
                         'join'=>''],
        'refundStatus'=>['text'=>'服务单号','field'=>"CASE ogrr.`status`
                        WHEN 0 THEN
                            '申请待处理'
                        WHEN 1 THEN
                            '已拒绝申请'
                        WHEN 2 THEN
                            '待卖家退款'
                        WHEN 3 THEN
                            '退款完成'
                        WHEN 4 THEN
                            '待买家发货'
                        WHEN 5 THEN
                            '待卖家收货'
                        WHEN 6 THEN
                            '已撤销申请'
                        WHEN 7 THEN
                            '待卖家退款'
                        END AS `refundStatus`",
            'join'=>''],
            'reason'=>['text'=>'退款/退货原因','field'=>'ogrr.reason','join'=>''],
            'remark'=>['text'=>'审核不通过原因','field'=>'ogrr.remark','join'=>''],
            'explain'=>['text'=>'原因描述','field'=>'ogrr.`explain`','join'=>''],
            'images'=>['text'=>'上传照片','field'=>'ogrr.images','join'=>''],
            'refund_amount'=>['text'=>'应退金额','field'=>'ogrr.refund_amount','join'=>''],
            'username'=>['text'=>'申请人','field'=>'u.username','join'=>'left join baiyang_user as u on ogrr.user_id=u.id','table'=>'baiyang_user'],
             'express_company'=>['text'=>'退货物流公司','field'=>'ogrr.express_company','join'=>''],
             'express_no'=>['text'=>'退货物流单号','field'=>'ogrr.express_no','join'=>''],
             'serv_nickname'=>['text'=>'审核人','field'=>'ogrr.serv_nickname','join'=>''],
             'update_time'=>['text'=>'审核时间','field'=>"if(ogrr.update_time>0,FROM_UNIXTIME(ogrr.update_time) ,'') as update_time",'join'=>''],
             'real_amount'=>['text'=>'实退款金额','field'=>'ogrr.real_amount','join'=>''],
             'goods_id'=>['text'=>'商品编号','field'=>'od.goods_id','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
             'goods_name'=>['text'=>'商品名称','field'=>'od.goods_name','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
             'specifications'=>['text'=>'规格','field'=>'od.specifications','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
             'unit_price'=>['text'=>'商品单价','field'=>'od.unit_price','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
             'refund_goods_number'=>['text'=>'数量','field'=>'ogr.refund_goods_number','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
             'price'=>['text'=>'小计','field'=>'od.price','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
             'promotion_total'=>['text'=>'实付价格','field'=>'od.promotion_total','join'=>'left join baiyang_order_goods_return as ogr on ogr.reason_id=ogrr.id left join baiyang_order_detail as od on ogr.order_goods_id=od.id','table'=>'baiyang_order_detail'],
//             'promotion_total'=>['text'=>'附属赠品','field'=>'order_goods_return.promotion_total','join'=>'left join baiyang_order_goods_return as order_goods_return on order_goods_return.reason_id=ogrr.id left join baiyang_order_detail as od on order_goods_return.order_goods_id=od.id','table'=>'baiyang_order_detail'],

    ];

    public $excel_group = [
        'group_1'=>['text'=>'基本信息','list'=>[

            'order_sn',
            'shop_name',
            'return_type',
            'refundStatus',
            'reason',
            'explain',
            'images',
            'refund_amount',
            'username',
            'express_company',
            'express_no',
            'remark',
            'serv_nickname',
            'update_time',
            'real_amount',
        ]],
        'group_2'=>['text'=>'退款商品信息','list'=>[
            'goods_id',
            'goods_name',
            'specifications',
            'unit_price',
            'refund_goods_number',
            'price',
            'promotion_total',
        ]],

    ];

    private $statusText = [
        0 => '您的服务单已申请，待客服审核中',
        1 => '您的申请不通过，如有疑问可以咨询客服',
        2 => '您的申请已通过，退款处理中',
        3 => '您的服务单已退款，请注意查收',
        4 => '您的申请已受理，请在7天内寄回商品并提交物流，过期讲自动取消申请，如有疑问请与客服联系',
        5 => '物流信息已提交，客服会在收到商品后处理退款',
        6 => '您的服务单已取消',
        7 => '您服务单的商品已收到，等待财务审核',
    ];

    /**
     * 服务单详情
     * @param $serviceSn
     * @return bool
     * @author Chensonglu
     */
    public function getRefundDetail($serviceSn)
    {
        $refundInfo = $this->getRefundInfo($serviceSn);
        if (!$refundInfo) {
            return false;
        }
        //服务单商品
        $refundInfo['products'] = $this->getServiceDetail($serviceSn);
        //服务单物流
        $refundInfo['logistics'] = $this->getRefundLogistics($refundInfo);
        //操作日志
        $refundInfo['operationLog'] = [];
        $operationLog = BaiyangOrderOperationLogData::getInstance()->getOperationLog([
            'serviceSn'=>$serviceSn
        ]);
        //操作日志格式调整
        if ($operationLog) {
            $addTimeCount = array_count_values(array_column($operationLog,'add_time'));
            $operationRemark = [];
            foreach ($operationLog as $val) {
                if ($addTimeCount[$val['add_time']] > 1 && $val['operation_type'] == 1){
                    $operationRemark[$val['add_time']] = $val['content'];
                } else {
                    $refundInfo['operationLog'][] = $val;
                }
            }
            if ($operationRemark) {
                foreach ($refundInfo['operationLog'] as $key => $item) {
                    if (isset($operationRemark[$item['add_time']])) {
                        $item['content'] .= "，备注：" . $operationRemark[$item['add_time']];
                        $refundInfo['operationLog'][$key] = $item;
                    }
                }
            }
        }
        return $refundInfo;
    }

    /**
     * 修改服务单状态
     * @param unknown $param
     * @author Chensonglu
     */
    public function changeRefundState($param)
    {
        if (!isset($param['serviceSn']) || !$param['serviceSn'] || !isset($param['isUpdate'])) {
            return $this->arrayData('参数错误','',$param,'error');
        }
        //获取服务单信息
        $refundInfo = $this->getRefundInfo($param['serviceSn']);
        //返回数据
        if (!$refundInfo) {
            return $this->arrayData('该服务单不存在','',$param,'error');
        }
        if (!$param['isUpdate']) {
            return $this->arrayData('可修改退款状态','',$refundInfo);
        }
        if (!isset($param['state']) || !isset($this->statusText[$param['state']]) || $param['state'] == $refundInfo['refundState']) {
            return $this->arrayData('请选择退款状态','',$param,'error');
        }
        $time = time();
        $set = " update_time = :upTime:,status = :status:";
        $data = [
            'serviceSn' => $param['serviceSn'],
            'upTime' => $time,
            'status' => $param['state'],
        ];
        // 开启事务
        $this->dbWrite->begin();
        //更新服务单信息
        $baseData = BaseData::getInstance();
        $refundUp = $baseData->update($set,'Shop\Models\BaiyangOrderGoodsReturnReason',$data,'service_sn = :serviceSn:');
        if (!$refundUp) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','更新服务单失败','error');
        }
        if (in_array($param['state'], [1,6]) && !in_array($refundInfo['refundState'], [1,6])) {
            $refundProducts = $this->getServiceDetail($param['serviceSn']);
            $isError = 0;
            //还原订单退款累计数量
            foreach ($refundProducts as $product) {
                $num = ($product['refund_goods_number'] - $product['refundNum']);
                $orderDetailUp = $baseData->update("refund_goods_number = :refundNum:,is_refund = :isRefund:",'Shop\Models\BaiyangOrderDetail',[
                    'id' => $product['order_goods_id'],
                    'refundNum' => $num > 0 ? $num : 0,
                    'isRefund' => 0,
                ],'id = :id:');
                if (!$orderDetailUp) {
                    $this->dbWrite->rollback();
                    $isError = 1;
                    break;
                }
            }
            if ($isError) {
                return $this->arrayData("修改失败",'','商品退款数量累计更新失败','error');
            }
        } elseif (!in_array($param['state'], [1,6]) && in_array($refundInfo['refundState'], [1,6])) {
            $refundProducts = $this->getServiceDetail($param['serviceSn']);
            $isError = 0;
            //还原订单退款累计数量
            foreach ($refundProducts as $product) {
                $num = ($product['refund_goods_number'] + $product['refundNum']);
                $orderDetailUp = $baseData->update("refund_goods_number = :refundNum:,is_refund = :isRefund:",'Shop\Models\BaiyangOrderDetail',[
                    'id' => $product['order_goods_id'],
                    'refundNum' => $num > $product['goods_number'] ? $product['goods_number'] : $num,
                    'isRefund' => $num == $product['goods_number'] ? 1 : 0,
                ],'id = :id:');
                if (!$orderDetailUp) {
                    $this->dbWrite->rollback();
                    $isError = 1;
                    break;
                }
            }
            if ($isError) {
                return $this->arrayData("修改失败",'','商品退款数量累计更新失败','error');
            }
        }
        //是否恢复库存
        $isRecover = $refundInfo['refundState'] == 3 ? false : true;
        if (($param['state'] == 3 && $refundInfo['refundState'] != 3) || (in_array($param['state'], [1,6]) && $refundInfo['refundState'] == 3)) {
            //恢复库存
            $changeStock = $this->_eventsManager->fire('stock:recoverStockAndSaleNumber', $this, [
                'serviceSn' => $param['serviceSn'],
                'isRecover' => $isRecover,
            ]);
            if (!$changeStock) {
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','','库存减去失败','error');
            }
        }
        //插入操作信息
        $operationLog = BaiyangOrderOperationLogData::getInstance()->addOperationLog([
            'belong_sn' => $param['serviceSn'],
            'belong_type' => 2,
            'content' => '修改退款状态',
            'operation_type' => 4,
            'operation_log' => json_encode($refundInfo),
        ]);
        if (!$operationLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','操作信息插入失败','error');
        }
        $content = isset($this->statusText[$param['state']])
            ? $this->statusText[$param['state']] : '您的服务单处理中';
        //添加服务单记录
        $addServiceLog = $baseData->insert('Shop\Models\BaiyangOrderServiceLog', [
            'service_sn' => $param['serviceSn'],
            'status' => $param['state'],
            'log_content' => json_encode([$content, date('Y-m-d H:i:s', $time)]),
            'operator_id' => $this->session->get('admin_id'),
            'add_time' => $time,
            'operator_name' => $this->session->get('admin_account'),
        ]);
        if (!$addServiceLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','服务单记录插入失败','error');
        }
        $this->dbWrite->commit();
        return $this->arrayData('修改成功','',[
            'username' => $this->session->get('admin_account'),
            'time' => date('Y-m-d H:i:s'),
            'content' => '修改退款状态'
        ]);
    }

    /**
     * 服务单退货物流
     * @param $param
     *              - express_no string 物流单号
     * @param int $sort 排序类型
     * @return array|bool
     * @author Chensonglu
     */
    public function getRefundLogistics($param, $sort = SORT_ASC)
    {
        //物流单号
        if (!isset($param['express_no']) || !$param['express_no']) {
            return false;
        }
        //获取数量信息
        $result = $this->_eventsManager->fire('express:getLogistics', $this, [
            'postid' => $param['express_no'],
        ]);
        if ($result['error']) {
            return false;
        }
        $result = $result['data']['list'];
        //整理格式
        $express = [];
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
            $express[] = $value;
        }
        return $express;
    }

    /**
     * 收货
     * @param $param
     *              - serviceSn string 服务单号
     *              - isReceiving int 是否确定收货
     * @return array
     * @author Chensonglu
     */
    public function receivingGoods($param)
    {
        //服务单号
        if (!isset($param['serviceSn']) || !$param['serviceSn']) {
            return $this->arrayData('请选择服务单','',$param,'error');
        }
        //是否发货
        if (!isset($param['isReceiving'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        //获取服务单信息
        $refundInfo = $this->getRefundInfo($param['serviceSn']);
        //返回数据
        if (!$refundInfo) {
            return $this->arrayData('该服务单不存在','',$param,'error');
        }
        //服务单不是待收货状态
        if (!in_array($refundInfo['refundState'], [4,5])) {
            return $this->arrayData('该服务单不用收货','',$param,'error');
        }
        //获取服务单商品信息
        $refundInfo['products'] = $this->getServiceDetail($param['serviceSn']);
        if (!$param['isReceiving']) {
            return $this->arrayData('可收货','',$refundInfo);
        }
        //第三方支付订单需财务审核 7
        $status = in_array($refundInfo['payment_id'], [3,4,7]) ? 2 : 7;
        $time = time();
        $set = " update_time = :upTime:,status = :status:";
        $data = [
            'serviceSn' => $param['serviceSn'],
            'upTime' => $time,
            'status' => $status,
        ];
        // 开启事务
        $this->dbWrite->begin();
        //更新服务单信息
        $baseData = BaseData::getInstance();
        $refundUp = $baseData->update($set,'Shop\Models\BaiyangOrderGoodsReturnReason',$data,'service_sn = :serviceSn:');
        if (!$refundUp) {
            $this->dbWrite->rollback();
            return $this->arrayData('收货失败','','更新服务单失败','error');
        }
        unset($refundInfo['products']);
        //插入操作信息
        $operationLog = BaiyangOrderOperationLogData::getInstance()->addOperationLog([
            'belong_sn' => $param['serviceSn'],
            'belong_type' => 2,
            'content' => '确认了收货',
            'operation_type' => 4,
            'operation_log' => json_encode($refundInfo),
        ]);
        if (!$operationLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('收货失败','','操作信息插入失败','error');
        }
        $content = in_array($refundInfo['payment_id'], [3,4,7])
            ? "您服务单的商品已收到，退款处理中" : "您服务单的商品已收到，等待财务审核";
        //添加服务单记录
        $addServiceLog = $baseData->insert('Shop\Models\BaiyangOrderServiceLog', [
            'service_sn' => $param['serviceSn'],
            'status' => $status,
            'log_content' => json_encode([$content, date('Y-m-d H:i:s', $time)]),
            'operator_id' => $this->session->get('admin_id'),
            'add_time' => $time,
            'operator_name' => $this->session->get('admin_account'),
        ]);
        if (!$addServiceLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('收货失败','','服务单记录插入失败','error');
        }
        $this->dbWrite->commit();
        return $this->arrayData('成功收货');
    }

    /**
     * 服务单退款操作
     * @param $param
     *              - serviceSn string 服务单号
     *              - isConfirm int 是否退款操作
     *              - disagreen int 是否拒绝退款
     *              - amountSel string 退款金额选项
     *              - partAmount float 退款金额
     *              - remark string 备注
     * @return array
     * @author Chensonglu
     */
    public function confirmRefund($param)
    {
        //服务单号
        if (!isset($param['serviceSn']) || !$param['serviceSn']) {
            return $this->arrayData('请选择服务单','',$param,'error');
        }
        //时候退款操作
        if (!isset($param['isConfirm'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        //获取服务单信息
        $refundInfo = $this->getRefundInfo($param['serviceSn']);
        if (!$refundInfo) {
            return $this->arrayData('该服务单不存在','',$param,'error');
        }
        //校验服务单是否待退款状态 2、7
        if ($refundInfo['refundState'] == 3) {
            return $this->arrayData('该服务单已退款完成','',$refundInfo,'error');
        } elseif ($refundInfo['refundState'] == 1) {
            return $this->arrayData('该服务单已拒绝退款','',$refundInfo,'error');
        }
        //获取服务单商品信息
        $refundInfo['products'] = $this->getServiceDetail($param['serviceSn']);
        if (!$param['isConfirm']) {
            return $this->arrayData('可退款','',$refundInfo);
        }
        //备注
        if (isset($param['remark']) && (strlen(trim($param['remark'])) > 200)) {
            return $this->arrayData('备注内容不能超过200个字符','',$param,'error');
        }
        //是否拒绝退款
        $isRefuse = isset($param['disagreen']) && $param['disagreen'] ? 1 : 0;
        $time = time();
        $set = " update_time = :upTime:";
        $data = [
            'serviceSn' => $param['serviceSn'],
            'upTime' => $time,
        ];
        // 开启事务
        $this->dbWrite->begin();
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        $baseData = BaseData::getInstance();
        //余额部分
        $balancePrice = 0;
        //实际退款金额
        $realAmount = 0;
        if ($isRefuse){
            $set .= ",status = :status:";
            $data['status'] = 1;
            $text = "您的服务单拒绝退款";
            $content = "拒绝了退款";
            $isError = 0;
            //还原订单退款累计数量
            foreach ($refundInfo['products'] as $product) {
                $num = ($product['refund_goods_number'] - $product['refundNum']);
                $orderDetailUp = $baseData->update("refund_goods_number = :refundNum:,is_refund = :isRefund:",'Shop\Models\BaiyangOrderDetail',[
                    'id' => $product['order_goods_id'],
                    'refundNum' => $num > 0 ? $num : 0,
                    'isRefund' => 0,
                ],'id = :id:');
                if (!$orderDetailUp) {
                    $this->dbWrite->rollback();
                    $isError = 1;
                    break;
                }
            }
            if ($isError) {
                return $this->arrayData("拒绝退款失败",'','商品退款数量累计更新失败','error');
            }
        } else {
            $content = "确认了退款，退款金额共";
            $text = "您的服务单已退款，请注意查收";
            if (!isset($param['amountSel']) || !$param['amountSel']) {
                return $this->arrayData('请选择退款金额','',$param,'error');
            }
            $set .= ",status = :status:";
            $data['status'] = 3;
            if ($param['amountSel'] == 'part') {
                //部分退款
                if (!isset($param['partAmount'])) {
                    return $this->arrayData('请输入退款金额','',$param,'error');
                }
                if ($param['partAmount'] > $refundInfo['refund_amount']) {
                    return $this->arrayData('退款金额要小于应退金额','',$param,'error');
                }
                $realAmount = $param['partAmount'];
            } elseif ($param['amountSel'] == 'all') {
                //全额退款
                $realAmount = $refundInfo['refund_amount'];
            }
            $content .= $realAmount;
            $set .= ",real_amount = :amount:";
            $data['amount'] = $realAmount;
            //可退余额
            $balancePrice = $refundInfo['balance_price'];
            $balancePrice = $realAmount >= $balancePrice ? $balancePrice : $realAmount;
            //第三方支付退款金额
            $set .= ",pay_fee = :payFee:";
            $data['payFee'] = !in_array($refundInfo['payment_id'],[3,4,7]) && $realAmount > $balancePrice
                ? ($realAmount - $balancePrice) : 0;

            //退优惠券
            if (($refundInfo['user_coupon_price'] > 0) && $this->isRefundCoupon($refundInfo, $realAmount)) {
                $refundCoupon = BaseData::getInstance()->update("is_used = 0,used_time = 0,order_sn = ''",'Shop\Models\BaiyangCouponRecord',
                    [
                        'order_sn'=>$refundInfo['total_sn'],
                        'user_id'=>$refundInfo['user_id'],
                    ],'order_sn=:order_sn: and user_id=:user_id: ');
                if (!$refundCoupon) {
                    $this->dbWrite->rollback();
                    return $this->arrayData('退款失败','','优惠券返还失败','error');
                }
            }
            //恢复库存
            $changeStock = $this->_eventsManager->fire('stock:recoverStockAndSaleNumber', $this, [
                'serviceSn' => $param['serviceSn'],
            ]);
            if (!$changeStock) {
                $this->dbWrite->rollback();
                return $this->arrayData('退款失败','','库存恢复失败','error');
            }
            $orderSet = "";
            $orderData = [];
            if (in_array($refundInfo['orderStatus'], ['待发货','已发货'])) {
                $orderSet = " status = :state:,last_status = :lastState:,is_refund = :isRefund:";
                $orderData['state'] = "refund";
                $orderData['lastState'] = ($refundInfo['orderStatus'] == '待发货') ? 'shipping' : 'shipped';
                $orderData['isRefund'] = 1;
            } elseif ($this->refundAllGoods($refundInfo['order_sn'])) {
                $orderSet = " is_refund = :isRefund:";
                $orderData['isRefund'] = 1;
            }
            if ($orderData && $orderSet) {
                $orderData['orderSn'] = $refundInfo['order_sn'];
                $orderUp = $baseData->update($orderSet, "Shop\Models\BaiyangOrder", $orderData, 'order_sn = :orderSn:');
                if (!$orderUp) {
                    $this->dbWrite->rollback();
                    return $this->arrayData('退款失败','','cpsOrder更新失败','error');
                }
            }
            //更新订单返利状态
            $cpsOrderUp = $baseData->update(" order_status = :state:","Shop\Models\BaiyangCpsOrderLog",[
                'orderSn' => $refundInfo['order_sn'],
                'state' => "refund",
            ],'order_sn = :orderSn:');
            if (!$cpsOrderUp) {
                $this->dbWrite->rollback();
                return $this->arrayData('退款失败','','cpsOrder更新失败','error');
            }
            //订单商品是否全部退完更新
            $isError = 0;
            foreach ($refundInfo['products'] as $product) {
                if ($product['goods_number'] == $product['refund_goods_number']) {
                    $orderDetailUp = $baseData->update("is_return = :isReturn:",'Shop\Models\BaiyangOrderDetail',[
                        'id' => $product['order_goods_id'],
                        'isReturn' => 1
                    ],'id = :id:');
                    if (!$orderDetailUp) {
                        $this->dbWrite->rollback();
                        $isError = 1;
                        break;
                    }
                }
            }
            if ($isError) {
                return $this->arrayData("退款失败",'','商品退款数量累计更新失败','error');
            }
        }
        //服务单退款备注
        if (isset($param['remark']) && trim($param['remark'])) {
            if ($isRefuse) {
                $set .= ",remark = :remark:";
                $data['remark'] = trim($param['remark']);
            }
            //插入信息
            $remarkData = [
                'belong_sn' => $param['serviceSn'],
                'belong_type' => 2,
                'content' => trim($param['remark']),
                'operation_type' => 1,
            ];
            $addRemark = $operationLogData->addOperationLog($remarkData);
            if (!$addRemark) {
                $this->dbWrite->rollback();
                return $this->arrayData("退款失败",'','备注添加失败','error');
            }
        }
        $mseText = $isRefuse ? "拒绝退款" : "退款";
        //更新服务单信息
        $refundUp = $baseData->update($set,'Shop\Models\BaiyangOrderGoodsReturnReason',$data,'service_sn = :serviceSn:');
        if (!$refundUp) {
            $this->dbWrite->rollback();
            return $this->arrayData("{$mseText}失败",'','更新服务单失败','error');
        }
        //计算银联支付部分
        $unionPay = ($refundInfo['payment_id'] == 6) ? ($realAmount - $balancePrice) : 0;
        //计算货到付款现金部分
        $cash = ($refundInfo['payment_id'] == 3 && $refundInfo['return_type'] == 2)
            ? ($realAmount - $balancePrice) : 0;
        $content .= $balancePrice > 0 ? " 余额：{$balancePrice} " : "";
        $content .= $cash ? " 货到付款：{$cash} " : "";
        //插入操作信息
        $operationLog = $operationLogData->addOperationLog([
            'belong_sn' => $param['serviceSn'],
            'belong_type' => 2,
            'content' => $content,
            'operation_type' => 3,
            'operation_log' => json_encode($refundInfo),
        ]);
        if (!$operationLog) {
            $this->dbWrite->rollback();
            return $this->arrayData("{$mseText}失败",'','添加操作信息失败','error');
        }
        //添加服务单记录
        $addServiceLog = $baseData->insert('Shop\Models\BaiyangOrderServiceLog', [
            'service_sn' => $param['serviceSn'],
            'status' => $data['status'],
            'log_content' => json_encode([$text,date('Y-m-d H:i:s', $time)]),
            'operator_id' => $this->session->get('admin_id'),
            'add_time' => $time,
            'operator_name' => $this->session->get('admin_account'),
        ]);
        if (!$addServiceLog) {
            $this->dbWrite->rollback();
            return $this->arrayData("{$mseText}失败",'','添加服务单记录失败','error');
        }
        //退银联
        if ($unionPay > 0) {
            $unionPayRefund = $this->bayoo->refund([
                'user_id' => $refundInfo['user_id'],
                'channel_subid' => $refundInfo['channel_subid'],
                'appOrderId' => $refundInfo['total_sn'],
                'isPartialRefund' => ($refundInfo['total_sn'] == $refundInfo['order_sn']) && ($refundInfo['total'] == $unionPay)
                    ? true : false,
                'amount' => $unionPay*100,
                'refundInfo' => $refundInfo['reason'].$refundInfo['explain'],
                'appRefundNo' => $refundInfo['service_sn'],
            ]);
            if ($unionPayRefund['code']) {
                $this->dbWrite->rollback();
                return $this->arrayData("{$mseText}失败",'',['银联退款失败',$unionPayRefund],'error');
            }
        }
        //退余额
        if ($balancePrice > 0) {
            $balanceRefund = $this->_eventsManager->fire('balance:external_refund_order', $this, [
                'order_sn' => $refundInfo['total_sn'],
                'refund_money' => $balancePrice,
            ]);
            if ($balanceRefund['status'] != 200) {
                $this->dbWrite->rollback();
                return $this->arrayData('退款失败','',['余额退款失败',$balanceRefund],'error');
            }
        }
        //退现金部分到余额
        /*if ($cash > 0) {
            $cashRefund = $this->_eventsManager->fire('balance:add_balance', $this, [
                'service_sn' => $param['serviceSn'],
                'refund_money' => $cash,
            ]);
            if ($cashRefund['status'] != 200) {
                $this->dbWrite->rollback();
                return $this->arrayData('退款失败','',['退货到付款现金部分退款失败',$cashRefund],'error');
            }
        }*/
        $this->dbWrite->commit();

        //退积分
        if (!$isRefuse) {
            $baiyangOrderData = \Shop\Datas\BaiyangOrderData::getInstance();
            $baiyangOrderData->refundOrderIntegral($param['serviceSn']);
        }

        //中明网同步
        /*if (!$isRefuse && $refundInfo['ad_source_id'] == 4) {
            $this->func->sendZmCps($refundInfo['order_sn']);
        }*/
        // 货到付款(未退货)，确认退款，发订单手动取消微信通知
        if (!$isRefuse && $refundInfo['payment_id'] == 3 && in_array($refundInfo['return_type'], [0,1]) && $param['amountSel'] == 'all') {
            $wapUrl = $this->config->wap_url;
            $env = $this->config->environment;
            $this->curl->sendPost($wapUrl[$env], [
                'order_sn' => $refundInfo['order_sn'],
                'is_manual' => 1,
            ]);
        }
        return $this->arrayData("{$mseText}成功");
    }

    /**
     * 是否退优惠券
     * @param $totalSn 母订单号
     * @return bool|int
     * @author Chensonglu
     */
    public function isRefundCoupon($info, $realAmount)
    {
        if (!$info) {
            return false;
        }
        $isRefundCoupon = 0;
        //获取使用优惠券订单信息
        $orderInfo = BaiyangOrderData::getInstance()->getUseCouponOrderInfo($info['total_sn']);
        if ($orderInfo) {
            $orderTotal = array_sum(array_column($orderInfo, 'total'));
            $orderSnStr = implode(',', array_column($orderInfo, 'order_sn'));
            if ($info['payment_id'] != 3) {
                if ($orderSnStr != $info['order_sn']) {
                    //获取同一母订单下子订单已完成的退款服务单
                    $where = "WHERE ogrr.order_sn IN ({$orderSnStr}) AND ogrr.status = 3";
                    $serviceInfo = BaiyangOrderGoodsReturnReasonData::getInstance()->getRefundAll("ogrr.service_sn,ogrr.real_amount", $where);
                    if ($serviceInfo) {
                        //以前退的部分加上现在要退的，跟所有使用优惠券订单的金额之和对比
                        $serviceRealAmount = array_sum(array_column($serviceInfo, 'real_amount')) + $realAmount;
                        $isRefundCoupon = ($orderTotal == $serviceRealAmount) ? 1 : 0;
                    }
                } elseif ($orderSnStr == $info['order_sn'] && $orderTotal == $realAmount) {
                    $isRefundCoupon = 1;
                }
            } else {
                $isRefundCoupon = (int)$this->refundAllGoods($orderSnStr);
            }
        }
        return $isRefundCoupon;
    }

    /**
     * 根据子订单号获取商品是否已全退
     * @param $orderSn string 订单号
     * @return bool
     * @author Chensonglu
     */
    public function refundAllGoods($orderSn)
    {
        if (!$orderSn) {
            return false;
        }
        $refundNum = 0;
        $buyNum = 0;
        $products = BaiyangOrderDetailData::getInstance()->getOrderDetail($orderSn);
        foreach ($products as $value) {
            if ($value['goods_type'] != 1) {
                $refundNum += $value['refund_goods_number'];
                $buyNum += $value['goods_number'];
            }
        }
        return ($refundNum > 0) && ($refundNum == $buyNum) ? true : false;
    }

    /**
     * 审核服务单
     * @param $param
     *              - serviceSn string 服务单号
     *              - isAudit int 是否审核操作
     *              - resultCode string 审核结果
     *              - auditRemark string 备注
     * @return array
     * @author Chensonglu
     */
    public function refundAudit($param)
    {
        //服务单号
        if (!isset($param['serviceSn']) || !$param['serviceSn']) {
            return $this->arrayData('请选择服务单','',$param,'error');
        }
        //是否审核操作
        if (!isset($param['isAudit'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        //获取服务单信息
        $refundInfo = $this->getRefundInfo($param['serviceSn']);
        if (!$refundInfo) {
            return $this->arrayData('该服务单不存在','',$param,'error');
        }
        //检验服务单是否待审核
        if ($refundInfo['refundState'] != 0) {
            return $this->arrayData('该服务单已审核','',$param,'error');
        }
        //获取服务单商品信息
        $refundInfo['products'] = $this->getServiceDetail($param['serviceSn']);
        if (!$param['isAudit']) {
            return $this->arrayData('可审核','',$refundInfo);
        }
        $time = time();
        $set = " serv_id = :servId:,serv_nickname = :nickname:,update_time = :upTime:";
        $data = [
            'serviceSn' => $param['serviceSn'],
            'servId' => $this->session->get('admin_id'),
            'nickname' => $this->session->get('admin_account'),
            'upTime' => $time,
        ];
        //审核结果
        if (!isset($param['resultCode']) || !$param['resultCode']) {
            return $this->arrayData('请选择审核结果','',$param,'error');
        }
        //备注
        if (isset($param['auditRemark']) && (strlen(trim($param['auditRemark'])) > 200)) {
            return $this->arrayData('备注内容不能超过200个字符','',$param,'error');
        }
        $text = "";
        $content = "进行了退款审核，处理结果为：";
        if ($param['resultCode'] == 'goods') {
            //退款退货
            $set .= ",return_type = :returnType:,status = :status:,return_way = :pattern:";
            $data['returnType'] = 2;
            $data['status'] = 4;
            $data['pattern'] = 1;
            $text = "您的申请已受理，请在7天内寄回商品并提交物流，过期将自动取消申请，如有疑问请与客服联系";
            $content .= "退货退款";
        } elseif ($param['resultCode'] == 'money') {
            //仅退款
            $set .= ",return_type = :returnType:,status = :status:";
            $data['returnType'] = 1;
            $data['status'] = in_array($refundInfo['payment_id'],[3,4,7]) ? 2 : 7;
            $text = "您的服务单售后已审核，退款处理中";
            $content .= "仅退款";
        } elseif ($param['resultCode'] == 'noPass') {
            //不通过
            $set .= ",status = :status:";
            $data['status'] = 1;
            $text = "您的服务单审核不通过";
            $content .= "不通过审核";
        }
        $baseData = BaseData::getInstance();
        //服务单日志内容
        $log_content = [
            $text,
            date('Y-m-d H:i:s', $time),
        ];
        //退款退货店铺收货人信息
        if ($param['resultCode'] == 'goods') {
            //获取店铺信息
            $shopInfo = $baseData->getData([
                'column' => '*',
                'table' => 'Shop\Models\BaiyangSkuSupplier',
                'where' => "WHERE id = :shopId:",
                'bind' => [
                    'shopId' => $refundInfo['shop_id'],
                ],
            ], true);
            $name = isset($shopInfo['user_name']) ? $shopInfo['user_name'] : '';
            $phone = isset($shopInfo['phone']) ? $shopInfo['phone'] : '';
            $phone = !$phone && isset($shopInfo['telephone']) ? $shopInfo['telephone'] : $phone;
            $address = isset($shopInfo['address']) ? $shopInfo['address'] : '';
            $log_content = [
                $text,
                date('Y-m-d H:i:s', $time),
                "收货人：{$name}",
                "电  话：{$phone}",
                "地  址：{$address}",
            ];
        }
        // 开启事务
        $this->dbWrite->begin();
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        //添加审核备注
        if (isset($param['auditRemark']) && trim($param['auditRemark'])) {
            if ($param['resultCode'] == 'noPass') {
                $set .= ",remark = :remark:";
                $data['remark'] = trim($param['auditRemark']);
            }
            //插入信息
            $remarkData = [
                'belong_sn' => $param['serviceSn'],
                'belong_type' => 2,
                'content' => trim($param['auditRemark']),
                'operation_type' => 1,
            ];
            $addRemark = $operationLogData->addOperationLog($remarkData);
            if (!$addRemark) {
                return $this->arrayData("审核失败",'','备注添加失败','error');
            }
        }
        //更新服务单信息
        $refundUp = $baseData->update($set,'Shop\Models\BaiyangOrderGoodsReturnReason',$data,'service_sn = :serviceSn:');
        if (!$refundUp) {
            $this->dbWrite->rollback();
            return $this->arrayData('审核失败','','服务单更新失败','error');
        }
        //审核不通过还原订单商品累计退货数量
        if ($param['resultCode'] == 'noPass') {
            $isError = 0;
            foreach ($refundInfo['products'] as $product) {
                $num = ($product['refund_goods_number'] - $product['refundNum']);
                $orderDetailUp = $baseData->update("refund_goods_number = :refundNum:,is_refund = :isRefund:",'Shop\Models\BaiyangOrderDetail',[
                    'id' => $product['order_goods_id'],
                    'refundNum' => $num > 0 ? $num : 0,
                    'isRefund' => 0
                ],'id = :id:');
                if (!$orderDetailUp) {
                    $this->dbWrite->rollback();
                    $isError = 1;
                    break;
                }
            }
            if ($isError) {
                return $this->arrayData("审核失败",'','商品退款数量累计更新失败','error');
            }
        }
        //插入操作信息
        $operationLog = $operationLogData->addOperationLog([
            'belong_sn' => $param['serviceSn'],
            'belong_type' => 2,
            'content' => $content,
            'operation_type' => 2,
            'operation_log' => json_encode($refundInfo),
        ]);
        if (!$operationLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('审核失败','','添加操作信息失败','error');
        }
        //添加服务单记录
        $addServiceLog = $baseData->insert('Shop\Models\BaiyangOrderServiceLog', [
            'service_sn' => $param['serviceSn'],
            'status' => $data['status'],
            'log_content' => json_encode($log_content),
            'operator_id' => $this->session->get('admin_id'),
            'add_time' => $time,
            'operator_name' => $this->session->get('admin_account'),
        ]);
        if (!$addServiceLog) {
            $this->dbWrite->rollback();
            return $this->arrayData('审核失败','','添加服务单记录失败','error');
        }
        $this->dbWrite->commit();
        return $this->arrayData('审核成功','',[
            'username' => $this->session->get('admin_account'),
            'time' => date('Y-m-d H:i:s', $time),
            'content' => '审核了服务单'
        ]);
    }

    /**
     * 根据服务单号获取服务单信息
     * @param $serviceSn string 服务单号
     * @return bool
     * @author Chensonglu
     */
    public function getRefundInfo($serviceSn)
    {
        if (!$serviceSn) {
            return false;
        }
        $column =  "ogrr.service_sn,ogrr.order_sn,ogrr.add_time,ogrr.reason,ogrr.explain,ogrr.refund_amount,"
            . "ogrr.status refundState,ogrr.return_type,ogrr.return_id,ogrr.update_time,ogrr.pay_fee,ogrr.remark,"
            . "ogrr.shop_name,ogrr.images,ogrr.real_amount,ogrr.express_no,ogrr.express_company express,"
            . "ogrr.return_way,o.total_sn,o.status orderStatus,o.last_status,o.shop_id,o.total,o.payment_id,o.channel_subid,"
            . "o.balance_price,o.total,o.real_pay,o.ad_source_id,o.user_coupon_price,u.phone,u.id user_id";
        $join = " INNER JOIN Shop\Models\BaiyangOrder o ON ogrr.order_sn = o.order_sn"
            . " INNER JOIN Shop\Models\BaiyangUser u ON o.user_id = u.id";
        $result = BaseData::getInstance()->getData([
            'column' => $column,
            'table' => 'Shop\Models\BaiyangOrderGoodsReturnReason ogrr',
            'join' => $join,
            'where' => "WHERE ogrr.service_sn = :serviceSn:",
            'bind' => [
                'serviceSn' => $serviceSn
            ],
        ], true);
        if (isset($result['images'])) {
            $result['images'] = json_decode($result['images'], true);
        }
        $result['orderStatus'] = isset($this->orderStat[$result['orderStatus']])
            ? $this->orderStat[$result['orderStatus']] : '未知状态';
        $result['paymentName'] = isset($this->orderPayment[$result['payment_id']])
            ? $this->orderPayment[$result['payment_id']] : '未知方式';
        if ($result['balance_price'] > 0) {
            $result['balance_price'] = $this->reckonBalancePrice($result['balance_price'], $result['order_sn']);
        }
        return $result;
    }

    /**
     * 计算可退余额
     * @param $balancePrice float 订单余额支付
     * @param $orderSn string 订单号
     * @return int 可退余额
     * @author Chensonglu
     */
    public function reckonBalancePrice($balancePrice, $orderSn)
    {
        $returnReasonData = BaiyangOrderGoodsReturnReasonData::getInstance();
        $where = "WHERE ogrr.service_sn <> '' AND ogrr.order_sn = '{$orderSn}' AND ogrr.status = 3";
        //计算可退余额
        if ($returnReasonData->getRefundNum($where)) {
            //获取已退款完成服务单信息
            $column = "ogrr.service_sn,ogrr.real_amount,ogrr.pay_fee";
            $finishData = $returnReasonData->getRefundAll($column, $where);
            $finishReturnBalance = 0;
            foreach ($finishData as $value) {
                $finishReturnBalance += ($value['real_amount'] - $value['pay_fee']);
            }
            $balancePrice -= $finishReturnBalance;
        }
        return $balancePrice > 0 ? $balancePrice : 0 ;
    }

    /**
     * 添加服务单备注
     * @param $param
     *              - orderSn 子订单号
     *              - remark 备注内容
     *              - type 备注类型（1订单、2服务单）
     * @return array
     * @author Chensonglu
     */
    public function addRemark($param)
    {
        //服务单号
        if (!isset($param['serviceSn']) || !$param['serviceSn']) {
            return $this->arrayData('请选择服务单','',$param,'error');
        }
        //是否添加
        if (!isset($param['isAdd'])) {
            return $this->arrayData('参数不完整','',$param,'error');
        }
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        if (!$param['isAdd']) {
            //获取服务单备注总数
            $remarkNum = $operationLogData->remarkNum($param['serviceSn']);
            return $remarkNum >= 10
                ? $this->arrayData('此订单的备注已超过10条，无法继续添加','',$param,'error')
                : $this->arrayData('可添加备注','',[
                    'account' => $this->session->get('admin_account')
                ]);;
        }
        //备注内容
        if (!isset($param['remark']) || !trim($param['remark'])) {
            return $this->arrayData('请填写备注（不能全部是空格）','',$param,'error');
        } elseif (mb_strlen(trim($param['remark'])) >= 200) {
            return $this->arrayData('备注内容不能超过200个字符','',$param,'error');
        }
        // 开启事务
        $this->dbWrite->begin();
        //插入信息
        $data = [
            'belong_sn' => $param['serviceSn'],
            'belong_type' => 2,
            'content' => trim($param['remark']),
            'operation_type' => 1,
        ];
        $addRemark = $operationLogData->addOperationLog($data);
        if (!$addRemark) {
            $this->dbWrite->rollback();
            return $this->arrayData('添加失败','','备注插入失败','error');
        }
        $this->dbWrite->commit();
        return $this->arrayData('备注添加成功');
    }

    /**
     * 根据服务单状态获取总数
     * @param int $status 服务单状态（默认统计待审核服务单数量）
     * @return mixed
     * @author Chensonglu
     */
    public function getPendingNum($status = 0)
    {
        $where = "WHERE ogrr.service_sn <> '' AND ogrr.auto = 0 AND ogrr.status = {$status}";
        //关联表
        $join = " LEFT JOIN baiyang_order o ON ogrr.order_sn = o.order_sn";
        $join .= " INNER JOIN baiyang_user u ON o.user_id = u.id";
        return BaiyangOrderGoodsReturnReasonData::getInstance()->getRefundNum($where, $join);
    }

    /**
     * 获取服务单列表
     * @param $param
     * @return array|bool
     * @author Chensonglu
     */
    public function getServiceAll($param)
    {
        $serviceInfo = $this->getServiceAllInfo($param);
        if (!isset($serviceInfo['list']) || !count($serviceInfo['list'])) {
            return false;
        }
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        foreach ($serviceInfo['list'] as $key => $value) {
            $value['payment_id'] = isset($this->orderPayment[$value['payment_id']])
                ? $this->orderPayment[$value['payment_id']] : '未知';
            if (!in_array($value['refundState'],[1,3,6])) {
                if ($value['balance_price'] > 0) {
                    $value['balance_price'] = $this->reckonBalancePrice($value['balance_price'], $value['order_sn']);
//                    echo '<pre>';print_r($this->reckonBalancePrice($value['balance_price'], $value['order_sn']));exit;
                }
                $value['pay_fee'] = $value['refund_amount'] > $value['balance_price']
                    ? sprintf('%.2f',$value['refund_amount'] - $value['balance_price']) :  sprintf('%.2f',0);
                $value['balance_price'] = $value['refund_amount'] >= $value['balance_price']
                    ? $value['balance_price'] : $value['refund_amount'];
            }
            $value['products'] = $this->getServiceDetail($value['service_sn']);
            $value['remarkLog'] = $operationLogData->getOperationLog([
                'serviceSn'=>$value['service_sn'],
                'type'=>1
            ]);
            $value['remarkCount'] = $operationLogData->remarkNum($value['service_sn']);
            $serviceInfo['list'][$key] = $value;
        }
        return $serviceInfo;
    }

    /**
     * 根据服务单号获取对应商品信息
     * @param $serviceSn string 服务单号
     * @return bool
     * @author Chensonglu
     */
    public function getServiceDetail($serviceSn)
    {
        if (!$serviceSn) {
            return false;
        }
        $column = "od.goods_id,od.goods_name,od.goods_image,od.price,od.unit_price,"
            . "od.goods_number,od.goods_type,spu.drug_type,cpr.name_id,cpr.name_id2,cpr.name_id3,od.promotion_price,"
            . "od.promotion_total,od.is_return,od.refund_goods_number,ogr.refund_goods_number refundNum,ogr.order_goods_id";
        $join = " LEFT JOIN Shop\Models\BaiyangOrderGoodsReturnReason ogrr ON ogr.reason_id = ogrr.id"
            . " LEFT JOIN Shop\Models\BaiyangOrderDetail od ON od.id = ogr.order_goods_id AND od.order_sn = ogrr.order_sn"
            . " LEFT JOIN Shop\Models\BaiyangGoods g ON g.id = od.goods_id"
            . " LEFT JOIN Shop\Models\BaiyangSpu spu ON spu.spu_id = g.spu_id"
            . " LEFT JOIN Shop\Models\BaiyangCategoryProductRule cpr ON cpr.category_id = spu.category_id";
        $goodsList = BaseData::getInstance()->getData([
            'column' => $column,
            'table' => 'Shop\Models\BaiyangOrderGoodsReturn ogr',
            'join' => $join,
            'where' => "WHERE ogrr.service_sn = :serviceSn:",
            'bind' => [
                'serviceSn' => $serviceSn
            ],
        ]);
        if ($goodsList) {
            $goodsRule = BaiyangProductRuleData::getInstance()->getAllGoodsRule();
            foreach ($goodsList as $key => $value) {
                //商品品规匹配
                $value['name_id'] = isset($goodsRule[$value['name_id']])
                    ? $goodsRule[$value['name_id']] : "";
                $value['name_id2'] = isset($goodsRule[$value['name_id2']])
                    ? $goodsRule[$value['name_id2']] : "";
                $value['name_id3'] = isset($goodsRule[$value['name_id3']])
                    ? $goodsRule[$value['name_id3']] : "";
                $value['goodsRefunds'] = sprintf("%.2f", $value['refundNum']*$value['promotion_price']);
                $goodsList[$key] = $value;
            }
        }
        return $goodsList;
    }

    /**
     * 根据搜索条件获取服务单信息
     * @param $param
     *              - orderSn string 订单号
     *              - startTime int 搜索开始时间
     *              - endTime int 搜索结束时间
     *              - refundStatus string 服务单状态
     *              - serviceSn string 服务单号
     *              - shopId int 发货商家
     *              - username string 用户名
     *              - phone int 用户手机号
     *              - goods_name string 商品名称
     *              - goods_id int 商品ID
     *              - searchType string 显示类型
     *              - psize int 每页显示条数
     *              - page int 当前页
     *              - url string 当前网址
     * @return array
     * @author Chensonglu
     */
    public function getServiceAllInfo($param)
    {
        //非--拼团自动退款的订单
        $where = "WHERE ogrr.auto = 0 AND ogrr.service_sn <> ''";
        //订单号
        if (isset($param['orderSn']) && $param['orderSn']) {
            $where .= " AND ogrr.order_sn = '{$param['orderSn']}'";
        }
        //申请退款时间
        if (isset($param['startTime']) && isset($param['endTime']) && $param['startTime'] && $param['endTime']) {
            $param['startTime'] = strtotime($param['startTime']);
            $param['endTime'] = strtotime($param['endTime']);
            $where .= " AND ogrr.add_time BETWEEN {$param['startTime']} AND {$param['endTime']}";
        } elseif (isset($param['startTime']) && $param['startTime'] && (!isset($param['endTime']) || !$param['endTime'])) {
            $param['startTime'] = strtotime($param['startTime']);
            $where .= " AND ogrr.add_time >= {$param['startTime']}";
        } elseif (isset($param['endTime']) && $param['endTime'] && (!isset($param['startTime']) || !$param['startTime'])) {
            $param['endTime'] = strtotime($param['endTime']);
            $where .= " AND ogrr.add_time <= {$param['endTime']}";
        }
        //退款状态
        if (isset($param['refundStatus']) && isset($this->refundState[$param['refundStatus']])) {
            if ($this->refundState[$param['refundStatus']] == 2) {
                $where .= " AND ogrr.status IN (2,7)";
            } else {
                $where .= " AND ogrr.status = {$this->refundState[$param['refundStatus']]}";
            }
        }
        //服务单号
        if (isset($param['serviceSn']) && $param['serviceSn']) {
            $where .= " AND ogrr.service_sn = '{$param['serviceSn']}'";
        }
        //发货商家
        if (isset($param['shopId']) && $param['shopId']) {
            $where .= " AND o.shop_id = {$param['shopId']}";
        }
        //用户名
        if (isset($param['username']) && $param['username']) {
            $where .= " AND (u.username LIKE '%".$param['username']."%' OR u.nickname LIKE '%".$param['username']."%' "
                . "OR u.email LIKE '%".$param['username']."%')";
        }
        //手机号
        if (isset($param['username']) && $this->func->isPhone($param['username'])) {
            $where .= " AND (u.phone = {$param['username']} OR u.user_id = {$param['username']})";
        }
        //审核人
        if (isset($param['auditor']) && $param['auditor']) {
            $where .= is_numeric($param['auditor'])
                ? " AND ogrr.serv_id = {$param['auditor']} OR ogrr.serv_nickname LIKE '%{$param['auditor']}%'"
                : " AND ogrr.serv_nickname LIKE '%{$param['auditor']}%'";
        }
        //是否关联orderDetail表
        $isJoinOrderDetail = 0;
        //商品ID或商品名称
        if (isset($param['goodsName']) && $param['goodsName']) {
            $where .= " AND od.goods_name LIKE '%".$param['goodsName']."%'";
            $isJoinOrderDetail = 1;
        }
        if (isset($param['goodsId']) && is_numeric($param['goodsId']) && $param['goodsId']) {
            $where .= " AND od.goods_id = {$param['goodsId']}";
            $isJoinOrderDetail = 1;
        }
        //类型
        if (isset($param['searchType']) && $param['searchType'] && $param['searchType'] != 'all'){
            if ($param['searchType'] == 'pending') {
                $where .= " AND ogrr.status = 0";
            } elseif ($param['searchType'] == 'shipping') {
                $where .= " AND ogrr.return_type = 1 AND ogrr.status <> 0 AND ((o.status = 'refund' AND o.last_status = 'shipping') "
                    . "OR o.status = 'shipping')";
            } elseif ($param['searchType'] == 'shipped') {
                $where .= " AND ogrr.return_type = 1 AND ogrr.status <> 0 AND ((o.status IN ('refund','evaluating','finished') "
                    . "AND o.last_status IN ('shipped','evaluating')) OR o.status = 'shipped')";
            } elseif ($param['searchType'] == 'refundReturn') {
                $where .= " AND ogrr.return_type = 2 AND ogrr.status <> 0 AND (o.status IN ('evaluating','finished') OR "
                    . "(o.status = 'shipped' OR (o.status = 'refund' AND o.last_status = 'shipped')))";
            } elseif ($param['searchType'] == 'refuse') {
                $where .= " AND ogrr.status = 1";
            }
        }
        //关联表
        $join = " LEFT JOIN baiyang_order o ON ogrr.order_sn = o.order_sn";
        $join .= " INNER JOIN baiyang_user u ON o.user_id = u.id";
        $join .= $isJoinOrderDetail ? " INNER JOIN baiyang_order_goods_return ogr ON ogr.reason_id = ogrr.id" : '';
        $join .= $isJoinOrderDetail ? " LEFT JOIN baiyang_order_detail od ON od.id = ogr.order_goods_id" : '';
        $returnReasonData = BaiyangOrderGoodsReturnReasonData::getInstance();
        //总记录数
        $counts = $returnReasonData->getRefundNum($where,$join);
        //分页
        $pages['psize'] = isset($param['psize']) && $param['psize'] ? (int)$param['psize'] : 15;//每页显示条数
        $pages['page'] = isset($param['page']) && $param['page'] ? (int)$param['page'] : 1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $pages['isShow'] = true;
        $page = $this->page->pageDetail($pages);
        $column = "ogrr.service_sn,ogrr.order_sn,ogrr.add_time,ogrr.reason,ogrr.explain,ogrr.refund_amount,ogrr.status refundState,"
            . "ogrr.return_type,ogrr.return_id,ogrr.update_time,ogrr.pay_fee,ogrr.real_amount,ogrr.serv_id,ogrr.serv_nickname,"
            . "ogrr.remark,ogrr.shop_name,o.status,o.last_status,o.shop_id,o.total,u.username,o.payment_id,o.balance_price";
        //数据
        $service = $returnReasonData->getRefundAll($column,$where,$join,"GROUP BY ogrr.service_sn","ORDER BY ogrr.add_time DESC","LIMIT {$page['record']},{$page['psize']}");
        return [
            'list' => $service,
            'page' => $page['page'],
            'count' => $counts,
        ];
    }

    /**
     * excel导出字段组函数
     * @return array
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
     * 查询导出数据、整理、导出
     * @param $param
     * @return mixed
     * @author Zhudan
     */
    public function excelService($param){

        $filed = "";
        $join = "";
        $join_table = [];

        //整理字段开始
        if($param['export_title']){

            foreach($param['export_title'] as $key){

                if(isset($this->excel_header[$key]) && $this->excel_header[$key]['join'] && isset($this->excel_header[$key]['table']) && (empty($join_table) || !in_array($this->excel_header[$key]['table'],$join_table))){
                    $join .= ' '.$this->excel_header[$key]['join'].' ';
                    $filed .= ', '.$this->excel_header[$key]['field'];
                    $join_table[] = $this->excel_header[$key]['table'];
                }else if($this->excel_header[$key]['field']){
                    $filed .= ', '.$this->excel_header[$key]['field'];
                }

            }
        }
        $filed =  'service_sn'.$filed;
//        var_dump($filed);
//        exit;

        $returnReasonData = BaiyangOrderGoodsReturnReasonData::getInstance();
        if($param['export_type'] == 'select_check'){
            //非--拼团自动退款的订单
            $where = "WHERE ogrr.auto = 0 AND ogrr.service_sn <> ''";
            //是否关联orderDetail表
            $isJoinOrderDetail = 0;
            //订单号
            if (isset($param['orderSn']) && $param['orderSn']) {
                $where .= " AND ogrr.order_sn = '{$param['orderSn']}'";
            }
            //申请退款时间
            if (isset($param['startTime']) && isset($param['endTime']) && $param['startTime'] && $param['endTime']) {
                $param['startTime'] = strtotime($param['startTime']);
                $param['endTime'] = strtotime($param['endTime']);
                $where .= " AND ogrr.add_time BETWEEN {$param['startTime']} AND {$param['endTime']}";
            } elseif (isset($param['startTime']) && $param['startTime'] && (!isset($param['endTime']) || !$param['endTime'])) {
                $param['startTime'] = strtotime($param['startTime']);
                $where .= " AND ogrr.add_time >= {$param['startTime']}";
            } elseif (isset($param['endTime']) && $param['endTime'] && (!isset($param['startTime']) || !$param['startTime'])) {
                $param['endTime'] = strtotime($param['endTime']);
                $where .= " AND ogrr.add_time <= {$param['endTime']}";
            }
            //退款状态
            if (isset($param['refundStatus']) && isset($this->refundState[$param['refundStatus']])) {
                if ($this->refundState[$param['refundStatus']] == 2) {
                    $where .= " AND ogrr.status IN (2,7)";
                } else {
                    $where .= " AND ogrr.status = {$this->refundState[$param['refundStatus']]}";
                }
            }
            //服务单号
            if (isset($param['serviceSn']) && $param['serviceSn']) {
                $where .= " AND ogrr.service_sn = '{$param['serviceSn']}'";
            }
            //发货商家
            if (isset($param['shopId']) && $param['shopId']) {
                $where .= " AND o.shop_id = {$param['shopId']}";
            }



            //用户名
            if (isset($param['username']) && $param['username']) {
                $where .= " AND (u.username LIKE '%".$param['username']."%' OR u.nickname LIKE '%".$param['username']."%' "
                    . "OR u.email LIKE '%".$param['username']."%')";
            }
            //手机号
            if (isset($param['phone']) && $param['phone']) {
                $where .= " AND (u.phone = {$param['phone']} OR u.user_id = {$param['phone']})";
            }

            //审核人
            if (isset($param['auditor']) && $param['auditor']) {
                $where .= is_numeric($param['auditor'])
                    ? " AND ogrr.serv_id = {$param['auditor']} OR ogrr.serv_nickname LIKE '%{$param['auditor']}%'"
                    : " AND ogrr.serv_nickname LIKE '%{$param['auditor']}%'";
            }
            //商品ID或商品名称
            if (isset($param['goodsName']) && $param['goodsName'] ) {
                $where .= " AND od.goods_name LIKE '%".$param['goodsName']."%'";
                $isJoinOrderDetail = 1;
            }
            if (isset($param['goodsId']) && is_numeric($param['goodsId']) && $param['goodsId']) {
                $where .= " AND od.goods_id = {$param['goodsId']}";
                $isJoinOrderDetail = 1;
            }
            //类型
            if (isset($param['searchType']) && $param['searchType'] && $param['searchType'] != 'all'){
                if ($param['searchType'] == 'pending') {
                    $where .= " AND ogrr.status = 0";
                } elseif ($param['searchType'] == 'shipping' || $param['searchType'] == 'shipped') {
                    $where .= " AND ogrr.return_type IN (0,1) AND ((o.status = 'refund' AND o.last_status = '{$param['searchType']}') "
                        . "OR o.status = '{$param['searchType']}')";
                } elseif ($param['searchType'] == 'refundReturn') {
                    $where .= " AND ogrr.return_type = 2 AND o.status IN ('evaluating','finished')";
                } elseif ($param['searchType'] == 'refuse') {
                    $where .= " AND ogrr.status = 1";
                }
            }
            //关联表
            $join .= !in_array('baiyang_order',$join_table) ?" LEFT JOIN baiyang_order o ON ogrr.order_sn = o.order_sn":'';
            $join .= !in_array('baiyang_user',$join_table)?" INNER JOIN baiyang_user u ON o.user_id = u.id":"";
            $join .= !in_array('baiyang_order_detail',$join_table)?" INNER JOIN baiyang_order_goods_return ogr ON ogr.reason_id = ogrr.id":"";
            $join .= $isJoinOrderDetail && !in_array('baiyang_order_goods_return',$join_table)? " LEFT JOIN baiyang_order_detail od ON od.id = ogr.order_goods_id" : '';


            //分页

//            $column = "ogrr.service_sn,ogrr.order_sn,ogrr.add_time,ogrr.reason,ogrr.explain,ogrr.refund_amount,"
//                . "ogrr.status refundState,ogrr.return_type,ogrr.return_id,ogrr.update_time,ogrr.pay_fee,ogrr.serv_id,"
//                . "ogrr.serv_nickname,ogrr.remark,ogrr.shop_name,o.status,o.last_status,o.shop_id,o.total,u.username";
            //数据
            $service = $returnReasonData->getRefundAll($filed,$where,$join,"GROUP BY ogrr.service_sn","ORDER BY ogrr.add_time DESC","LIMIT 0,3000");

        }else{
            $service = $returnReasonData->getRefundAll($filed,'',$join,"GROUP BY ogrr.service_sn","ORDER BY ogrr.add_time DESC","LIMIT 0,3000");
        }

        if($service){
            $this->downExcel($service);
        }else{
            return $service;
        }
    }

    /**
     * 导出
     * @param $list array 要导出数据
     * @author Zhudan
     */
    public function downExcel($list){

        $head_title = array_keys($list[0]);
        $obj = \Shop\Libs\Excel::getInstance();
        $headArray = [];
        foreach($head_title as $key){
            $headArray[] = $this->excel_header[$key]['text'];
        }
        //        $obj::$_exist_photo = true;
        $obj->exportExcel($headArray,$list,'服务单导出列表','服务单单列表');

    }

}