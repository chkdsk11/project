<?php
/**
 * @author 邓永军
 */
namespace Shop\Home\Controllers;
use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\GoodsetService;
use Shop\Home\Services\HproseService;

class GoodsetController extends ControllerBase
{
    public function listAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(GoodsetService::getInstance());
        $hprose->start();
    }
}