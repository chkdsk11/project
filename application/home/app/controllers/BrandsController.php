<?php
/**
 * Created by PhpStorm.
 * User: 林晓聪
 * Date: 2016/10/25
 * Time: 14:34
 */

namespace Shop\Home\Controllers;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\BrandsService;

class BrandsController extends ControllerBase
{
    public function indexAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->addInstanceMethods(BrandsService::getInstance());
        $data = $hprose->start();
    }

}