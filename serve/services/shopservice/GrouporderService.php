<?php
/**
 * Created by PhpStorm.
 * User: yanbo
 * Date: 2017/5/22
 * Time: 10:02
 */

namespace Shop\Services;
use Shop\Models\BaiyangGroupFightActivity;
use Shop\Models\BaiyangRegion;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyangOrderOperationLogData;
use Shop\Datas\BaiyangOrderGoodsReturnReasonData; //退款
use Shop\Datas\BaiyangRegionData;  //收货地址
use Shop\Datas\BaiyangOrderData;  //普通订单

class GrouporderService extends BaseService{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance = null;
    //定义变量
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
    //全部订单状态
    public $allStatus = [
        'paying' => '待付款',
        '1' => '待成团',
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '已收货',
        'finished' => '交易完成',
        '10' => '团未成，退款中',
        '11' => '团未成，已退款',
        'canceled' => '交易关闭',
        '12' => '未中奖，退款中',
        '13' => '未中奖，已退款',
        'draw' => '等待抽奖'
    ];
    //拼团中订单状态
    public $progressStatus = [
        '1' => '待成团'
    ];
    //拼团成功订单状态
    public $successStatus = [
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '已收货',
        'finished' => '交易完成',
        'draw' => '等待抽奖'
    ];
    //拼团失败订单状态
    public $refundStatus = [
        '10' => '团未成，退款中',
        '11' => '团未成，已退款',
        '12' => '未中奖，退款中',
        '13' => '未中奖，已退款'
    ];
    //可修改的订单状态
    public $changeStat = [
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '已收货',
        'finished' => '交易完成'
    ];

    //定义使用的table
    private $act_table = "\\Shop\\Models\\BaiyangGroupFightActivity as fa";
    private $fight_table = "\\Shop\\Models\\BaiyangGroupFight as f";
    private $fight_buy_table = "\\Shop\\Models\\BaiyangGroupFightBuy fb";
    private $user_table = "\\Shop\\Models\\BaiyangUser as u";
    private $order_table = "\\Shop\\Models\\BaiyangOrder as o";
    private $refund_table = "\\Shop\\Models\\BaiyangOrderGoodsReturnReason as r";
    private $order_detail_table = "\\Shop\\Models\\BaiyangOrderDetail as od";
    private $region_table = "\\Shop\\Models\\BaiyangRegion as re"; //地址


    /**
     * 获取订单
     * @param $param
     * @return array
     */
    public function getList($param){
        $where = " o.order_type = 5 "; //表示为拼团订单
        $map = [];
        if(!empty($param['seaData'])){
            $seaData = $param['seaData'];
            //订单类型及订单状态
            if($seaData['orderType'] == 'progress'){ //拼团中--订单类型
                $where .= " AND fb.gfu_state = 1";
            }else if($seaData['orderType'] == 'success'){ //开团成功--订单类型
                if(!empty($seaData['status'])){
                    $where .= " AND (fb.gfu_state = 2 AND o.status = :status:)";
                    $map['status'] = $seaData['status'];
                }else{ //所有订单状态
                    $where .= " AND (fb.gfu_state = 2 AND (o.status = 'shipping' OR o.status = 'shipped' OR o.status = 'evaluating' OR o.status = 'finished' OR o.status = 'draw'))";
                }
            }else if($seaData['orderType'] == 'refund'){ //开团失败或退款订单--订单类型
                if($seaData['status'] == '10'){ //团未成，退款中
                    $where .= " AND (fb.gfu_state = 3 AND (o.status = 'await' OR r.status <= 2))";
                }else if($seaData['status'] == '11'){ //团未成，已退款
                    $where .= " AND (fb.gfu_state = 3 AND r.status = 3)";
                }else if($seaData['status'] == '12'){ //未中奖，退款中
                    $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND (o.status = 'await' OR r.status <= 2))";
                }else if($seaData['status'] == '13'){ //未中奖，已退款
                    $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND r.status = 3)";
                }else{ //所有订单状态
                    $where .= " AND ((fb.gfu_state = 3 AND o.status != 'canceled') OR (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0))";
                }
            }else{ //全部订单--订单类型
                if(!empty($seaData['status'])){
                    if ($seaData['status'] == '1') { //待成团
                        $where .= " AND fb.gfu_state = 1";
                    }else if($seaData['status'] == '10'){ //团未成，退款中
                        $where .= " AND (fb.gfu_state = 3 AND (o.status = 'await' OR r.status <= 2))";
                    }else if($seaData['status'] == '11'){ //团未成，已退款
                        $where .= " AND (fb.gfu_state = 3 AND r.status = 3)";
                    }else if($seaData['status'] == '12'){ //未中奖，退款中
                        $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND (o.status = 'await' OR r.status <= 2))";
                    }else if($seaData['status'] == '13'){ //未中奖，已退款
                        $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND r.status = 3)";
                    }else{ //其他状态
                        $where .= " AND o.status = :status:";
                        $map['status'] = $seaData['status'];
                    }
                }
            }
            //订单编号
            if(!empty($seaData['order_sn'])){
                $where .= " AND o.order_sn = :order_sn:";
                $map['order_sn'] = $seaData['order_sn'];
            }
            //下单时间,时间需要转换
            if(!empty($seaData['startTime'])){
                $seaData['startTime'] = strtotime(str_replace('/','-',$seaData['startTime']));
                $where .= " AND o.add_time >= :startTime:";
                $map['startTime'] = $seaData['startTime'];
            }
            if(!empty($seaData['endTime'])){
                $seaData['endTime'] = strtotime(str_replace('/','-',$seaData['endTime']));
                $where .= " AND o.add_time <= :endTime:";
                $map['endTime'] = $seaData['endTime'];
            }
            //活动名称
            if(!empty($seaData['gfa_name'])){
                $where .= " AND fa.gfa_name LIKE :gfa_name:";
                $map['gfa_name'] = '%' . $seaData['gfa_name'] . '%';
            }
            //发货商家
            if(!empty($seaData['shop_id'])){
                $where .= " AND o.shop_id = :shop_id:";
                $map['shop_id'] = $seaData['shop_id'];
            }
            //下单终端
            if(!empty($seaData['channel_subid'])){
                $where .= " AND o.channel_subid = :channel_subid:";
                $map['channel_subid'] = $seaData['channel_subid'];
            }
            //用户名
            if(!empty($seaData['username'])){
                $where .= " AND u.username LIKE :username:";
                $map['username'] = '%' . $seaData['username'] . '%';
            }
            //用户手机
            if(!empty($seaData['phone'])){
                $where .= " AND u.phone = :phone:";
                $map['phone'] = $seaData['phone'];
            }
            //商品名，活动设置名称
            if(!empty($seaData['goods_name'])){
                $where .= " AND fa.goods_name LIKE :goods_name:";
                $map['goods_name'] = '%' . $seaData['goods_name'] . '%';
            }
            //商品SKU
            if(!empty($seaData['goods_id'])){
                $where .= " AND fa.goods_id = :goods_id:";
                $map['goods_id'] = $seaData['goods_id'];
            }
        }
        //查询字段
        $countfield = "count(1) as counts"; //数量字段
        $field = "fa.gfa_name,fa.gfa_type,fa.gfa_user_count,o.order_sn,o.add_time,o.pay_time,o.channel_subid,o.payment_id,o.shop_id,o.carriage,u.username,u.phone,fb.is_head,fa.goods_name,o.goods_price,od.unit_price,od.goods_number,od.goods_image,od.goods_number,o.total,o.consignee,o.telephone,o.province,o.city,o.county,o.address,o.express_type,o.express,o.express_sn,o.buyer_message,o.remark,fa.gfa_is_draw,fb.gfu_state,fb.is_win,o.status,r.status as rstatus,fb.gf_id";
        $table = $this->order_table;
        $joinStr = "INNER JOIN {$this->order_detail_table} ON o.order_sn = od.order_sn INNER JOIN {$this->user_table} ON o.user_id = u.id INNER JOIN {$this->fight_buy_table} ON o.order_sn = fb.order_sn INNER JOIN {$this->act_table} ON fb.gfa_id = fa.gfa_id LEFT JOIN {$this->refund_table} ON o.order_sn = r.order_sn";

        //数量
        $recount = BaseData::getInstance()->select($countfield, $table, $map, $where,$joinStr);
        if($recount && $recount[0]['counts'] > 0){
            $count = $recount[0]['counts'];
        }else{
            return array(
                'status' => 'success',
                'list' => [],
                'page' => ''
            );
        }
        //分页
        $pages['psize'] = isset($param['psize']) && $param['psize'] ? (int)$param['psize'] : 15;//每页显示条数
        $pages['page'] = $param['page']; //当前页
        $pages['counts'] = $count;
        $pages['url'] = $param['url'];
//        $pages['url_back'] = $param['url_back'];
//        $pages['home_page'] = $param['home_page'];
        $pages['isShow'] = true;
        $page = $this->page->pageDetail($pages);
        //获取列表
        $where .= " ORDER BY o.add_time desc LIMIT {$page['record']},{$page['psize']}";
        $result = BaseData::getInstance()->select($field, $table, $map, $where,$joinStr);
        //获取订单备注，退款退货处理状态
        if(empty($result) == false && is_array($result)){
            foreach($result as $k => &$v){
                //收货地址
                $regionName = BaiyangRegionData::getInstance();
                $address = is_numeric($v['province'])
                    ? $regionName->getRegionName($v['province']) . ' '
                    . $regionName->getRegionName($v['city']) . ' '
                    . $regionName->getRegionName($v['county']) . ' '
                    : $v['province']  . ' ' . $v['city']  . ' ' . $v['county'] . ' ';
                $v['addressInfo'] = $address . $v['address'];
                //订单备注
                $v['remark'] = BaiyangOrderOperationLogData::getInstance()->getOperationLog([
                    'orderSn'=> $v['order_sn'],
                    'type'=> 1
                ]);
                $v['remarkCount'] = BaiyangOrderOperationLogData::getInstance()->remarkNum($v['order_sn']);
                //退款退货
                $v['serviceInfo'] = $this->getOrderServiceInfo($v['order_sn']);
            }
        }
        return array(
            'status' => 'success',
            'list' => $result,
            'page' => $page['page']
        );
    }

    /**
     * 某个团的拼团详情
     * 状态：待成团，拼团失败，待抽奖，已抽奖
     * @param $gf_id 开团表ID
     * @return array
     */
    public function groupDetail($gf_id){
        $return_array = array(
            'gfa_type' => 0, //活动类型，0普通，1抽奖
            'status' => '团长未付款', //该团状态
            'gfu_state' => '0', //0 未参团, 1 拼团中 , 2 已成团 , 3 拼团失败
            'gfa_is_draw' => 0, //0没抽奖 , 1已抽过奖
            'gfa_user_count' => 0, //成团人数
            'join_num' => 0, //已参团人数
            'gf_start_time' => '', //该团开始时间
            'gf_end_time' => '',  //该团过期时间
            'list' => [] //参团人信息数组
        );
        $field = "fa.gfa_type,fa.gfa_is_draw,fa.gfa_user_count,fb.gf_id,fb.gfu_state,fb.gf_start_time,fb.gf_end_time,fb.order_sn,fb.is_head,fb.is_win,u.username";
        $table = $this->fight_buy_table;
        $joinStr = "INNER JOIN {$this->user_table} ON fb.user_id = u.id INNER JOIN {$this->act_table} ON fb.gfa_id = fa.gfa_id";
        $where = "fb.gf_id = :gf_id: AND fb.gfu_state != 0 ORDER BY fb.add_time asc";
        $map['gf_id'] = (int)$gf_id;
        $result = BaseData::getInstance()->select($field, $table, $map, $where,$joinStr);
        if($result){
            $return_array['gfa_type'] = $result[0]['gfa_type'];
            $return_array['gfu_state'] = $result[0]['gfu_state'];
            $return_array['gfa_is_draw'] = $result[0]['gfa_is_draw'];
            $return_array['gfa_user_count'] = $result[0]['gfa_user_count'];
            $return_array['join_num'] = count($result);
            $return_array['gf_start_time'] = date('Y-m-d H:i:s',$result[0]['gf_start_time']);
            $return_array['gf_end_time'] = date('Y-m-d H:i:s',$result[0]['gf_end_time']);
            $return_array['list'] = $result;
            //处理该团状态
            if($return_array['gfu_state'] == 1){
                $return_array['status'] = "待成团";
            }else if($return_array['gfu_state'] == 3){
                $return_array['status'] = "拼团失败";
            }else if($return_array['gfu_state'] == 2){
                if($return_array['gfa_type'] == 1 && $return_array['gfa_is_draw'] == 0){
                    $return_array['status'] = "等待抽奖";
                }else if($return_array['gfa_type'] == 1 && $return_array['gfa_is_draw'] == 1){
                    $return_array['status'] = "已抽奖";
                }else{
                    $return_array['status'] = "已成团";
                }
            }
            return $this->arrayData('获取成功','',$return_array,'success');
        }
        return $this->arrayData('该团尚未开','','','error');
    }

    /**
     * 某个拼团订单的所有状态相关字段
     * @param $order_sn
     * @return bool
     */
    public function orderStatus($order_sn){
        if(empty($order_sn)){
            return false;
        }
        $where = "o.order_sn = :order_sn:";
        $map['order_sn'] = $order_sn;
        $field = "o.order_sn,fa.gfa_is_draw,fb.gfu_state,fb.is_win,o.status,r.status as rstatus";
        $table = $this->order_table;
        $joinStr = "INNER JOIN {$this->fight_buy_table} ON o.order_sn = fb.order_sn INNER JOIN {$this->act_table} ON fb.gfa_id = fa.gfa_id LEFT JOIN {$this->refund_table} ON o.order_sn = r.order_sn";
        $result = BaseData::getInstance()->select($field, $table, $map, $where,$joinStr);
        if($result && count($result) > 0){
            $status = 0;
            if(array_key_exists($result[0]['status'],$this->allStatus)){
                $status = $result[0]['status'];
            }
            if($result[0]['gfu_state'] == 1){
                $status = $result[0]['gfu_state'];
            }
            if($result[0]['gfu_state'] == 3 && (($result[0]['status'] == 'await') || ($result[0]['rstatus'] <= 2))){
                $status = 10;
            }else if($result[0]['gfu_state'] == 3 && $result[0]['rstatus'] == 3){
                $status = 11;
            }else if($result[0]['gfu_state'] == 2 && $result[0]['gfa_is_draw'] == 1 && $result[0]['is_win'] == 0 && ($result[0]['status'] == 'await' || $result[0]['rstatus'] <= 2)){
                $status = 12;
            }else if($result[0]['gfu_state'] == 2 && $result[0]['gfa_is_draw'] == 1 && $result[0]['is_win'] == 0 && $result[0]['rstatus'] == 3){
                $status = 13;
            }
            return $status;
        }
        return false;
    }

    /**
     * 获取订单是否为开团或者参团人
     * @param $order_sn
     * @return bool
     */
    public function headerOrder($order_sn){
        if(empty($order_sn)){
            return false;
        }
        $result = BaseData::getInstance()->getData([
            'column' => 'is_head',
            'table' => '\Shop\Models\BaiyangGroupFightBuy',
            'where' => 'where order_sn = :order_sn:',
            'bind' => ['order_sn' => $order_sn]
        ],true);
        return $result;
    }

    /**
     * 根据订单号获取服务单信息
     * @param $orderSn 订单号
     * @return bool
     * @author
     */
    public function getOrderServiceInfo($orderSn)
    {
        if (!$orderSn) {
            return false;
        }
        $column = "service_sn,status";
        return BaiyangOrderGoodsReturnReasonData::getInstance()->getRefundAll($column,"WHERE order_sn = {$orderSn}");
    }

    /**
     * ---------------------------------------------------------------------------------------
     * 拼团订单导出
     * --------------------------------------------------------------------------------------
     */

    //导出题头
    public $excel_header = [
        'order_sn'=>['text'=>'订单编号','field'=>'o.order_sn','join'=>''],
        'total_sn'=>['text'=>'订单编号','field'=>'o.total_sn','join'=>''],
        'order_source'=>['text'=>'订单来源','field'=>"if(ISNULL(p.prescription_id),'" . $this->config['company_name'] . "商城','易复诊') as order_source",'join'=>'LEFT JOIN baiyang_prescription p ON o.total_sn = p.order_id','table'=>'baiyang_prescription'],
        'shop_id'=>['text'=>'店铺','field'=>"sku_supplier.name as shop_id",'join'=>'LEFT JOIN baiyang_sku_supplier as sku_supplier on sku_supplier.id=o.shop_id','table'=>'baiyang_sku_supplier'],

//        'searchType'=>['text'=>'订单状态','field'=>"CASE o.`status`
//                        WHEN 'paying' THEN
//                            '普通待付款订单'
//                        WHEN 'shipping' THEN
//                            '待发货'
//                        WHEN 'shipped' THEN
//                            '已发货'
//                        WHEN 'evaluating' THEN
//                            '交易完成'
//                        WHEN 'refund' THEN
//                            '退款/售后'
//                        WHEN 'canceled' THEN
//                            '取消订单'
//                        WHEN 'finished' THEN
//                            '交易完成'
//                        END AS `searchType`",
//            'join'=>''],
        'searchType'=>['text'=>'订单状态','field'=>"o.status,fb.gfu_state,r.status as rstatus,fa.gfa_is_draw,fb.is_win",'join'=>''],

        'channel_subid'=>['text'=>'下单终端','field'=>"CASE o.channel_subid
                        WHEN '85' THEN
                            '微商城'
                        WHEN '89' THEN
                            'APP(IOS)'
                        WHEN '90' THEN
                            'APP(Android)'
                        WHEN '91' THEN
                            'WAP'
                        WHEN '95' THEN
                            'PC'
                        END AS 'channel_subid'",
            'join'=>''],

        'order_type'=>['text'=>'订单类型','field'=>"CASE o.order_type WHEN 0 THEN '普通订单' WHEN 2 THEN '处方订单' WHEN 5 THEN '拼团订单' END  as order_type",'join'=>''],
        //'callback_phone'=>['text'=>'回拨电话','field'=>"o.callback_phone",'join'=>''],
        //'ordonnance_photo_picture'=>['text'=>'处方药照片','field'=>"o.ordonnance_photo as ordonnance_photo_picture",'join'=>''],
        //'admin_account'=>['text'=>'药师姓名','field'=>"o.admin_account",'join'=>''],
        //'doctor_sign_picture'=>['text'=>'药师签名','field'=>"p.doctor_sign as doctor_sign_picture",'join'=>'LEFT JOIN baiyang_prescription p ON o.total_sn = p.order_id','table'=>'baiyang_prescription'],
        'group_fight_head_nickname'=>['text'=>'拼团人','field'=>"fb.phone as group_fight_head_nickname",'join'=>'left join baiyang_group_fight_buy as fb on fb.order_sn=o.order_sn','table'=>'baiyang_group_fight_buy'],

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
    //导出内容
    public $excel_group = [
        'group_1'=>['text'=>'来源','list'=>['order_source']],
        'group_2'=>['text'=>'店铺','list'=>['shop_id']],
        'group_3'=>['text'=>'订单状态','list'=>['searchType']],
        'group_4'=>['text'=>'下单终端','list'=>['channel_subid']],
        'group_5'=>['text'=>'订单类型','list'=>['order_type']],
        //'group_6'=>['text'=>'处方药','list'=>['callback_phone','ordonnance_photo_picture','admin_account','doctor_sign_picture']],
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
     * excel导出
     * @param $param
     * @return array
     */
    public function excelOrder($param)
    {
        $where = " AND o.order_type = 5 "; //表示为拼团订单
        $join = "";
        $filed = "";

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
        //处理筛选
        $join .= !in_array('baiyang_group_fight_buy',$join_table)? " LEFT JOIN baiyang_group_fight_buy as fb on fb.order_sn = o.order_sn" : '';
        $join .= !in_array('baiyang_order_goods_return_reason',$join_table)? " LEFT JOIN baiyang_order_goods_return_reason as r ON o.order_sn = r.order_sn" : '';
        $join .= !in_array('baiyang_group_fight_act',$join_table)? " LEFT JOIN baiyang_group_fight_act as fa ON fb.gfa_id = fa.gfa_id" : '';
        $join .= !in_array('baiyang_user',$join_table)? " INNER JOIN baiyang_user u ON o.user_id = u.id" : '';
        if($param['export_type'] == 'select_check'){
            //订单类型及订单状态
            if($param['orderType'] == 'progress'){ //拼团中--订单类型
                $where .= " AND fb.gfu_state = 1";
            }else if($param['orderType'] == 'success'){ //开团成功--订单类型
                if(!empty($param['status'])){
                    $where .= " AND (fb.gfu_state = 2 AND o.status = '{$param['status']}')";
                }else{ //所有订单状态
                    $where .= " AND (fb.gfu_state = 2 AND (o.status = 'shipping' OR o.status = 'shipped' OR o.status = 'evaluating' OR o.status = 'finished' OR o.status = 'draw'))";
                }
            }else if($param['orderType'] == 'refund'){ //开团失败或退款订单--订单类型
                if($param['status'] == '10'){ //团未成，退款中
                    $where .= " AND (fb.gfu_state = 3 AND (o.status = 'await' OR r.status <= 2))";
                }else if($param['status'] == '11'){ //团未成，已退款
                    $where .= " AND (fb.gfu_state = 3 AND r.status = 3)";
                }else if($param['status'] == '12'){ //未中奖，退款中
                    $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND (o.status = 'await' OR r.status <= 2))";
                }else if($param['status'] == '13'){ //未中奖，已退款
                    $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND r.status = 3)";
                }else{ //所有订单状态
                    $where .= " AND ((fb.gfu_state = 3 AND o.status != 'canceled') OR (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0))";
                }
            }else{ //全部订单--订单类型
                if(!empty($param['status'])){
                    if ($param['status'] == '1') { //待成团
                        $where .= " AND fb.gfu_state = 1";
                    }else if($param['status'] == '10'){ //团未成，退款中
                        $where .= " AND (fb.gfu_state = 3 AND (o.status = 'await' OR r.status <= 2))";
                    }else if($param['status'] == '11'){ //团未成，已退款
                        $where .= " AND (fb.gfu_state = 3 AND r.status = 3)";
                    }else if($param['status'] == '12'){ //未中奖，退款中
                        $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND (o.status = 'await' OR r.status <= 2))";
                    }else if($param['status'] == '13'){ //未中奖，已退款
                        $where .= " AND (fb.gfu_state = 2 AND fa.gfa_is_draw = 1 AND fb.is_win = 0 AND r.status = 3)";
                    }else{ //其他状态
                        $where .= " AND o.status = '{$param['status']}'";
                    }
                }
            }
            //订单编号
            if(!empty($param['order_sn'])){
                $where .= " AND o.order_sn = '{$param['order_sn']}'";
            }
            //下单时间,时间需要转换
            if(!empty($param['startTime'])){
                $param['startTime'] = strtotime(str_replace('/','-',$param['startTime']));
                $where .= " AND o.add_time >= {$param['startTime']}";
            }
            if(!empty($param['endTime'])){
                $param['endTime'] = strtotime(str_replace('/','-',$param['endTime']));
                $where .= " AND o.add_time <= {$param['endTime']}";
            }
            //活动名称
            if(!empty($param['gfa_name'])){
                $gfa_name = '%' . $param['gfa_name'] . '%';
                $where .= " AND fa.gfa_name LIKE '{$gfa_name}'";
            }
            //发货商家
            if(!empty($param['shop_id'])){
                $where .= " AND o.shop_id = {$param['shop_id']}";
            }
            //下单终端
            if(!empty($param['channel_subid'])){
                $where .= " AND o.channel_subid = {$param['channel_subid']}";
            }
            //用户名
            if(!empty($param['username'])){
                $username = '%' . $param['username'] . '%';
                $where .= " AND u.username LIKE '{$username}'";
            }
            //用户手机
            if(!empty($param['phone']) && $this->func->isPhone($param['phone'])){
                $where .= " AND u.phone = '{$param['phone']}'";
            }
            //商品名，活动设置名称
            if(!empty($param['goods_name'])){
                $goods_name = '%' . $param['goods_name'] . '%';
                $where .= " AND fa.goods_name LIKE '{$goods_name}'";
            }
            //商品SKU
            if(!empty($param['goods_id']) && is_numeric($param['goods_id'])){
                $where .= " AND fa.goods_id = {$param['goods_id']}";
            }
        }

        $orderData = BaiyangOrderData::getInstance();
        $column = ' o.total_sn '.$filed;
        $group = '';
        $order_by = 'ORDER BY o.add_time DESC';
        $limit = 'LIMIT 0,2000';
        //数据
        $totalOrder = $orderData->getTotalOrderExcel($column,$where,$join,$group,$order_by,$limit);
        if($totalOrder){
            $is_have_address = false;
            $region = [];
            if(in_array('province',$param['export_title']) ){
                $is_have_address = true;
                $region =  array_merge($region,array_column($totalOrder,'province'));
            }

            if(in_array('city',$param['export_title']) ){
                $is_have_address = true;
                $region =  array_merge($region,array_column($totalOrder,'city'));
            }

            if(in_array('county',$param['export_title']) ){
                $is_have_address = true;
                $region =  array_merge($region,array_column($totalOrder,'county'));
            }

            if($is_have_address){
                $region = array_unique($region);
                if($region){
                    $region_arr = BaseData::getInstance()->getData([
                        'column' => 'id,region_name',
                        'table' => 'Shop\Models\BaiyangRegion',
                        'where'=>'where id in ('.implode(',',$region).')'
                    ]);

                    if($region_arr){
                        $region = [];
                        foreach($region_arr as $v){
                            $region[$v['id']] = $v['region_name'];
                        }
                    }
                }
            }

            foreach($totalOrder as & $order){
                if(in_array('invoice_info',$param['export_title']) || in_array('invoice_type',$param['export_title']) || $is_have_address ){
                    if(isset($order['invoice_info'])  && !empty($order['invoice_info'])){
                        $invoice_info = json_decode($order['invoice_info'],true);
                        $order['invoice_info'] = $invoice_info['content_type'];
                        $order['invoice_rise'] = $invoice_info['title_name'];
                    }

                    if(isset($order['province']) && $order['province']){
                        $order['province'] = $region[$order['province']];
                    }
                    if(isset($order['city']) && $order['city']){
                        $order['city'] = $region[$order['city']];
                    }
                    if(isset($order['county']) && $order['county']){
                        $order['county'] = $region[$order['county']];
                    }
                }
                //订单状态start
                if(in_array('searchType',$param['export_title'])){
                    if($order['gfu_state'] == 1){
                        $order['searchType'] = '待成团';
                    }else if($order['gfu_state'] == 3 && ($order['status'] == 'await' || $order['rstatus'] <= 2)){
                        $order['searchType'] = '团未成，退款中';
                    }else if($order['gfu_state'] == 3 && $order['rstatus'] == 3){
                        $order['searchType'] = '团未成，已退款';
                    }else if($order['gfu_state'] == 2 && $order['gfa_is_draw'] == 1 && $order['is_win'] == 0 && ($order['status'] == 'await' || $order['rstatus'] <= 2)){
                        $order['searchType'] = '未中奖，退款中';
                    }else if($order['gfu_state'] == 2 && $order['gfa_is_draw'] == 1 && $order['is_win'] == 0 && $order['rstatus'] == 3){
                        $order['searchType'] = '未中奖，已退款';
                    }else if($order['status'] == 'paying'){
                        $order['searchType'] = '待付款';
                    }else if($order['status'] == 'draw'){
                        $order['searchType'] = '等待抽奖';
                    }else if($order['status'] == 'shipping'){
                        $order['searchType'] = '待发货';
                    }else if($order['status'] == 'shipped'){
                        $order['searchType'] = '已发货';
                    }else if($order['status'] == 'evaluating'){
                        $order['searchType'] = '待评价';
                    }else if($order['status'] == 'refund'){
                        $order['searchType'] = '退款/售后';
                    }else if($order['status'] == 'canceled'){
                        $order['searchType'] = '交易关闭';
                    }else if($order['status'] == 'finished'){
                        $order['searchType'] = '交易完成';
                    }else{
                        $order['searchType'] = '未知';
                    }
                    unset($order['status']);
                    unset($order['gfu_state']);
                    unset($order['rstatus']);
                    unset($order['gfa_is_draw']);
                    unset($order['is_win']);
                }
                //订单状态end
            }

            $this->downExcel($totalOrder);
        }else{
            return $totalOrder;
        }

    }

    /**
     * 下载
     * @param $list
     */
    public function downExcel($list){
        $head_title = array_keys($list[0]);
        $obj = \Shop\Libs\Excel::getInstance();
        $headArray = [];
        foreach($head_title as $key){
            $headArray[] = $this->excel_header[$key]['text'];
        }
//        $obj::$_exist_photo = true;
        $obj->exportExcel($headArray,$list,'拼团订单导出列表','拼团订单列表');

    }

}