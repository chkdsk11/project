<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/5 0005
 * Time: 上午 9:23
 */

namespace Shop\Admin\Controllers;
use Phalcon\Mvc\Controller;
use Shop\Services\AdminService;
use Shop\Services\AdminRoleService;
use Shop\Models\CacheKey;

class AdminController extends ControllerBase
{

    /**
     * [$code description]
     * @var array
     */
    public $code = array('code'=>201,'msg'=>'失败','data'=>'');

    /**
     * [initialize 相当于构造方法,如需要用，必须先调用父级的]
     * @return [type] [description]
     */
    public function initialize()
    {
        parent::initialize();
    }

    /**
     * [indexAction 管理员列表]
     * @return [type] [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function indexAction()
    {
        $list = $map = array();
        $page = '';
        $pageParam = array();
        $pageParam['page'] = $this->getParam('page','int',1);
        $pageParam['url'] = $this->automaticGetUrl();
        $res = AdminService::getInstance()->getPage($map,$pageParam);
        if($res){
            $list = $res['list'];
            $page = $res['page'];
        }
        $roleList = AdminRoleService::getInstance()->getAll();
        $this->view->setVar('list',$list);
        $this->view->setVar('page',$page);
        $this->view->setVar('roleList',$roleList);
    }

    /**
     * [addAction description]
     * @author: edgeto/qiuqiuyuan
     */
    public function addAction()
    {
        if($this->request->isPost()){
            $post = $this->request->getPost();
            $jump_url = "/admin/index";
            $res = AdminService::getInstance()->add($post);
            if(empty($res)){
                $this->code['msg'] = '失败';
                $this->code['data'] = AdminService::getInstance()->error;
            }else{
                $this->code['code'] = 200;
                $this->code['msg'] = '成功';
                $this->code['data'] = $jump_url;
            }
            return $this->response->setJsonContent($this->code);
        }
        $roleList = AdminRoleService::getInstance()->getAll();
        $this->view->setVar('roleList',$roleList);
    }

    /**
     * [editAction 修改]
     * @return [type] [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function editAction()
    {
        $jump_url = "/admin/index";
        if($this->request->isPost()){
            $post = $this->request->getPost();
            $res = AdminService::getInstance()->edit($post);
            if(empty($res)){
                $this->code['msg'] = '失败';
                $this->code['data'] = AdminService::getInstance()->error;
            }else{
                $this->code['code'] = 200;
                $this->code['msg'] = '成功';
                $this->code['data'] = $jump_url;
            }
            return $this->response->setJsonContent($this->code);
        }else{
            $id = intval($this->request->get('id','trim',0));
            if(empty($id)){
                return $this->success( '参数不完整或者参数错误！',$jump_url,'error',3);
            }
            $data = AdminService::getInstance()->getById($id);
            if(empty($data)){
                $error = AdminRoleService::getInstance()->error;
                return $this->success($error,$jump_url,'error',3);
            }
            $roleList = AdminRoleService::getInstance()->getAll();
            $this->view->setVar('data',$data);
            $this->view->setVar('roleList',$roleList);
        }
    }

    /**
     * [passwordAction 编辑管理员密码]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function passwordAction($id = 0)
    {
        $jump_url = "/admin/index";
        if($this->request->isPost()){
            $post = $this->request->getPost();
            $res = AdminService::getInstance()->edit($post);
            if(empty($res)){
                $this->code['msg'] = '失败';
                $this->code['data'] = AdminService::getInstance()->error;
            }else{
                $this->code['code'] = 200;
                $this->code['msg'] = '成功';
                $this->code['data'] = $jump_url;
            }
            return $this->response->setJsonContent($this->code);
        }else{
            $id = intval($this->request->get('id','trim',0));
            if(empty($id)){
                return $this->success( '参数不完整或者参数错误！',$jump_url,'error',3);
            }
            $data = AdminService::getInstance()->getById($id);
            if(empty($data)){
                $error = AdminRoleService::getInstance()->error;
                return $this->success($error,$jump_url,'error',3);
            }
            $roleList = AdminRoleService::getInstance()->getAll();
            $this->view->setVar('data',$data);
        }
    }

    /**
     * [LoginEdPasswordAction 编辑当前登录管理员密码]
     */
    public function LoginEdPasswordAction()
    {
        $jump_url = "/admin/index";
        if($this->request->isPost()){
            $post = $this->request->getPost();
            $res = AdminService::getInstance()->edit($post);
            if(empty($res)){
                $this->code['msg'] = '失败';
                $this->code['data'] = AdminService::getInstance()->error;
            }else{
                $this->code['code'] = 200;
                $this->code['msg'] = '成功';
                $this->code['data'] = $jump_url;
            }
            return $this->response->setJsonContent($this->code);
        }else{
            $id = $this->session->get('admin_id');
            if(empty($id)){
                return $this->success( '参数不完整或者参数错误！',$jump_url,'error',3);
            }
            $data = AdminService::getInstance()->getById($id);
            if(empty($data)){
                $error = AdminRoleService::getInstance()->error;
                return $this->success($error,$jump_url,'error',3);
            }
            $roleList = AdminRoleService::getInstance()->getAll();
            $this->view->setVar('data',$data);
            $this->view->pick('admin/password');
        }
    }

    /**
     *  登录验证
     *
     */
    public function loginAction()
    {
        //如果已经登录，跳转到菜单页
        if($this->session->get('is_login')){
            $this->response->redirect('http://'.$this->config->domain->admin);
        }
        //登录
        if($this->request->isPost()){
            $chkCode=$this->session->get('code');
            $code=strtolower($this->request->getPost('code','trim'));
            //表单来源与验证码校验
            if($chkCode==$code){
                $param=[];
                $param['admin_account']=$this->request->getPost('username','trim');
                $param['admin_password']=$this->request->getPost('password','trim');
                $adminService=AdminService::getInstance();
                //用户名与密码校验
                $ret=$adminService->loginAuth($param);
                //用户锁定
                if($ret==='locking'){
                    $this->view->setVar('error','该用户已锁定,登录失败');
                }
                if($ret==='no_user'){
                    $this->view->setVar('error','用户不存在');
                }
                //失败次数上限
                if($ret==='overtop'){
                    $this->view->setVar('error','登录失败次数已达上限，将账号锁定2小时，请联系管理员');
                }
                if(is_array($ret) && !empty($ret)) {
                    //校验通过
                    if ($ret['auth'] === true) {
                        $menu=$adminService->getAdminPermission($ret['account']['id']);
                        $this->session->set('username',$ret['account']['admin_account']);
                        $this->session->set('is_login',1);
                        $this->session->set('user_id',$ret['account']['id']);
                        $this->session->set('menu',$menu);
                        $this->session->set('site_id',$ret['account']['site_id']);
                        $this->response->redirect('http://'.$this->config->domain->admin);
                    }
                    //校验不通过
                    if ($ret['auth']===false) {
                        $this->view->setVar('error', "密码错误");
                    }
                }
            }else{
                $this->view->setVar('error','验证码错误');
            }
        }
    }

    /**
     * 退出登录
     */
    public function logoutAction()
    {
        $this->view->disable();
        $this->response->setContentType('application/json', 'UTF-8');
        $adminAccount=$this->request->getPost('username','trim');
        if($this->session->get('is_login') && ($this->session->get('username')==$adminAccount)) {
            $cookieName=$this->session->getName();
            $this->cookies->set($cookieName,'',time()-3600,'/',false,$this->config->cookie->domain);
            $this->cookies->delete($cookieName);
            $this->session->destroy();
        }
        return $this->response->setJsonContent([
            'status'=>'success'
        ]);
    }

    /**
     * [clearRedisAction 清除指定前缀的redis]
     * @return [type] [description]
     */
    public function clearRedisAction()
    {
        if($this->request->isPost()){
            $all = false;
            $index_res = $this->clearRedisIndex();
            $keys  = $this->request->getPost('keys','trim','');
            if($keys){
                $redis_keys = $this->cache->getAllRedisKeys();
                $keys = explode("\r\n",$keys);
                if($keys){
                    foreach ($keys as $key => $value) {
                        if($value == '*'){
                            $this->code['msg'] = '后台登录key不能删除，删除了指定前缀的key';
                            $all = true;
                        }else{
                            // 全匹配
                            $delete_res = $this->cache->delete($value);
                            if($redis_keys && empty($delete_res)){
                                foreach ($redis_keys as $_key => $_value) {
                                    $cache_value = $this->cache->smembersSet($_value);
                                    if(!empty($cache_value)){
                                        foreach ($cache_value as $cache_value_key => $_cache_value_value) {
                                                $pattern = "/^{$value}/";
                                                $res = preg_match($pattern, $_cache_value_value);
                                                if($res){
                                                    $this->cache->sremSet($_value,$_cache_value_value);
                                                    $this->cache->delete($_cache_value_value);
                                                }
                                        }
                                        // 再拿一次数量，没有就全清掉
                                        $cache_count = $this->cache->scardSet($_value);
                                        if(empty($cache_count)){
                                            $this->cache->sremSet(CacheKey::SOA_ALL_REDIS_KEYS_ARR,$_value);
                                        }
                                    }
                                }
                            }
                        }
                    }
                    if(!$all){
                        $this->code['code'] = 200;
                        $this->code['msg'] = '处理成功！';
                    }
                }else{
                    $this->code['msg'] = '一行一个key';
                }
            }else{
                // $this->code['msg'] = '没有指定的keys';
                $this->code['code'] = 200;
                $this->code['msg'] = '处理成功！';
            }
            $this->view->disable();
            return $this->response->setJsonContent($this->code);
        }
        // redies 索引分类
        $redisIndexKeys = $this->cache->prefixCategoryArr();
        $this->view->setVar('redisIndexKeys',$redisIndexKeys);
        // var_dump($redisIndexKeys);exit;
    }

    /**
     * [clearRedisIndex 清除指定前缀的redis key]
     * @return [type] [description]
     */
    public function clearRedisIndex()
    {
        $index = $this->request->getPost('index','trim','');
        if($index){
            foreach ($index as $key => $value) {
                if(strpos($value,'pc_')!==false){
                    if($value == 'pc_allAd'){
                        $this->cache->delete($this->cache->keys($value . '*'));
                    }else{
                        $this->cache->delete($value);
                    }
                }else {
                    $redis_key = $value . "_index__";
                    $redis_key_value = $this->cache->smembersSet($redis_key);
                    if (!empty($redis_key_value)) {
                        foreach ($redis_key_value as $_key => $_value) {
                            // 删除集合
                            $res = $this->cache->sremSet($redis_key, $_value);
                            // 删除redis
                            $this->cache->delete($_value);
                        }
                    }
                }
            }
        }
    }
}