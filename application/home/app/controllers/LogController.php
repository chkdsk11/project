<?php
/**
 * Author: DengYongJun
 * Email: i@darkdin.com
 * Time: 2017/02/07/11:14
 */
namespace Shop\Home\Controllers;
use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\LogService;
use Shop\Libs\SaveLog;

class LogController extends ControllerBase
{
    public function saveAction()
    {
        $this->view->disable();
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(LogService::getInstance());
        $hprose->start();
    }
}