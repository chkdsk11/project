<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 2017/12/21
 * Time: 9:43
 */

namespace Shop\Services;

use Shop\Datas\BaseData;
use Shop\Datas\BaiyangUserData;
use Shop\Services\BaseService;

class StatisticsService extends BaseService
{

    protected static $instance=null;

    /**
     * 单例
     * @return class
     */
    public static function getInstance()
    {
        if(empty(static::$instance)){
            static::$instance=new static();
        }
        return static::$instance;
    }

    /**
     * 用户统计查询
     * @param $channel_name 渠道
     * @param $startTime    开始时间
     * @param $endTime      结束时间
     * @return mixed
     */
    public function userCount($channel_name,$startTime,$endTime){
        //查询条件
        $table = 'Shop\Models\BaiyangUser';
        $channel_name = strtolower($channel_name);
        $where = array(
            'is_dummy'      =>  array('<>','1'),
            'channel_name'  =>  array('eq',"{$channel_name}"),
            'add_time'      =>  array(array('egt',$startTime),array('lt',$endTime)),
        );
        $userData = BaiyangUserData::getInstance();
        $userCount = $userData->count($table,null,$where);
        if(!$userCount) return 0;
        return $userCount;
    }
    /**
     * 用户总统计
     * @return mixed
     */
    public function totalUserCount(){
        $table = 'Shop\Models\BaiyangUser';
        $count = BaseData::getInstance()->getData([
            'column'    =>  "count('channel_name')",
            'table'     =>  $table,
            'where'     =>  "where is_dummy <> '1' and channel_name in ('app','pc','wap','wechat')",
            'group'     =>  'group by channel_name'
        ]);
        return $count;
    }

    /**
     * 各时间段用户统计数组
     * @return array
     */
    public function userCountList(){
        $timeArray = $this->getTime();

        $userCount = $this->totalUserCount();

        return array(
            array(
                'channel_name'=>'PC',
                'count'=>$userCount['1'],
                'countYesterday'=>$this->userCount('pc',$timeArray['yesterdayTime'],$timeArray['todayTime']),
                'countToday'=>$this->userCount('pc',$timeArray['todayTime'],$timeArray['tomorrowTime']),
                'countMonth'=>$this->userCount('pc',$timeArray['thisMonthTime'],$timeArray['nextMonthTime']),
                'countQuarter'=>$this->userCount('pc',$timeArray['quarterStartTime'],$timeArray['quarterEndTime']),
                'countYear'=>$this->userCount('pc',$timeArray['thisYearTime'],$timeArray['nextYearTime']),
            ),
            array(
                'channel_name'=>'APP',
                'count'=>$userCount['0'],
                'countYesterday'=>$this->userCount('app',$timeArray['yesterdayTime'],$timeArray['todayTime']),
                'countToday'=>$this->userCount('app',$timeArray['todayTime'],$timeArray['tomorrowTime']),
                'countMonth'=>$this->userCount('app',$timeArray['thisMonthTime'],$timeArray['nextMonthTime']),
                'countQuarter'=>$this->userCount('app',$timeArray['quarterStartTime'],$timeArray['quarterEndTime']),
                'countYear'=>$this->userCount('app',$timeArray['thisYearTime'],$timeArray['nextYearTime']),
            ),
            array(
                'channel_name'=>'WAP',
                'count'=>$userCount['2'],
                'countYesterday'=>$this->userCount('wap',$timeArray['yesterdayTime'],$timeArray['todayTime']),
                'countToday'=>$this->userCount('wap',$timeArray['todayTime'],$timeArray['tomorrowTime']),
                'countMonth'=>$this->userCount('wap',$timeArray['thisMonthTime'],$timeArray['nextMonthTime']),
                'countQuarter'=>$this->userCount('wap',$timeArray['quarterStartTime'],$timeArray['quarterEndTime']),
                'countYear'=>$this->userCount('wap',$timeArray['thisYearTime'],$timeArray['nextYearTime']),
            ),
            array(
                'channel_name'=>'微商城',
                'count'=>$userCount['3'],
                'countYesterday'=>$this->userCount('wechat',$timeArray['yesterdayTime'],$timeArray['todayTime']),
                'countToday'=>$this->userCount('wechat',$timeArray['todayTime'],$timeArray['tomorrowTime']),
                'countMonth'=>$this->userCount('wechat',$timeArray['thisMonthTime'],$timeArray['nextMonthTime']),
                'countQuarter'=>$this->userCount('wechat',$timeArray['quarterStartTime'],$timeArray['quarterEndTime']),
                'countYear'=>$this->userCount('wechat',$timeArray['thisYearTime'],$timeArray['nextYearTime']),
            ),
        );
    }

    /**
     * 商品查询
     * @param $where
     * @param $field
     * @return int|mixed
     */
    public function goodsField($where,$field,$returnOne = false){
        $where = 'where '.$where;
        $table = 'Shop\Models\BaiyangGoods';
        $return = BaseData::getInstance()->getData([
            'column'    =>  $field,
            'table'     =>  $table,
            'where'     =>  $where,
        ],$returnOne);
        return $return;
    }

    /**
     * 获取缺货商品数量
     * @param $where
     * @return int
     */
    public function getGoodsOutStockCount($where){
        $field = 'v_stock,goods_number,is_use_stock';
        $outStockList = $this->goodsField($where,$field,false);
        if($outStockList){
            $stockCount = 0;
            foreach ($outStockList as $item) {
                if($item['is_use_stock'] == '1'){
                    if($item['v_stock'] <= '0'){
                        ++$stockCount;
                    }
                }else{
                    if($item['goods_number'] <= '0'){
                        ++$stockCount;
                    }
                }
            }
            return $stockCount;
        }
    }

    /**
     * 商品列表统计
     * @return array
     */
    public function goodsCountList(){
        $timeArray = $this->getTime();
        return array(
            array(
                'type' => '国内',
                'total'	=> $this->goodsField("is_on_sale = '1' and is_global = '0'","count('id')",true),
                'stockOutCount' => $this->getGoodsOutStockCount("is_on_sale = '1' and is_global = '0'"),
                'yesterdaySaleCount'=>$this->goodsCount('order',$timeArray['yesterdayTime'],$timeArray['todayTime']),
                'todaySaleCount'=>$this->goodsCount('order',$timeArray['todayTime'],$timeArray['tomorrowTime']),
                'monthSaleCount'=>$this->goodsCount('order',$timeArray['thisMonthTime'],$timeArray['nextMonthTime']),
            ),
            array(
                'type' => '跨境',
                'total'	=> $this->goodsField("is_on_sale = '1' and is_global = '0'","count('id')",true),
                'stockOutCount' => $this->getGoodsOutStockCount("is_on_sale = '1' and is_global = '0'"),
                'yesterdaySaleCount'=>$this->goodsCount('kj_order',$timeArray['yesterdayTime'],$timeArray['todayTime']),
                'todaySaleCount'=>$this->goodsCount('kj_order',$timeArray['todayTime'],$timeArray['tomorrowTime']),
                'monthSaleCount'=>$this->goodsCount('kj_order',$timeArray['thisMonthTime'],$timeArray['nextMonthTime']),
            )
        );
    }

    /**
     * 商品统计
     * @param $orderType    订单类型，普通订单，海外购订单
     * @param $startTime    开始时间
     * @param $endTime      结束时间
     * @return int
     */
    public function goodsCount($orderType,$startTime,$endTime){
        $where = "where o.status in ('shipping','shipped','evaluating','finished') and o.add_time >= '{$startTime}' and o.add_time <= '{$endTime}' and od.goods_type = '0'";
        if($orderType == 'order') {
            $table = 'Shop\Models\BaiyangOrder as o';
            $join = "inner join Shop\Models\BaiyangOrderDetail od on od.order_sn = o.order_sn";
        }else{
            $table = 'Shop\Models\BaiyangKjOrder as o';
            $join = "inner join Shop\Models\BaiyangKjOrderDetail od on od.order_sn = o.order_sn";
        }
        $goodsCount = BaseData::getInstance()->getData([
            'column'    =>  "sum('od.goods_id')",
            'table'     =>  $table,
            'join'      =>  $join,
            'where'     =>  $where,
            'group'     =>  'group by od.goods_id',
        ],false);
        if(!$goodsCount) return 0;
        $count = 0;
        foreach ($goodsCount as $item) {
            $count += $item;
        }
        return $count;
    }

    /**
     * 成交订单总数
     * @return int|mixed
     */
    public function orderCount(){
        $table = 'Shop\Models\BaiyangOrder';
        $return = BaseData::getInstance()->getData([
            'column'    =>  "count('id')",
            'table'     =>  $table,
            'where'     =>  "where channel_subid in ('85','89','90','91','95') and status in('evaluating','finished') and is_dummy <> '1'",
        ],true);
        return $return;
    }

    /**
     * 订单统计
     * @param $where    查询条件
     * @param $field    字段
     * @return int
     */
    public function orderField($where,$field,$order='order'){
        $where = 'where '.$where;
        if($order =='order') {
            $table = 'Shop\Models\BaiyangOrder';
        }else{
            $table = 'Shop\Models\BaiyangKjOrder';
        }
        $orderField = BaseData::getInstance()->getData([
            'column'    =>  $field,
            'table'     =>  $table,
            'where'     =>  $where,
        ]);
        if(!$orderField) return 0;
        return $orderField;
    }

    /**
     * 订单各状态量统计
     * @return array
     */
    public function orderCountList(){
        $pcArray = $this->getOrderList('PC',"'95'");
        $appArray = $this->getOrderList('APP',"'89','90'");
        $wapArray = $this->getOrderList('WAP',"'91'");
        $wechatArray = $this->getOrderList('微商城',"'85'");
        return array($pcArray,$appArray,$wapArray,$wechatArray);
    }

    /**
     * 海外购订单各状态统计
     * @return array
     */
    public function kjOrderCountList(){
        $pcArray = $this->getOrderList('PC',"'95'",'kj_order');
        $appArray = $this->getOrderList('APP',"'89','90'",'kj_order');
        $wapArray = $this->getOrderList('WAP',"'91'",'kj_order');
        $wechatArray = $this->getOrderList('微商城',"'85'",'kj_order');
        return array($pcArray,$appArray,$wapArray,$wechatArray);
    }

    /**
     * 订单数据统计获取
     * @param $channel_name
     * @param $channel_subid
     * @param string $orderType
     * @return array
     */
    public function getOrderList($channel_name,$channel_subid,$orderType = 'order'){
        $where = "status in ('paying','shipping','shipped','evaluating','refund','canceled','finished') and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        $Array = array(
            'channel_name'=> $channel_name,
        );
        if($orderType == 'order'){
            $Total = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Total = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['count'] = $Total['count']?$Total['count']:0;
        $Array['total'] = $Total['total']?$Total['total']:0;
        $where = "status = 'paying' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Paying = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Paying = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['payingCount'] = $Paying['count']?$Paying['count']:0;
        $Array['payingTotal'] = $Paying['total']?$Paying['total']:0;
        $where = "status = 'shipping' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Shipping = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Shipping = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['shippingCount'] = $Shipping['count']?$Shipping['count']:0;
        $Array['shippingTotal'] = $Shipping['total']?$Shipping['total']:0;
        $where = "status = 'shipped' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Shipped = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Shipped = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['shippedCount'] = $Shipped['count']?$Shipped['count']:0;
        $Array['shippedTotal'] = $Shipped['total']?$Shipped['total']:0;
        $where = "status = 'evaluating' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Evaluating = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Evaluating = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['evaluatingCount'] = $Evaluating['count']?$Evaluating['count']:0;
        $Array['evaluatingTotal'] = $Evaluating['total']?$Evaluating['total']:0;
        $where = "status = 'refund' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Refund = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Refund = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['refundCount'] = $Refund['count']?$Refund['count']:0;
        $Array['refundTotal'] = $Refund['total']?$Refund['total']:0;
        $where = "status = 'canceled' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Canceled = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Canceled = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['canceledCount'] = $Canceled['count']?$Canceled['count']:0;
        $Array['canceledTotal'] = $Canceled['total']?$Canceled['total']:0;
        $where = "status = 'finished' and channel_subid in ({$channel_subid}) and is_dummy <> '1'";
        if($orderType == 'order'){
            $Finished = $this->orderField($where,"count('id') as count,sum('total') as total");
        }else{
            $Finished = $this->orderField($where,"count('id') as count,sum('total') as total",'kj_order');
        }
        $Array['finishedCount'] = $Finished['count']?$Finished['count']:0;
        $Array['finishedTotal'] = $Finished['total']?$Finished['total']:0;
        return $Array;
    }

    /**
     * 时间戳数组
     * @return array
     */
    public function getTime(){
        $day = date('d');
        $month = date('m');
        $year = date('Y');
        $timeArray = array();
        $timeArray['todayTime']= mktime(0,0,0,$month,$day,$year);
        $timeArray['yesterdayTime'] = mktime(0,0,0,$month,$day - 1,$year);
        $timeArray['tomorrowTime'] = mktime(0,0,0,$month,$day + 1,$year);
        $timeArray['thisMonthTime'] = mktime(0,0,0,$month,1,$year);
        $timeArray['nextMonthTime'] = mktime(0,0,0,$month + 1,1,$year);
        $timeArray['thisYearTime'] = mktime(0,0,0,1,1,$year);
        $timeArray['nextYearTime'] = mktime(0,0,0,1,1,$year + 1);

        $remainder = $month % 3;
        $quarterStartNum = $remainder;
        $quarterEndNum = $remainder;
        switch($remainder){
            case '0':$quarterStartNum = $quarterStartNum +2;$quarterEndNum = $quarterEndNum +1;break;
            case '1':$quarterStartNum = $quarterStartNum -1;$quarterEndNum = $quarterEndNum +2;break;
            case '2':$quarterStartNum = $quarterStartNum -1;break;
        }
        $timeArray['quarterStartTime'] = mktime(0,0,0,$month - $quarterStartNum,1,$year);
        $timeArray['quarterEndTime'] = mktime(0,0,0,$month + $quarterEndNum,1,$year);
        return $timeArray;
    }

}