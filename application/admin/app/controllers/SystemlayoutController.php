<?php
/**
 * Created by PhpStorm.
 * User: CSL
 * Date: 2017/12/28
 * Time: 10:47
 */

namespace Shop\Admin\Controllers;

use Shop\Services\SystemLayoutService;


class SystemlayoutController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 商城支付方式控制
     * @return mixed
     */
    public function editPaymentsAction()
    {
        if($this->request->isAjax()){
            $param = $_POST;;
            //修改支付方式
            $result = SystemLayoutService::getInstance()->editPayment($param);
            $this->view->disable();
            return $this->response->setJsonContent($result);
        }
        $service = SystemLayoutService::getInstance();
        //获取所有支付方式
        $allPayment = $service->getAllPayment();
        $this->view->setVar('allPayment', $allPayment);
        $this->view->setVar('channelName', $service->channelName);
        //页面
        $this->view->pick('systemlayout/Payments');
    }
}