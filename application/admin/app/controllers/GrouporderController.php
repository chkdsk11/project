<?php
/**
 * 拼团订单CLASS
 * Created by PhpStorm.
 * User: yanbo
 * Date: 2017/5/22
 * Time: 9:53
 */

namespace Shop\Admin\Controllers;
use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\GrouporderService;
use Shop\Services\OrderService;
use Shop\Services\RefundService;

class GrouporderController extends ControllerBase{

    public function initialize(){
        parent::initialize();
        $this->setTitle('拼团订单管理');
        $list = OrderService::getInstance();
        //发货商家
        $this->view->setVar('shops', $list->getShops());
        //下单终端
        $this->view->setVar('terminal', $list->orderTerminal);
        //付款方式
        $this->view->setVar('payment', $list->orderPayment);
        //配送方式
        $this->view->setVar('delivery', array_flip($list->orderDelivery));
        //退款状态
        $refundState = array_flip(RefundService::getInstance()->refundState);
        $refundState[7] = '待卖家退款';
        $this->view->setVar('refundState', $refundState);
    }

    /**
     * 订单列表
     */
    public function orderListAction(){
        //单独处理四类订单类型
        $seaData['orderType'] = $this->getParam('orderType','trim','all'); //订单类型
        //筛选条件
        $seaData['order_sn'] = $this->getParam('order_sn','trim',''); //订单编号
        $seaData['startTime'] = $this->getParam('startTime','trim','');  //下单时间start
        $seaData['endTime'] = $this->getParam('endTime','trim','');   //下单时间end
        $seaData['gfa_name'] = $this->getParam('gfa_name','trim',''); //活动名称
        $seaData['shop_id'] = $this->getParam('shop_id','trim','');//发货商家
        $seaData['channel_subid'] = $this->getParam('channel_subid','trim','');//下单终端
        $seaData['username'] = $this->getParam('username','trim',''); //用户名
        $seaData['phone'] = $this->getParam('phone','trim',''); //用户手机
        $seaData['goods_name'] = $this->getParam('goods_name','trim','');  //商品名
        $seaData['goods_id'] = $this->getParam('goods_id','trim','');  //商品SKU
        $seaData['status'] = $this->getParam('status','trim','');  //订单状态
        $seaData['psize'] = $this->getParam('psize','int','15');
        //处理不同订单类型下的订单状态筛选
        $filterStat = array();
        if($seaData['orderType'] == 'all'){ //全部订单
            $filterStat = GrouporderService::getInstance()->allStatus;
        }
        if($seaData['orderType'] == 'progress'){
            $filterStat = GrouporderService::getInstance()->progressStatus;
        }
        if($seaData['orderType'] == 'success'){
            $filterStat = GrouporderService::getInstance()->successStatus;
        }
        if($seaData['orderType'] == 'refund'){
            $filterStat = GrouporderService::getInstance()->refundStatus;
        }
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            //'url_back' => '',
            //'home_page' => '/groupact/list',
            'psize' => $seaData['psize'],
            'seaData' => $seaData
        );
        $result = GrouporderService::getInstance()->getList($param);
        $this->view->setVar('totalOrder',$result['list']);
        $this->view->setVar('seaData',$seaData);
        $this->view->setVar('page',$result['page']);
        $this->view->setVar('psize',$seaData['psize']);
        $this->view->setVar('filterStat',$filterStat);
        //导出字段
        $this->view->setVar('excelField', GrouporderService::getInstance()->getExcelField());
        $this->view->pick('grouporder/orderList');
    }

    /**
     * 拼团详情
     * @return mixed
     */
    public function groupDetailAction(){
        if($this->request->isAjax()){
            $gf_id = $this->request->getPost('gf_id','int',0);
            $result = GrouporderService::getInstance()->groupDetail($gf_id);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 订单详情
     */
    public function orderDetailAction(){
        $orderSn = $this->getParam('orderSn','trim');
        if(empty($orderSn)){
            return $this->success( '参数缺失','/grouporder/orderlist','error',3);
        }
        //调用普通订单详情口
        $orderInfo = OrderService::getInstance()->getOrderInfo($orderSn);
        //获取该订单的状态key
        $status = GrouporderService::getInstance()->orderStatus($orderSn);
        //查询该订单是开团人还是参团人
        $ishead = GrouporderService::getInstance()->headerOrder($orderSn);
        $this->view->setVar('ishead', isset($ishead['is_head'])?$ishead['is_head']:0);
        //订单数据
        $this->view->setVar('orderInfo', $orderInfo);
        //修改订单状态权限
        $this->view->setVar('alterState', $this->func->checkActionAuth('order','changeOrderState'));
        //修改收货地址权限
        $this->view->setVar('alterSite', $this->func->checkActionAuth('order','changeAddress'));
        //修改发票权限
        $this->view->setVar('alterBill', $this->func->checkActionAuth('order','changeBill'));
        //-------------拼团订单状态---------
        //所有订单状态
        $this->view->setVar('orderStat',GrouporderService::getInstance()->allStatus);
        //可改变的订单状态
        $this->view->setVar('changeStat',GrouporderService::getInstance()->changeStat);
        //获取该拼团订单的状态,key
        $this->view->setVar('orderStatus',$status);
        //页面
        $this->view->pick('grouporder/orderDetail');
    }

    /**
     * 导出订单
     */
    public function orderExcelAction(){
        $this->view->disable();
        $res = GrouporderService::getInstance()->excelOrder($_GET);
        if(!$res){
            $this->msgRedirect('没有数据导出','orderlist');
        }
    }

}