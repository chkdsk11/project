<?php
/**
 * Created byPhpStorm .
 * User: Administrator
 * Date: 2016/9/21 0021
 * Time: 上午 10:17
 */

namespace Shop\Home\Controllers;

use Shop\Home\Controllers\ControllerBase;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\CategoryService;


class CategoryController extends \Phalcon\Mvc\Controller
{
    /**
     *  测试hprose
     */
    public function indexAction()
    {
        $this->view->disable();
        $hprose=new HproseService();
        $hprose->addInstanceMethods(CategoryService::getInstance());
        $hprose->start();
    }

}