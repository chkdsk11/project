<?php

/**
 * 预警通知
 * @author yanbo
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\SmsNotifyService;

class SmsnotifyController extends ControllerBase {

    /**
     * 预警通知人列表
     */
    public function listAction() {
        $option['content'] = $this->getParam('content', 'trim');
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/Smsnotify/list',
            'option' => $option
        );
        $result = SmsNotifyService::getInstance()->getAll($param);
        $status = SmsNotifyService::getInstance()->getStatus(); //预警通知是否开启
        $this->view->setVars(
                array(
                    'data' => $result,
                    'status' => $status,
                    'option' => $option
                )
        );
    }

    /**
     * 删除预警通知人
     */
    public function delAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = SmsNotifyService::getInstance()->delUser($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 添加预警通知人
     */
    public function addAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = SmsNotifyService::getInstance()->addUser($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 更新预警通知状态
     * @return array json
     */
    public function updateAction() {
        $result = SmsNotifyService::getInstance()->updateStatus();
        return $this->response->setJsonContent($result);
    }

}
