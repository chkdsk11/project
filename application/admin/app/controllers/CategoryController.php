<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\CategoryService;
use Shop\Services\CategoryProductRuleService;


class CategoryController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    /**
     * @desc 分类列表
     * @param
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function listAction()
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
        $category = CategoryService::getInstance()->categoryLists();
        return $this->response->setJsonContent($category);
    }

    /**
     * @desc 获取分类信息
     * @param
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function getCategoryAction()
    {
        $this->view->disable();
        $id = $this->getParam('id','int',0);
        $productRule = $this->getParam('productRule','int',0);
        $CategoryService = CategoryService::getInstance();
        $category = $CategoryService->getCategory($id);
        if($productRule){
            if(count($category['data'])){
                foreach($category['data'] as $k=>$v){
                    $tmp = $CategoryService->getCategory($v['id'],'id');
                    $category['data'][$k] = $tmp['data'][0];
                }
            }
        }
        return $this->response->setJsonContent($category);
    }

    /**
     * @desc 添加分类信息
     * @param
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function addAction()
    {
        //判断是否post提交
        $CategoryService = CategoryService::getInstance();
        if($this->request->isPost() || $this->request->isAjax()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $res = $CategoryService->insertCategory($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            $pid = $this->getParam('id','int');
            $res = $CategoryService->categoryLists();
            //给模板引擎添加自定义函数
            $volt = $this->di->get("volt", [$this->view, $this->di]);
            $compiler = $volt->getCompiler();
            $compiler->addFunction('isset', 'isset');
            $this->view->setVar('category',$res);
            $this->view->setVar('pid',$pid);
            $this->view->pick('category/edit');
        }
    }

    /**
     * @desc 修改分类信息
     * @param
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function editAction()
    {
        //判断是否post提交
        $CategoryService = CategoryService::getInstance();
        if($this->request->isPost() || $this->request->isAjax()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $res = $CategoryService->updateCategory($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            $id = $this->getParam('id','int');
            $res = $CategoryService->getCategory($id,'id');
            if($res['status'] == 'error'){
                $this->response->redirect($res['url']);
                return;
            }
            $this->view->setVar('categoryOne',$res['data'][0]);
            $res = $CategoryService->categoryLists();
            $this->view->setVar('category',$res);
            $this->view->setVar('act',1);
            $volt = $this->di->get("volt", [$this->view, $this->di]);
            $compiler = $volt->getCompiler();
            $compiler->addFunction('isset', 'isset');
            $this->view->pick('category/edit');
        }
    }

    /**
     * @desc 分类删除
     * @param $id int 要删除的分类的ID
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function delAction(){
        $id = $this->getParam('id','trim');
        $res = CategoryService::getInstance()->delCategory($id);
        return $this->response->setJsonContent($res);
    }
    /**
     * @desc 启用|禁用
     * @param $id int 要切换的分类ID
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function isSwitchAction()
    {
        $id = $this->getParam('id','trim');
        $is_enable = $this->getParam('is_enable','trim');
        $res = CategoryService::getInstance()->isSwitch($id,$is_enable);
        return $this->response->setJsonContent($res);
    }

    /*********************************分类品规管理***********************************/
    /**
     * @desc 多品规列表
     * @param
     * @author 梁伟
     * @date: 2016/8/25
     */
    public function rulelistAction()
    {
        $CategoryService = CategoryService::getInstance();
        $category = $CategoryService->getCategory();
        $category2 = $CategoryService->getCategory($category['data'][0]['id']);
        $tmp = $CategoryService->getCategory($category2['data'][0]['id']);
        $list = array();
        foreach($tmp['data'] as $k => $v){
            $tmps = $CategoryService->getCategory($v['id'],'id');
            $list[$k] = $tmps['data'];
        }
        $this->view->setVar('category',$category['data']);
        $this->view->setVar('category2',$category2['data']);
        $this->view->setVar("list",$list);
    }

    /**
     * 编辑商品规格
     */
    public function ruleeditAction(){
        $param = $this->postParam($this->request->getPost(), 'trim', '');
        $res = CategoryProductRuleService::getInstance()->addCategoryProductRule($param);
        return $this->response->setJsonContent($res);
    }
}