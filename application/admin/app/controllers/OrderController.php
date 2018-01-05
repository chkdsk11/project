<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/5
 * Time: 16:41
 */

namespace Shop\Admin\Controllers;

use Shop\Datas\BaiyangRegionData;
use Shop\Services\OrderService;
use Shop\Services\RefundService;
use Shop\Services\BaseService;


class OrderController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();

        $list = OrderService::getInstance();
        //订单类型
        $this->view->setVar('orderType', $list->orderType);
        //发货商家
        $this->view->setVar('shops', $list->getShops());
        //下单终端
        $this->view->setVar('terminal', $list->orderTerminal);
        //付款方式
        $this->view->setVar('payment', $list->orderPayment);
        //配送方式
        $this->view->setVar('delivery', array_flip($list->orderDelivery));
        //订单来源
        $this->view->setVar('source', $list->orderSource);
        //订单状态
        $this->view->setVar('orderStat', $list->orderStat);
        //退款状态
        $refundState = array_flip(RefundService::getInstance()->refundState);
        $refundState[7] = '待卖家退款';
        $this->view->setVar('refundState', $refundState);
        //商品跳转地址
        $this->view->setVar('jumpUrl', $this->config['wap_base_url'][$this->config->environment]);
    }

    /**
     * 普通订单列表
     * @author Chensonglu
     */
    public function orderListAction()
    {
        $list = OrderService::getInstance();
        foreach($this->request->get() as $k=>$v){
            if(!is_array($v)){
                $param[$k] = $this->getParam($k,'trim');
            }
        }

        $param['page'] = $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $orderList = $list->getAllOrder($param);

        //组织搜索条件,显示在前端页面
        $this->view->setVar('order_sn', isset($param['order_sn']) ? $param['order_sn'] : '');
        $this->view->setVar('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->view->setVar('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->view->setVar('order_type', isset($param['order_type']) ? $param['order_type'] : '');
        $this->view->setVar('shop_id', isset($param['shop_id']) ? $param['shop_id'] : '');
        $this->view->setVar('channel_subid', isset($param['channel_subid']) ? $param['channel_subid'] : '');
        $this->view->setVar('username', isset($param['username']) ? $param['username'] : '');
        $this->view->setVar('phone', isset($param['phone']) ? $param['phone'] : '');
        $this->view->setVar('telephone', isset($param['telephone']) ? $param['telephone'] : '');
        $this->view->setVar('goods_name', isset($param['goods_name']) ? $param['goods_name'] : '');
        $this->view->setVar('goods_id', isset($param['goods_id']) ? $param['goods_id'] : '');
        $this->view->setVar('payment_id', isset($param['payment_id']) ? $param['payment_id'] : '');
        $this->view->setVar('express_type', isset($param['express_type']) ? $param['express_type'] : '');
        $this->view->setVar('order_source', isset($param['order_source']) ? $param['order_source'] : '');
        $this->view->setVar('psize', isset($param['psize']) ? $param['psize'] : 15);
        $this->view->setVar('searchType', isset($param['searchType']) ? $param['searchType'] : 'all');
        //待审核订单数
        $this->view->setVar('auditNum', (int)$list->getConditionNum('audit'));
        //待发货订单数
        $this->view->setVar('shippingNum', (int)$list->getConditionNum('shipping'));
        //配送方式
        $this->view->setVar('deliverySel', $list->orderDelivery);
        //数据返回页面
        $this->view->setVar('totalOrder', $orderList['totalOrder']);
        $this->view->setVar('page', $orderList['page']);
        //导出字段
        $excel_div = '';
        foreach ($list->getExcelField() as $key=>$item) {
            $excel_div .= "<div style='padding-bottom: 2px;'><input type='checkbox' class='check_all' value='{$key}'>{$item['text']}全选/取消全选</div><div class='clearfix'><ul>";
            $excel_list_ed = array(
                'express_type',
                'carriage',
                'o2o_remark',
                'total',
                'buyer_message',
                'real_pay',
                'is_pay',
                'pay_type',
                'goods_name',
                'goods_number'
            );
            foreach ($item['list'] as $list_key=>$item_list) {
                if(in_array($list_key,$excel_list_ed)){
                    $excel_div .= "<li><input class='{$key}' type='checkbox' name='export_title[]' value='{$list_key}' checked='checked'>{$item_list}</li>";
                }else{
                    $excel_div .= "<li><input class='{$key}' type='checkbox' name='export_title[]' value='{$list_key}'>{$item_list}</li>";
                }
            }
            $excel_div .= "</ul></div>";
        }
        $excel_div .= '</div>';
        $this->view->setVar('excelField', $excel_div);
        //页面
        $this->view->pick('order/orderList');
    }

    /**
     * 订单导出
     * @author Zhudan
     */
    public function orderExcelAction(){
        $this->view->disable();
        $list = OrderService::getInstance();
        $res = $list->excelOrder($_GET);
        if(!$res){
            $this->msgRedirect('没有数据导出','orderlist');
        }
    }

    /**
     * 处方药订单审核
     * @return mixed
     * @author Chensonglu
     */
    public function orderAuditAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = OrderService::getInstance()->auditOrder($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 导出待发货订单
     * @return mixed
     * @author Chensonglu
     */
    public function guideInvoicesAction()
    {
        $order = OrderService::getInstance();
        if($this->request->isAjax()){
            $result = $order->guideInvoices(true);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
        $this->view->disable();
        $result = $order->guideInvoices();
        $filename = '待发货订单'.date('YmdHis');
        $headArray = [
            '订单编号',
            '收货人',
            '联系电话',
            '收货地址',
            '物流公司',
            '物流单号',
        ];
        $this->excel->exportExcel($headArray,$result,$filename,'发货单','xls');
    }

    /**
     * 单个订单发货
     * @return mixed
     * @author Chensonglu
     */
    public function shipmentsAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = OrderService::getInstance()->oneShipments($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 批量发货
     * @return mixed
     * @author Chensonglu
     */
    public function batchShipmentsAction()
    {
        ini_set('max_execution_time', '0');
        $step = $this->request->getPost('step','trim');
        if($this->request->isPost()){
            $param = $_POST;
            //文件上传
            if ($this->request->hasFiles() && $param['step'] == 2) {
                $type = isset($_FILES['files']['name']) && $_FILES['files']['name']
                    ? substr($_FILES['files']['name'], strrpos($_FILES['files']['name'], '.')+1) : '';
                if (!$type) {
                    return $this->response->setJsonContent([
                        'status' => 'error',
                        'info' => '请选择上传文件'
                    ]);
                } elseif (!in_array($type,['xlsx','xls'])) {
                    return $this->response->setJsonContent([
                        'status' => 'error',
                        'info' => '上传文件格式错误（请上传xlsx或xls格式文件）'
                    ]);
                }
                $param['type'] = $type;
                $import = BaseService::getInstance()->filesUpload($this->request, '', '', $type);
                if($import['status'] == 'success'){
                    $param['path'] = $import['data'][0]['filePath'].$import['data'][0]['fileName'];
                } else {
                    echo $import['info'].'<script>window.onload = function(){setTimeout(window.location.href="/order/batchShipments", 3000);}</script>';
                }
            }
            $result = OrderService::getInstance()->batchShipments($param);
            if (isset($result['status'])) {
                $this->view->disable();
                echo $result['info'].'<script>window.onload = function(){setTimeout(window.location.href="/order/batchShipments", 3000);}</script>';exit;
            }
            $this->view->setVar('result', $result);
        }
        //批量发货步骤
        $this->view->setVar('step', $step ? $step : 1);
        //页面
        $this->view->pick('order/batchShipments');
    }

    /**
     * 添加备注
     * @return mixed
     * @author Chensonglu
     */
    public function addOrderRemarkAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = OrderService::getInstance()->addOrderRemark($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 订单详情
     * @author Chensonglu
     */
    public function orderDetailAction()
    {
        $orderSn = $this->getParam('orderSn','trim');
        $orderInfo = OrderService::getInstance()->getOrderInfo($orderSn);
        
        // 订单来源 为不影响更多地方,在外层修改
        if ($orderInfo['more_platform_sign'] == 'yukon') {
            $orderInfo['orderSource'] = '育学园';
        }
        
        //订单数据
        $this->view->setVar('orderInfo', $orderInfo);
        //修改订单状态权限
        $this->view->setVar('alterState', $this->func->checkActionAuth('order','changeOrderState'));
        //修改订单配送信息权限
        $alterExpress = $this->func->checkActionAuth('order','changeExpressInfo') && in_array($orderInfo['status'],['shipped','evaluating','finished'])
            ? 1 : 0;
        $this->view->setVar('alterExpress', $alterExpress);
        //修改收货地址权限
        $this->view->setVar('alterSite', $this->func->checkActionAuth('order','changeAddress'));
        //修改发票权限
        $this->view->setVar('alterBill', $this->func->checkActionAuth('order','changeBill'));
        //申请退款/取消权限
        $this->view->setVar('alterRefund', $this->func->checkActionAuth('order','applyRefund'));
        //页面
        $this->view->pick('order/orderDetail');
    }

    /**
     * 修改订单状态
     * @return mixed
     * @author Chensonglu
     */
    public function changeOrderStateAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            if (!isset($param['isUpdate'])) {
                $result = ['status' => 'error','info' => '参数不完整',];
            } elseif (!$param['isUpdate']) {
                $result = OrderService::getInstance()->checkCanChangeOrder($param);
            } else {
                $param['updateType'] = 1;
                $result = OrderService::getInstance()->updateOrder($param);
            }
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 修改配送信息
     * @return mixed
     * @author Chensonglu
     */
    public function changeExpressInfoAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = OrderService::getInstance()->oneShipments($param, true);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 修改收货地址
     * @return mixed
     * @author Chensonglu
     */
    public function changeAddressAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $param['updateType'] = 2;
            $result = OrderService::getInstance()->updateOrder($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 修改发票信息
     * @return mixed
     * @author Chensonglu
     */
    public function changeBillAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $param['updateType'] = 3;
            $result = OrderService::getInstance()->updateOrder($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 申请退款/退货/取消
     * @return mixed
     * @author Chensonglu
     */
    public function applyRefundAction()
    {
        $orderService = OrderService::getInstance();
        if($this->request->isPost() && $this->request->isAjax()){
            $this->view->disable();
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = $orderService->applyRefund($param);
            return $this->response->setJsonContent($result);
        }
        $orderSn = $this->getParam('orderSn','trim');
        $info = $orderService->getOrderInfo($orderSn);
        //是否售后
        $isSale = in_array($info['status'], ['shipping','shipped']) ? 1 : 0;
        //获取退款原因
        $this->view->setVar('reason', $orderService->getRefundReason($isSale));
        //数据
        $this->view->setVar('info', $info);
        //页面
        $this->view->pick('order/orderRefund');
    }

    /**
     * 育学园订单列表
     * @author Chensonglu
     */
    public function yukonOrderListAction()
    {
        $list = OrderService::getInstance();
        foreach($this->request->get() as $k=>$v){
            if(!is_array($v)){
                $param[$k] = $this->getParam($k,'trim');
            }
        }

        $param['page'] = $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $orderList = $list->getYukonOrder($param);

        //组织搜索条件,显示在前端页面
        $this->view->setVar('order_sn', isset($param['order_sn']) ? $param['order_sn'] : '');
        $this->view->setVar('start_time', isset($param['start_time']) ? $param['start_time'] : '');
        $this->view->setVar('end_time', isset($param['end_time']) ? $param['end_time'] : '');
        $this->view->setVar('userType', isset($param['userType']) ? $param['userType'] : '');
        $this->view->setVar('goodsType', isset($param['goodsType']) ? $param['goodsType'] : '');
        $this->view->setVar('searchType', isset($param['searchType']) ? $param['searchType'] : 'all');
        //配送方式
        $this->view->setVar('deliverySel', $list->orderDelivery);
        //数据返回页面
        $this->view->setVar('orderList', isset($orderList['orderList']) ? $orderList['orderList'] : '');
        $this->view->setVar('page', isset($orderList['page']) ? $orderList['page'] : '');
        //导出字段
        $this->view->setVar('excelField', $list->getExcelField());
        //添加备注权限
        $this->view->setVar('alterRemark', $this->func->checkActionAuth('order','addOrderRemark'));
        //页面
        $this->view->pick('order/yukonOrderList');
    }

    /**
     * 订单导出
     * @author Zhudan
     */
    public function yukonOrderExcelAction(){
        $this->view->disable();
        $list = OrderService::getInstance();
        $res = $list->excelOrder($_GET, 1);
        if(!$res){
            $this->msgRedirect('没有数据导出','orderlist');
        }
    }
}