<?php
namespace Shop\Admin\Controllers;
//use Shop\Services\;
use Shop\Datas\BaseData;
class CpsOrderController extends ControllerBase
{
    public $per_page_type = array(10, 20, 50, 100);

    //订单状态
    public $order_status = array(
        'paying' => '待付款',
        'shipping' => '待发货',
        'shipped' => '已发货',
        'evaluating' => '待评价',
        'refund' => '退款/售后',
        'canceled' => '交易关闭',
        'finished' => '订单完成',
    );

    /***
     *  chenrui
     *  cps 订单列表
     */
    public function listAction(){

        $base = BaseData::getInstance();
        $param = array(
            'order_id'=>'',
            'user_id'=>'',
            'channel'=>'',
            'code'=>'',
            'platform'=>'',
            'start_time'=>'',
            'end_time'=>'',
            'order_status'=>'',
            'exel' => 0,
        );
        $pay_type = array(
            '1' => '支付宝',
            '2' => '微信',
            '3' => '货到付款',
            '4' => '红包',
            '5' => 'Apple',
            '6' => '银联',
            '7' => '余额'
        );
        $order_status = array(
            'paying' => '待付款',
            'shipping' => '待发货',
            'shipped' => '已发货',
            'evaluating' => '待评价',
            'refund' => '退款/售后',
            'canceled' => '交易关闭',
            'finished' => '订单完成',
        );
        $channel_type = array(
            '89' => 'IOS',
            '90' => 'Andriod',
            '95' => 'PC',
            '91' => 'WAP',
            '85' => 'WeChat'
        );
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['page'] = $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $data['column'] = 'channel_id,channel_name,tags';
        $data['table'] = $data_cps_channel = '\Shop\Models\BaiyangCpsChannel';
        $data['where'] = " where channel_status = 1 ";
        $ch =  $base->getData($data);
        foreach($ch as $val ){
            $channel[$val['channel_id']] = $val['channel_name'];
        }
        $data = array();
        $this->view->setVar('channel_lset',$ch);

        $data['table'] = '\Shop\Models\BaiyangCpsOrderLog as co';
        $data_detail_log = '\Shop\Models\BaiyangCpsOrderDetailLog as cod';
        $data_cps_user = '\Shop\Models\BaiyangCpsUser as cu';
        $data['column'] = 'co.order_sn';
        $data['join'] = ' LEFT JOIN  '.$data_detail_log.' ON co.order_sn = cod.order_sn
                          LEFT JOIN  '.$data_cps_user.' ON cod.invite_code = cu.invite_code
                          LEFT JOIN  '.$data_cps_channel.' AS ch ON cu.channel_id = ch.channel_id';
        $where = " where 1=1 and cod.price<>'' ";
        if (!empty($param)) {
            if ($param['order_id']) {
                $where .= "AND co.order_sn='".$param['order_id']."' ";
            }
            if ($param['user_id']) {
                $where .= "AND co.user_id='".$param['user_id']."' ";
            }
            if ($param['channel']) {
                $where .= "AND cu.channel_id=".$param['channel']." ";
            }
            if ($param['code']) {
                $where .= "AND cod.invite_code='".$param['code']."' ";   //20160602 edit CSL
            }
            if ($param['platform']) {
                $where .= "AND co.platform_id=".$param['platform']." ";
            }
            if ($param['start_time'] && $param['end_time']) {
                $where .= "AND co.order_time BETWEEN ".strtotime($param['start_time'])." AND ".strtotime($param['end_time'])." ";
            }
            if ($param['start_time'] && !$param['end_time']) {
                $where .= "AND co.order_time BETWEEN ".strtotime($param['start_time'])." AND ".  time()." ";
            }
            if ($param['order_status']) {
                $where .= "AND co.order_status='".$param['order_status']."' ";
            }
        }else {
            $where .= "AND co.order_time BETWEEN ".strtotime(date('Y-m-01 00:00:00', strtotime('-1 month')))." AND ".time()." ";
        }

        $where .= " GROUP BY co.order_sn";
        $data['where'] = $where;

        $counts = count($base->getData($data));
        if(($counts>6000) && $param['exel']==1){
            echo "数量超过6000不可以直接导出，请先使用筛选条件筛选";exit;
        }
        $this->view->setVar('channel',$param);

        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }

        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $data['order'] = 'ORDER BY co.order_time DESC';
        if($param['exel']!=1){
            $data['limit'] = "LIMIT ".$page['record'].','.$page['psize'];
        }
        $data['column'] = 'cu.short_code_office,cu.area,cu.cps_status,co.platform_id,co.balance,co.real_pay,co.freight,'
            . 'cod.price,co.user_id,co.channel_id,co.order_sn,cod.back_amount,co.order_time,co.pay_id,co.pay_time,'
            . 'co.m_channel_id,co.order_status,co.clearing,co.clearing_time,co.discount_data';
        $result =  $base->getData($data);
        if($result){
            $orderSn = array_column($result,'order_sn');
            foreach ($orderSn as $key => $val) {
                $orderSn[$key] = "'".$val."'";
            }
            $orderSnStr = implode(',', $orderSn);
            $data = array();
            $data['column'] = 'ord.order_sn,ord.qty,ord.channel_id,ord.invite_code,ord.price,ord.back_amount';
            $data['where'] = " where ch.channel_status <> 0 AND ord.order_sn IN ({$orderSnStr})";
            $data['table'] = '\Shop\Models\BaiyangCpsOrderDetailLog as ord';
            $data_cps_channel = '\Shop\Models\BaiyangCpsChannel as ch' ;
            $data['join'] = ' LEFT JOIN  '.$data_cps_channel.'  ON ord.channel_id = ch.channel_id';
            $r =  $base->getData($data);
            $coupon = $this->get_coupon_all($orderSnStr);
            $orderPromotion = $this->getOrderPromotion($orderSnStr);
            foreach($result as $key=>$val ){
                $result[$key]['back_amount'] = 0;
                $result[$key]['price'] = 0;
                $result[$key]['moneyOff'] = isset($orderPromotion[$val['order_sn']])
                    ? $orderPromotion[$val['order_sn']] : '0.00';
                $invite_code = array();
                if (is_array($r) && $r) {
                    foreach ($r as $value) {
                        if ($val['order_sn'] === $value['order_sn']) {
                            $result[$key]['back_amount'] += round($value['back_amount'], 2);
                            $result[$key]['price'] += round($value['price'], 2);
                            $invite_code[] = $value['invite_code'];
                            $result[$key]['channel_id'] = isset($channel[$value['channel_id']])
                                ? $channel[$value['channel_id']]:'未设置';   //  20160602 add CSL
                        }
                    }
                }
                $result[$key]['invite_code'] = implode(',', array_unique($invite_code));
                $result[$key]['back_amount'] = sprintf('%.2f', $result[$key]['back_amount']);

                $result[$key]['order_time'] = $val['order_time'] ? date('Y-m-d H:i:s', $val['order_time']) : '';
                $result[$key]['pay_id'] = isset($pay_type[$val['pay_id']]) ? $pay_type[$val['pay_id']] : '未支付';
                $result[$key]['pay_time'] = $val['pay_time'] ? date('Y-m-d H:i:s', $val['pay_time']) : '';
                $result[$key]['m_channel_id'] = isset($channel_type[$val['m_channel_id']])
                    ? $channel_type[$val['m_channel_id']] : '';
                $result[$key]['order_status'] = isset($order_status[$val['order_status']])
                    ? $order_status[$val['order_status']] : '';
                $result[$key]['clearing'] = $val['clearing'] ? '是' : '否';
                $result[$key]['clearing_time'] = $val['clearing_time'] ? date('Y-m-d H:i:s', $val['clearing_time']) : '';


                $result[$key]['coupon_name'] = '未使用优惠券';
                $result[$key]['coupon_amount'] = '0.00';
                if (!empty($val['discount_data'])) {
                    $discount_data = json_decode($val['discount_data'], true);
                    if (isset($discount_data['coupon'])) {
                        $result[$key]['coupon_name'] = isset($coupon[$discount_data['coupon']['coupon_id']])
                            ? $coupon[$discount_data['coupon']['coupon_id']] : '';
                        $result[$key]['coupon_amount'] = isset($discount_data['coupon']['value'])
                            ? $discount_data['coupon']['value'] : '0.00';
                    }
                }
            }
        }
        if($param['exel']==1){
            header("Content-type:text/csv");
            header("Content-Disposition:attachment;filename=".date("Y-m-d").".csv");
            header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
            header('Expires:0');
            header('Pragma:public');

            $sgbk = "";
            $str_1 = iconv('utf-8','gbk',"订单编号,下单时间,用户名,商品金额,付款方式,支付时间,优惠券名称,优惠券金额,运费,余额,应付款,");
            $str_2 = iconv('utf-8','gbk',"推广渠道,推广ID,推广返利金额,订单状态,地区,分办,是否冷冻,");
            $strs = $str_1.$str_2;
            if($result){
                foreach($result as $row){
                    $order_sn = $row['order_sn']."\t";
                    $row['cps_status']=($row['cps_status']==2)?'是':'否';
                    //$row['cps_status'] = iconv('utf-8','gbk',$row['cps_status']);
                    $str = $order_sn.",".$row['order_time'].",".$row['user_id'].",".$row['price'].",".$row['pay_id'].","
                        .$row['pay_time'].",".$row['coupon_name'].",".$row['coupon_amount'].",".$row['freight'].","
                        .$row['balance'].",".$row['real_pay'].",".$row['channel_id'].",".'"'.$row['invite_code'].'"'.","
                        .$row['back_amount'].",".$row['order_status'].",".$row['area'].",".$row['short_code_office'].",".$row['cps_status'];
                    $sgbk.= iconv('utf-8','gbk',$str)."\n";

                }
            }
            echo $strs."\n".$sgbk;
            exit;
        }

        $list = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        $this->view->setVar('list',$list);
    }


    public function get_coupon_all($order_sn = '')
    {
        if (!$order_sn) {
            return array();
        }

        $base = BaseData::getInstance();

        $data['table'] = '\Shop\Models\BaiyangOrderPromotionDetail as op';
        $data['column'] = "op.promotion_id as coupon_id,op.promotion_name as name,cp.id " ;

        $region = '\Shop\Models\BaiyangCoupon as cp';
        $data['join'] = " left join  ".$region."  on  op.promotion_id = cp.coupon_sn ";
        $data['where'] = " where op.discount_type = 1 AND op.order_sn IN ({$order_sn}) ";

        $result =  $base->getData($data);

        $coupon = array();
        if ($result) {
            foreach ($result as $k => $v) {
                if ($v['id']) {
                    $coupon[$v['id']] = $v['name'];
                }
                if ($v['coupon_id']) {
                    $coupon[$v['coupon_id']] = $v['name'];
                }
            }
        }
        return $coupon;
    }

    public function getOrderPromotion($orderSn)
    {
        if (!$orderSn) {
            return false;
        } elseif (is_array($orderSn)) {
            foreach ($orderSn as $key => $val) {
                $orderSn[$key] = "'".$val."'";
            }
            $id_str = implode(',', $orderSn);
        } else {
            $id_str = $orderSn;
        }
        $base = BaseData::getInstance();
        $data['table'] = ' \Shop\Models\BaiyangOrderPromotionDetail';
        $data['column'] = 'order_sn,discount_money' ;
        $data['where'] = " where order_sn in ({$id_str}) AND discount_type = 4";
        $result = $base->getData($data);
        if ($result) {
            $result = array_column($result, 'discount_money', 'order_sn');
        }
        return $result;
    }

}
?>
