<?php
/**
 * Created by PhpStorm.
 * User: Chensonglu
 * Date: 2017/5/5
 * Time: 16:41
 */

namespace Shop\Admin\Controllers;

use Shop\Services\OrderService;
use Shop\Services\RefundService;


class RefundController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();

        //退款状态
        $refundState = array_flip(RefundService::getInstance()->refundState);
        $refundState[7] = '待卖家退款';
        $this->view->setVar('refundState', $refundState);
        //发货商家
        $orderService = OrderService::getInstance();
        //发货商家
        $this->view->setVar('shops', $orderService->getShops());
        //订单状态
        $this->view->setVar('orderStat', $orderService->orderStat);
        //商品跳转地址
        $this->view->setVar('jumpUrl', $this->config['wap_base_url'][$this->config->environment]);
    }

    /**
     * 服务单列表
     * @author Chensonglu
     */
    public function refundListAction()
    {
        $refundService = RefundService::getInstance();
        foreach($this->request->get() as $k=>$v){
            if(!is_array($v)){
                $param[$k] = $this->getParam($k,'trim');
            }
        }
        $param['page'] = $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $serviceList = $refundService->getServiceAll($param);
        //退款状态选项值
        $this->view->setVar('refundStateSel', $refundService->refundState);
        //待处理服务单数量
        $this->view->setVar('pendingNum', $refundService->getPendingNum());

        //组织搜索条件,显示在前端页面
        $this->view->setVar('orderSn', isset($param['orderSn']) ? $param['orderSn'] : '');
        $this->view->setVar('startTime', isset($param['startTime']) ? $param['startTime'] : '');
        $this->view->setVar('endTime', isset($param['endTime']) ? $param['endTime'] : '');
        $this->view->setVar('refundStatus', isset($param['refundStatus']) ? $param['refundStatus'] : '');
        $this->view->setVar('serviceSn', isset($param['serviceSn']) ? $param['serviceSn'] : '');
        $this->view->setVar('shopId', isset($param['shopId']) ? $param['shopId'] : '');
        $this->view->setVar('username', isset($param['username']) ? $param['username'] : '');
        $this->view->setVar('auditor', isset($param['auditor']) ? $param['auditor'] : '');
        $this->view->setVar('goodsName', isset($param['goodsName']) ? $param['goodsName'] : '');
        $this->view->setVar('goodsId', isset($param['goodsId']) ? $param['goodsId'] : '');
        $this->view->setVar('searchType', isset($param['searchType']) ? $param['searchType'] : 'all');
        $this->view->setVar('psize', isset($param['psize']) ? $param['psize'] : 15);
        $this->view->setVar('excelField', $refundService->getExcelField());

        //数据返回页面
        $this->view->setVar('list', isset($serviceList['list']) ? $serviceList['list'] : 0);
        $this->view->setVar('page', isset($serviceList['page']) ? $serviceList['page'] : 0);
        //页面
        $this->view->pick('refund/serviceList');
    }

    /**
     * 服务单导出
     * @author Zhudan
     */
    public function serviceExcelAction(){
        $this->view->disable();
        $list = RefundService::getInstance();

        $res = $list->excelService($_GET);
        if(!$res){
            $this->msgRedirect('没有数据导出','refundlist');
        }
    }

    /**
     * 服务单详情
     * @author Chensonglu
     */
    public function refundDetailAction()
    {
        $serviceSn = $this->getParam('serviceSn','trim');
        $info = RefundService::getInstance()->getRefundDetail($serviceSn);
        //订单来源
        if (isset($info['order_sn'])) {
            $info['orderSource'] = OrderService::getInstance()->orderSource($info['order_sn']);
        }
        //订单数据
        $this->view->setVar('info', $info);
        //修改订单状态权限
        $this->view->setVar('alterState', $this->func->checkActionAuth('refund','changeRefundState'));
        //页面
        $this->view->pick('refund/serviceDetail');
    }

    /**
     * 添加备注
     * @return mixed
     * @author Chensonglu
     */
    public function addRemarkAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = RefundService::getInstance()->addRemark($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 审核服务单
     * @return mixed
     * @author Chensonglu
     */
    public function refundAuditAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = RefundService::getInstance()->refundAudit($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 服务单退款
     * @return mixed
     * @author Chensonglu
     */
    public function confirmRefundAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = RefundService::getInstance()->confirmRefund($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 服务单收货
     * @return mixed
     * @author Chensonglu
     */
    public function receivingGoodsAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = RefundService::getInstance()->receivingGoods($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 修改服务单状态
     * @return mixed
     * @author Chensonglu
     */
    public function changeRefundStateAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;
            $result = RefundService::getInstance()->changeRefundState($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
    }
}