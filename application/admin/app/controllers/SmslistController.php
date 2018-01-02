<?php

/**
 * 黑白名单管理
 * @author yanbo
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\SmsListService;

class SmslistController extends ControllerBase {

    /**
     * 黑名单列表
     */
    public function blacklistAction() {
        $option['selecttime'] = $this->getParam('selecttime', 'trim');
        $option['starttime'] = $this->getParam('starttime', 'trim');
        $option['endtime'] = $this->getParam('endtime', 'trim');
        $option['content'] = $this->getParam('content', 'trim');
        $option['list_type'] = "black";

        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/Smslist/list',
            'option' => $option
        );
        $result = SmsListService::getInstance()->getAll($param);
        $this->view->setVars(array(
            'data' => $result,
            'option' => $option
                )
        );
    }

    /**
     * 添加黑名单
     * @return json
     */
    public function addblackAction() {
        if ($this->request->isAjax()) {
            $param['ip_address'] = $this->request->getPost('ip_address', 'trim');
            $param['phone'] = $this->request->getPost('phone', 'trim');
            $param['list_type'] = 'black';
            $result = SmsListService::getInstance()->addList($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 解除黑名单
     * @return json
     */
    public function delblackAction() {
        if ($this->request->isAjax()) {
            $list_id = $this->request->getPost('list_id', 'int');
            $result = SmsListService::getInstance()->delList($list_id);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 白名单列表
     */
    public function whitelistAction() {
        $option['selecttime'] = $this->getParam('selecttime', 'trim');
        $option['starttime'] = $this->getParam('starttime', 'trim');
        $option['endtime'] = $this->getParam('endtime', 'trim');
        $option['content'] = $this->getParam('content', 'trim');
        $option['list_type'] = "white";

        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/SmsList/list',
            'option' => $option
        );
        $result = SmsListService::getInstance()->getAll($param);
        $this->view->setVars(array(
            'data' => $result,
            'option' => $option
                )
        );
    }

    /**
     * 添加白名单
     * @return json
     */
    public function addwhiteAction() {
        if ($this->request->isAjax()) {
            $param['ip_address'] = $this->request->getPost('ip_address', 'trim');
            $param['phone'] = $this->request->getPost('phone', 'trim');
            $param['list_type'] = 'white';
            $result = SmsListService::getInstance()->addList($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 解除白名单
     * @return json
     */
    public function delwhiteAction() {
        if ($this->request->isAjax()) {
            $list_id = $this->request->getPost('list_id', 'int');
            $result = SmsListService::getInstance()->delList($list_id);
            return $this->response->setJsonContent($result);
        }
    }

}
