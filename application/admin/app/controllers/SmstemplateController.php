<?php

/**
 * 短信模板设置
 * @author yanbo
 */

namespace Shop\Admin\Controllers;

use Shop\Services\SmsTemplateService;

/**
 * Description of SmsTemplateController
 *
 * @author dell
 */
class SmstemplateController extends ControllerBase {

    /**
     * 短信模板列表
     * @return type
     */
    public function listAction() {
        $option['signature'] = $this->getParam('signature', 'trim', '');
        $option['contents'] = $this->getParam('contents', 'trim', '');
        $param = array(
            'page' => (int) $this->getParam('page', 'trim', 1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/Smstemplate/list',
            'option' => $option
        );
        $result = SmsTemplateService::getInstance()->getList($param);
        $this->view->setVars(array(
            'data' => $result,
            'option' => $option
        ));
    }

    /**
     * 修改短信模板
     * @return type
     */
    public function editAction() {
        if ($this->request->isAjax()) {
            $param = $this->postParam($this->request->getPost(), 'trim', '');
            $result = SmsTemplateService::getInstance()->editTemplate($param);
            return $this->response->setJsonContent($result);
        }
        $template_id = (int) $this->getParam('template_id', 'trim', '');
        $result = SmsTemplateService::getInstance()->getInfo($template_id);
        $this->view->setVar('data', $result);
    }

}
