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


class BaiyangOrderData extends BaseData
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
     * @desc 获取订单列表，带分页 (V2版本)
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
    public function getOrderListV2($param,$rw = 'read')
    {
        // 读写切换
        $column1 = $param['column'].',1 as sign'; // 子订单(普通)
        $column2 = $param['column'].',2 as sign'; // 跨境订单
        $column3 = $param['column'].',3 as sign'; // 母订单(普通)
        $parentWhere = $sonWhere = $globalWhere = $param['where'];

        if($param['status'] == OrderEnum::ORDER_ALL){
            // 所有订单
            $parentWhere = $parentWhere." and (payment_id = 0 or (payment_id > 0 and audit_state != 1))";
            $sonWhere = $sonWhere." and (order_sn = total_sn or (payment_id > 0 and audit_state = 1))";
            $orderSql = "select {$column1} from baiyang_order where {$sonWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere} UNION ALL select {$column3} from baiyang_parent_order where {$parentWhere}";
        }elseif ($param['status'] == OrderEnum::ORDER_SHIPPING || $param['status'] == OrderEnum::ORDER_SHIPPED){
           $globalWhere .= " and status != 'refund'";
            // 待发货或待收货订单
            $orderSql = "select {$column1} from baiyang_order where {$sonWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere}";
        }elseif ($param['status'] == OrderEnum::ORDER_PAYING){
            // 待付款订单
            $parentWhere = $parentWhere." and (payment_id = 0 or (payment_id > 0 and audit_state != 1))" ;
            $sonWhere = $sonWhere." and order_sn = total_sn";
            $orderSql = "select {$column3} from baiyang_parent_order where {$parentWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere} UNION ALL select {$column1} from baiyang_order where {$sonWhere}";
        }else{
            // 待评价订单
            $orderSql = "select {$column1} from baiyang_order where {$sonWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere}";
        }
        if(isset($param['order']) && !empty($param['order'])){
            $orderSql .= " {$param['order']}";
        }
        if(isset($param['limit']) && !empty($param['limit'])){
            $orderSql .= " limit {$param['limit']}";
        }
        $stmt = $this->dbRead->prepare($orderSql);
        $stmt->execute();
        $orderData = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if(empty($orderData)){
            return [];
        }
        return ['data' => $orderData];
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
        $column1 = $param['column'].',1 as sign'; // 子订单(普通)
        $column2 = $param['column'].',2 as sign'; // 跨境订单
        $column3 = $param['column'].',3 as sign'; // 母订单(普通)
        $parentWhere = $sonWhere = $globalWhere = $param['where'];

        if($param['status'] == OrderEnum::ORDER_ALL){
            // 所有订单
            $parentWhere = $parentWhere." and (payment_id = 0 or (payment_id > 0 and audit_state != 1))";
            $sonWhere = $sonWhere." and (order_sn = total_sn or (payment_id > 0 and audit_state = 1))";
            $orderSql = "select {$column1} from baiyang_order where {$sonWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere} UNION ALL select {$column3} from baiyang_parent_order where {$parentWhere}";
        }elseif($param['status'] == OrderEnum::ORDER_REFUND){
            // 退款/售后订单(需要特殊处理)
            $refundColumn1 = 'od.id,od.order_sn,od.total_sn,od.status,od.payment_id,od.add_time,1 as sign';
            $orderSql = "select {$refundColumn1} from baiyang_order as od inner join baiyang_order_goods_return_reason as odrr on od.order_sn = odrr.order_sn and odrr.`status` in (0,2) and odrr.return_type = 2 where od.user_id = {$param['user_id']} and od.`status` ='refund' and od.is_delete = 0 and od.order_type != 5 group by od.order_sn";
        }elseif($param['status'] == OrderEnum::ORDER_PAYING){
            // 待付款订单
            $parentWhere = $parentWhere." and (payment_id = 0 or (payment_id > 0 and audit_state != 1))" ;
            $sonWhere = $sonWhere." and order_sn = total_sn and (payment_id = 0 or (payment_id > 0 and audit_state != 1))";
            $orderSql = "select {$column3} from baiyang_parent_order where {$parentWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere} UNION ALL select {$column1} from baiyang_order where {$sonWhere}";
        }else{
            // 待发货或待收货订单
            $orderSql = "select {$column1} from baiyang_order where {$sonWhere} UNION ALL select {$column2} from baiyang_kj_order where {$globalWhere}";
        }

        if(isset($param['order']) && !empty($param['order'])){
            $orderSql .= " {$param['order']}";
        }
        if(isset($param['limit']) && !empty($param['limit'])){
            $orderSql .= " limit {$param['limit']}";
        }
        $stmt = $this->dbRead->prepare($orderSql);
        $stmt->execute();
        $orderData = $stmt->fetchall(\PDO::FETCH_ASSOC);
        if(empty($orderData)){
            return [];
        }
        return ['data' => $orderData];
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
                $param['condition'] = "status = 'evaluating' and express_time > {$time} and is_refund = 0";
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
        $where = "user_id = {$param['user_id']} and is_delete = 0 and order_type != 5";
        // 根据订单状态拼接对应条件
        if (isset($param['condition']) && !empty($param['condition'])) {
            $where .= " and {$param['condition']}";
        }
        $baseData = BaseData::getInstance();
        $orderCounts = $kjOrderCounts = $orderCounts1 = $orderCounts2 = 0;
        $parentWhere = $sonWhere = $globalWhere = $where;
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
        return $totalItem > 0 ?  $totalItem : 0;
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
            'where' => "where user_id = :user_id: and order_sn = :order_sn: and status in('shipping','shipped','finished','evaluating') and order_type!=5",
            'bind' => [
                'user_id' => $userId,
                'order_sn' => $orderSn,
            ],
        ];
        $data = $this->getData($condition,true);
        return $data;
    }

    /**
     * 验证订单是否能够申请退款(待发货、待收货状态才能申请退款)+是否有服务单正在处理
     * @param $orderSn
     * @param $status
     * @return array|bool
     */
    public function newVerifyOrderIsCanRefund($userId ,$orderSn)
    {

        $condition = [
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason ',
            'column' => '*',
            'where' => "where  order_sn = :order_sn: and status not in (1,3,6)",
            'bind' => [
                'order_sn' => $orderSn,
            ],
        ];
        $data = $this->getData($condition,true);
        if(!$data){
            return $this->verifyOrderIsCanRefund($userId,$orderSn);
        }
        return false;
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
     * @desc 更改退货商品状态
     * @param string $orderSn 订单编号
     * @param int $status 是否申请退换货(1:已申请 0:未申请)
     * @return bool true|false 结果信息
     * @author 朱丹
     */
    public function newUpdateReturnGoodsStatus($id,$refund_goods_number,$is_refund=0)
    {
        $result = $this->updateData([
            'column' => 'refund_goods_number= :refund_goods_number: ,is_refund=:is_refund:',
            'table' => '\Shop\Models\BaiyangOrderDetail',
            'where' => 'where id = :id:  ',
            'bind' => [
                'id' => $id,
                'refund_goods_number'=>$refund_goods_number,
                'is_refund'=>$is_refund
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
        // 主订单
        // 余额支付
        if (count($param['supplierList']) > 1) {
            if ($param['costBalance'] > 0) {
                $addData = array(
                    'table' => '\Shop\Models\BaiyangOrderPayDetail',
                    'bind' => array(
                        'order_sn' => $param['orderSn'],
                        'order_channel' => $this->config->channel_subid,
                        'payid' => '905_20',
                        'pay_name' => '商城余额支付',
                        'pay_money' => $param['costBalance'],
                        'pay_time' => date('Y-m-d H:i:s'),
                        'trade_no' => $param['expendSn'],
                        'pay_remark' => "用户使用商城余额支付{$param['costBalance']}元",
                        'create_time' => date('Y-m-d H:i:s'),
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
                    'bind' => array(
                        'order_sn' => $param['orderSn'],
                        'order_channel' => $this->config->channel_subid,
                        'payid' => '905_17',
                        'pay_name' => '货到付款',
                        'pay_money' => $param['costPrice'],
                        'pay_time' => date('Y-m-d H:i:s'),
                        'trade_no' => '',
                        'pay_remark' => '',
                        'create_time' => date('Y-m-d H:i:s'),
                    )
                );
                if (!$this->addData($addData)) {
                    return false;
                }
            }
        }
        // 子订单
        foreach ($param['supplierList'] as $key => $value) {
            $tempRemark = count($param['supplierList']) > 1 ? "；母订单号：{$param['orderSn']}" : "";
            if ($value['costBalance'] > 0) {
                $addData = array(
                    'table' => '\Shop\Models\BaiyangOrderPayDetail',
                    'bind'  => array(
                        'order_sn'        => $value['orderSn'],
                        'order_channel'   => $this->config->channel_subid,
                        'payid'           => '905_20',
                        'pay_name'        => '商城余额支付',
                        'pay_money'       => $value['costBalance'],
                        'pay_time'        => date('Y-m-d H:i:s'),
                        'trade_no'        => $param['expendSn'],
                        'pay_remark'      => "用户使用商城余额支付{$value['costBalance']}元".$tempRemark,
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
                        'order_sn'        => $value['orderSn'],
                        'order_channel'   => $this->config->channel_subid,
                        'payid'           => '905_17',
                        'pay_name'        => '货到付款'.$tempRemark,
                        'pay_money'       => $value['costPrice'],
                        'pay_time'        => date('Y-m-d H:i:s'),
                        'trade_no'        => '',
                        'pay_remark'      => trim($tempRemark, '；'),
                        'create_time'     => date('Y-m-d H:i:s'),
                    )
                );
                if (!$this->addData($addData)) {
                    return false;
                }
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
        // 主订单
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
                'type_id' => $param['invoiceType'],//电子发票
                'taxpayer_number' => $param['taxpayerNumber'],
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
        // 需要有多个子订单才插入母订单
        if (count($param['supplierList']) > 1) {
            $addData = array(
                'table' => '\Shop\Models\BaiyangParentOrder',
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
                    'status' => $param['paymentId'] > 0 ? "shipping" : "paying",
                    'discount_remark' => $param['discount_remark'],
                    'last_status' => "paying",
                    'pay_type' => $param['paymentId'] == 3 ? 0 : 1,
                    'express_type' => $param['expressType'],
                    'o2o_remark' => !empty($param['o2oInfo']) ? $param['o2oInfo']['remark'] : (isset($param['o2oTime']) && $param['o2oTime'] ? date('Y-m-d', $param['o2oTime']) : ''),
                    'shop_id' => $param['shopId'],
//                    'invoice_type' => $param['invoiceType'] > 0 ? 3 : 0,
                    'invoice_type' => $param['invoiceType'],
                    'invoice_info' => $param['invoiceInfo'],
                    'invoice_money' => $param['invoiceMoney'],
                    'buyer_message' => $param['buyerMessage'],
                    'is_comment' => 0,
                    'is_return' => 0,
                    'add_time' => time(),
                    'audit_time' => $param['needAudit'] == 1 ? 0 : time(),
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
                    'payment_code' => $param['paymentId'] == 7 ? 'balance' : '',
                    'channel_subid' => $this->config->channel_subid,
                    'channel_name' => $param['channelName'],
                    'trade_no' => '',
                    'express_status' => 0,
                    'express_time' => 0,
                    'allow_comment' => (($param['allRx'] == 1) || ($param['isDummy'] == 1)) ? 0 : 1,
                    'audit_state' => $param['needAudit'] == 1 ? 0 : 1,
                    'callback_phone' => $param['rxExist'] == 1 ? $param['callbackPhone'] : '',
                    'ordonnance_photo' => $param['rxExist'] == 1 ? $param['ordonnancePhoto'] : '',
                    'more_platform_sign' => $param['more_platform_sign'],
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        // 插入子订单
        foreach ($param['supplierList'] as $key => $value) {
            $invoiceInfo = '';
            $invoiceMoney = 0;
            if ($param['invoiceType'] > 0) {
                $invoiceContentType = $value['rxExist'] == 1 ? 10 : 16;
                $invoiceInfo = json_encode([
                    'title_type' => ($param['invoiceType'] == 1) ? '个人': '单位',
                    'title_name' => $param['invoiceHeader'],
                    'content_type' => OrderEnum::$receiptContent[$invoiceContentType],
                    'type_id' => $param['invoiceType'],//电子发票
                    'taxpayer_number' => $param['taxpayerNumber'],
                ], JSON_UNESCAPED_UNICODE);
                $invoiceMoney = bcsub($value['costPrice'], $value['costBalance'], 2);
            }
            // 支付备注信息
            $pay_remark = [];
            if ($value['costBalance'] > 0) $pay_remark[] = "用户使用余额支付{$value['costBalance']}元";
            if ($param['paymentId'] == 3) $pay_remark[] = "货到付款" . bcsub($value['costPrice'], $value['costBalance'], 2);
            $pay_remark = implode(';', $pay_remark);
            $addData = array(
                'table' => '\Shop\Models\BaiyangOrder',
                'bind'  => array(
                    'agent_id' => 1,
                    'user_id' => $param['userId'],
                    'total_sn' => $param['orderSn'],
                    'order_sn' => $value['orderSn'],
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
                    'total' => $value['costPrice'],
                    'pay_remark' => $pay_remark,
                    'real_pay' => $value['costBalance'],
                    'carriage' => $value['freight'],
                    'is_pay' => bcsub($value['costPrice'], $value['costBalance'], 2) == 0 ? 1 : 0,
                    'pay_time' => $value['costBalance'] > 0 ? time() : 0,
                    'received' => 0,
                    'status' => $param['paymentId'] > 0 ? "shipping" : "paying",
                    'discount_remark' => '',
                    'last_status' => 'paying',
                    'pay_type' => $param['paymentId'] == 3 ? 0 : 1,
                    'express_type' => $param['expressType'],
                    'o2o_remark' => !empty($param['o2oInfo']) ? $param['o2oInfo']['remark'] : (isset($param['o2oTime']) && $param['o2oTime'] ? date('Y-m-d', $param['o2oTime']) : ''),
//                    'shop_id' => $value['supplier_id'],
                    'shop_id' => $param['shopId'],
//                    'invoice_type' => $param['invoiceType'] > 0 ? 3 : 0,
                    'invoice_type' => $param['invoiceType'],
                    'invoice_info' => $invoiceInfo,
                    'invoice_money' => $invoiceMoney,
                    'buyer_message' => $param['buyerMessage'],
                    'is_comment' => 0,
                    'is_return' => 0,
                    'add_time' => time(),
                    'audit_time' => $param['needAudit'] == 1 ? 0 : time(),
                    'addr_id' => $param['addressId'],
                    'goods_price' => $value['goodsTotalPrice'],
                    'user_coupon_id' => !empty($param['couponInfo']) ? $param['couponInfo']['coupon_sn'] : '',
                    'user_coupon_price' => $value['couponPrice'],
                    'order_discount_money' => $value['orderDiscountMoney'],
                    'detail_discount_money' => "0.00",
                    'youhui_price' => $value['youhuiPrice'],
                    'balance_price' => $value['costBalance'],
                    'payment_name' => $param['paymentName'],
                    'payment_id' => $param['paymentId'],
                    'payment_code' => $param['paymentId'] == 7 ? 'balance' : '',
                    'channel_subid' => $this->config->channel_subid,
                    'channel_name' => $param['channelName'],
                    'trade_no' => '',
                    'express_status' => 0,
                    'express_time' => 0,
                    'allow_comment' => (($value['allRx'] == 1) || (isset($value['isDummy']) && $value['isDummy'] == 1)) ? 0 : 1,
                    'audit_state' => $param['needAudit'] == 1 ? 0 : 1,
                    'callback_phone' => $value['rxExist'] == 1 ? $param['callbackPhone'] : '',
                    'ordonnance_photo' => $value['rxExist'] == 1 ? $param['ordonnancePhoto'] : '',
                    'more_platform_sign' => $param['more_platform_sign'],
                )
            );
            if (!$this->addData($addData)) {
                return false;
            }
        }
        return true;
    }


    /**
     * 获取退款原因列表
     */
    public function RefundReason($type=0){

        $condition = [
            'table' => '\Shop\Models\BaiyangRefundReason',
            'column' => 'reason_desc',
            'where'=>'where type = :type:',
            'bind'=>[
                'type'=>$type
            ]
        ];
        $data = $this->getData($condition,false);
        return $data;
    }


    /**
     *
     * @param $service_sn
     * @param int $status
     * @param int $operator_id
     * @return bool|string
     */
    public function addOrderServiceLog($service_sn,Array $log_content,$status=0,$operator_name='',$operator_id=0){
        $addData = array(
            'table' => '\Shop\Models\BaiyangOrderServiceLog',
            'bind'  => array(
                'service_sn'=>$service_sn,
                'status'=>$status,
                'operator_id'=>$operator_id,
                'add_time'=>time(),
                'log_content'=>json_encode($log_content),
                'operator_name'=>$operator_name,
            )
        );
        return $this->addData($addData);
    }


    /**
     * 获取服务单日志列表
     * @param $param
     * @return array|bool
     */
    public function getOrderServiceLog($param){
        !isset($param['order'])?$param['order'] = 'order by id desc':'';
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderServiceLog',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
            'order'=> $param['order'],
        ];
        return $this->getData($condition, false);

    }
    /**
     * 获取服务单日志文案
     * @param int $status
     * @param array $data
     * @return array
     */
    public function getServiceStatusText(int $status,Array $data=array()){

        switch($status){
            case 0:
                $data[] = '您的服务单已申请，待客服审核中';
                break;
            case 1:
                $data[] = '您的申请不通过，如有疑问可以咨询客服';
                break;
            case 4:
                $data[] = '您的申请已受理，请在7天内寄回商品并提交物流，过期讲自动取消申请，如有疑问请与客服联系';
                break;
            case 5:
                $data[] = '物流信息已提交，客服会在收到商品后处理退款';
                break;
            case 2:
                $data[] = '您的申请已通过，退款处理中';
                break;
            case 3:
                $data[] = '您的服务单已退款，请注意查收';
                break;
            case 6:
                $data[] = '您的服务单已取消';
                break;
            case 7:
                $data[] = '您服务单的商品已收到，等待财务审核';
                break;
        }
        $data[] = date('Y-m-d H:i:s');
        return $data;
    }


    /**
     * 获取服务单
     * @param array $param
     * @return array|bool
     */
    public function getReturnService(array $param)
    {
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition, true);
    }


    /**
     * 收货取消的服务单
     * @param $orderSn
     * @return array|bool
     */
    public function getOrderCanCancelService($orderSn){
        return  $this->getReturnService([
            'column'=>'*',
            'where'=>"order_sn=:order_sn: and status in (0,4,5,2)",
            'bind'=>[
                'order_sn' => $orderSn,
            ]
        ],true);
    }

    /**
     * 获取服务单的退款商品
     * @param $param
     * @return array|bool
     */
    public function getReturnGoods($param){
        $condition = [
            'table' => '\Shop\Models\BaiyangOrderGoodsReturn',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition);
    }


    /**
     * 修改服务单
     * @param array $param
     * @return bool
     */
    public function updateReturnService(array $param)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangOrderGoodsReturnReason',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->updateData($condition);
    }

    /**
     * @desc 获取订单的服务单信息
     * @param array $param
     *      -column string
     *      -where string
     *      -bind []
     * @param int $shopId 店铺id (0:自营 大于0:非自营)
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getOrderServiceInfo(array $param,$shopId = 0)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangOrderGoodsReturnReason as a',
            'join' => 'inner join Shop\Models\BaiyangOrderServiceLog as b on a.service_sn = b.service_sn',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        $data = $this->getData($condition, true);
        if(!empty($data)){
            $this->handleServiceData($data,$shopId);
        }
        return $data;
    }

    /**
     * @desc 处理服务单数据
     * @param array $data 服务单信息 [一维数组]
     * @param int $shopId 店铺id
     * @author 吴俊华
     */
    public function handleServiceData(&$data,$shopId)
    {
        $data['log_content'] = json_decode($data['log_content'],true);
        if($data['status'] == 7) $data['status'] = 2;
        switch ($data['status']) {
            case 0  :   $data['service_info'] = '申请待处理';break;
            case 1  :   $data['service_info'] = '审核不通过';break;
            case 2  :   $data['service_info'] = '退款处理中';break;
            case 3  :   $data['service_info'] = '已完成';break;
            case 4  :   $data['service_info'] = '待寄回退货';break;
            case 5  :   $data['service_info'] = $shopId == 1 ? '待' . $this->config['company_name'] . '收货' : '待卖家收货';break;
            case 6  :   $data['service_info'] = '已取消';break;
            default:    $data['service_info'] = '';break;
        }
    }

    /**
     * @desc 获取订单的服务单数量
     * @param array $param
     *          -string order_sn 订单编号
     * @return mixed  结果信息
     * @author  吴俊华
     */
    public function getOrderServiceNumber(array $param)
    {
        $condition = [
            'table' => 'Shop\Models\BaiyangOrderGoodsReturnReason',
            'where' => 'where order_sn = :order_sn:',
            'bind' => [ 'order_sn' => $param['order_sn']],
        ];
        $data = $this->countData($condition);
        return !empty($data) ? $data : '';
    }


    public function getReturnServiceList($param,$rw='read'){
        // 读写切换
        $db = $this->switchRwDb($rw);
        $sql = "select {$param['column']} from  baiyang_order_goods_return_reason  WHERE user_id={$param['user_id']}  ";

        if(isset($param['order_sn']) && $param['order_sn']){
            $sql .= "AND order_sn='{$param['order_sn']}' ";
        }
        if(isset($param['order']) && !empty($param['order'])){
            $sql .= " {$param['order']}";
        }

         $baiyangOrder = new \Shop\Models\BaiyangOrderGoodsReturnReason();

        // 分页查询
        $data = new PageModel([
            'data' => new Resultset(null,$baiyangOrder,$db->query($sql)),
            'page' =>$param['pageStart'],
            'limit' => $param['pageSize'],
        ]);

        $orderData = [];
        $orderSn = '';
        if(count($data->getPaginate()->items)){
            // 对象转为数组
            foreach($data->getPaginate()->items as $item){
//                $orderSn .= "'".$item->order_sn."',";
                $service_row = $item->toArray();
                $service_row['status_text'] = $this->serviceStatusText($service_row['status']);
                $service_row['kefu_status_text'] = $this->getServiceStatusText($service_row['status']);
                $service_row['kefu_status_text'] = reset( $service_row['kefu_status_text'] );
                $service_row['add_time'] = date('Y-m-d H:i:s',$service_row['add_time']);
                $orderData[] = $service_row;
                unset($item);
            }
//            $orderSn = rtrim($orderSn,',');
            // 得到页数与条数
            $totalPages = $data->getPaginate()->total_pages;
            $totalItems = $data->getPaginate()->total_items;
            unset($data);
            return ['list' => $orderData,'pageCount' => $totalPages,'pageNum' => $totalItems,'pageStart'=>$param['pageStart'],'pageSize'=>$param['pageSize']];
        }
        unset($data);
        return [];
    }



    /**
     * 服务单的状态
     * @param $status
     * @return string
     */
    public function serviceStatusText($status){
        switch($status){
            case 0:
                $data = '申请待处理';
                break;
            case 1:
                $data = '已拒绝退款';
                break;
            case 2:
                $data = '待卖家退款';
                break;
            case 3:
                $data = '退款完成';
                break;
            case 4:
                $data = '待买家发货';
                break;
            case 5:
                $data = '卖家待收货';
                break;
            case 6:
                $data = '已撤销申请';
                break;
            case 7:
                $data = '卖家已收货';
                break;
            default:
                $data = '';
                break;
        }
        return $data;

    }
    
    
    /**
     * @desc 获得母订单信息
     * @param array $param
     *      -string column  字段
     *      -string where  条件
     *      -array bind  绑定参数
     * @return array [] 结果信息
     * @author  吴俊华
     */
    public function getParentOrder(array $param)
    {
        //读写切换
        $condition = [
            'table' => 'Shop\Models\BaiyangParentOrder',
            'column' => $param['column'],
            'where' => 'where ' . $param['where'],
            'bind' => $param['bind'],
        ];
        return $this->getData($condition, true);
    }
    
    /**
     * @desc 获得一个未支付的订单信息
     * @param string $order_sn
     * @return array [] 结果信息(order_sign:1-海外购，2-母订单，3-没有母订单的子订单，4-有母订单的子订单)
     * @author  柯琼远
     */
    public function getOrderInfo($order_sn) {
        $global = $order_sn[0] == OrderEnum::KJ ? 1 : 0;
        $condition = [
            'table' => 'Shop\Models\BaiyangOrder',
            'column' => '*',
            'where' => "where order_sn=:order_sn: and is_delete=0",
            'bind' => ['order_sn'=>$order_sn],
        ];
        if ($global) {
            // 海外购订单
            $condition['table'] = 'Shop\Models\BaiyangKjOrder';
            $result = $this->getData($condition, true);
            $order_sign = 1;
        } else {
            // 普通母订单
            $condition['table'] = 'Shop\Models\BaiyangParentOrder';
            $result = $this->getData($condition, true);
            $order_sign = 2;
            if (empty($result)) {
                // 普通子订单
                $condition['table'] = 'Shop\Models\BaiyangOrder';
                $result = $this->getData($condition, true);
                if (!empty($result)) {
                    $order_sign = $result['total_sn'] == $result['order_sn'] ? 3 : 4;
                }
            }
        }
        if (!empty($result)) {
            // 子订单支付前隐藏
            if ($order_sign == 4 && $result['payment_id'] == 0) {
                return [];
            }
            $result['order_sign'] = $order_sign;
            $result['order_id'] = $result['order_sn'];
            $result['paid'] = $global ? $result['order_total_amount'] : $result['total'];
        }
        return $result;
    }
    

    /**
     * @desc 获得母订单信息
     * @param array $param
     *       string order_sn
     * @param bool/false $returnOne 是否显示多条
     * @return array
     * @author 秦亮
     */
    public function getOrderData($param, $returnOne = false)
    {
        $reasonCondition = [
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'total_sn,order_sn,channel_subid,total,user_id,shop_id,pay_total,real_pay,status',
            'bind' => [
                'order_sn'   => $param['order_sn']
            ],
            'where' => 'where order_sn = :order_sn:',
        ];
        return $this->getData($reasonCondition, $returnOne);
    }

    /**
     * 是否开发票
     * @param $param array 参数
     *              - user_id int 用户ID
     *              - is_first int 是否开发票
     * @return int
     * @author CSL 20171120
     */
    public function getLastOrderInvoice($param)
    {
        if (!isset($param['user_id']) || !isset($param['is_first']) || (isset($param['is_first']) && !$param['is_first'])) {
            return 0;
        }
        $result = $this->getData([
            'table' => '\Shop\Models\BaiyangOrder',
            'column' => 'invoice_type',
            'bind' => [
                'user_id'   => $param['user_id'],
            ],
            'where' => 'where user_id = :user_id: ',
            'order' => 'ORDER BY add_time DESC'
        ], true);
        return isset($result['invoice_type']) && $result['invoice_type'] ? 1 : 0;
    }

}