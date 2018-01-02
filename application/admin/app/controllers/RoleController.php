<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/31 0031
 * Time: 下午 1:54
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\BaseService;
use Shop\Services\RoleService;
use Shop\Services\SiteService;
use Shop\Services\AdminService;

class RoleController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        // 给模板引擎添加自定义函数
        $volt = $this->di->get("volt", [$this->view, $this->di]);
        /** @var \Phalcon\Mvc\View\Engine\Volt\Compiler $compiler */
        $compiler = $volt->getCompiler();
        $compiler->addFunction('in_array', 'in_array');
    }

    /**
     * 查
     */
    public function listAction()
    {
        foreach($this->request->get() as $k=>$v){
            $data[$k]  =   $this->getParam($k,'trim');
        }
        $param = [
            'param' => $data,
            'page' => $this->getParam('page','int',1),
            'url' => $this->automaticGetUrl(),
            'url_back' => '',
            'home_page' => '/role/list',
        ];
        $roleService=RoleService::getInstance();
        $roleValues=$roleService->getRoleList($param);
        if(is_array($roleValues) && $roleValues['status']) {
            $this->view->setVar('role', $roleValues['list']);
            $this->view->setVar('page', $roleValues['page']);
            $this->view->setVar('site',$roleValues['site']);
        }
    }

    /**
     * 添加
     */
    public function addAction()
    {
        $this->setTitle('角色添加');
        if($this->request->isPost()){
            $role=[];
            $role['role_name']=$this->request->getPost('role_name','trim');
            $role['is_enable']=intval($this->request->getPost('is_enable'));
            $menuId=$this->request->getPost('menu_id');
            if(empty($role['role_name'])){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('角色名称不能为空', '/role/add', 'error'));
            }
            if(is_array($menuId) && !empty($menuId)){
                $menuId=implode(',',$menuId);
            }else{
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('权限不能为空', '/role/add', 'error'));
            }
            $role['menu_id']=$menuId;
            $role['site_id']=intval($this->request->getPost('site_id'));
            $ret=RoleService::getInstance()->addRole($role);
            if($ret > 0){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('添加成功', '/role/list', ''));
            }elseif($ret===false){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('添加失败', '/role/add', 'error'));
            }elseif($ret==='repeat'){
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('角色名不能重复', '/role/list', 'error'));
            }
        }else {
            $site = SiteService::getInstance()->getAllSite();
            $menu = AdminService::getInstance()->getAllMenu();
            foreach ($menu as $k => $v) {
                foreach ($v['son'] as $kk => $vv) {
                    foreach ($vv['son'] as $kkk => $vvv) {
                        if (in_array($vvv['id'], explode(',', $site[0]['site_menus']))) {
                            $menu[$k]['son'][$kk]['son'][$kkk]['is_enable'] = 1;
                        }
                    }
                }
            }
            $this->view->setVar('site', $site);
            $this->view->setVar('menus', $menu);
        }
    }

    /**
     * 改
     */
    public function editAction()
    {
        $this->setTitle('角色编辑');
        if($this->request->isPost()){
           $role=[];
           $role['role_id']=intval($this->request->getPost('role_id'));
           if($role['role_id']){
               $role['role_name']=trim($this->request->getPost('role_name'));
               if(empty($role['role_name'])){
                   return $this->response->setJsonContent(BaseService::getInstance()->arrayData('角色名称不能为空', '/role/edit?role_id='.$role['role_id'], 'error'));
               }
               $role['site_id']=intval($this->request->getPost('site_id'));
               $role['menu_id']=$this->request->getPost('menu_id');
               if(is_array($role['menu_id']) && !empty($role['menu_id'])){
                   $role['menu_id']=implode(',',$role['menu_id']);
               }elseif(empty($role['menu_id'])){
                   return $this->response->setJsonContent(BaseService::getInstance()->arrayData('权限不能为空', '/role/edit?role_id='.$role['role_id'], 'error'));
               }
               $role['is_enable']=intval($this->request->getPost('is_enable'));
                $ret=RoleService::getInstance()->updateAdminRole([
                    'set'=>'role_name=:role_name:,site_id=:site_id:,menu_id=:menu_id:,is_enable=:is_enable:,
                    controller_id=:controller_id:,
                    module_id=:module_id:',
                    'bind'=>$role,
                    'where'=>'role_id=:role_id:'
                ]);
               if($ret){
                   return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新成功', '/role/list', ''));
               }else{
                   return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新失败', '/role/edit?role_id='.$role['role_id'], 'error'));
               }
           }
        }else {
            $roleId=intval($this->request->get('role_id'));
            if ($roleId) {
                $role = RoleService::getInstance()->getRoleOne([
                    'where' => 'role_id=:role_id:',
                    'bind' => [
                        'role_id' => $roleId
                    ]
                ]);
                $site = SiteService::getInstance()->getAllSite();
                $menus = AdminService::getInstance()->getAllMenu();
                $roleValue=array_filter(array_map(function($item)use($role){
                        if($item['site_id']==$role[0]['site_id']){
                            return explode(',',$item['site_menus']);
                        }
                },$site));
                $permission=[];
                foreach($roleValue as $v){
                    $permission=array_merge($permission,$v);
                }
                //权限修剪
                foreach($menus as $k=>$v){
                    if(isset($v['son']) && !empty($v['son'])){
                        foreach($v['son'] as $kk=>$vv){
                            if(isset($vv['son']) && !empty($vv['son'])){
                                foreach($vv['son'] as $kkk=>$vvv){
                                    if(!in_array($vvv['id'],$permission)){
                                        unset($menus[$k]['son'][$kk]['son'][$kkk]);
                                    }
                                }
                            }
                        }
                    }
                }
                $this->view->setVar('role', $role[0]);
                $this->view->setVar('site', $site);
                $this->view->setVar('menus', $menus);
            }
        }
    }

    /**
     * 删除
     */
    public function delAction()
    {

    }

    /**
     * 禁止与启用角色
     */
    public function banAction()
    {
        if($this->request->isAjax()){
            $this->AjaxHead();
            $roleId=intval($this->request->get('role_id'));
            $isEnable=intval($this->request->get('is_enable'));
            if($roleId){
                $ret=RoleService::getInstance()->updateAdminRole([
                    'set'=>'is_enable=:is_enable:',
                    'where'=>'role_id=:role_id:',
                    'bind'=>[
                        'is_enable'=>$isEnable,
                        'role_id'=>$roleId,
                    ]
                ]);
                return $this->response->setJsonContent(['status'=>$ret]);
            }
        }
    }

    /**
     * 角色权限选择
     */
    public function menuAction()
    {
        if($this->request->isAjax()){
            $this->AjaxHead();
            $roleId=intval($this->request->get('role_id'));
            $siteId=intval($this->request->get('site_id'));
            if($roleId && $siteId){
                //获取role数据
                $role=RoleService::getInstance()->getRoleOne([
                    'where'=>'role_id=:role_id:',
                    'bind'=>[
                        'role_id'=>$roleId
                    ]
                ]);

                //获取site数据
                $site=SiteService::getInstance()->getSiteOne([
                    'where'=>'site_id=:site_id:',
                    'bind'=>[
                        'site_id'=>$siteId
                    ]
                ]);
                if($role[0]['site_id']==$site['site_id']){
                    $roleMenu=array_intersect($site['site_menus'],$role[0]['menu_id']);
                }
                //获取menu数据
                $menu=AdminService::getInstance()->getAllMenu();
                $html='';
                foreach($menu as $k=>$v){
                    $html.='<h3 class="header smaller lighter blue">'.$v['menu_title'].'</h3><div class="row">';
                    foreach($v['son'] as $kk=>$vv){
                        $html.=' <div class="col-xs-12 col-sm-5"><div class="control-group">
                        <label class="control-label bolder blue">'.$vv['menu_title'].'</label>';
                        foreach($vv['son'] as $kkk=>$vvv){
                            if(in_array($vvv['id'],$site['site_menus'])){
                                if(isset($roleMenu)){
                                    if(in_array($vvv['id'],$roleMenu)){
                                        $html .= '<div class="checkbox"><label><input name="menu_id[]" value="' . $vvv['id'] . '" class="ace ace-checkbox-2" type="checkbox" checked/>
                                                    <span class="lbl">' . $vvv['menu_title'] . '</span>
                                                    </label></div>';
                                    }else{
                                        $html .= '<div class="checkbox"><label><input name="menu_id[]" value="' . $vvv['id'] . '" class="ace ace-checkbox-2" type="checkbox" />
                                                <span class="lbl">' . $vvv['menu_title'] . '</span>
                                                </label></div>';
                                    }
                                }else {
                                    $html .= '<div class="checkbox">
                                            <label><input name="menu_id[]" value="' . $vvv['id'] . '" class="ace ace-checkbox-2" type="checkbox" />
                                            <span class="lbl">' . $vvv['menu_title'] . '</span>
                                            </label></div>';
                                }
                            }
                        }
                        $html.='</div></div>';
                    }
                    $html.='<div/></div>';
                }
                return $this->response->setJsonContent(['ret'=>$html]);
            }
        }
    }

    /**
     *  站点权限选择
     */
    public function sitemenuAction()
    {
        $siteId=intval($this->request->get('site_id'));
        if($siteId) {
            //获取site数据
            $site = SiteService::getInstance()->getSiteOne([
                'where' => 'site_id=:site_id:',
                'bind' => [
                    'site_id' => $siteId
                ]
            ]);
            if(is_array($site) && !empty($site)){
                //获取menu数据
                $menu=AdminService::getInstance()->getAllMenu();
                //组装权限选择
                $html='';
                foreach($menu as $k=>$v){
                    $html.='<h3 class="header smaller lighter blue">'.$v['menu_title'].'</h3><div class="row">';
                    foreach($v['son'] as $kk=>$vv){
                        $html.=' <div class="col-xs-12 col-sm-5"><div class="control-group">
                        <label class="control-label bolder blue">'.$vv['menu_title'].'</label>';
                        foreach($vv['son'] as $kkk=>$vvv){
                            if(in_array($vvv['id'],$site['site_menus'])){
                                $html .= '<div class="checkbox"><label><input name="menu_id[]" value="' . $vvv['id'] . '" class="ace ace-checkbox-2" type="checkbox" />
                                    <span class="lbl">' . $vvv['menu_title'] . '</span>
                                    </label></div>';
                            }
                        }
                        $html.='</div></div>';
                    }
                    $html.='<div/></div>';
                }
                $this->AjaxHead();
                return $this->response->setJsonContent([
                    'status'=>true,
                    'data'=>$html,
                ]);
            }
        }
    }
}