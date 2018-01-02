<?php

/**
 * 短信发送记录
 * @author yanbo
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\SmsRecordsService;

class SmsrecordsController extends ControllerBase {

    /**
     * 短信发送列表
     */
    public function listAction() {
        $option['provider_code'] = $this->getParam('provider_code', 'trim');
        $option['client_code'] = $this->getParam('client_code', 'trim');
        $option['content'] = $this->getParam('content', 'trim');
        $option['starttime'] = $this->getParam('starttime', 'trim');
        $option['endtime'] = $this->getParam('endtime', 'trim');
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/Smsrecords/list',
            'option' => $option
        );
        $result = SmsRecordsService::getInstance()->getAll($param);
        $this->view->setVars(array(
            'data' => $result,
            'option' => $option
        ));
    }

    /**
     * 数据总览，短信数据和图表
     * @return array
     */
    public function statementAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim');
            $result = SmsRecordsService::getInstance()->statement($param);
            return $this->response->setJsonContent($result);
        }
    }

}
