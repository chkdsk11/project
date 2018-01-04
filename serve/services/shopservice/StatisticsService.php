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
     * 本周数据
     */
    public function getThisWeekCount(){
        $timeList = $this->getTimeByType('beginThisWeek');
        $table = 'Shop\Models\BaiyangOrder as o';
        $join = 'inner join Shop\Models\BaiyangRegion as r on o.province = r.id';
        $orderWhere = "where o.add_time >= '{$timeList['beginThisWeek']}' and o.add_time <='{$timeList['now']}' and is_dummy <> 1 ";
        $fields = "o.id,o.province,o.status,r.region_name";
        $return = array();
        $orderList = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $orderWhere,
            'join'      =>  $join
        ]);
        if($orderList){
            $return['order_count'] = count($orderList);
            //paying-待付款，shipping-待发货，shipped-待收货, evaluating-待评价, refund-退款/售后, canceled-取消订单 , finished-订单完成, draw-待抽奖 , await-待成团,  all-所有',
            $return['order_paid_count'] = 0;
            $return['order_canceled_count'] = 0;
            $paid_array = array('shipping','shipped','evaluating','finished');
            $tempArray = array();
            $areaArray = array();
            foreach ($orderList as $item) {
                if(in_array($item['status'],$paid_array)){
                    $return['order_paid_count'] ++;
                }
                if($item['status'] == 'canceled'){
                    $return['order_canceled_count'] ++;
                }
                if(in_array($item['region_name'],$tempArray)){
                    $areaArray[$item['region_name']] ++;
                }else{
                    $tempArray[] = $item['region_name'];
                    $areaArray[$item['region_name']] = 1;
                }
            }
            $return['orderList'] = $orderList;
            $return['areaOrder'] = $this->convertData($areaArray);
            $return['goodMoveList'] = $this->goodMoveList();
            $return['user_average_daily_count'] = $this->lastWeekUserAverageDailyRegisterCount();
            $return['daysOrder'] = $this->daysOrder();
            $return['weekOrderContrast'] = $this->weekOrderContrast();
            $return['channelOrderContrast'] = $this->channelOrderContrast();
            $return['weekUserLogin'] = $this->weekUserLogin();
            $return['weekUserRegister'] = $this->weekUserRegister();
            $return['weekUserRegister'] = $this->weekUserRegister();

        }
        return $return;
    }

    /**
     * 动销商品
     * @return mixed
     */
    public function goodMoveList(){
        $timeList = $this->getTimeByType('beginThisMonth');
        $table = 'Shop\Models\BaiyangOrder as o';
        $orderWhere = "where o.add_time >= '{$timeList['beginThisMonth']}' and o.add_time <='{$timeList['now']}'";
        $fields = "count(od.goods_id) as count,od.goods_name as name";
        $group = "group by od.goods_id";
        $join = "inner join Shop\Models\BaiyangOrderDetail as od on od.order_sn = o.order_sn";
        $orderList = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $orderWhere,
            'group'     =>  $group,
            'join'      =>  $join,
        ]);
        return $orderList;
    }

    /**
     * 过去一周用户注册
     * @return array
     */
    public function weekUserRegister(){
        $timeList = $this->getTimeByType('LastWeekFormNow');
        $beginDate = date('Y-m-d',$timeList['beginLastWeek']);
        $endDate = date('Y-m-d',$timeList['endLastWeek']);
        $table = 'Shop\Models\BaiyangUser';
        $where = "where last_login_time >= '{$beginDate}' and last_login_time <='{$endDate}'";
        $fields = "count('id') as count,DATE_FORMAT(last_login_time,'%Y-%m-%d') as day";
        $order = "order by day";
        $group = 'group by day';
        $return = array();
        $list = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $where,
            'order'     =>  $order,
            'group'     =>  $group
        ]);
        $begin = $timeList['beginLastWeek'];
        $now   = $timeList['endLastWeek'];
        $days = array();
        $return['days'] = array();
        $tempCount = array();
        for($i = $begin; $i<$now; $i += 86400){
            array_push($days,date('Y-m-d',$i));
            array_push($return['days'],date('m-d',$i));
        }
        $false = false;
        foreach ($days as $itemTemp) {
            if ($list) {
                foreach ($list as $item) {
                    if ($item['day'] == $itemTemp) {
                        $tempCount[] = intval($item['count']);
                        $false = true;
                        break;
                    }
                }
                if(!$false) $tempCount[] = 0;

                $false = false;
            }
        }
        $return['counts'] = $tempCount;
        $return['max'] = (!empty($tempCount))?max($tempCount):10;
        $return['sum'] = array_sum($tempCount);
        return $return;
    }

    /**
     * 过去一周用户登录
     * @return array
     */
    public function weekUserLogin(){
        $timeList = $this->getTimeByType('LastWeekFormNow');
        $table = 'Shop\Models\BaiyangUser';
        $where = "where add_time >= '{$timeList['beginLastWeek']}' and add_time <='{$timeList['endLastWeek']}'";
        $fields = "count('id') as count,FROM_UNIXTIME(add_time,'%Y-%m-%d') as day";
        $order = "order by day";
        $group = 'group by day';
        $return = array();
        $list = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $where,
            'order'     =>  $order,
            'group'     =>  $group
        ]);
        $begin = $timeList['beginLastWeek'];
        $now   = $timeList['endLastWeek'];
        $days = array();
        $return['days'] = array();
        $tempCount = array();
        for($i = $begin; $i<$now; $i += 86400){
            array_push($days,date('Y-m-d',$i));
            array_push($return['days'],date('m-d',$i));
        }
        $false = false;
        foreach ($days as $itemTemp) {
            if ($list) {
                foreach ($list as $item) {
                    if ($item['day'] == $itemTemp) {
                        $tempCount[] = intval($item['count']);
                        $false = true;
                        break;
                    }
                }
                if(!$false) $tempCount[] = 0;

                $false = false;
            }
        }
        $return['counts'] = $tempCount;
        $return['max'] = (!empty($tempCount))?max($tempCount):10;
        $return['sum'] = array_sum($tempCount);
        return $return;
    }

    /**
     * 数据转换
     * @param $areaArray
     * @return array
     */
    public function convertData($areaArray){
        $return = array();
        $provinceArray = array('北京','安徽','福建','甘肃','广东','广西','贵州','海南','河北','河南','黑龙江','湖北','湖南','吉林','江苏','江西','辽宁','内蒙古','宁夏','青海','山东','山西','陕西','上海','四川','天津','西藏','新疆','云南','浙江','重庆','香港','澳门','台湾');
        foreach ($areaArray as $key=>$item) {
            if(in_array($key,$provinceArray)){
                $return['list'][] = array('name'=>$key,'value'=>$item);
            }else{
                foreach ($provinceArray as $provinceItem) {
                    if(strpos($key,$provinceItem) !== false){
                        $return['list'][] = array('name'=>$provinceItem,'value'=>$item);
                        break;
                    }
                }
            }
        }
        if($return['list']){
            $return['max'] = max(array_column($return['list'],'value'));
        }else{
            $return['max'] = 10;
        }

        return $return;
    }

    /**
     * 15天订单对比
     * @return array
     */
    public function daysOrder(){
        $timeList = $this->getTimeByType('fifteenDays');
        $table = 'Shop\Models\BaiyangOrder as o';
        $orderWhere = "where o.add_time >= '{$timeList['begin']}' and o.add_time <='{$timeList['now']}'";
        $fields = "count(o.id) as count,o.status,FROM_UNIXTIME(o.add_time,'%Y-%m-%d') as day";
        $orderListFifteenDays = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $orderWhere,
            'order'     =>  'order by day',
            'group'     =>  'group by day'
        ]);

        $begin = $timeList['begin'];
        $now   = $timeList['now'];
        $returnList = array();
        $days = array();
        $returnList['days'] = array();
        $temp = array();
        for($i = $begin; $i<$now; $i += 86400){
            array_push($days,date('Y-m-d',$i));
            array_push($returnList['days'],date('m-d',$i));
        }

        if($orderListFifteenDays){
            $false = false;
            foreach ($days as $itemTemp) {
                foreach ($orderListFifteenDays as $item) {
                    if($item['day'] == $itemTemp){
                        $temp[] = $item['count'];
                        $false = true;
                        break;
                    }
                }
                if(!$false){
                    $temp[] = 0;
                }
                $false = false;
            }
            $percent = array();
            if($temp){
                foreach ($temp as $order_count_key=>$item_order_count) {
                    if($order_count_key != 0){
                        $percent[] = bcdiv(($temp[$order_count_key]['count'] - $temp[$order_count_key-1]['count']), $temp[$order_count_key-1]['count'],2) * 100;
                    }else{
                        $percent[] = 0;
                    }
                }
            }
            if(empty($percent)){
                $percent = ['0','0','0','0','0','0','0','0','0','0','0','0','0','0','0','0'];
            }
            $returnList['max_order_counts'] = max($temp);
            $returnList['min_order_counts'] = min($temp);
            $returnList['order_counts'] = $temp;
            $returnList['max_percent'] = max($percent);
            $returnList['min_percent'] = min($percent);
            $returnList['max'] = ($returnList['max_order_counts']>$returnList['max_percent'])?$returnList['max_order_counts']:$returnList['max_percent'];
            $returnList['min'] = ($returnList['min_order_counts']<$returnList['min_percent'])?$returnList['min_order_counts']:$returnList['min_percent'];
            $returnList['percent'] = $percent;
        }
        return $returnList;
    }

    /**
     * 上周和本周的订单对比
     */
    public function weekOrderContrast(){
        $lastTimeList = $this->getTimeByType('betweenLastWeek');
        $thisTimeList = $this->getTimeByType('beginThisWeek');
        $table = 'Shop\Models\BaiyangOrder';
        $where = "where add_time >= '{$lastTimeList['beginLastWeek']}' and add_time <='{$thisTimeList['now']}'";
        $where.=" and is_dummy <> 1";
        $fields = "id,add_time";
//        $fields.=",FROM_UNIXTIME(add_time,'%Y-%m-%d') as day";
        $orderList = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $where
        ]);
        $return = array();
        if($orderList){
            $return['last']['order_count'] = 0;
            $return['this']['order_count'] = 0;
            foreach ($orderList as $item) {
                if(($item['add_time'] >= $lastTimeList['beginLastWeek']) && ($item['add_time']) <= $lastTimeList['endLastWeek']){
                    $return['last']['order_count']++;
                }
                if(($item['add_time'] >= $thisTimeList['beginThisWeek']) && ($item['add_time']) <= $thisTimeList['now']){
                    $return['this']['order_count']++;
                }
            }
            $return['last']['average'] = ceil($return['last']['order_count']/7);
            $return['this']['average'] = ceil($return['this']['order_count']/7);
        }
        return $return;
    }

    /**
     * 渠道订单对比
     * @return array
     */
    public function channelOrderContrast(){
        $timeList = $this->getTimeByType('sevenDays');
        $table = 'Shop\Models\BaiyangOrder';
        $whereBase = "where add_time >= '{$timeList['begin']}' and add_time <='{$timeList['now']}'";
        $whereBase.=" and is_dummy <> 1 and channel_subid = ";
        $whereStr = "'85'";
        $where = $whereBase.$whereStr;
        $fields = "count('id') as count";
        $fields.=",FROM_UNIXTIME(add_time,'%Y-%m-%d') as day";
        $order = "order by day";
        $group = 'group by day';
        $orderListWechat = $this->getDataList($fields,$table,$where,$order,$group);
        $whereStr = "'89'";
        $where = $whereBase.$whereStr;
        $orderListIOS = $this->getDataList($fields,$table,$where,$order,$group);
        $whereStr = "'90'";
        $where = $whereBase.$whereStr;
        $orderListAndroid = $this->getDataList($fields,$table,$where,$order,$group);
        $whereStr = "'95'";
        $where = $whereBase.$whereStr;
        $orderListPC = $this->getDataList($fields,$table,$where,$order,$group);
        $begin = $timeList['begin'];
        $now   = $timeList['now'];
        $returnList = array();
        $days = array();
        $returnList['days'] = array();
        $temp = array();
        for($i = $begin; $i<$now; $i += 86400){
            array_push($days,date('Y-m-d',$i));
            array_push($returnList['days'],date('m-d',$i));
        }
        $false_wechat = false;
        $false_ios = false;
        $false_android = false;
        $false_pc = false;
        foreach ($days as $itemTemp) {
            if($orderListWechat) {
                foreach ($orderListWechat as $item) {
                    if ($item['day'] == $itemTemp) {
                        $temp['wechat'][] = $item['count'];
                        $false_wechat = true;
                        break;
                    }
                }
            }
            if($orderListIOS) {
                foreach ($orderListIOS as $item) {
                    if ($item['day'] == $itemTemp) {
                        $temp['ios'][] = $item['count'];
                        $false_ios = true;
                        break;
                    }
                }
            }
            if($orderListAndroid) {
                foreach ($orderListAndroid as $item) {
                    if ($item['day'] == $itemTemp) {
                        $temp['android'][] = $item['count'];
                        $false_android = true;
                        break;
                    }
                }
            }
            if($orderListPC) {
                foreach ($orderListPC as $item) {
                    if ($item['day'] == $itemTemp) {
                        $temp['pc'][] = $item['count'];
                        $false_pc = true;
                        break;
                    }
                }
            }
            if(!$false_wechat) $temp['wechat'][] = 0;
            if(!$false_ios) $temp['ios'][] = 0;
            if(!$false_android) $temp['android'][] = 0;
            if(!$false_pc) $temp['pc'][] = 0;

            $false_wechat = false;
            $false_ios = false;
            $false_android = false;
            $false_pc = false;
        }
        $returnList['channel'] = $temp;
        return $returnList;
    }

    /**
     * 获取数据
     * @param $fields
     * @param $table
     * @param $where
     * @param $order
     * @param $group
     * @return mixed
     */
    public function getDataList($fields,$table,$where,$order,$group){
        return BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $where,
            'order'     =>  $order,
            'group'     =>  $group
        ]);
    }

    /**
     * 上一周日用户日均注册
     * @return mixed
     */
    public function lastWeekUserAverageDailyRegisterCount(){
        $timeList = $this->getTimeByType('LastWeekFormNow');
        $table = 'Shop\Models\BaiyangUser';
        $where = "where add_time >= '{$timeList['beginLastWeek']}' and add_time <='{$timeList['endLastWeek']}'";
        $fields = "count(id)";
        $count = BaseData::getInstance()->getData([
            'column'    =>  $fields,
            'table'     =>  $table,
            'where'     =>  $where
        ],true);
        $count = ceil($count/7);
        return $count;
    }

    /**
     * 获取时间戳
     * @param $type
     * @return bool
     */
    public function getTimeByType($type){
        $resultList = false;
        switch ($type){
            // 今日起止时间
            case 'beginToday':
                $resultList['begin'] = mktime(0,0,0,date('m'),date('d'),date('y'));
                $resultList['now'] = time();
                break;
            case 'sevenDays':
                $resultList['begin'] = mktime(0,0,0,date('m'),date('d')-6,date('y'));
                $resultList['now'] = time();
                break;
            //15天时间
            case 'fifteenDays':
                $resultList['begin'] = mktime(0,0,0,date('m'),date('d')-15,date('y'));
                $resultList['now'] = time();
                break;
            //昨天起至时间
            case 'betweenYesterday':
                $resultList['beginYesterday'] = mktime(0,0,0,date('m'),date('d')-1,date('y'));
                $resultList['endYesterday'] = mktime(0,0,0,date('m'),date('d'),date('y'))-1;
                break;
            //本周起止时间
            case 'beginThisWeek':
                $resultList['beginThisWeek'] = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('y'));
                $resultList['now'] = time();
                break;
            //上一周起止时间
            case 'betweenLastWeek':
                $resultList['beginLastWeek'] = mktime(0,0,0,date('m'),date('d')-date('w')-6,date('y'));
                $resultList['endLastWeek'] = mktime(23,59,59,date('m'),date('d')-date('w'),date('y'));
                break;
            //上一周日期间时间
            case 'LastWeekFormNow':
                $resultList['beginLastWeek'] = mktime(0,0,0,date('m'),date('d')-7,date('y'));
                $resultList['endLastWeek'] = mktime(0,0,0,date('m'),date('d'),date('y'));
                break;
            //本月时间
            case 'beginThisMonth':
                $resultList['beginThisMonth'] = mktime(0,0,0,date('m'),1,date('y'));
                $resultList['now'] = mktime(0,0,0,date('m'),date('d'),date('y'))-1;
                break;
            //近三个月起止时间
            case 'beginLastThreeMonth':
                $resultList['beginLastThreeMonth'] = mktime(0,0,0,date('m'),date('d')-1,date('y'));
                $resultList['now'] = time();
                break;
        }
        return $resultList;
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
