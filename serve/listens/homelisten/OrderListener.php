<?php
namespace Shop\Home\Listens;
use Shop\Home\Datas\BaiyangGoodsComment;
use Shop\Home\Datas\BaiyangOrderData;
use Shop\Home\Datas\BaseData;
use Shop\Home\Datas\BaiyangSkuData;
use Shop\Models\HttpStatus;
use Shop\Models\CacheKey;
use Shop\Models\OrderEnum;

class OrderListener extends BaseListen
{
    /**
     * @desc 获取订单状态 (V2)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *    -array  orderInfo 订单数据
     *    -int    is_global 是否海外购订单(1:是 0:否)
     * @return string $orderStatus 订单状态
     * @author 吴俊华
     */
    public function getOrderStatus($event, $class, $param)
    {
        $orderInfo = $param['orderInfo'];
        $global = $param['is_global'];
        // 根据不同状态获取订单列表信息
        switch ($orderInfo['status']) {
            // 待支付
            case OrderEnum::ORDER_PAYING:
                $orderStatus = OrderEnum::ORDER_PAYING; break;
            // 待发货或待支付
            case OrderEnum::ORDER_SHIPPING:
                $orderStatus = ($orderInfo['audit_state'] == 1) ? OrderEnum::ORDER_SHIPPING : OrderEnum::ORDER_PAYING;
                break;
            // 待收货
            case OrderEnum::ORDER_SHIPPED: $orderStatus = OrderEnum::ORDER_SHIPPED; break;
            // 交易完成
            case OrderEnum::ORDER_EVALUATING: $orderStatus = OrderEnum::ORDER_FINISHED; break;
            case OrderEnum::ORDER_FINISHED: $orderStatus = OrderEnum::ORDER_FINISHED; break;
            // 交易关闭
            case OrderEnum::ORDER_CANCELED: $orderStatus = OrderEnum::ORDER_CLOSED; break;
            case OrderEnum::ORDER_REFUND: $orderStatus = $global ? OrderEnum::ORDER_CLOSED : ''; break;
            default: $orderStatus = ''; break;
        }
        return $orderStatus;
    }

    /**
     * @desc 获取订单的评价信息 (V2)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *    -array  orderInfo 订单数据
     *    -array  orderInfo 订单数据
     * @return array $orderInfo 订单信息
     * @author 吴俊华
     */
    public function getOrderCommentInfo($event, $class, $param)
    {
        $orderInfo = $param['orderInfo'];
        $orderSn = $orderInfo['order_sn'];
        $commentGoodsList = $param['commentGoodsList'];
        $goodsCommentData = BaiyangGoodsComment::getInstance();
        $allowCommentNumber = count($commentGoodsList); // 允许评论的商品数
        $orderInfo['allow_comment_number'] = $allowCommentNumber ? $allowCommentNumber : '';
        $time = time() - 90*86400;

        if(!empty($commentGoodsList)){
            $goodsIds = implode(',',array_column($commentGoodsList,'goods_id'));
            $goodsComment = $goodsCommentData->getGoodsComment([
                'order_sn' => $orderSn,
                'goods_id' => $goodsIds,
            ]);
            if(!empty($goodsComment)){
                // 有评论过且已收货时间超过90天：已评价
                if($orderInfo['express_time'] < $time){
                    $orderInfo['comment_status'] = OrderEnum::EVALUATED; // 已评价
                }else{
                    $commentNumber = count($goodsComment); // 订单商品的评论数
                    // 只要有商品未评价过的(待评价状态)
                    if($allowCommentNumber != $commentNumber){
                        $orderInfo['comment_status'] = OrderEnum::EVALUATING; // 待评价
                    }else{
                        $commentIds = implode(',',array_column($goodsComment,'id'));
                        $goodsCommentImageNumber = count($goodsCommentData->getGoodsCommentImageNumber(['comment_id' => $commentIds])); // 已上传图片的商品数
                        if($goodsCommentImageNumber == 0){
                            // 没有上传过图片：追加评价
                            $orderInfo['comment_status'] = OrderEnum::APPEND_EVALUATED;
                            if($allowCommentNumber == 1){
                                $orderInfo['comment_id'] = $goodsComment[0]['id'];
                            }
                        }else{
                            // 所有商品都上传过图片:已评价；反之，追加评价
                            $orderInfo['comment_status'] = ($commentNumber == $goodsCommentImageNumber) ? OrderEnum::EVALUATED : OrderEnum::APPEND_EVALUATED;
                        }
                    }
                }
            }else{
                // 收货时间超过90天都没有评价过的:不能评价;(订单会变成已完成)
                $orderInfo['comment_status'] = ($orderInfo['express_time'] < $time) ? OrderEnum::NOT_EVALUATED : OrderEnum::EVALUATING;
            }
        }else{
            // 只有处方商品、赠品：不能评价
            $orderInfo['comment_status'] = OrderEnum::NOT_EVALUATED; // 不能评价
        }
        if($orderInfo['comment_status'] == OrderEnum::NOT_EVALUATED){
            $orderInfo['allow_comment_number'] = '';
        }
        return $orderInfo;
    }

    /**
     * @desc 获取不同状态的订单数量 (V2)
     * @param string $event 侦听器方法
     * @param object $class 对象
     * @param array  $param
     *    -int  user_id 用户id
     * @return string $orderStatus 订单状态
     * @author 吴俊华
     */
    public function getOrderNumberByStatus($event, $class, $param)
    {
        $userId = $param['user_id'];
        // 根据状态得到统计数据
        $data[OrderEnum::ORDER_ALL] = $this->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_ALL]);
        $data[OrderEnum::ORDER_PAYING] = $this->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_PAYING]);
        $data[OrderEnum::ORDER_SHIPPING] = $this->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_SHIPPING]);
        $data[OrderEnum::ORDER_SHIPPED] = $this->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_SHIPPED]);
        $data[OrderEnum::ORDER_EVALUATING] = $this->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_EVALUATING]);
        $data[OrderEnum::ORDER_REFUND] = $this->getCountOrderByStatus(['user_id' => $userId, 'status' => OrderEnum::ORDER_REFUND]);
        return $data;
    }

    /**
     * @desc 获取不同状态的订单数量 (V2)
     * @param array $param
     *      -int user_id  用户id
     *      -string status  订单状态
     * @return int 结果信息
     * @author  吴俊华
     */
    private function getCountOrderByStatus(array $param)
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
                $param['condition'] = "(status = 'shipping' or (status = 'refund' and last_status = 'shipping')) and audit_state = 1 and is_refund = 0";
                break;
            // 待收货订单
            case OrderEnum::ORDER_SHIPPED:
                $param['condition'] = "(status = 'shipped' or (status = 'refund' and last_status = 'shipped')) and is_refund = 0";
                break;
            // 待评价订单
            case OrderEnum::ORDER_EVALUATING:
                $time = time() - 90*24*60*60;
                $param['condition'] = "status = 'evaluating' and express_time > {$time} and is_refund = 0";
                break;
            // 退款/售后订单
            case OrderEnum::ORDER_REFUND:
                $param['condition'] = "";
                break;
        }
        return $this->countOrderByStatus($param);
    }

    /**
     * @desc 计算不同状态的订单数量 (V2)
     * @param array $param
     *      -int user_id  用户id
     *      -string status  订单状态
     *      -string condition  条件
     * @return int 结果信息
     * @author  吴俊华
     */
    private function countOrderByStatus(array $param)
    {
        $userId = $param['user_id'];
        $where = "user_id = {$userId} and is_delete = 0 and order_type != 5";
        // 根据订单状态拼接对应条件
        if (isset($param['condition']) && !empty($param['condition'])) {
            $where .= " and {$param['condition']}";
        }
        $baseData = BaseData::getInstance();
        $orderCounts2 = 0;
        $parentWhere = $sonWhere = $globalWhere = $where;
        if($param['status'] != OrderEnum::ORDER_REFUND){
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
        }else{
            // 退款/售后服务单数量
            $sql = "SELECT COUNT(1) as counts FROM baiyang_order_goods_return_reason where user_id = {$userId} and status NOT IN (1,3,6)";
            $stmt = $this->dbWrite->prepare($sql);
            $stmt->execute();
            $ret = $stmt->fetch(\PDO::FETCH_ASSOC);   //单条
            $totalItem = !empty($ret) ? $ret['counts'] : 0;
        }
        return $totalItem > 0 ?  $totalItem : 0;
    }



}