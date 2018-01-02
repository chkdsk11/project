<?php
/**
 * 管理员角色控制器
 * Class AdminroleController
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */

namespace Shop\Admin\Controllers;
use Shop\Services\AdminRoleService;
use Shop\Services\AdminResourceService;

class AdminroleController extends ControllerBase
{

	/**
     * [$code description]
     * @var array
     */
    public $code = array('code'=>201,'msg'=>'失败','data'=>'');

	/**
     * [indexAction 列表]
     * @param  integer $pid [description]
     * @return [type]       [description]
     */
	public function indexAction()
	{
		$list = $map = array();
		$page = '';
		$pageParam = array();
    	$pageParam['page'] = $this->getParam('page','int',1);
    	$pageParam['url'] = $this->automaticGetUrl();
		$res = AdminRoleService::getInstance()->getPage($map,$pageParam);
		if($res){
			$list = $res['list'];
			$page = $res['page'];
		}
		$this->view->setVar('list',$list);
		$this->view->setVar('page',$page);
	}

	/**
	 * [addAction 添加]
	 */
	public function addAction()
	{
		if($this->request->isPost()){
			$post = $this->request->getPost();
			$jump_url = "/adminrole/index";
			$res = AdminRoleService::getInstance()->add($post);
			if(empty($res)){
				$this->code['msg'] = '失败';
				$this->code['data'] = AdminRoleService::getInstance()->error;
			}else{
				$this->code['code'] = 200;
				$this->code['msg'] = '成功';
				$this->code['data'] = $jump_url;
			}
			return $this->response->setJsonContent($this->code);
		}
	}

	/**
	 * [editAction 修改]
	 * @return [type] [description]
	 */
	public function editAction()
	{
		$jump_url = "/adminrole/index";
		if($this->request->isPost()){
			$post = $this->request->getPost();
			$res = AdminRoleService::getInstance()->edit($post);
			if(empty($res)){
				$this->code['msg'] = '失败';
				$this->code['data'] = AdminRoleService::getInstance()->error;
			}else{
				$this->code['code'] = 200;
				$this->code['msg'] = '成功';
				$this->code['data'] = $jump_url;
			}
			if(isset($post['is_enable']) && $post['is_enable'] == 0){
				// 禁用的角色清掉权限缓存
				$role_id = $post['role_id'];
				AdminRoleService::getInstance()->delCacheSingleAccess($role_id);
			}
			return $this->response->setJsonContent($this->code);
		}else{
			$role_id = intval($this->request->get('role_id','trim',1));
			if(empty($role_id)){
				return $this->success( '参数不完整或者参数错误！',$jump_url,'error',3);
			}
			$data = AdminRoleService::getInstance()->getByRoleId($role_id);
			if(empty($data)){
				$error = AdminRoleService::getInstance()->error;
		        return $this->success($error,$jump_url,'error',3);
			}
			$this->view->setVar('data',$data);
		}
	}

	/**
	 * [delAction 删除]
	 * @return [type] [description]
	 */
	public function delAction()
    {
        $this->AjaxHead();
        $role_id = intval($this->request->get('role_id','trim',0));
        if(empty($role_id)){
			$this->code['msg'] = '参数不完整或者参数错误！';
		}
		$res = AdminRoleService::getInstance()->del($role_id);
		if(empty($res)){
			$this->code['msg'] = '失败';
			$this->code['data'] = AdminRoleService::getInstance()->error;
		}else{
			$this->code['code'] = 200;
			$this->code['msg'] = '成功';
		}
		return $this->response->setJsonContent($this->code);
    }

    /**
     * [assignAccessAction 权限分配]
     * @param  integer $role_id [description]
     * @return [type]           [description]
     */
    public function assignAccessAction($role_id = 0)
    {
    	if($this->request->isPost()){
			$post = $this->request->getPost();
			$resource_id = $this->request->getPost('resource_id','trim','');
			$post['rules'] = '';
			if($resource_id){
				$post['rules'] = implode(',',$resource_id);
			}
			$res = AdminRoleService::getInstance()->edit($post);
			if(empty($res)){
				$this->code['msg'] = '失败';
				$this->code['data'] = AdminRoleService::getInstance()->error;
			}else{
				$this->code['code'] = 200;
				$this->code['msg'] = '成功';
			}
			// 更新单个角色权限缓存
			AdminRoleService::getInstance()->cacheSingleAccess($post);
			return $this->response->setJsonContent($this->code);
		}
		$jump_url = "/adminrole/index";
    	$role_id = intval($this->request->get('role_id','trim',0));
        if(empty($role_id)){
			$this->code['msg'] = '参数不完整或者参数错误！';
		}
		$adminRole = AdminRoleService::getInstance()->getByRoleId($role_id);
		if(empty($adminRole)){
			$error = AdminRoleService::getInstance()->error;
	        return $this->success($error,$jump_url,'error',3);
		}
		$adminRole['rules'] = json_encode(explode(',',$adminRole['rules']));
		// 统一后台权限资源
		$reourceList = AdminResourceService::getInstance()->getDefaultAll();
		// PC后台权限资源
		$reourcePcList = AdminResourceService::getInstance()->getPcAll();
		$this->view->setVar('adminRole',$adminRole);
		$this->view->setVar('reourceList',$reourceList);
		$this->view->setVar('reourcePcList',$reourcePcList);
    }

}