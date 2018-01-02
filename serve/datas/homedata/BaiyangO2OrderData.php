<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Datas;

use Phalcon\Paginator\Adapter\Model as PageModel;
use Shop\Models\BaiyangOrder;
use Shop\Models\BaiyangOrderDetail;
use Shop\Models\BaiyangKjOrder;
use Shop\Models\BaiyangKjOrderDetail;
use Phalcon\Mvc\Model\Resultset\Simple as Resultset;
use Shop\Queue\Redis\Cli\Models\BaseModel;
use Shop\Models\BaiyangOrderShipping;
use Shop\Models\BaiyangCheckGlobalLogistrack;
use Shop\Models\BaiyangUserConsignee;
use Shop\Models\OrderEnum;


class BaiyangO2OrderData extends BaseData
{
    protected static $instance=null;

    public function isNewUser($param)
    {
        $user_id=$param;
        $result = $this->countData([
            'table' => '\Shop\Models\BaiyangOrder',
            'where' => 'where payment_id > :payment_id: AND user_id = :user_id: ',
            'bind' => [
                'payment_id' => 0,
                'user_id' => $user_id
            ]
        ]);
        if($result > 0){
            return 0;
        }else{
            return 1;
        }
        return $result;
    }

    /**
     * @desc 获取用户已支付的正常订单
     * @param array $param
     *       -int|string goods_id 商品id
     *       -int user_id 用户id
     * @return array|bool [] 用户订单信息|false
     * @author 吴俊华
     */
    public function getUserOrder($param)
    {
        //排除：已取消、待付款且已删除、退款且状态为退款中/退款完成的订单
        $userOrderCondition = [
            'table' => '\Shop\Models\BaiyangOrder as a',
            'join' => 'left join \Shop\Models\BaiyangOrderDetail as b on a.order_sn = b.order_sn left join \Shop\Models\BaiyangOrderGoodsReturnReason as c on a.order_sn = c.order_sn',
            'column' => 'a.order_sn,a.user_id,b.goods_id,b.goods_number,a.channel_subid',
            'bind' => [
                'user_id' => $param['user_id']
            ],
            'where' => "where a.user_id = :user_id: and b.goods_id in(".$param['goods_id'].") and (a.status in('shipping','shipped','evaluating','finished') OR (a.status = 'paying' and a.is_delete = 0) OR (a.status = 'refund' and c.status in(0,1)))"
        ];
        return $this->getData($userOrderCondition);
    }

    /**
     * @desc 获取订单列表，带分页
     * @param array $param
     *      -status string 订单状态
     *      -column string 字段
     *      -where string 条件
     *      -order string 排序
     *      -pageStart int 当前页码
     *      -pageSize int 每页条数
     * @param $rw string  数据实时，一致，读写切换 read读 write写
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getOrderList($param,$rw = 'read')
    {
        // 读写切换
        $db = $this->switchRwDb($rw);
        if($param['status'] == OrderEnum::ORDER_REFUND){
            // 退款/售后订单(需要特殊处理)
            $orderSql = "select od.order_sn,od.add_time from baiyang_order as od inner join baiyang_order_goods_return_reason as odrr on od.order_sn = odrr.order_sn and odrr.`status` in (0,2) and odrr.return_type = 2 where od.user_id = {$param['user_id']} and od.`status` ='refund' and od.is_delete = 0 and od.order_type != 5 group by od.order_sn";
        }else{
            $orderSql = "select {$param['column']} from baiyang_order where {$param['where']} UNION ALL select {$param['column']} from baiyang_kj_order where {$param['where']}";
        }
        if(isset($param['order']) && !empty($param['order'])){
            $orderSql .= " {$param['order']}";
        }
        $orderSql .= " limit {$param['limit']}";

        $baiyangOrder = new BaiyangOrder();
        // 分页查询
        $data = new PageModel([
            'data' => new Resultset(null,$baiyangOrder,$db->query($orderSql)),
            'page' => ($param['pageStart'] - 1) * $param['pageSize'],
            'limit' => $param['pageSize'],
        ]);

        $orderData = [];
        $orderSn = '';
        if(count($data->getPaginate()->items)){
            // 对象转为数组
            foreach($data->getPaginate()->items as $item){
                $orderSn .= "'".$item->order_sn."',";
                $orderData[] = $item->toArray();
                unset($item);
            }
            $orderSn = rtrim($orderSn,',');
            // 得到页数与条数
            $totalPages = $data->getPaginate()->total_pages;
            $totalItems = $data->getPaginate()->total_items;
            unset($data);
            return ['data' => $orderData,'order_sn' => (string)$orderSn,'total_page' => $totalPages,'total_item' => $totalItems];
        }
        unset($data);
        return [];
    }

    /**
     * 获得单个订单
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @param $rw string
     * @return []
     * @author  陈松炉
     */
    public function getTheOrder(array $param,string $rw='read')
    {
        //读写切换
        $this->switchRwDb($rw);

        $phql="select {$param['column']} from Shop\Models\BaiyangOrder where {$param['where']}";

        //普通订单
        $ret=$this->modelsManager->executeQuery($phql,$param['bind'])->getFirst();

        if(empty($ret)){
            $kjSql="select {$param['column']} from Shop\Models\BaiyangKjOrder where {$param['where']}";
            $ret=$this->modelsManager->executeQuery($kjSql,$param['bind'])->getFirst();
        }

        return $ret ? $ret->toArray() : [];
    }

    /**
     * @desc 获得单个订单信息
     * @param array $param
     *      -string column  字段
     *      -string where  条件
     *      -array bind  绑定参数
     * @param string $rw  读写行为
     * @param int $global  是否海外购订单 (1:海外购 0:普通订单)
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getOneOrder(array $param, string $rw = 'read', int $global = 0)
    {
        //读写切换
        $this->switchRwDb($rw);
        $table = $global ? 'Shop\Models\BaiyangKjOrder' : 'Shop\Models\BaiyangOrder';
        $condition = [
            'table' => $table,
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition, true);
    }

    /**
     * @desc 获取不同状态的订单数量
     * @param array $param
     *      -int user_id  用户id
     *      -string status  订单状态
     * @return int 结果信息
     * @author  吴俊华
     */
    public function getCountOrderByStatus(array $param)
    {
        // 根据不同状态获取订单数量
        switch ($param['status']) {
            // 所有订单
            case OrderEnum::ORDER_ALL:
                $param['condition'] = "";
                break;
            // 待支付订单
            case OrderEnum::ORDER_PAYING:
                $param['condition'] = "(status = 'paying' or (status = 'shipping' and audit_state = 0))";
                break;
            // 待发货订单
            case OrderEnum::ORDER_SHIPPING:
                $param['condition'] = "status = 'shipping' and audit_state = 1";
                break;
            // 待收货订单
            case OrderEnum::ORDER_SHIPPED:
                $param['condition'] = "status = 'shipped'";
                break;
            // 待评价订单
            case OrderEnum::ORDER_EVALUATING:
                $time = time() - 90*24*60*60;
                $param['condition'] = "status = 'evaluating' and express_time > {$time}";
                break;
        }
        return $this->countOrderByStatus($param);
    }

    /**
     * @desc 计算不同状态的订单数量
     * @param array $param
     *      -int user_id  用户id
     *      -string status  订单状态
     *      -string condition  条件
     * @return int 结果信息
     * @author  吴俊华
     */
    private function countOrderByStatus(array $param)
    {
        $where = "where user_id = :user_id: and is_delete = 0 and order_type != 5";
        // 根据订单状态拼接对应条件
        if (isset($param['condition']) && !empty($param['condition'])) {
            $where .= " and {$param['condition']}";
        }
        $bind = [
            'user_id' => $param['user_id']
        ];
        $KjOrderNum = $orderNum = 0;
        // 计算订单总数
        if($param['status'] == OrderEnum::ORDER_REFUND){
            // 退款/售后订单(需要特殊处理)
            $stmt = $this->dbRead->prepare("SELECT count(order_sn) AS counts FROM (SELECT od.order_sn FROM baiyang_order AS od INNER JOIN baiyang_order_goods_return_reason AS odrr ON od.order_sn = odrr.order_sn AND odrr.`status` IN (0,2) AND odrr.return_type = 2 WHERE od.user_id = {$param['user_id']} AND od.`status` ='refund' AND od.is_delete = 0 AND od.order_type != 5 GROUP BY od.order_sn) AS order_refund");
            $stmt->execute();
            $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
            if(!empty($ret)){
                $orderNum = $ret['counts'];
            }
        }else{
            // 普通订单数量
            $orderNum = $this->countData([
                'table' => '\Shop\Models\BaiyangOrder',
                'where' => $where,
                'bind' => $bind,
            ]);
            // 跨境订单数量
            $KjOrderNum = $this->countData([
                'table' => '\Shop\Models\BaiyangKjOrder',
                'where' => $where,
                'bind' => $bind,
            ]);
        }
        return $orderNum + $KjOrderNum;
    }

    /**
     * 得到用户收货地址
     * @param array $param
     *      -column string
     *      -where string
     *      -bind   []
     * @return []
     * @author  康涛
     */
    public function getUserReciveAddress(array $param)
    {
        if(!isset($param['column']) || empty($param['column'])){
            $param['column']='*';
        }
        $phql="select {$param['column']} from Shop\Models\BaiyangUserConsignee";
        if(isset($param['where']) && !empty($param['where'])){
            $phql.=" where {$param['where']}";
        }
        if(isset($param['order_by']) && !empty($param['order_by'])){
            $phql.=$param['order_by'];
        }
        $ret=[];
        if(isset($param['bind']) && is_array($param['bind']) && !empty($param['bind'])){
            $ret=$this->modelsManager->executeQuery($phql,$param['bind']);
        }else{
            $ret=$this->modelsManager->executeQuery($phql);
        }
        if(count($ret)){
            return $ret->toArray();
        }
        return $ret;
    }

    /**
     * @desc 验证订单是否能够申请退款 (待发货、待收货状态才能申请退款)
     * @param int $userId 用户id
     * @param string $orderSn 订单编号
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function verifyOrderIsCanRefund($userId, $orderSn)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => '*',
            'where' => "where user_id = :user_id: and order_sn = :order_sn: and status in('shipping','shipped')",
            'bind' => [
                'user_id' => $userId,
                'order_sn' => $orderSn,
            ],
        ];
        $data = $this->getData($condition,true);
        return $data;
    }

    /**
     * @desc 插入订单日志
     * @param int $userId 用户id
     * @param string $orderSn 订单编号
     * @param array  $logContent 订单内容
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function addOrderLog($userId, $orderSn, $logContent)
    {
        $result = $this->addData([
            'table' => '\Shop\Models\BaiyangOrderLog',
            'bind' => [
                'user_id' => $userId,
                'order_sn' => $orderSn,
                'log_time' => time(),
                'log_content' => serialize($logContent),
                'pcname' => php_uname(),
                'ipname' => $_SERVER['REMOTE_ADDR'],

            ]
        ]);
        return $result > 0 ? true : false;
    }

    /**
     * @desc 更改退货订单状态
     * @param string $orderSn 订单编号
     * @param string $status 订单状态
     * @param string $lastStatus 上一次订单的状态
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function updateReturnOrderStatus($orderSn, $status, $lastStatus)
    {
        $result = $this->updateData([
            'column' => 'status = :status:,last_status = :last_status:',
            'table' => '\Shop\Models\BaiyangOrder',
            'where' => 'where order_sn = :order_sn:',
            'bind' => [
                'order_sn' => $orderSn,
                'status' => $status,
                'last_status' => $lastStatus,
            ]
        ]);
        return $result;
    }

    /**
     * @desc 更改退货商品状态
     * @param string $orderSn 订单编号
     * @param int $status 是否申请退换货(1:已申请 0:未申请)
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function updateReturnGoodsStatus($orderSn, $status = 0)
    {
        $result = $this->updateData([
            'column' => 'is_return = :status:',
            'table' => '\Shop\Models\BaiyangOrderDetail',
            'where' => 'where order_sn = :order_sn: and goods_type = 0',
            'bind' => [
                'order_sn' => $orderSn,
                'status' => $status,
            ]
        ]);
        return $result;
    }

    /**
     * @desc 获取退货订单相关信息 (订单、商品、退货理由等)
     * @param int $userId 用户id
     * @param string $orderSn 订单编号
     * @return array [] 结果信息
     * @author 吴俊华
     */
    public function getReturnOrderInfo($userId, $orderSn)
    {
        $result = $this->getData([
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason as r',
            'join' => 'left join \Shop\Models\BaiyangOrder as o on o.order_sn = r.order_sn',
            'column' => 'o.order_sn,o.express_type,o.address,o.province,o.city,o.county,o.consignee,o.o2o_remark,o.invoice_type,
            o.e_invoice_url,o.callback_phone,o.invoice_info,o.ordonnance_photo,o.buyer_message,o.add_time,
            o.status,o.last_status,o.total,o.carriage,o.status,o.order_discount_money,o.detail_discount_money,
            o.shop_id,r.status return_status, r.return_type, r.reason, r.explain, r.images',
            'where' => 'where o.user_id = :user_id: and r.order_sn = :order_sn: and o.status = :status: and r.status = 0',
            'bind' => [
                'user_id' => $userId,
                'order_sn' => $orderSn,
                'status' => OrderEnum::ORDER_REFUND,
            ]
        ],true);
        return $result;
    }

    /**
     * @desc 获取订单的物流信息
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @param int $global  是否海外购订单 (1:海外购 0:普通订单)
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getLogisticsInfo(array $param, int $global = 0)
    {
        $table = $global ? 'Shop\Models\BaiyangCheckGlobalLogistrack' : 'Shop\Models\BaiyangOrderShipping';
        $condition = [
            'table' => $table,
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition, true);
    }

    /**
     * @desc 更改订单信息
     * @param array $param
     *      -string column 字段
     *      -string where 条件
     *      -array bind 参数绑定
     * @param int $global  是否海外购订单 (1:海外购 0:普通订单)
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function updateOrderInfo(array $param, int $global = 0)
    {
        $table = $global ? 'Shop\Models\BaiyangKjOrder' : 'Shop\Models\BaiyangOrder';
        $condition = [
            'table' => $table,
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->updateData($condition);
    }

    /**
     * @desc 更新cps订单信息
     * @param array $param
     *      -string column 字段
     *      -string where 条件
     *      -array bind 参数绑定
     * @return bool true|false 结果信息
     * @author 吴俊华
     */
    public function updateCpsOrderInfo(array $param)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangCpsOrderLog',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->updateData($condition);
    }

    /**
     * @desc 插入订单支付详情
     * @param array $param
     * @return bool true|false 结果信息
     * @author 柯琼远
     */
    public function insertOrderPayDetail($param) {
        // 余额支付
        if ($param['costBalance'] > 0) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrderPayDetail',
                'bind'  => array(
                    'order_sn'        => $param['orderSn'],
                    'order_channel'   => $this->config->channel_subid,
                    'payid'           => '905_20',
                    'pay_name'        => '商城余额支付',
                    'pay_money'       => $param['costBalance'],
                    'pay_time'        => date('Y-m-d H:i:s'),
                    'trade_no'        => $param['expendSn'],
                    'pay_remark'      => "用户使用商城余额支付{$param['costBalance']}元",
                    'create_time'     => date('Y-m-d H:i:s'),
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        // 货到付款
        if ($param['paymentId'] == 3) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrderPayDetail',
                'bind'  => array(
                    'order_sn'        => $param['orderSn'],
                    'order_channel'   => $this->config->channel_subid,
                    'payid'           => '905_17',
                    'pay_name'        => '货到付款',
                    'pay_money'       => $param['costPrice'],
                    'pay_time'        => date('Y-m-d H:i:s'),
                    'trade_no'        => '',
                    'pay_remark'      => '',
                    'create_time'     => date('Y-m-d H:i:s'),
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @desc 插入订单
     * @param array $param
     * @return bool true|false 结果信息
     * @author 柯琼远
     */
    public function insertOrder($param) {
        $param['invoiceInfo'] = '';
        $param['invoiceMoney'] = 0;
        if (bcsub($param['costPrice'], $param['costBalance'], 2) == 0) {
            $param['invoiceType'] = 0;
        }
        if ($param['invoiceType'] > 0) {
            $invoiceContentType = $param['rxExist'] == 1 ? 10 : 16;
            $param['invoiceInfo'] = array(
                'title_type' => ($param['invoiceType'] == 1) ? '个人': '单位',
                'title_name' => $param['invoiceHeader'],
                'content_type' => OrderEnum::$receiptContent[$invoiceContentType],
                'type_id' => 3,//电子发票
            );
            $param['invoiceInfo'] = json_encode($param['invoiceInfo'], JSON_UNESCAPED_UNICODE);
            $param['invoiceMoney'] = bcsub($param['costPrice'], $param['costBalance'], 2);
        }
        if (empty($param['channelName'])) {
            switch ($this->config->channel_subid) {
                case '95':
                    $param['channelName'] = 'pc';break;
                case '91':
                    $param['channelName'] = 'wap';break;
                case '90':
                    $param['channelName'] = 'android';break;
                case '89':
                    $param['channelName'] = 'ios';break;
                case '85':
                    $param['channelName'] = 'weixin';break;
                default :
                    $param['channelName'] = 'pc';
            }
        }
        // 支付备注信息
        $pay_remark = [];
        if ($param['costBalance'] > 0) $pay_remark[] = "用户使用余额支付{$param['costBalance']}元";
        if ($param['paymentId'] == 3) $pay_remark[] = "货到付款" . bcsub($param['costPrice'], $param['costBalance'], 2);
        $pay_remark = implode(';', $pay_remark);
        $addData = array(
            'table' => '\Shop\Models\BaiyangOrder',
            'bind'  => array(
                'agent_id' => 1,
                'user_id' => $param['userId'],
                'total_sn' => $param['orderSn'],
                'order_sn' => $param['orderSn'],
                'delivery_status' => 0,
                'consignee' => $param['consigneeInfo']['consignee'],
                'telephone' => $param['consigneeInfo']['telphone'],
                'zipcode' => $param['consigneeInfo']['zipcode'],
                'province' => $param['consigneeInfo']['province'],
                'city' => $param['consigneeInfo']['city'],
                'county' => $param['consigneeInfo']['county'],
                'address' => $param['consigneeInfo']['address'],
                'express' => '',
                'express_sn' => '',
                'total' => $param['costPrice'],
                'pay_remark' => $pay_remark,
                'real_pay' => $param['costBalance'],
                'carriage' => $param['freight'],
                'is_pay' => bcsub($param['costPrice'], $param['costBalance'], 2) == 0 ? 1 : 0,
                'pay_time' => $param['costBalance'] > 0 ? time() : 0,
                'received' => 0,
                'status' => $param['status'],
                'discount_remark' => isset($param['discount_remark']) ? $param['discount_remark'] :'',
                'last_status' => OrderEnum::ORDER_PAYING,
                'pay_type' => $param['paymentId'] == 3 ? 0 : 1,
                'express_type' => $param['expressType'],
                'o2o_remark' => !empty($param['o2oInfo']) ? $param['o2oInfo']['remark'] : '',
                'shop_id' => $param['shopId'],
                'invoice_type' => $param['invoiceType'] > 0 ? 3 : 0,
                'invoice_info' => $param['invoiceInfo'],
                'invoice_money' => $param['invoiceMoney'],
                'buyer_message' => $param['buyerMessage'],
                'is_comment' => 0,
                'is_return' => 0,
                'add_time' => time(),
                'audit_time' => $param['rxExist'] == 1 ? 0 : time(),
                'addr_id' => $param['addressId'],
                'goods_price' => $param['goodsTotalPrice'],
                'user_coupon_id' => !empty($param['couponInfo']) ? $param['couponInfo']['coupon_sn'] : '',
                'user_coupon_price' => !empty($param['couponInfo']) ? $param['couponInfo']['coupon_price'] : 0,
                'order_discount_money' => $param['orderDiscountMoney'],
                'detail_discount_money' => "0.00",
                'youhui_price' => $param['youhuiPrice'],
                'balance_price' => $param['costBalance'],
                'payment_name' => $param['paymentName'],
                'payment_id' => $param['paymentId'],
                'payment_code' => '',
                'channel_subid' => $this->config->channel_subid,
                'channel_name' => $param['channelName'],
                'trade_no' => '',
                'express_status' => 0,
                'express_time' => 0,
                'allow_comment' => $param['allRx'] == 1 ? 0 : 1,
                'audit_state' => $param['rxExist'] == 1 ? 0 : 1,
                'callback_phone' => $param['rxExist'] == 1 ? $param['callbackPhone'] : '',
                'ordonnance_photo' => $param['rxExist'] == 1 ? $param['ordonnancePhoto'] : '',
            )
        );
        if (!$this->addData($addData)) {
            return false;
        }
        return true;
    }

}