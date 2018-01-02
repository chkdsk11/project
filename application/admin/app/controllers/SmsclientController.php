<?php

/**
 * 短信或验证码启用设置
 * @author yanbo
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\SmsClientService;

class SmsclientController extends ControllerBase {

    /**
     * 短信\图片启用列表
     */
    public function listAction() {
        $option['template_name'] = $this->getParam('template_name', 'trim', '');
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/Smsclient/list',
            'option' => $option
        );
        $result = SmsClientService::getInstance()->getList($param);
        $this->view->setVars(array(
            'data' => $result,
            'option' => $option
        ));
    }

    /**
     * 修改启用状态
     * @return type
     */
    public function editAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = SmsClientService::getInstance()->editClient($param);
            return $this->response->setJsonContent($result);
        }
    }

}
