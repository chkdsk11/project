<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\BaseService;
use Shop\Services\OrderMangeService;


class AppordermangeController extends ControllerBase
{
    public $service;

    //已支付订单状态
    public $order_type = array(
        'shipping' => '待发货', //待发货
        'shipped' => '待收货', //待收货
        'evaluating' => '待评价', //待评价
        'refund' => '退款', //退款/售后
        'finished' => '交易完成',  //订单完成,
    );

    public function initialize()
    {
        parent::initialize();
        $this->service =  OrderMangeService::getInstance();
    }

    public function indexAction()
    {
        $param =[];
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param['param'] = $data;
        $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $exporturl = $param['url'].'&export=export';
        $list = $this->service->getList($param);
        $result = $this->reconstruction_data($list['list'],$data);
        //导出统计
        if($data['export']=='export'){
            header("Content-type:application/vnd.ms-excel");  //定义输出的文件类型
            header("content-Disposition:filename=order_report.csv");  //定义输出的文件名，也就是设置一个下载类型，下载的时候对文件从新命名
            $csv=fopen("php://output","w");
            $keys_arr=array(
                'add_user'=>'新增用户',
                'pay_order'=>'已支付订单数',
                'price_count'=>'销售额（即已支付订单金额）',
                'bargain_num'=>'成交人数',
                'order_avg'=>'客单价',
                'repeat_or'=>'复购率（不去重）',
                'repeat_us'=>'复购率（去重）',
            );
            foreach($keys_arr as $k=>$title){
                $keys_arr[$k]=iconv('utf-8', 'gbk', $title);
            }
            fputcsv($csv,$keys_arr);
            if ($result) {
                $print_arr=array();
                foreach($keys_arr as $key=>$v){
                    $value = (is_numeric($result[$key]) && mb_strlen($result[$key]) > 10) ? $result[$key] . "\t" : $result[$key];
                    $print_arr[]=iconv('utf-8', 'gbk', $value);
                }
                fputcsv($csv,$print_arr);
            }
            fclose($csv);
            die;
        }else{
            $this->view->setVar('export',$exporturl);
            $this->view->setVar('info',$result);
            $this->view->setVar('search',$data);
        }

    }


    public function reconstruction_data ($data, $search = array())
    {

        if (isset($search['start_time']) && isset($search['end_time'])) {
            $start = strtotime($search['start_time']);
            $end = strtotime($search['end_time']);
        } else {
            $time = $this->service->default_time();
            $start = $time['start'];
            $end = $time['end'];
        }
        $pay_order = 0;//支付订单数
        $price_count = 0;//订单总金额
        $old_order_count = 0;//老用户订单数
        $old_user_id = array();//老用户ID
        $user_id = array();//成交用户ID
        if ($data) {
            foreach ($data as $k => $v) {
                if (isset($this->order_type[$v['status']])) {
                    if (!$v['payment_time']) {
                        $pay_order += 1;
                        $user_id[] = $v['user_id'];
                        $price_count += $v['gathering'];
                    } elseif ($v['payment_time'] <= $end && $v['payment_time'] >= $start) {
                        $pay_order += 1;
                        $user_id[] = $v['user_id'];
                        $price_count += $v['gathering'];
                    }
                }
            }
            //支付了订单的用户ID
            $user_id = array_unique($user_id);
            //获取重复购买的用户数量
            $id = implode(',', $user_id);
            $old_user_id = $this->service->get_old_user_all($search, $id);
            if ($old_user_id) {
                $old_user_id = array_flip($old_user_id);
                foreach ($data as $k => $v) {
                    if (isset($old_user_id[$v['user_id']]) && isset($this->order_type[$v['status']])) {
                        if (!$v['payment_time']) {
                            $old_order_count += 1;
                        } elseif ($v['payment_time'] <= $end && $v['payment_time'] >= $start) {
                            $old_order_count += 1;
                        }
                    }
                }
            }
        }
        //在时间段内第一次登录APP的人数
        $add_user = $this->service->get_first_login_count($search);
        //分母不为零
        $user_count = $user_id ? count($user_id) : 1;
        $old_user_count = $old_user_id ? count($old_user_id) : 0;
        $p_o_count = $pay_order ? $pay_order : 1;

        return array(
            'add_user' => $add_user?$add_user:0,
            'pay_order' => $pay_order,
            'price_count' => $price_count,
            'bargain_num' => count($user_id),
            'order_avg' => round($price_count/$user_count, 2),
            'repeat_or' => round($old_order_count/$p_o_count, 4) * 100 . "%",
            'repeat_us' => round($old_user_count/$user_count, 4) * 100 . "%",
        );
    }
}