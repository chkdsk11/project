<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/9/7 0007
 * Time: 上午 10:41
 */

namespace Shop\Admin\Controllers;
use Shop\Admin\Controllers\ControllerBase;
use Shop\Datas\BaiyangUserData;
use Shop\Datas\BaseData;
use Shop\Services\BaseService;
use Shop\Services\UserService;
use Shop\Services\RoleService;

class UserController extends ControllerBase
{
    /***
     *  相当于构造方法
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * 用户列表
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
            'home_page' => '/user/list',
        ];
        //print_r($param);die;
        $userService=UserService::getInstance();
        $userValues=$userService->getAdminUserList($param);
        if(is_array($userValues) && $userValues['status']) {
            $this->view->setVar('user', $userValues['list']);
            $this->view->setVar('page', $userValues['page']);
            $this->view->setVar('site',$userValues['site']);
            $this->view->setVar('role',$userValues['role']);
        }
    }


    /**
     * @desc 用户修改密码
     * @return \Phalcon\Http\Response|\Phalcon\Http\ResponseInterface
     */
    public function changepswAction()
    {
        if($this->request->isPost() && $this->request->isAjax()){
            $this->AjaxHead();
            $admin=[];
            $roleUser=[];
            $admin['admin_account']=$this->request->getPost('username','trim');
            $admin['admin_password']=$this->request->getPost('password','trim');
            $admin['is_lock']=intval($this->request->getPost('is_lock'));
            $admin['id']=intval($this->request->getPost('id'));
            $roleUser['role_id']=intval($this->request->getPost('role_id'));
            $roleUser['admin_id']=intval($this->request->getPost('id'));
            $set=$admin['admin_password']?'admin_password=:admin_password:,':'';
            $ret=UserService::getInstance()->updateAdmin([
                'set'=>$set.'admin_account=:admin_account:,is_lock=:is_lock:,site_id=:site_id:',
                'bind'=>$admin,
                'where'=>'id=:id:'
            ],[
                'set'=>'role_id=:role_id:',
                'bind'=>$roleUser,
                'where'=>'admin_id=:admin_id:'
            ]);
            if($ret){
                if($this->session->get('is_login')) {
                    $cookieName=$this->session->getName();
                    $this->cookies->set($cookieName,'',time()-3600,'/',false,$this->config->cookie->domain);
                    $this->cookies->delete($cookieName);
                    $this->session->destroy();
                }
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('用户修改密码成功', '/admin/login', ''));
            }else{
                return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新失败', '/user/edit?id='.$admin['id'], 'error'));
            }
        }else {
            $this->setTitle('用户编辑');
            $id = intval($this->request->get('id'));
            if (empty($id)) {
                $this->response->redirect('http://' . $this->config->domain->admin . '/user/list');
            }
            $userService=UserService::getInstance();
            $adminUser = $userService->getAdminOne([
                'column' => '*',
                'where' => 'id=:id:',
                'bind' => [
                    'id' => $id
                ]
            ]);
            $roleUser=$userService->getRoleUser([
                'where'=>'admin_id=:admin_id:',
                'bind'=>[
                    'admin_id'=>$adminUser['id']
                ]
            ]);
            $roles = RoleService::getInstance()->getAllRole();
            $this->view->setVar('roleuser',$roleUser);
            $this->view->setVar('user', $adminUser);
            $this->view->setVar('role', $roles);
            $this->view->pick("user/edit");
        }
    }

    /**
     * 运营后台用户编辑
     */
    public function editAction()
    {

        if($this->request->isPost() && $this->request->isAjax()){
            $this->AjaxHead();
            $admin=[];
            $roleUser=[];
            $admin['admin_account']=$this->request->getPost('username','trim');
            $admin['admin_password']=$this->request->getPost('password','trim');
            $admin['is_lock']=intval($this->request->getPost('is_lock'));
            $admin['id']=intval($this->request->getPost('id'));
            $roleUser['role_id']=intval($this->request->getPost('role_id'));
            $roleUser['admin_id']=intval($this->request->getPost('id'));
            $set=$admin['admin_password']?'admin_password=:admin_password:,':'';
            $ret=UserService::getInstance()->updateAdmin([
               'set'=>$set.'admin_account=:admin_account:,is_lock=:is_lock:,site_id=:site_id:',
                'bind'=>$admin,
                'where'=>'id=:id:'
            ],[
                'set'=>'role_id=:role_id:',
                'bind'=>$roleUser,
                'where'=>'admin_id=:admin_id:'
            ]);
           if($ret){
               return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新成功', '/user/list', ''));
           }else{
               return $this->response->setJsonContent(BaseService::getInstance()->arrayData('更新失败', '/user/edit?id='.$admin['id'], 'error'));
           }
        }else {
            $this->setTitle('用户编辑');
            $id = intval($this->request->get('id'));
            if (empty($id)) {
                $this->response->redirect('http://' . $this->config->domain->admin . '/user/list');
            }
            $userService=UserService::getInstance();
            $adminUser = $userService->getAdminOne([
                'column' => '*',
                'where' => 'id=:id:',
                'bind' => [
                    'id' => $id
                ]
            ]);
            $roleUser=$userService->getRoleUser([
                'where'=>'admin_id=:admin_id:',
                'bind'=>[
                    'admin_id'=>$adminUser['id']
                ]
            ]);
            $roles = RoleService::getInstance()->getAllRole();
            $this->view->setVar('roleuser',$roleUser);
            $this->view->setVar('user', $adminUser);
            $this->view->setVar('role', $roles);
        }
    }

    /**
     * 用户添加
     */
    public function addAction()
    {
        $this->setTitle('用户添加');
        if($this->request->isPost() && $this->request->isAjax()) {      //添加用户post
            $this->AjaxHead();
            $type = $this->request->getPost('type','trim');
            if($type == 1){
                $adminAccount=$this->request->getPost('username','trim');
                if($adminAccount) {
                    $admin = UserService::getInstance()->getAdminOne([
                        'column'=>'*',
                        'bind'=>[
                            'admin_account'=>$adminAccount,
                        ],
                        'where'=>'admin_account=:admin_account:'
                    ]);
                    if(is_array($admin) && !empty($admin)){
                        return $this->response->setJsonContent(['status'=>true]);
                    }
                }
            }else{
                $admin=[];
                $admin['admin_account'] = $this->request->getPost('username','trim');
                $admin['admin_password'] = md5($this->request->getPost('password','trim'));
                $admin['role_id'] = intval($this->request->getPost('role_id','trim'));
                $admin['is_lock'] = intval($this->request->getPost('is_lock'));
                $roleUser['role_id'] = intval($this->request->getPost('role_id'));
                if(empty($admin['admin_account'])){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData('用户账号不能为空', '', 'error'));
                }else if(empty($admin['admin_password'])){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData('密码不能为空', '', 'error'));
                }else if(empty($admin['role_id'])){
                    return $this->response->setJsonContent(BaseService::getInstance()->arrayData('角色不能为空', '', 'error'));
                }else{
                    $ret=UserService::getInstance()->addAdminUser($admin,$roleUser);
                    if($ret==='repeat'){
                        return $this->response->setJsonContent(BaseService::getInstance()->arrayData('用户名不能重复', '/user/add', 'error'));
                    }elseif($ret===true){
                        return $this->response->setJsonContent(BaseService::getInstance()->arrayData('用户添加成功', '/user/list', ''));
                    }elseif($ret===false){
                        return $this->response->setJsonContent(BaseService::getInstance()->arrayData('用户添加失败', '/user/add', 'error'));
                    }
                }
            }
        } else {    //用户添加
            $roles = RoleService::getInstance()->getAllRole();
            $this->view->setVar('role', $roles);
        }
    }

    /**
     * @desc ajax通过号码判断用户是否存在
     * @return bool
     * @author 邓永军
     */
    public function getUserIdByPhoneAction()
    {
        $this->view->disable();
        if($this->request->isAjax() && $this->request->isPost()){
            $phone = $this->request->getPost('phone');
            $user_id = BaiyangUserData::getInstance()->findUserIdByPhone($phone);
            if($user_id == false){
                return 'no';
            }
            return 'yes';
        }else{
            return 'no';
        }

    }
}