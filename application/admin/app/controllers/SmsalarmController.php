<?php

/**
 * 预警值设置
 * @author yanbo
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\SmsAlarmService;

class SmsalarmController extends ControllerBase {

    /**
     * 警戒值设置列表
     */
    public function listAction() {
        $result = SmsAlarmService::getInstance()->getList();
        $this->view->setVar('data', $result);
    }

    /**
     * 警戒值修改
     * @return array json
     */
    public function editAction() {
        if ($this->request->isAjax()) {
            $param = json_decode($this->request->getPost('arr'));
            $result = SmsAlarmService::getInstance()->editAlarm($param);
            return $this->response->setJsonContent($result);
        }
    }

}
