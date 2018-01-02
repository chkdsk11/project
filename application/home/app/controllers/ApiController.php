<?php
/**
 * Created byPhpStorm .
 * User: qinliang
 * Date: 2017/05/22
 * Time: 上午 10:45
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\ApiService;


class ApiController extends \Phalcon\Mvc\Controller
{
    
    /**
     *  @desc 冻结 确认收货 退单完成(订单作废) 海典调用官网接口
     *  @author qinliang
     */
    public function orderNoticeOperationAction()
    {
        $this->view->disable();
        ApiService::getInstance()->orderNoticeOperation($this->request->getPost());
    }
    
    /**
     * @desc 用户申请退款通知海典
     * @author qinliang
     */
    /* public function erpApplyREfundNoticeAction()
    {
        $this->view->disable();
        $result = ApiService::getInstance()->erpApplyREfundNotice($this->request->getPost());
        print_r($result);exit;
    } */
    
    /**
     * @desc 定时任务 重新推送失败申请退款记录
     * @author qinliang
     */
    public function erpErrorREfundNoticeAction()
    {
        $this->view->disable();
        ApiService::getInstance()->erpErrorREfundNotice($this->request->getPost());
    }

}