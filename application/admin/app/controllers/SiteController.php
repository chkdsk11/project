<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/17 0017
 * baiyang_site表管理
 * Time: 下午 3:50
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\BaseService;
use Shop\Services\SiteService;
use Shop\Services\AdminService;

class SiteController extends ControllerBase
{

    /**
     *
     */
    public function initialize()
    {
        parent::initialize();
        $this->setTitle('功能权限管理');
        // 给模板引擎添加自定义函数
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        /** @var \Phalcon\Mvc\View\Engine\Volt\Compiler $compiler */
        $compiler = $volt->getCompiler();
        $compiler->addFunction('in_array', 'in_array');
    }

    /**
     *
     */
    public function indexAction()
    {

    }

    /**
     * 增
     */
    public function addAction()
    {
        $this->setTitle('站点添加');
        if($this->request->isPost()){
            $site=[];
            $site['site_name']=$this->request->getPost('site_name','trim');
            $site['is_enable']=intval($this->request->getPost('is_enable'));
            $site['site_menus']=$this->request->getPost('menu_id');
            if(empty($site['site_name'])){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('站点名称不能为空', '/site/add', 'error'));
            }
            if(is_array($site['site_menus']) && !empty($site['site_menus'])){
                $site['site_menus']=implode(',',$site['site_menus']);
            }else{
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('权限不能为空', '/site/add', 'error'));
            }
            $ret=SiteService::getInstance()->addSite($site);
            if($ret==='repeat'){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('站点名称重复', '/site/add', 'error'));
            }elseif($ret==true){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('添加成功', '/site/list', ''));
            }else{
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('添加失败', '/site/add', 'error'));
            }
        }else {
            $menus = AdminService::getInstance()->getAllMenu();
            $this->view->setVar('menus', $menus);
        }
    }

    /**
     * 删
     */
    public function delAction()
    {

    }

    /**
     * 改
     */
    public function editAction()
    {
        if($this->request->isPost()){
            $site=[];
            $site['site_id']=intval($this->request->getPost('site_id'));
            $site['site_name']=$this->request->getPost('site_name','trim');
            $site['is_enable']=$this->request->getPost('is_enable','int');
            $site['site_menus']=$this->request->getPost('menu_id');
            if(empty($site['site_id'])){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('站点id不存在', '/site/list', 'error'));
            }
            if(empty($site['site_name'])){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('站点名称不能为空', '/site/edit?site_id='.$site['site_id'], 'error'));
            }
            if(is_array($site['site_menus']) && !empty($site['site_menus'])){
                $site['site_menus']=implode(',',$site['site_menus']);
            }else{
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('权限不能为空', '/site/edit?site_id='.$site['site_id'], 'error'));
            }
            $ret=SiteService::getInstance()->updateSite([
                'set'=>'site_name=:site_name:,is_enable=:is_enable:,
                site_menus=:site_menus:,site_controllers=:site_controllers:,site_module=:site_module:',
                'bind'=>$site,
                'where'=>'site_id=:site_id:'
            ]);
            if($ret){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新成功', '/site/list', ''));
                //return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新成功', '/site/edit?site_id='.$site['site_id'], ''));
            }else{
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新失败', '/site/edit?site_id='.$site['site_id'], 'error'));
            }
        }else {
            $siteId = $this->getParam('site_id', 'int');
            $siteValue = SiteService::getInstance()->getSiteOne([
                'bind' => [
                    'site_id' => $siteId,
                ],
                'where' => 'site_id=:site_id:'
            ]);
            $allRules = AdminService::getInstance()->getAllMenu();
            //处理权限
            $menu = array_map(function ($item) use ($siteValue) {
                foreach ($item['son'] as $k => $v) {
                    foreach($v['son'] as $key=>$value) {
                        if (in_array($value['id'], $siteValue['site_menus'])){
                            $item['son'][$k]['son'][$key]['check'] = 'checked';
                        }
                    }
                }
                return $item;
            }, $allRules);
            $this->view->setVar('site_menu', $siteValue);
            $this->view->setVar('menus', $menu);
        }
    }

    /**
     * 查看
     */
    public function listAction()
    {
        $this->setTitle('站点列表');
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/site/list',
        ];
        $siteService=SiteService::getInstance();
        $siteValues=$siteService->getSiteList($param);
        if(is_array($siteValues) && $siteValues['status']) {
            $this->view->setVar('site', $siteValues['list']);
            $this->view->setVar('page', $siteValues['page']);
        }
    }
}