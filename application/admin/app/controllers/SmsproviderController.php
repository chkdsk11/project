<?php

namespace Shop\Admin\Controllers;

use Shop\Services\SmsProviderService;

/**
 * 短信服务商
 * @author yanbo
 */
class SmsproviderController extends ControllerBase {

    public function initialize() {
        parent::initialize();
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        $compiler = $volt->getCompiler();
        $compiler->addFunction("in_array", "in_array");
    }

    /**
     * 短信运营商列表
     */
    public function listAction() {
        $result = SmsProviderService::getInstance()->getAll();
        $this->view->setVar('list', $result);
    }

    /**
     * 更新运营商状态
     * @return type
     */
    public function updateAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = SmsProviderService::getInstance()->updateState($param);
            return $this->response->setJsonContent($result);
        }
    }

    /**
     * 修改比例
     * @return type
     */
    public function editscaleAction() {
        if ($this->request->isAjax()) {
            $param = json_decode($this->request->getPost('arr'));
            $result = SmsProviderService::getInstance()->editScale($param);
            return $this->response->setJsonContent($result);
        }
        $result = SmsProviderService::getInstance()->getAll();
        $this->view->setVar('list', $result);
    }

    /**
     * 修改补发优先级
     * @return type
     */
    public function editpriorityAction() {
        if ($this->request->isAjax()) {
            $param = json_decode($this->request->getPost('arr'));
            $result = SmsProviderService::getInstance()->editPriority($param);
            return $this->response->setJsonContent($result);
        }
        $result = SmsProviderService::getInstance()->getAll();
        $this->view->setVar('list', $result);
    }

    /**
     * 获取密码修改记录
     */
    public function pwlogAction() {
        $option['selecttime'] = $this->getParam('selecttime', 'trim');
        $option['provider_id'] = $this->getParam('provider_id', 'int');
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/Smsprovider/pwLog',
            'option' => $option
        );
        $result = SmsProviderService::getInstance()->getLogs($param);
        $this->view->setVars(array(
            'data' => $result,
            'option' => $option
        ));
    }

    /**
     * 修改密码或周期
     */
    public function editpwAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = SmsProviderService::getInstance()->editPw($param);
            return $this->response->setJsonContent($result);
        } else {
            $result = SmsProviderService::getInstance()->getAll();
            $this->view->setVar('list', $result);
        }
    }

}
