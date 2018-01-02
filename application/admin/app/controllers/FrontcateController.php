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
use Shop\Services\FrontCategoryService as fcate;
use Shop\Datas\BaiyangFrontCateData;
use Shop\Services\BaseService;
use Shop\Services\CategoryService;

class FrontCateController extends ControllerBase
{
	
	public function pclistAction ()
	{
		$this->view->disable();
		$url = 'http://www.baiyangwang.com/Admin/Ad/mainCategoryList/pid/0.html';
		header( "HTTP/1.1 301 Moved Permanently" );
		header('Location:'.$url);
	}
	
	public function applistAction ()
	{
	}
	
	/**
	 * @desc 获取全部分类信息树
	 * return
	 * @author 梁伟
	 * @date: 2016/8/25
	 */
	public function getCateAction()
	{
		$param = $this->request->get();
		$category = fcate::getInstance()->categoryLists($param);
		return $this->response->setJsonContent($category);
	}
	
	public function addChildAction ()
	{
		//判断是否post提交
		$CategoryService = fcate::getInstance();
		$CateService = CategoryService::getInstance();
		if($this->request->isPost() || $this->request->isAjax()){
			$param  = $this->postParam($this->request->getPost(), 'trim', '');
			#var_dump($param);exit();
			$res = $CategoryService->insertCategory($param);
			#var_dump($res);exit();
			$this->view->disable();
			return $this->response->setJsonContent($res);
		}else{
			$param = $this->request->get();
			$pres = $CategoryService->getCategoryApp($param['id']);
			$ptype = $CategoryService->getProType();
			$backres = $CateService->categoryLists();
			#var_dump($backres);exit();
			//给模板引擎添加自定义函数
			$volt = $this->di->get("volt", [$this->view, $this->di]);
			$compiler = $volt->getCompiler();
			$compiler->addFunction('isset', 'isset');
			$this->view->setVars(
				[
					'category'=>    $pres['data'],
					'types'  => $ptype,
					'bcategory' =>  $backres
				]
			);
			$this->view->setVar('pid',$param['id']);
		}
	}
	
	public function addfirstAction ()
	{
		//判断是否post提交
		$CategoryService = fcate::getInstance();
		$CateService = CategoryService::getInstance();
		$backres = $CateService->categoryLists();
		$ptype = $CategoryService->getProType();
		//给模板引擎添加自定义函数
		$this->view->setVar('types',$ptype);
		$this->view->setVar('bcategory',$backres);
	}
	
	public function editAction ()
	{
		//判断是否post提交
		$CategoryService = fcate::getInstance ();
		$CateService = CategoryService::getInstance();
		if($this->request->isPost() || $this->request->isAjax()){
			$param  = $this->postParam($this->request->getPost(), 'trim', '');
			$res = $CategoryService->updateCategory($param);
			#var_dump($res);exit();
			$this->view->disable();
			return $this->response->setJsonContent($res);
		}else {
			$param = $this->request->get ();
			$pres = $CategoryService->getCategoryApp ($param[ 'id' ]);
			$pid = $pres['data'][0]['product_category_id'];
			$ptype = $CategoryService->getProType();
			$backres = $CateService->categoryLists();
			#var_dump($pres);exit();
			$volt = $this->di->get ("volt", [$this->view, $this->di]);
			$compiler = $volt->getCompiler ();
			$compiler->addFunction ('isset', 'isset');
			$this->view->setVars (
				[
					'pid'   =>  $pid,
					'category' => $pres[ 'data' ],
					'types'    => $ptype,
					'bcategory' =>  $backres
				]
			);
			$this->view->pick ('frontcate/edit');
		}
	}
	
	public function delAction ()
	{
		
	}
	
	public function setGoodsAction ()
	{
		
	}
	
	public function isSwitchAction ()
	{
		$gets = $this->request->get();
		$res = fcate::getInstance()->isSwitch($gets);
		return $this->response->setJsonContent($res);
	}
	
	public function uploadAction()
	{
		if ($this->request->hasFiles())
		{
			$res = BaseService::getInstance()->uploadFile($this->request, $this->config['application']['uploadDir'].'images/cates/');
			//判断是否是编辑上传图片，是则返回编辑器json格式
			if($this->getParam('dir', 'trim', '') == 'image'){
				$res = array('error' => 0, 'url' => $res['data'][0]['src']);
			}
			return $this->response->setJsonContent($res);
		}
	}

    /**
     * 更新排序
     * @return mixed
     */
	public function editSortAction(){
        //判断是否post提交
        if($this->request->isPost() || $this->request->isAjax()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $CategoryService = fcate::getInstance ();
            $res = $CategoryService->editSort($param);
            #var_dump($res);exit();
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }
    }
}

