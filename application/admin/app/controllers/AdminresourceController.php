<?php
/**
 * 后台权限资源控制器
 * Class AdminresourceController
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */

namespace Shop\Admin\Controllers;
use Shop\Services\AdminResourceService;
use Shop\Models\BaiyangAdminMenus;
use Shop\Models\BaiyangAdminResource;

class AdminresourceController extends ControllerBase
{

	/**
     * [$code description]
     * @var array
     */
    public $code = array('code'=>201,'msg'=>'失败','data'=>'');

    /**
     * [indexAction 统一后台权限资源]
     * @param  integer $pid [description]
     * @return [type]       [description]
     */
	public function indexAction($pid = 0)
	{
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->AjaxHead();
			// 取统一后台权限资源
			$list = AdminResourceService::getInstance()->getDefaultAll();
			return $this->response->setJsonContent($list);
		}
	}

	/**
	 * [indexPcAction PC后台资源]
	 * @return [type] [description]
	 */
	public function indexPcAction()
	{
		if($this->request->isAjax()) {
			$this->view->disable();
			$this->AjaxHead();
			// 取PC后台权限资源
			$list = AdminResourceService::getInstance()->getPcAll();
			return $this->response->setJsonContent($list);
		}
	}

	/**
	 * [addAction 添加资源]
	 */
	public function addAction()
	{
		$res = $pidData = array();
		$type = $this->request->get('type','trim','');
		$pid = $this->request->get('pid','trim',0);
		$level = $this->request->get('level','trim',1);
		if(!empty($pid)){
			// 添加子模块
			$pidData = AdminResourceService::getInstance()->getById($pid);
			if(empty($pidData)){
				return $this->success( '数据不存在！','/adminresource/index','error',3);
			}
			$level = $pidData['level'] + 1;
		}
		switch ($level) {
			case 2:
				# 二级
				$res = $this->addLevelChild($pidData);
				break;
			case 3:
				# 三级
				$res = $this->addLevelChild($pidData);
				break;
			default:
				# 一级
				$res = $this->addLevelOne();
				break;
		}
		if($res){
			return $this->response->setJsonContent($res);
		}
		
	}

	/**
	 * [addLevelOne 添加一级资源]
	 */
	public function addLevelOne()
	{
		if($this->request->isPost()){
			$post = $this->request->getPost();
			$site = $this->request->get('site','trim',0);
			$jump_url = "/adminresource/index";
			if($site == 1){
				// PC后台
				$jump_url = "/adminresource/indexpc";
			}
			$res = AdminResourceService::getInstance()->add($post);
			if(empty($res)){
				$this->code['msg'] = '失败';
				$this->code['data'] = AdminResourceService::getInstance()->error;
			}else{
				$this->code['code'] = 200;
				$this->code['msg'] = '成功';
				$this->code['data'] = $jump_url;
			}
			return $this->code;
		}
		$site = $this->request->get('site','trim',0);
		$this->view->setVar('site',$site);
		$this->view->pick('adminresource/addlevelone');
	}

	/**
	 * [addLevelChild 添加二三级资源]
	 * @param array $pidData [description]
	 */
	public function addLevelChild($pidData = array())
	{
		if($this->request->isPost()){
			$post = $this->request->getPost();
			$site = $this->request->get('site','trim',0);
			$jump_url = "/adminresource/index";
			if($site == 1){
				// PC后台
				$jump_url = "/adminresource/indexpc";
			}
			$res = AdminResourceService::getInstance()->add($post);
			if(empty($res)){
				$this->code['msg'] = '失败';
				$this->code['data'] = AdminResourceService::getInstance()->error;
			}else{
				$this->code['code'] = 200;
				$this->code['msg'] = '成功';
				$this->code['data'] = $jump_url;
			}
			return $this->code;
		}
		if(empty($pidData)){
			return $this->success( '数据不存在！',$jump_url,'error',3);
		}
		// 子模块
		$level = $pidData['level'] + 1;
		$this->view->setVar('pidData',$pidData);
		$this->view->setVar('level',$level);
		$this->view->pick('adminresource/addLevelChild');
	}

	/**
	 * [editAction 编辑资源]
	 * @return [type] [description]
	 */
	public function editAction()
	{
		$site = $this->request->get('site','trim',0);
		$jump_url = "/adminresource/index";
		if($site == 1){
			// PC后台
			$jump_url = "/adminresource/indexpc";
		}
		if($this->request->isPost()){
			$post = $this->request->getPost();
			$res = AdminResourceService::getInstance()->edit($post);
			if(empty($res)){
				$this->code['msg'] = '失败';
				$this->code['data'] = AdminResourceService::getInstance()->error;
			}else{
				$this->code['code'] = 200;
				$this->code['msg'] = '成功';
				$this->code['data'] = $jump_url;
			}
			return $this->response->setJsonContent($this->code);
		}else{
			$id = intval($this->request->get('id','trim',1));
			if(empty($id)){
				return $this->success( '参数不完整或者参数错误！',$jump_url,'error',3);
			}
			$data = AdminResourceService::getInstance()->getById($id);
			if(empty($data)){
				$error = AdminResourceService::getInstance()->error;
		        return $this->success($error,$jump_url,'error',3);
			}
			$this->view->setVar('data',$data);
		}
	}

	/**
	 * [delAction 删除资源]
	 * @return [type] [description]
	 */
	public function delAction()
    {
        $this->AjaxHead();
        $id = intval($this->request->get('id','trim',0));
        if(empty($id)){
			$this->code['msg'] = '参数不完整或者参数错误！';
		}
		$res = AdminResourceService::getInstance()->del($id);
		if(empty($res)){
			$this->code['msg'] = '失败';
			$this->code['data'] = AdminResourceService::getInstance()->error;
		}else{
			$this->code['code'] = 200;
			$this->code['msg'] = '成功';
		}
		return $this->response->setJsonContent($this->code);
    }

    public function testAction($value='')
    {
    	$BaiyangAdminMenus = new BaiyangAdminMenus();
    	$data = $BaiyangAdminMenus->find(
    		array('menu_level=1 and id > 33')
    	);
    	$BaiyangAdminResource = new BaiyangAdminResource();
    	$data = $data->toArray();	
    	$_data = array();
    	foreach ($data as $key => $value) {
    		$tmp = array();
    		$tmp['name'] = $value['menu_title'];
    		$tmp['level'] = 1;
    		$_data[] = $tmp;
    	}
    	// var_dump($_data);exit;
    	

    	
    	$BaiyangAdminResource->insertAll($_data);
    	var_dump($_data);exit;
    }

}
