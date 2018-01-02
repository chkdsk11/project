<?php
/**
 * Created by PhpStorm.
 * User: qinliang
 * Date: 2017/05/22 
 * Time: 上午 10:55
 */

namespace Shop\Home\Services;


// 加载Datas
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangOrderPayDetailData;
use Shop\Home\Datas\BaiyangOrderData;
use Shop\Home\Datas\BaiyangOrderPromotionData;
use Shop\Home\Datas\BaiyangUserData;
use Shop\Home\Datas\BaiyangO2oData;
use Shop\Home\Datas\BaiyangOrderDetailData;
use Shop\Home\Datas\BaiyangOrderOperationLogData;
use Shop\Home\Datas\BaiyangOrderGoodsReturnReasonData;
use Shop\Home\Datas\BaiyangSkuSupplierData;
use Shop\Home\Datas\BaiyangOrderStatusHistoryData;

// 加载Events
use Phalcon\Events\Manager as EventsManager;
use Phalcon\Events\Event;

// 加载 Services
use Shop\Home\Services\BaseService;
use Shop\Home\Services\AuthService;

// 加载Models
use Shop\Models\OrderEnum;
use Shop\Models\HttpStatus;
use Shop\Models\BaiyangOrder;
use Shop\Models\BaiyangOrderDetail;
use Shop\Models\CacheKey;

// 加载Libs
use Shop\Libs\CacheRedis;



class ApiService extends BaseService
{
    protected static $instance=null;
    protected $message = [
        0 => '您的服务单售后已审核，退款处理中',
        1 => '您的申请不通过，备注原因：xxxxx，如有疑问可以咨询客服', 
        2 => '您的服务单售后已审核，退款处理中',
        3 => '您的服务单已退款，请注意查收',
        4 => '您的申请已受理，请在7天内寄回商品并提交物流，过期讲自动取消申请',
        7 => '您服务单的商品已收到，等待财务审核',
    ];
    public $appsecret = 'AS*%^w8h78*hiKjH**AQI@^525WE22q2@2^%2KJ3';

    
    /**
     * 实例化当前类
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new ApiService();
        }
        
        return static::$instance;
    }
    
    
    /**
     * @desc 冻结 确认收货 退单完成(订单作废) 海典调用官网接口
     * @param array $param
     *       -order_sn string 子订单编号（*）
     *       -service_sn string 拆单服务单号(*)
     *       -operator_id int 操作员ID(*)
     * @return json array
     * @author qinliang
     */
    public function orderNoticeOperation($param)
    {
        // 格式化参数
        $paramPost = [];
        $paramPost['appid']        = isset($param['appid']) && is_string($param['appid']) ? htmlspecialchars($param['appid']) : '';
        $paramPost['order_sn']     = isset($param['order_sn']) && is_string($param['order_sn']) ? htmlspecialchars($param['order_sn']) : '';
        $paramPost['sign']         = isset($param['sign']) && is_string($param['sign']) ? (string)$param['sign'] : '';
        $paramPost['status']       = isset($param['status']) && is_numeric($param['status']) ? (int)$param['status'] : 0;
        $paramPost['date_updated'] = isset($param['date_updated']) && is_numeric($param['date_updated']) ? (int)$param['date_updated'] : 0;
        $paramPost['operator_id']  = isset($param['operator_id']) && is_numeric($param['operator_id']) ? (int)$param['operator_id'] : 0;
        $paramPost['service_sn']   = isset($param['service_sn']) && is_string($param['service_sn']) ? htmlspecialchars($param['service_sn']) : '';
        $param['reason']           = isset($param['reason']) && is_string($param['reason']) ? htmlspecialchars($param['reason']) : '';
        $param['code']             = isset($param['code']) && intval($param['code']) === 1 ? 1 : 0;
        
        // 判断参数合法性
        if ($paramPost['appid'] != 5 
            || empty($paramPost['order_sn']) 
            || empty($paramPost['sign']) 
            || $paramPost['date_updated'] < 1 
            || $paramPost['operator_id'] < 1 
        ) {
            return $this->uniteReturnResultJson(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        } 
        if (!in_array($paramPost['status'], [9,10,11]) ) {
            return $this->uniteReturnResultJson(HttpStatus::NOT_TO_ORDER_STATUS);
        }
        if ($paramPost['status'] === 11) {
            if (empty($param['reason']) && $param['code'] === 0 ) {
                return $this->uniteReturnResultJson(HttpStatus::PARAM_ERROR, ['param'=> $param]);
            }
        }
       
        // 签名校验
        $paramPost['appsecret'] = $this->appsecret;
        if (!$this->checkSign($paramPost)) {
            return $this->uniteReturnResultJson(HttpStatus::VERIFY_FAILED);
        }
        
        // 处理业务逻辑
        if ($paramPost['status'] === 9) {
            $this->erpArticleNotice($paramPost); //确认收货
        } elseif ($paramPost['status'] === 10) {
            $this->erpRefundNotice($paramPost); //订单审核通过(订单作废)
        } elseif ($paramPost['status'] === 11 && $param['code'] === 1) {
            $this->erpFreezeSuccessful($paramPost); // 冻结成功 (截单成功)
        } elseif ($paramPost['status'] === 11 && $param['code'] === 0) {
            return $this->uniteReturnResultJson(HttpStatus::SUCCESS); // 暂时不予操作逻辑直接返回状态
            //$this->erpFreezeFailure($paramPost, $param['reason']); // 冻结失败 (截单失败)
        } else {
            return $this->uniteReturnResultJson(HttpStatus::ILLEGAL_REQUEST, ['param'=> $param]); //非法请求
        }
        /* elseif ($paramPost['status'] === 12 ) {
            $this->erpAuditFailure($paramPost, $param['reason']); // 订单审核失败 (拒绝退款)
        } */
        
    }
    
    
    /**
     * @desc 退款单审核失败(退款申请审核未通过)
     * @param array $param
     *       -order_sn string 子订单编号（*）
     *       -service_sn string 拆单服务单号(*)
     *       -operator_id int 操作员ID(*)
     * @param $reason 审核失败原因
     * @return json array
     * @author qinliang
     */
    private function erpAuditFailure($param, $reason)
    {
        // 格式化参数
        $order_sn    = isset($param['order_sn']) && is_string($param['order_sn']) ? htmlspecialchars($param['order_sn']) : '';
        //$service_sn  = isset($param['service_sn']) && is_string($param['service_sn']) ? htmlspecialchars($param['service_sn']) : '';
        $operator_id = isset($param['operator_id']) && is_numeric($param['operator_id']) ? (int)$param['operator_id'] : 0;
        
        // 实例data
        $orderDetailData  = BaiyangOrderDetailData::getInstance();
        $orderData        = BaiyangOrderData::getInstance();
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        $ReasonData       = BaiyangOrderGoodsReturnReasonData::getInstance();
        
        // 获取服务端号信息
        $orderServiceInfo = $orderDetailData->getOrderDetailReturnData($order_sn, true);
        if (!$orderServiceInfo) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
        
        // 状态:0-申请中，1-拒绝退款，2-退款中（同意退款），3-退款完成，4-等待退货（客服已受理），5-物流已提交，6-取消退款 ,7-您服务单的商品已收到,等待财务审核
        if ($orderServiceInfo['status'] == 1 || $orderServiceInfo['status'] == 3 || $orderServiceInfo['status'] == 6) {
            return $this->uniteReturnResultJson(HttpStatus::NOT_TO_SERVICE_STATUS);
        }
        
        // 根据订单号获取订单信息
        $orderList = $orderData->getData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'id,status,pay_type,payment_id,total,balance_price',
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $orderServiceInfo['order_sn'],
            ]
        ], true);
        if (!$orderList) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
        
        // 获取服务单信息
        $productList = $orderDetailData->getOrderDetailByService($orderServiceInfo['service_sn']);

        // 开启事物
        $this->dbWrite->begin();
        
        // 修改服务号状态
        $updateResult = $orderDetailData->updateData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => "status = 1",
            'where'  => 'where order_sn = :order_sn: AND service_sn = :service_sn:',
            'bind'   => [
                'order_sn' => $order_sn,
                'service_sn' => $orderServiceInfo['service_sn']
            ]
        ]);
        if (!$updateResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 审核失败 退还商品数量
        if ($productList) {
            foreach ($productList as $product) {
                $num = ($product['refund_goods_number'] - $product['now_refund_goods_number']);
                $refundNum = $num > 0 ? $num : 0;
                $updateDetailResult = $orderDetailData->updateData([
                    'table' => 'Shop\Models\BaiyangOrderDetail',
                    'column' => "refund_goods_number = $refundNum, is_return = 0",
                    'where'  => 'where id = :id:',
                    'bind'   => [
                        'id' => $product['order_goods_id'],
                    ]
                ]);
                if (!$updateDetailResult) {
                    $this->dbWrite->rollback();
                    return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
                }
            }
        }
        
        // 插入售后服务记录
        $insertServiceLogResult = $orderDetailData->addData([
            'table' => '\Shop\Models\BaiyangOrderServiceLog',
            'bind'  => [
                'service_sn'  => $orderServiceInfo['service_sn'],
                'status'      => 1,
                'operator_id' => $operator_id,
                'log_content' => json_encode([0 => "您的申请不通过，备注原因：{$reason}，如有疑问可以咨询客服", 1 => date('Y-m-d H:i:s',time())]),
                'add_time'    => time(),
            ]
        ]);
        if (!$insertServiceLogResult) { 
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 插入订单/服务单操作日志表
        $insertOperationLog = $operationLogData->addData([
            'table' => '\Shop\Models\BaiyangOrderOperationLog',
            'bind'  => [
                'belong_sn'   => $orderServiceInfo['service_sn'],
                'belong_type' => 2,
                'content' => '拒绝了退款',
                'operation_log' => !empty($orderServiceInfo) ? json_encode($orderServiceInfo) : '',
                'operation_type' => 5,
                'operator_id' => $operator_id,
                'add_time'    => time(),
            ]
        ]);
        if (!$insertOperationLog) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 执行
        $this->dbWrite->commit();
        
        // 返回结果
        return $this->uniteReturnResultJson(HttpStatus::SUCCESS);
    }
    
    
    /**
     * @desc 冻结 成功处理逻辑
     * @param array $param
     *       -order_sn string 子订单编号（*）
     *       -service_sn string 拆单服务单号(*)
     *       -operator_id int 操作员ID(*)
     * @return json array
     * @author qinliang
     */
    private function erpFreezeSuccessful($param)
    {
        // 格式化参数
        $order_sn    = isset($param['order_sn']) && is_string($param['order_sn']) ? htmlspecialchars($param['order_sn']) : '';
        //$service_sn  = isset($param['service_sn']) && is_string($param['service_sn']) ? htmlspecialchars($param['service_sn']) : '';
        $operator_id = isset($param['operator_id']) && is_numeric($param['operator_id']) ? (int)$param['operator_id'] : 0;
        
        // 实例data
        $orderDetailData  = BaiyangOrderDetailData::getInstance();
        $orderData        = BaiyangOrderData::getInstance();
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
        
        // 获取服务端号信息
        $orderServiceInfo = $orderDetailData->getOrderDetailReturnData($order_sn, true);
        if (!$orderServiceInfo) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
        
        // 状态:0-申请中，1-拒绝退款，2-退款中（同意退款），3-退款完成，4-等待退货（客服已受理），5-物流已提交，6-取消退款 ,7-您服务单的商品已收到,等待财务审核
        if ($orderServiceInfo['status'] != 0) {
            return $this->uniteReturnResultJson(HttpStatus::NOT_TO_SERVICE_STATUS);
        }
        
        // 根据订单号获取订单信息
        $orderList = $orderData->getData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'id,status,pay_type,payment_id,total,balance_price',
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $orderServiceInfo['order_sn'],
            ]
        ], true);
        if (!$orderList) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
        
        // 开启事物
        $this->dbWrite->begin();
        
        // 修改服务号状态
        $updateResult = $orderDetailData->updateData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => "status = 2",
            'where'  => 'where order_sn = :order_sn: AND service_sn = :service_sn:',
            'bind'   => [
                'order_sn' => $order_sn,
                'service_sn' => $orderServiceInfo['service_sn']
            ]
        ]);
        if (!$updateResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 插入售后服务记录
        $insertServiceLogResult = $orderDetailData->addData([
            'table' => '\Shop\Models\BaiyangOrderServiceLog',
            'bind'  => [
                'service_sn'  => $orderServiceInfo['service_sn'],
                'status'      => 2,
                'operator_id' => $operator_id,
                'log_content' => isset($this->message[2]) ? json_encode([0 => $this->message[2], 1 => date('Y-m-d H:i:s',time())]) : '',
                'add_time'    => time(),
            ]
        ]);
        if (!$insertServiceLogResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        } 
        
        // 插入订单/服务单操作日志表
        $content = $orderServiceInfo['return_type'] == 2 ? '进行了退款审核，处理结果为：退货退款' : '进行了退款审核，处理结果为：退款';
        $insertOperationLog = $operationLogData->addData([
            'table' => '\Shop\Models\BaiyangOrderOperationLog',
            'bind'  => [
                'belong_sn'   => $orderServiceInfo['order_sn'],
                'belong_type' => 2,
                'content' => $orderServiceInfo['return_type'] == 0 ? '进行了退款审核，处理结果为：货到付款' : $content,
                'operation_log' => !empty($orderServiceInfo) ? json_encode($orderServiceInfo) : '',
                'operation_type' => 5,
                'operator_id' => $operator_id,
                'add_time'    => time(),
            ]
        ]);
        if (!$insertOperationLog) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 执行
        $this->dbWrite->commit();
        
        // 返回结果
        return $this->uniteReturnResultJson(HttpStatus::SUCCESS);
    }
    
    
    /**
     * @desc 退货退款 海典通知商城已经收到用户寄回商品(收货通知)
     * @param array $param
     *       -order_sn string 子订单编号（*）
     *       -service_sn string 拆单服务单号(*)
     *       -operator_id int 操作员ID(*)
     * @return json array
     * @author qinliang
     */
    private function erpArticleNotice($param)
    {
        // 格式化参数
        $order_sn    = isset($param['order_sn']) && is_string($param['order_sn']) ? htmlspecialchars($param['order_sn']) : '';
        $operator_id = isset($param['operator_id']) && is_numeric($param['operator_id']) ? (int)$param['operator_id'] : 0;
    
        // 实例data
        $orderData        = BaiyangOrderData::getInstance();
        $orderDetailData  = BaiyangOrderDetailData::getInstance();
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
    
        // 获取服务端号信息
        $orderServiceInfo = $orderDetailData->getOrderDetailReturnData($order_sn, true);
        if (!$orderServiceInfo) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
    
        // 状态:0-申请中，1-拒绝退款，2-退款中（同意退款），3-退款完成，4-等待退货（客服已受理），5-物流已提交，6-取消退款 ,7-您服务单的商品已收到,等待财务审核
        if ($orderServiceInfo['status'] != 0 
            && $orderServiceInfo['status'] != 4 
            && $orderServiceInfo['status'] != 5 
            && $orderServiceInfo['return_type'] != 0 
            && $orderServiceInfo['return_type'] != 2 
         ) {
            return $this->uniteReturnResultJson(HttpStatus::NOT_TO_SERVICE_STATUS);
        }
    
        // 根据订单号获取订单信息
        $orderList = $orderData->getData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'id,status,pay_type,payment_id,total,balance_price',
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $order_sn
            ]
        ], true);
        if (!$orderList || $orderList['payment_id'] == 0) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
    
        // 开启事物
        $this->dbWrite->begin();
        
        // 修改服务号状态
        $updateResult = $orderDetailData->updateData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => "status = 7",
            'where'  => 'where order_sn = :order_sn: AND service_sn = :service_sn:',
            'bind'   => [
                'order_sn' => $order_sn,
                'service_sn' => $orderServiceInfo['service_sn']
            ]
        ]);
        if (!$updateResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 插入售后服务记录
        $insertServiceLogResult = $orderDetailData->addData([
            'table' => '\Shop\Models\BaiyangOrderServiceLog',
            'bind'  => [
                'service_sn'  => $orderServiceInfo['service_sn'],
                'status'      => 7,
                'operator_id' => $operator_id,
                'log_content' => isset($this->message[7]) ? json_encode([0 => $this->message[7], 1 => date('Y-m-d H:i:s',time())]) : '',
                'add_time'    => time(),
            ]
        ]);
        if (!$insertServiceLogResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 插入订单/服务单操作日志表
        $insertOperationLog = $operationLogData->addData([
            'table' => '\Shop\Models\BaiyangOrderOperationLog',
            'bind'  => [
                'belong_sn'   => $orderServiceInfo['service_sn'],
                'belong_type' => 2,
                'content' => '已收到货',
                'operation_log' => !empty($orderServiceInfo) ? json_encode($orderServiceInfo) : '',
                'operation_type' => 5,
                'operator_id' => $operator_id,
                'add_time'    => time(),
            ]
        ]);
        if (!$insertOperationLog) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 执行
        $this->dbWrite->commit();
    
        // 返回结果
        return $this->uniteReturnResultJson(HttpStatus::SUCCESS);
    }
    
    
    /**
     * @desc 退货退款 海典通知商城退款或订单交易终止(通知退款 或 关闭订单)
     * @param array $param
     *       -order_sn string 子订单编号（*）
     *       -service_sn string 拆单服务单号(*)
     *       -operator_id int 操作员ID(*)
     * @return json array
     * @author qinliang
     */
    private function erpRefundNotice($param)
    {
        // 格式化参数
        $order_sn    = isset($param['order_sn']) && is_string($param['order_sn']) ? htmlspecialchars($param['order_sn']) : '';
        $operator_id = isset($param['operator_id']) && is_numeric($param['operator_id']) ? (int)$param['operator_id'] : 0;
    
        // 实例datas
        $orderData       = BaiyangOrderData::getInstance();
        $orderDetailData = BaiyangOrderDetailData::getInstance();
        $operationLogData = BaiyangOrderOperationLogData::getInstance();
    
        // 获取服务端号信息
        $orderServiceInfo = $orderDetailData->getOrderDetailReturnData($order_sn, true);
        if (!$orderServiceInfo) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
        // 状态:0-申请中，1-拒绝退款，2-退款中（同意退款），3-退款完成，4-等待退货（客服已受理），5-物流已提交，6-取消退款 ,7-您服务单的商品已收到,等待财务审核
        if ($orderServiceInfo['status'] != 0 && $orderServiceInfo['status'] != 2 && $orderServiceInfo['status'] != 4 && $orderServiceInfo['status'] != 5 && $orderServiceInfo['status'] != 7) {
            return $this->uniteReturnResultJson(HttpStatus::NOT_TO_SERVICE_STATUS);
        }
    
        // 根据订单号获取订单信息
        $orderList = $orderData->getData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'id,status,pay_type,payment_id,balance_price',
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $order_sn
            ]
        ], true);
        if (!$orderList) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
         
        // 开启事物
        $this->dbWrite->begin();
        
        // 修改服务号状态 为 财务审核中
        $updateResult = $orderDetailData->updateData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => "status = 2",
            'where'  => 'where order_sn = :order_sn: AND service_sn = :service_sn:',
            'bind'   => [
                'order_sn' => $order_sn,
                'service_sn' => $orderServiceInfo['service_sn']
            ]
        ]);
        if (!$updateResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 插入售后服务记录
        $insertResult = $orderDetailData->addData([
            'table' => '\Shop\Models\BaiyangOrderServiceLog',
            'bind'  => [
                'service_sn'  => $orderServiceInfo['service_sn'],
                'status'      => 2,
                'operator_id' => $operator_id,
                'log_content' => json_encode(array(0 => $this->message[2], 1 => date('Y-m-d H:i:s',time()))),
                'add_time'    => time(),
            ]
        ]);
        if (!$insertResult) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 插入订单/服务单操作日志表
        $insertOperationLog = $operationLogData->addData([
            'table' => '\Shop\Models\BaiyangOrderOperationLog',
            'bind'  => [
                'belong_sn'   => $orderServiceInfo['service_sn'],
                'belong_type' => 2,
                'content' => '财务审核通过，待退款',
                'operation_log' => !empty($orderServiceInfo) ? json_encode($orderServiceInfo) : '',
                'operation_type' => 5,
                'operator_id' => $operator_id,
                'add_time'    => time(),
            ]
        ]);
        if (!$insertOperationLog) {
            $this->dbWrite->rollback();
            return $this->uniteReturnResultJson(HttpStatus::OPERATE_ERROR);
        }
        
        // 执行
        $this->dbWrite->commit();
    
        // 返回结果
        return $this->uniteReturnResultJson(HttpStatus::SUCCESS);
    }
    
    /**
     * @desc 申请退货退款接口 (商城调用海典接口)
     * @param array $param
     *       -service_sn string 拆单服务单号(*)
     * @return json array
     * @author qinliang
     */
    public function erpApplyREfundNotice($param)
    {
        // 格式化参数
        $param['service_sn'] = isset($param['service_sn']) && is_string($param['service_sn']) ? (string)$param['service_sn'] : '';
        
        // 判断参数合法性
        if (empty($param['service_sn'])) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
        
        // 实例data
        $orderData    = BaiyangOrderData::getInstance();
        $userData     = BaiyangUserData::getInstance();
        $supplierData = BaiyangSkuSupplierData::getInstance();
        $detailData   = BaiyangOrderDetailData::getInstance();
        $ReasonData   = BaiyangOrderGoodsReturnReasonData::getInstance();
        $historyData  = BaiyangOrderStatusHistoryData::getInstance();
        
        // 获取退款主表信息
        $returnReasonInfo = $ReasonData->getOrderGoodsReturnReason($param, true);
        if (!$returnReasonInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        
        // 获取订单信息
        $orderInfo = $orderData->getOrderData($returnReasonInfo, true);
        if (!$orderInfo) {
            return $this->uniteReturnResult(HttpStatus::NO_DATA);
        }
        
        // 获取订单是否已发货 (已发货订单不推送)
        $historyInfo = $historyData->getOrderHistory($returnReasonInfo['order_sn'], true);
        if ($historyInfo && $historyInfo['status'] == 8 || $orderInfo['status'] == 'shipped' || $orderInfo['status'] == 'evaluating' || $orderInfo['shop_id'] != 1) {
            return $this->uniteReturnResult(HttpStatus::ORDER_SHIPPED);
        }
        
        // 获取用户信息
        $userInfo = $userData->getUserInfo($orderInfo['user_id'], 'nickname,username');
        
        // 获取店铺信息
        $shopInfo = $supplierData->getSupplier($orderInfo['shop_id'], true);
        
        // 推送数据
        $sendReturnData = [];
        $sendReturnData['refunds']['groupid'] = 1019; //企业编码
        $sendReturnData['refunds']['eccode'] = 905; //平台编码
        $sendReturnData['refunds']['olshopid'] = $orderInfo['channel_subid']; //网店编码
        $sendReturnData['refunds']['busi_zone'] = 'busi_zone'; //机构区域
        $sendReturnData['refunds']['olorderno'] = $returnReasonInfo['order_sn']; //订单号
        $sendReturnData['refunds']['refund_id'] = $returnReasonInfo['service_sn']; //服务单号
        $sendReturnData['refunds']['oid'] = $returnReasonInfo['order_sn']; //子订单号
        $sendReturnData['refunds']['total_fee'] = $orderInfo['total']; //交易总金额
        $sendReturnData['refunds']['buyer_nick'] = isset($userInfo['nickname']) && !empty($userInfo['nickname']) ? $userInfo['nickname'] : $userInfo['username']; //买家昵称
        $sendReturnData['refunds']['created'] = date("Y-m-d H:i:s",$returnReasonInfo['add_time']); //退款申请时间
        $sendReturnData['refunds']['modified'] = date("Y-m-d H:i:s",time()); //更新时间
        $sendReturnData['refunds']['order_status'] = 1; //退款对应订单交易状态
        $sendReturnData['refunds']['off_status'] = 1; //退款状态
        $sendReturnData['refunds']['has_good_return'] = ($returnReasonInfo['return_type'] == 0 || $returnReasonInfo['return_type'] == 2) ? 1 : 0; //买家是否需要退货
        $sendReturnData['refunds']['descr'] = $returnReasonInfo['explain']; //退款说明
        $sendReturnData['refunds']['reason'] = $returnReasonInfo['reason']; //退款原因
        $sendReturnData['refunds']['seller_nick'] = isset($shopInfo['user_name']) ? $shopInfo['user_name'] : ''; //退货人信息
        $sendReturnData['refunds']['address'] = isset($shopInfo['address']) ? $shopInfo['address'] : ''; //退货人收货地址
        $sendReturnData['refunds']['refund_fee'] = $returnReasonInfo['refund_amount']; //实际退款金额-客服确定
        $sendReturnData['refunds']['payment'] = $orderInfo['real_pay']; //实际支付给商城的金额
        $sendReturnData['refunds']['num'] = 0; //退货数量
        $sendReturnData['refunds']['num_iid'] = 0; //商品ID
        $sendReturnData['refunds']['price'] = 0; //商品价格
        $sendReturnData['refunds']['title'] = 0; //商品标题
        
        //print_r($sendReturnData);exit;
        // 发送请求到海典 http://119.29.15.230:61222/gw/addrefund/1/1
        //$result = $this->sendDataNotice($sendReturnData, "/search/gw/addrefund/1/1");
        $result = $this->sendDataNotice($sendReturnData, "/search/gw/addrefund/1/1");
        
        // 返回结果
        if ($result['code'] == 1) {
            $httpCode = HttpStatus::SUCCESS;
        } else {
            $this->rPushData(CacheKey::ERP_ORDER_RETURN_REASON_NOTICE, $sendReturnData, true);
            $httpCode = HttpStatus::OPERATE_ERROR;
        }
        return $this->uniteReturnResult($httpCode);
    }
    
    
    /**
     * 发送数据
     * @param unknown $data
     * @return mixed
     */
    private function sendDataNotice($data, $strUrl)
    {
        $sendResult = false;
        if (isset($this->config->erp_url[$this->config->environment]) && !empty($this->config->erp_url[$this->config->environment])) {
            $erpUrl     = $this->config->erp_url[$this->config->environment] . $strUrl;
            $sendResult = json_decode($this->curl->sendPost($erpUrl, json_encode($data)),true);
        }
        return $sendResult;
    }
    
    /**
     * 追加数据
     * @param unknown $key 
     * @param unknown $data
     * @param string $is_json
     */
    private function rPushData($key, $data, $is_json = false)
    {
        $redis = $this->cache;
        $redis->selectDb(0);
        if ($is_json) {
            $data = json_encode($data);
        }
        return $redis->rPush($key, $data);
    }
    
    /**
     * @desc 定时任务 重新推送失败申请退款记录
     * @param unknown $param
     * @return \Shop\Home\Services\json
     */
    public function erpErrorREfundNotice($param)
    {
        // sign签名保证
        $sign = isset($param['sign']) && is_string($param['sign']) ? (string)$param['sign'] : '';
        
        // 随手拼接sign加密
        if ($sign !== 'as@SAg98*Fa2s%ASDF3@jfHK*98D72f') {
            return $this->uniteReturnResultJson(HttpStatus::VERIFY_FAILED);
        }
        
        // 获取未推送成功的申请退款数据
        $redis = $this->cache;
        $redis->selectDb(0);
        $noticeData = $redis->lRange(CacheKey::ERP_ORDER_RETURN_REASON_NOTICE,0,100);
        
        // 获取数据重新推送,推送失败继续保存
        if ($noticeData) {
            foreach($noticeData as $val) {
                $sendResult = $this->sendDataNotice(json_decode($val, true));
                $redis->lPop(CacheKey::ERP_ORDER_RETURN_REASON_NOTICE);
                if ($sendResult['code'] != 1) {
                    $this->rPushData(CacheKey::ERP_ORDER_RETURN_REASON_NOTICE, $val, true);
                    $this->log->error(json_encode($sendResult));
                }
            }
        }
        
        // 返回结果
        return $this->uniteReturnResultJson(HttpStatus::SUCCESS);
    }
    
    
    /**
     * @desc 海典同步发货信息到商城
     * @param unknown $param
     */
    /* public function erpExpressNotice($param)
    {
       
        // 格式化参数
        $order_sn        = isset($param['order_sn']) && is_string($param['order_sn']) ? htmlspecialchars($param['order_sn']) : '';
        $express_company = isset($param['express_company']) && is_string($param['express_company']) ? (string)htmlspecialchars($param['express_company']) : '';
        $express_no      = isset($param['express_no']) && is_string($param['express_no']) ? (string)htmlspecialchars(trim($param['express_no'])) : '';
        $order_send_time = isset($param['order_send_time']) ? strtotime($param['order_send_time']) : time();
    
        // 判断参数是否合法
        if (empty($order_sn) || empty($express_company) || empty($express_no)) {
            return $this->uniteReturnResultJson(HttpStatus::PARAM_ERROR, ['param'=> $param]);
        }
    
        // 获取订单信息
        $orderData = BaiyangOrderData::getInstance();
        $orderInfo = $orderData->getData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'id,status,express_type,user_id,order_sn,last_status,express,express_sn,delivery_time,send_time,channel_subid',
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $order_sn
            ]
        ], true);
        if (!$orderInfo) {
            return $this->uniteReturnResultJson(HttpStatus::NO_ORDER);
        }
    
        // 不是发货状态不允许修改订单物流信息
        if ($orderInfo['status'] != 'shipping') {
            return $this->uniteReturnResultJson(HttpStatus::NOT_TO_SEND);
        }
    
        //自提的无物流单号
        $express_company = $express_company == 'zps' ? '' : $express_company;
    
        // 通知易复诊订单状态变更
        if ($orderInfo['channel_subid'] != '95') {
            //$yfzUrl = 'http://221.215.222.117:8694/yfz/send_order_to_yfz'; 
            $yfzUrl = $this->config->wap_url[$this->config->environment] . '/yfz/send_order_to_yfz'; // 地址有待确认
            $this->curl->sendPost($yfzUrl, http_build_query(['order_id'=>$orderInfo['order_sn'], 'status'=>'paid']));
        }
    
        // 修改订单物流信和订单状态
        $updateResult = $orderData->updateData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => "status = 'shipped',last_status = 'shipping',express = '$express_company',express_sn = '$express_no',delivery_time = $order_send_time,send_time = $order_send_time",
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $order_sn
            ]
        ]);
    
        // 记录修改订单日志
        $orderAddLogResult = false;
        if ($updateResult) {
            $orderAddLogResult = $orderData->addOrderLog($orderInfo['user_id'], $orderInfo['order_sn'], $orderInfo);
        }
    
        // 返回结果
        $httpStatus = $orderAddLogResult ? HttpStatus::SUCCESS : HttpStatus::OPERATE_ERROR;
        return $this->uniteReturnResultJson($httpStatus);
    } */
    
    
    /**
     * @desc 订单数据推送到海典
     * @param unknown $total_sn
     */
    /* public function erpOrderNotice($param)
    {
        
        // 格式化参数 
        $total_sn = isset($param['total_sn']) && is_string($param['total_sn']) ? (string)$param['total_sn'] : '';
        
        // 判断参数是否合法
        if (empty($total_sn) || strstr($total_sn, OrderEnum::KJ)) {
            return $this->uniteReturnResult(HttpStatus::PARAM_ERROR);
        }
        
        // 实例Data
        $O2oData            = BaiyangO2oData::getInstance();
        $userData           = BaiyangUserData::getInstance();
        $orderData          = BaiyangOrderData::getInstance();
        $payDetailData      = BaiyangOrderPayDetailData::getInstance();
        $orderPromotionData = BaiyangOrderPromotionData::getInstance();

        $orderList = $orderData->getParentOrderData($total_sn);
        if (!$orderList) {
            return $this->uniteReturnResult(HttpStatus::NO_ORDER);
        }
        $orderList = $orderData->relationArray($orderList,'order_sn');
        
        // 获取订单详情信息
        $orderListDetail = $orderData->getData([
            'table' => '\Shop\Models\BaiyangOrderDetail',
            'column' => '*',
            'where'  => 'where total_sn = :total_sn:',
            'bind'   => [
                'total_sn' => $total_sn
            ]
        ]);
        if (!$orderListDetail) {
            return $this->uniteReturnResult(HttpStatus::NO_ORDER);
        }
        //print_r($orderListDetail);exit;
        $orderDetail = [];
        foreach ($orderListDetail as $k=>$v) {
            $orderDetail[$v['order_sn']][] = $v;
        }
        
        // 获取支付详情信息
        $orderListPayDetail = $payDetailData->getData([
            'table' => '\Shop\Models\BaiyangOrderPayDetail',
            'column' => '*',
            'where'  => 'where order_sn = :order_sn:',
            'bind'   => [
                'order_sn' => $total_sn
            ]
        ], true);
        if (!$orderListPayDetail) {
            return $this->uniteReturnResult(HttpStatus::NO_ORDER);
        }
        
        // 获取促销明细信息
        $promotionsDetailInfo = $orderPromotionData->getData([
            'table' => '\Shop\Models\BaiyangOrderPromotionDetail',
            'column' => '*',
            'where' => "where order_sn = :order_sn:",
            'bind' => [
                'order_sn' => $total_sn,
            ],
        ]);
        $promotionsDetail = [];
        if ($promotionsDetailInfo) {
            foreach ($promotionsDetailInfo as $k=>$v) {
                $promotionsDetail[$v['order_sn']][] = $v;
            }
        }
        
        //print_r($orderDetail);exit;
        // 推送数据
        $orderData = [];
        foreach ($orderList as $order_sn => $orderInfo) 
        {
            //订单信息
            $orderData[$order_sn]['order_sn'] = $orderInfo['order_sn']; // 子订单号
            $orderData[$order_sn]['order_channel'] = $orderInfo['channel_subid']; // 网店渠道
            $orderData[$order_sn]['order_status'] = 2; // 已付款
            $orderData[$order_sn]['order_time'] = date("Y-m-d H:i:s", $orderInfo['add_time']); // 下单时间
            $order_discount_money = $orderInfo['order_discount_money'] ? $orderInfo['order_discount_money'] : 0;
            $orderData[$order_sn]['detail_discount_money'] = $orderInfo['detail_discount_money'] ? $orderInfo['detail_discount_money'] : 0; // 折扣明细金额
            $orderData[$order_sn]['order_total_price'] = $orderData[$order_sn]['detail_discount_money'] + $orderInfo['total'] + $order_discount_money; // 订单总额
            $orderData[$order_sn]['pro_total_price'] = $orderData[$order_sn]['order_total_price'] - $orderInfo['carriage']; // 订单商品总价
            $orderData[$order_sn]['affix_money'] = $orderInfo['carriage']; // 油费
            $orderData[$order_sn]['order_need_pay_money'] = $orderInfo['total']; // 订单实际应支付金额包括
            $orderData[$order_sn]['order_discount_money'] = $order_discount_money; //整单折扣金额
            $orderData[$order_sn]['discount_remark'] = $orderInfo['discount_remark'] ? $orderInfo['discount_remark'] : ''; // 订单优惠备注
            $orderData[$order_sn]['pay_remark'] = $orderInfo['pay_remark']; // 订单支付明细备注
            $orderData[$order_sn]['user_remark'] = $orderInfo['buyer_message']; // 买家留言
            
            // 发票信息        
            $aaadata = json_decode($orderInfo['invoice_info'], true);
            $orderData[$order_sn]['invoice_type'] = $aaadata['type_id']; // 发票类型
            $orderData[$order_sn]['invoice_title'] = $aaadata['title_name']; // 发票抬头
            $orderData[$order_sn]['invoice_content'] = $aaadata['content_type']; // 发票内容
            $orderData[$order_sn]['invoice_money'] = $orderData[$order_sn]['invoice_type'] ? $orderInfo['total'] : 0; // 发票金额
            
            //付款信息
            $orderData[$order_sn]['order_payed_money'] = $orderInfo['total']; // 对应订单应付金额的实际已支付金额
            $orderData[$order_sn]['cash_on_delivery'] = 0; // 货到付款
            if ($orderInfo['payment_id'] == 3) {
                $orderData[$order_sn]['order_payed_money'] = $orderInfo['balance_price'] ? $orderInfo['balance_price'] : 0; // 对应订单应付金额的实际已支付金额
                $orderData[$order_sn]['cash_on_delivery'] = $orderData[$order_sn]['order_need_pay_money'] - $orderData[$order_sn]['order_payed_money']; // 货到付款
            }
            $orderData[$order_sn]['last_payed_time'] = $orderInfo['pay_time'] == 0 ? date("Y-m-d H:i:s", time()) : date("Y-m-d H:i:s", $orderInfo['pay_time']); //最后付款时间
            $orderData[$order_sn]['pay_type'] = $orderInfo['pay_type'] == 0 ? 2 : 1; // 支付类型
            $orderData[$order_sn]['pay_no'] = $orderInfo['order_sn']; // 支付明细号
            $orderData[$order_sn]['payid'] = $orderListPayDetail['payid']; // 支付方式id
            $orderData[$order_sn]['pay_name'] = $orderListPayDetail['pay_name']; // 支付方式名称
            $orderData[$order_sn]['pay_money'] = $orderListPayDetail['pay_money']; // 支付金额
            $orderData[$order_sn]['pay_time'] = $orderListPayDetail['pay_time']; // 支付时间
            $orderData[$order_sn]['trade_no'] = $orderListPayDetail['trade_no']; // 第三方支付流水号
            $orderData[$order_sn]['pay_remark'] = $orderListPayDetail['pay_remark']; // 支付备注
            
            // 用户信息
            $column = 'id,union_user_id,phone,nickname,username,email';
            $userInfo = $userData->getUserInfo($orderInfo['user_id'], $column);
            $orderData[$order_sn]['promoter_id'] = $userInfo['union_user_id']; // 通行证
            $orderData[$order_sn]['userid'] =  $userInfo['phone'] ? $userInfo['phone'] : $userInfo['id']; //下单人ID
            $orderData[$order_sn]['order_user_name'] = $userInfo['nickname'] ? $userInfo['nickname'] : $userInfo['username']; // 下单人
            $orderData[$order_sn]['user_login_account'] = $userInfo['phone'] ? $userInfo['phone'] : $userInfo['email']; // 登陆账号
            $orderData[$order_sn]['user_reg_mobile'] = isset($userInfo['phone']) ? $userInfo['phone'] : ''; // 注册手机号
            
            // 收货信息
            $orderData[$order_sn]['receiver_tel'] = $orderInfo['telephone']; // 收货人电话
            $orderData[$order_sn]['receiver_name'] = $orderInfo['consignee']; // 收货人
            
            $post_province = $O2oData->getRegionName($orderInfo['province'])['region_name'];
            $post_city = $O2oData->getRegionName($orderInfo['city'])['region_name'];
            $post_district = $O2oData->getRegionName($orderInfo['county'])['region_name'];
            $orderData[$order_sn]['post_province'] = $post_province; //省,
            $orderData[$order_sn]['post_city'] = $post_city; //市
            $orderData[$order_sn]['post_district'] = $post_district; //区
            $orderData[$order_sn]['post_address'] = preg_replace("/\s|　/","",trim($orderInfo['address'])); //去除所有空格
            $orderData[$order_sn]['post_code'] = $orderInfo['zipcode'];
            
            //O2O信息
            $regionInfo = $O2oData->getO2OType($orderInfo['county']);
            
            //自提的要排除
            if ($regionInfo && $orderInfo['express_type'] != 1 && !preg_match('/开封路88号/', $orderInfo['address'])) {
                //M('order')->where(array('order_sn'=>$order['order_sn']))->save(array('o2o_syn'=>1));
                if ($orderInfo['express_type'] < 2) {
                    $orderData[$order_sn]['user_remark'] .= ' (普通订单转极速达)';
                    $orderInfo['express_type'] = 3;
                }
            }
            if (($orderInfo['express_type'] == 2 || $orderInfo['express_type'] == 3 )) {
                if ($orderInfo['o2o_remark']) {
                    $o2o_order = 1;
                }
                $orderData[$order_sn]['order_type'] = 1;
                if ($orderInfo['express_type'] == 3) {
                    $orderData[$order_sn]['o2o_delivery_date'] = trim($orderInfo['o2o_remark'])?trim($orderInfo['o2o_remark']):'2099-01-01';
                    $orderData[$order_sn]['o2o_from_time'] = "00:00";
                    $orderData[$order_sn]['o2o_to_time'] = "23:59";
                } elseif ($orderInfo['express_type'] == 2) {
                    $date_array = explode('—', $orderInfo['o2o_remark']);
                    $date1 = strtotime($date_array[0]);
                    $date2 = strtotime($date_array[1]);
                    $orderData[$order_sn]['o2o_delivery_date'] = $date1 ? date('Y-m-d', $date1) : "";
                    $orderData[$order_sn]['o2o_from_time'] = date('H:i', $date1);
                    $orderData[$order_sn]['o2o_to_time'] = date('H:i', $date2);
                }
            } else {
                $orderData[$order_sn]['o2o_delivery_date'] = '';
                $orderData[$order_sn]['o2o_from_time'] = '';
                $orderData[$order_sn]['o2o_to_time'] = '';
                $orderData[$order_sn]['order_type'] = 0;
            }
            
            // 订单详细信息
            if (isset($orderDetail[$orderInfo['order_sn']]) && !empty($orderDetail[$orderInfo['order_sn']])) {
                foreach ($orderDetail[$orderInfo['order_sn']] as $key => $orderDetailInfo) {
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['pro_code'] = $orderDetailInfo['goods_id']; // 商品ID
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['pro_name'] = $orderDetailInfo['goods_name']; // 商品名称
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['detail_price'] = $orderDetailInfo['unit_price'] + $orderDetailInfo['discount_price']; // 商品单价
                    if ($orderDetailInfo['goods_type'] == 1) {
                        $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['detail_price'] = 0;
                    }
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['detail_quantity'] = $orderDetailInfo['goods_number']; // 商品数量
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['detail_total'] = ($orderDetailInfo['unit_price'] + $orderDetailInfo['discount_price']) * $orderDetailInfo['goods_number']; // 总额
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['detail_discount_money'] = $orderDetailInfo['discount_price'] * $orderDetailInfo['goods_number']; // 明细折扣总金额
                
                    // 集团绩效统计
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['promotion_origin'] = $orderDetailInfo['promotion_origin']; // 订单推广来源
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['promotion_code'] = $orderDetailInfo['promotion_code']; // 推广来源编码,处方单 对应处方号
                
                    // 大品牌
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['invitation_code'] = $orderDetailInfo['invite_code']; // 邀请码
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['code_bu'] = $orderDetailInfo['code_bu']; // 备案邀请码所属事业部
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['code_region'] = $orderDetailInfo['code_region']; // 备案邀请码所属大区
                    $orderData[$order_sn]['order_detail'][$orderDetailInfo['id']]['code_office'] = $orderDetailInfo['code_office']; // 备案邀请码所属分办
                }
            }
            
            // 促销信息
            if (isset($promotionsDetail[$orderInfo['order_sn']]) && !empty($promotionsDetail[$orderInfo['order_sn']]) ) {
                foreach ($promotionsDetail[$orderInfo['order_sn']] as $k1 => $v1) {
                    $orderData[$order_sn]['promotion_detail'][$v1['promotion_detail_id']]['discount_type'] = $v1['discount_type']; //促销类型
                    $orderData[$order_sn]['promotion_detail'][$v1['promotion_detail_id']]['promotion_id'] = $v1['promotion_id']; //促销ID
                    $orderData[$order_sn]['promotion_detail'][$v1['promotion_detail_id']]['promotion_name'] = $v1['promotion_name']; //促销名称
                    $orderData[$order_sn]['promotion_detail'][$v1['promotion_detail_id']]['promotion_remark'] = $v1['promotion_remark']; //促销描述
                    $orderData[$order_sn]['promotion_detail'][$v1['promotion_detail_id']]['discount_money'] = $v1['discount_money']; //折扣金额
                    $orderData[$order_sn]['promotion_detail'][$v1['promotion_detail_id']]['promotion_range'] = $v1['promotion_range']; //促销活动的范围
                }
            }
        }
        
        // 发送请求到海典
        $erpUrl = $this->config->erp_url[$this->config->environment] . '/service/orderInfo'; //地址有待商定
        $sendResult = json_decode($this->curl->sendPost($erpUrl, http_build_query($orderData)),true);
        
        // 返回结果
        return $this->uniteReturnResult($sendResult['status']);
    } */
   
}