<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\SupplierService;


class SupplierController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
    }

    public function listAction()
    {
        $param  = $this->request->get();
        $param['url'] = $this->automaticGetUrl();
        $SupplierService = SupplierService::getInstance();
        $res = $SupplierService->getList($param);
        $this->view->setVar('list',$res['list']);
        $this->view->setVars(
        	[
        		'name'=>$param['name'],
        	    'user_name'=>$param['user_name'],
        	    'address'=>$param['address'],
        	    'phone'=>$param['phone'],
        	    'code'=>$param['code'],
        	    'id'=>$param['id'],
            ]
        );
        $this->view->setVar('page',$res['page']);
    }

    public function getSupplierAction()
    {
        $param  = $this->request->get();
        $res = SupplierService::getInstance()->getSupplier($param);
        return $this->response->setJsonContent($res);
    }

    public function setSupplierAction()
    {
        $param  = $this->postParam($this->request->getPost(), 'trim', '');
        $res = SupplierService::getInstance()->setSupplier($param);
        return $this->response->setJsonContent($res);
    }
	
	public function addSupplierAction ()
	{
		$param  = $this->postParam($this->request->getPost(), 'trim', '');
		$res = SupplierService::getInstance()->addSupplier($param);
		return $this->response->setJsonContent($res);
    }
}