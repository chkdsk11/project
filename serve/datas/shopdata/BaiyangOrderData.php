<?php
/**
 * @author 邓永军
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangOrder;
class BaiyangOrderData extends BaseData
{
    protected static $instance=null;

    /**
     * 订单总数
     * @param string $where 条件
     * @param string $join 联表
     * @param bool $isParent 是否统计母订单数
     * @return int
     * @author Chensonglu
     */
    public function getOrderNum($where = '', $join = '', $isParent = true)
    {
        $column = $isParent ? 'o.total_sn' : 'o.order_sn';
        $sql = "SELECT COUNT(DISTINCT {$column}) AS 'count' FROM baiyang_order o {$join} "
            . "WHERE o.is_dummy = 0 {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetch(\PDO::FETCH_ASSOC);
        return isset($ret['count']) ? $ret['count'] : 0;
    }

    /**
     * 查询所有订单数据
     * @param string $column 查询字段
     * @param string $where 条件
     * @param string $join 联表
     * @param string $group 分组
     * @param string $order 排序
     * @param string $limit 分页/限制条数
     * @return bool
     * @author Chensonglu
     */
    public function getTotalOrderAll($where = '',$join = '',$group = '',$order = '',$limit = '')
    {
        $sql = "SELECT o.total_sn,o.order_sn FROM baiyang_order o {$join} WHERE o.is_dummy = 0 {$where} {$group} {$order} {$limit}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * 查询订单数据
     * @param string $column 字段
     * @param string $where 条件
     * @param string $join 关联表
     * @param string $group 分组
     * @param string $order 排序
     * @param string $limit 条数
     * @return bool
     * @author Zhudan
     */
    public function getTotalOrderExcel($column = '',$where = '',$join = '',$group = '',$order = '',$limit = '')
    {
        $sql = "SELECT {$column} FROM baiyang_order  o "
            . " {$join} WHERE 1 {$where}  "
            . "{$group} {$order} {$limit}";

        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * 根据母订单号获取子订单号
     * @param $totalSn
     * @return array|bool
     * @author Chensonglu
     */
    public function getChildOrderSn($totalSn)
    {
        if ($totalSn) {
            $sql = "SELECT order_sn FROM baiyang_order WHERE total_sn = '{$totalSn}' ORDER BY goods_price DESC,id ASC";
            $stmt = $this->dbRead->prepare($sql);
            $stmt->execute();
            $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
            //判断查询结果
            if (count($ret)) {
                return array_column($ret, 'order_sn');
            }
            return false;
        }
        return false;
    }

    /**
     * 获取未审核主订单信息
     * @param $totalSn
     * @return array|bool
     * @author Chensonglu
     */
    public function getUnauditedOrder($totalSn)
    {
        if (!$totalSn) {
            return false;
        }
        $column = "total_sn,user_id,ordonnance_photo photo,callback_phone phone,status";
        $parentOrder = $this->getData([
            'column' => $column . ",1 isTotal",
            'table' => 'Shop\Models\BaiyangParentOrder',
            'where' => 'WHERE total_sn = :totalSn: AND audit_state = 0',
            'bind' => ['totalSn'=>$totalSn],
        ], true);
        if (!$parentOrder) {
            $parentOrder = $this->getData([
                'column' => $column . ",0 isTotal",
                'table' => 'Shop\Models\BaiyangOrder',
                'where' => 'WHERE total_sn = order_sn AND total_sn = :totalSn: AND audit_state = 0',
                'bind' => ['totalSn'=>$totalSn],
            ], true);
        }
        if ($parentOrder) {
            $order = $this->getData([
                'column' => "order_sn",
                'table' => 'Shop\Models\BaiyangOrder',
                'where' => 'WHERE total_sn = :totalSn: ',
                'bind' => ['totalSn'=>$totalSn],
            ]);
            $parentOrder['orderSn'] = $order ? array_column($order, 'order_sn') : [];
        }
        return $parentOrder;
    }

    /**
     * 是否易复诊订单
     * @param $orderSn 订单号
     * @return bool|int
     * @author Chensonglu
     */
    public function isPrescriptionOrder($orderSn)
    {
        if (!$orderSn) {
            return false;
        }
        return $this->count('Shop\Models\BaiyangPrescription', [
            'orderSn' => $orderSn
        ], "order_id = :orderSn:");
    }

    /**
     * 获取使用优惠券订单信息
     * @param $totalSn string 母订单号
     * @return bool
     * @author Chensonglu
     */
    public function getUseCouponOrderInfo($totalSn)
    {
        if (!$totalSn) {
            return false;
        }
        $sql = "SELECT * FROM baiyang_order WHERE total_sn = '{$totalSn}' AND user_coupon_price > 0";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * 查询子订单信息
     * @param $orderSn array 母/子订单号
     * @param bool $isTotal 是否根据母订单号查询
     * @return bool
     * @author Chensonglu
     */
    public function getOrderInfo ($orderSn, $isTotal = false)
    {
        if (!$orderSn) {
            return false;
        }
        if (is_array($orderSn)) {
            foreach ($orderSn as $key => $item) {
                $orderSn[$key] = "'{$item}'";
            }
            $where = $isTotal ? "total_sn IN (" . implode(',', $orderSn) .")" : "order_sn IN (" . implode(',', $orderSn) .")";
        } else {
            $where = $isTotal ? "total_sn = '{$orderSn}'" : "order_sn = '{$orderSn}'";
        }
        $column = "total_sn,order_sn,user_id,shop_id,add_time,pay_time,total,real_pay,status,payment_id,"
            . "province,city,county,pay_type,express_sn,express,channel_subid,consignee,express_type,"
            . "invoice_info,address,is_remind,telephone,zipcode,goods_price,carriage,order_discount_money,"
            . "balance_price,youhui_price,user_coupon_price,detail_discount_money,real_pay,delivery_time,"
            . "buyer_message,remark,audit_state,callback_phone,ordonnance_photo,order_type,invoice_type,more_platform_sign";
        $sql = "SELECT {$column} FROM baiyang_order WHERE {$where}";
        $stmt = $this->dbRead->prepare($sql);
        $stmt->execute();
        $ret = is_string($orderSn) ? $stmt->fetch(\PDO::FETCH_ASSOC) : $stmt->fetchall(\PDO::FETCH_ASSOC);
        //判断查询结果
        if (count($ret)) {
            return $ret;
        }
        return false;
    }

    /**
     * 售后完毕后 处理积分
     * @param $order_sn
     * $order_sn:订单号，$refundGoods：申请售后的商品[['goods_id'=>1234,'goods_number'=>1],['goods_id'=>1234,'goods_number'=>1]] ,$isAllRefund:是否整单退
     */
    public function refundOrderIntegral($service_sn){

        $service =   $this->getData([
            'column' =>'*',
            'table' => 'Shop\Models\BaiyangOrderGoodsReturnReason',
            'where' => 'WHERE service_sn=:service_sn:',
            'bind'  => [
                'service_sn'=>$service_sn
            ]
        ], true);

        $order_sn = $service['order_sn'];
        $order_info = $this->getOrderInfo($order_sn);


        $rule =  $this->getData([
            'column' =>'*',
            'table' => 'Shop\Models\BaiyangPointsRule',
            'where' => 'WHERE send_points_type=1',
        ], true);

        $refund_money = $service['real_amount'];

        //如果退积分不考虑运费就不需要执行下面一句  产品说的 不考虑运费
        //$refund_money += ($order_info['carriage']*($refund_money/($order_info['total']-$order_info['carriage'])));

        if($refund_money<=$order_info['total'] && $order_info['balance_price']<$order_info['total']){
            $orderRate = ($order_info['total']- $order_info['balance_price'])/$order_info['total'];
        }else{
            return false;
        }

        //退积分处理
        if($rule && $rule['order_return_points']>0){
            $refund_points = floor($rule['order_return_points']*$orderRate*$refund_money);  //计算应退积分（取整）
            $stmtP = $this->dbRead->prepare("SELECT `point` FROM baiyang_user WHERE id={$order_info['user_id']}");
            $stmtP->execute();
            $userPoint = $stmtP->fetch(\PDO::FETCH_ASSOC) ;
            if (isset($userPoint['point'])) {
                $refund_points = ($userPoint['point'] > $refund_points) ? $refund_points : $userPoint['point'];
                $this->dbWrite->begin();

                $sql = "update baiyang_user set `point`=`point`-{$refund_points} where id={$order_info['user_id']}";

                $stmt = $this->dbRead->prepare($sql);
                $result = $stmt->execute();

                $is_ok = false;
                if($result && $refund_points){
                    $is_ok =  $this->insert('Shop\Models\BaiyangPointsChange',[
                        'user_id'=>  $order_info['user_id'],
                        'order_sn'=> $order_sn,
                        'points'=> $refund_points,
                        'add_time'=>time(),
                        'sent_type'=>4,
                        'service_sn'=>$service['service_sn']
                    ]);
                }
                if($is_ok){
                    $this->dbWrite->commit();
                    return true;
                }else{
                    $this->dbWrite->rollback();
                }
            }
        }
        return false;
    }
}