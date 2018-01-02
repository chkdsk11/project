<?php
/**
 *
 *
 *
 */
namespace Shop\Admin\Controllers;
use Shop\Admin\Controllers\ControllerBase;
use Phalcon\Mvc\Application\Exception;
use Phalcon\Mvc\Controller;
use Shop\Datas\BaiyangRoleData;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangSku;
use Shop\Services\AdminService;
use Shop\Services\CustomerconsultService;
use Shop\Services\CustomerconsultService as clt;

class CustomesController extends ControllerBase
{
	
	/**
	 * 药师咨询
	 */
	public function listAction ()
	{
		foreach($this->request->get() as $k=>$v)
		{
			$param[$k]  =   $this->getParam($k,'trim');
		}
		//注入函数到模版
		$volt = $this->di->get("volt", [$this->view, $this->di]);
		$compiler = $volt->getCompiler();
		$compiler->addFunction('empty', 'empty');
		$param['page']  =   $this->getParam('page','int',1);
		$param['url'] = $this->automaticGetUrl();
		$cltData = (clt::getInstance())->getAllClt($param);
		$this->view->setVars([
			'cltData' => $cltData,
			'msg_content' =>  ($param['msg_content'])??$param['msg_content']??'',
			'serv_nickname' =>  ($param['serv_nickname'])??$param['serv_nickname']??'',
			'msg_status' =>  ($param['msg_status'])??$param['msg_status']??'',
		]);
	}
	
	/**
	 * 批量关闭反馈
	 * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
	 */
	public function batchCancelCltAction ()
	{
		$this->view->disable();
		$param = $this->request->getPost();
		#return false;#
		$cltServer = CustomerconsultService::getInstance();
		$clt = $cltServer->updateIdsClt($param);
		return $this->response->setJsonContent($clt);
	}
	
	/**
	 *用户留言
	 */
	public function messageListAction ()
	{
		foreach($this->request->get() as $k=>$v)
		{
			$param[$k]  =   $this->getParam($k,'trim');
		}
		//注入函数到模版
		$volt = $this->di->get("volt", [$this->view, $this->di]);
		$compiler = $volt->getCompiler();
		$compiler->addFunction('in_array', 'in_array');
		$param['page']  =   $this->getParam('page','int',1);
		$param['url'] = $this->automaticGetUrl();
		$cltData = (clt::getInstance())->getAllMessage($param);
		$this->view->setVars([
			'cltData' => $cltData,
			'telephone' =>  ($param['telephone'])??$param['telephone']??'',
		]);
	}
	
	public function cancelMsgAction ()
	{
		$this->view->disable();
		$ids = $this->request->getPost('ids');
		$cltServer = CustomerconsultService::getInstance();
		$clt = $cltServer->updateMsg($ids);
		return $this->response->setJsonContent($clt);
	}
	
	public function remarkMsgAction ()
	{
		$this->view->disable();
		$params = $this->request->getPost();
		$cltServer = CustomerconsultService::getInstance();
		$clt = $cltServer->remarkMsg($params);
		return $this->response->setJsonContent($clt);
	}
}

