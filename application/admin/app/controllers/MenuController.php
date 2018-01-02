<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/9 0009
 * Time: 上午 11:37
 */

namespace Shop\Admin\Controllers;

use Shop\Admin\Controllers\ControllerBase;
use Shop\Services\AdminService;


class MenuController extends ControllerBase
{
    /**
     *  相当于构造方法
     */
    public function initialize()
    {
        parent::initialize();
    }

    public function indexAction()
    {

    }

    /**
     * 功能权限列表
     */
    public function listAction()
    {

    }

    public function allAction()
    {
        if($this->request->isAjax()) {
            $this->AjaxHead();
            $adminService = AdminService::getInstance();
            $menu = $adminService->getAllMenu();
            return $this->response->setJsonContent($menu);
        }
    }

    /**
     * 功能权限添加
     */
    public function addAction()
    {
        if($this->request->isPost()){
            $menuService=AdminService::getInstance();
            $menu=[];
            $menu['menu_level']=intval($this->request->getPost('menu_level'));
            $menu['menu_title']=$this->request->getPost('menu_title','trim');
            $ret=false;
            if(!empty($menu['menu_level']) && !empty($menu['menu_title'])){
                switch($menu['menu_level']){
                    case 1:
                        $menu['parent_id']=0;
                        $menu['has_child']=1;
                        $ret=$menuService->addAdmin($menu);
                        break;
                    case 2:
                        $menu['parent_id']=intval($this->request->getPost('parent_id','trim'));
                        $menu['menu_path']=$this->request->getPost('menu_path','trim');
                        $menu['has_child']=1;
                        $ret=$menuService->addAdmin($menu);
                        break;
                    case 3:
                        $menu['parent_id']=intval($this->request->getPost('parent_id','trim'));
                        $menu['menu_path']=$this->request->getPost('menu_path','trim');
                        $menu['is_show_left']=intval($this->request->getPost('is_show_left'));
                        $menu['is_show_top']=intval($this->request->getPost('is_show_top'));
                        $menu['has_child']=0;
                        $ret=$menuService->addAdmin($menu);
                        break;
                }
                if($ret===true){
                    $this->view->disable();
                    echo '<script>alert("添加成功");location.href="/menu/list";</script>';
                }elseif($ret===false){
                    $this->view->disable();
                    echo '<script>alert("添加失败");history.go(-1);</script>';
                }elseif($ret==='repeat'){
                    $this->view->disable();
                    echo '<script>alert("权限名称不能重复");history.go(-1);</script>';
                }
            }else{
                $this->view->disable();
                echo '<script>alert("数据不能为空");history.go(-1);</script>';
            }
        }elseif($this->request->isGet()) {
            $type = $this->request->get('type', 'trim');
            switch ($type) {
                case 'module':
                    $this->setTitle('添加功能模块');
                    $this->view->pick("menu/module");
                    break;
                case 'menu':
                    $this->setTitle('添加子模块');
                    $menu=[];
                    $menu['menu_level']=intval($this->request->get('menu_level'));
                    $menu['parent_id']=intval($this->request->get('parent_id'));
                    if(!empty($menu['menu_level']) && !empty($menu['parent_id'])){
                        $menu['menu_level']+=1;
                        $this->view->setVar('menus',$menu);
                        $this->view->pick('menu/child');
                    }
                    break;
            }
        }elseif($this->request->isAjax()){

        }
    }

    /**
     * 功能权限删除
     */
    public function delAction()
    {
        $this->AjaxHead();
        $id=intval($this->request->get('id'));
        if($id){
            $ret=AdminService::getInstance()->delMenus([
                'bind'=>[
                    'id'=>$id
                ],
                'where'=>'id=:id:'
            ]);

            return $this->response->setJsonContent(['status'=>$ret]);
        }
    }

    /**
     * 功能权限编辑
     */
    public function editAction()
    {
        $this->setTitle('功能编辑');
        $adminService = AdminService::getInstance();
        if($this->request->isPost()){   //menus更新
            $menu=[];
            $menu['menu_title']=$this->request->getPost('menu_title','trim');
            $menu['id']=intval($this->request->getPost('id'));
            $menu['parent_id']=intval($this->request->getPost('parent_id'));
            $menu['menu_level']=intval($this->request->getPost('menu_level'));
            $menu['is_show_left']=intval($this->request->getPost('is_show_left'));
            $menu['is_show_top']=intval($this->request->getPost('is_show_top'));
            $menu['menu_path']=$this->request->getPost('menu_path','trim');
            $menu['has_child']=intval($this->request->getPost('has_child'));
            $ret=$adminService->updateMenus([
                'set'=>'menu_title=:menu_title:,parent_id=:parent_id:,menu_level=:menu_level:,menu_path=:menu_path:,has_child=:has_child:,is_show_left=:is_show_left:,is_show_top=:is_show_top:',
                'bind'=>$menu,
                'where'=>'id=:id:'
            ]);
            if($ret){
                $this->view->disable();
                echo '<script type="text/javascript">alert("更改成功!!!");location.href="/menu/list";</script>';
            }else{
                $this->view->disable();
                echo '<script type="text/javascript">alert("更改失败!!!");location.href="/menu/edit?id='.$menu['id'].'";</script>';
            }
        }else {
            $id = intval($this->request->get('id'));
            if (!empty($id)) {
                $menuValue = $adminService->getMenuOne([
                    'bind' => [
                        'id' => $id
                    ],
                    'where' => 'id=:id:'
                ]);
                if (is_array($menuValue) && !empty($menuValue)) {
                    $this->view->setVar('menus', $menuValue);
                    switch ($menuValue['menu_level']) {
                        case 1:
                            $this->view->pick('menu/limb');
                            break;
                        case 2:
                        case 3:
                            //得到父功能
                            $level = $menuValue['menu_level'] - 1;
                            $parentValue = $adminService->getMenus([
                                'where' => 'menu_level=:menu_level:',
                                'bind' => [
                                    'menu_level' => $level
                                ]
                            ]);
                            $this->view->setVar('parent', $parentValue);
                            $this->view->pick('menu/leaf');
                            break;
                    }
                }
            }
        }
    }
}