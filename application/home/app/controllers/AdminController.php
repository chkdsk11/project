<?php
/**
 * 管理员控制器
 * Class AdminController
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */
namespace Shop\Home\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Home\Services\HproseService;
use Shop\Home\Services\AdminService;

class AdminController extends Controller
{

	/**
	 * [hproseAction 后台管理员]
	 * @return [type] [description]
	 */
    public function hproseAction()
    {
        $this->view->disable();
        $hprose = new HproseService();
        $hprose->addInstanceMethods(AdminService::getInstance());
        $data = $hprose->start();
    }
}
