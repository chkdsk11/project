<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/9 0009
 * Time: 上午 11:12
 * 后台用户服务类
 */

namespace Shop\Services;

use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyAdminData;
use Shop\Datas\BaiyangRoleData;
use Shop\Datas\SiteData;
use Shop\Models\CacheKey;
use Shop\Datas\BaiyangAdminData;
use Shop\Datas\BaiyangAdminRoleData;
use Shop\Datas\BaiyangAdminResourceData;

class AdminService extends BaseService
{
    //必须声明此静态属性，单例模式下防止实例对象覆盖
    protected static $instance=null;

    /**
     * [$error 错误提示]
     * @var string
     */
    public $error = '';

    /**
     * [$error 错误字段]
     * @var string
     */
    public $field = '';

    /**
     * [getPage 分页]
     * @param  array  $map       [description]
     * @param  array  $pageParam [description]
     * @return [type]            [description]
     */
    public function getPage($map = array(),$pageParam = array())
    {
        $where = $this->makeMap($map);
        $conditions = $this->makeConditions($map);
        $res = BaiyangAdminData::getInstance()->getPage($where,$conditions,$pageParam);
        if(empty($res)){
            $this->error = BaiyangAdminData::getInstance()->error;
            return false;
        }
        return $res;
    }

    /**
     * [makeMap 条件集合]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function makeMap($map = array())
    {
        $where = ' 1 = 1 ';
        if($map){
            foreach ($map as $key => $value) {
                if(!is_array($value)){
                    $where .= $key ." = :{$key}: AND ";
                }
            }
            $where = rtrim($where,"AND ");
        }
        return $where;
    }

    /**
     * [makeConditions description]
     * @param  array  $map [description]
     * @return [type]      [description]
     */
    public function makeConditions($map = array())
    {
        $conditions = array();
        if($map){
            foreach ($map as $key => $value) {
                if(!is_array($value)){
                    $conditions[$key] = $value;
                }
            }
        }
        return $conditions;
    }

    /**
     * [add description]
     * @param array $data [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function add($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminData::getInstance()->add($data);
        if(empty($res)){
            $this->error = BaiyangAdminData::getInstance()->error;
            return false;
        }
        return true;
    }

    /**
     * [edit description]
     * @param  array  $data [description]
     * @return [type]       [description]
     * @author: edgeto/qiuqiuyuan
     */
    public function edit($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminData::getInstance()->edit($data);
        if(empty($res)){
            $this->error = BaiyangAdminData::getInstance()->error;
            return false;
        }
        return true;
    }

    /**
     * [getById description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function getById($id = 0)
    {
        if(empty($id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminData::getInstance()->getById($id);
        if(empty($res)){
            $this->error = BaiyangAdminData::getInstance()->error;
            return false;
        }
        return $res;
    }

    /**
     * 登录验证
     * @param $param=['admin_account'=>string,'admin_password'=>string]
     * @return string|[]
     *
     */
    public function loginAuth($param)
    {
        if(empty($param['code'])){
            $this->error = "验证码不能为空";
            $this->field = "code";
            return false;
        }
        $chkCode = $this->session->get('code');
        $code = strtolower($this->request->getPost('code','trim'));
        if($chkCode != $code){
            $this->error = "验证码不正确";
            $this->field = "code";
            return false;
        }
        if(empty($param['admin_account'])){
            $this->error = "管理员用户名不能为空";
            $this->field = "admin_account";
            return false;
        }
        if(empty($param['admin_password'])){
            $this->error = "管理员密码不能为空";
            $this->field = "admin_password";
            return false;
        }
        //验证失败登录次数,超过五次禁止二小时
        if($this->chkLoginCounts($param['admin_account'])){
            $this->error = "登录失败次数已达上限，将账号锁定2小时，请联系管理员";
            $this->field = "admin_account";
            return false;
        }
        $admin_account = addslashes(trim($param['admin_account']));
        $accountData = BaiyangAdminData::getInstance();
        $account = $accountData->getByAdminAccount($admin_account);
        if(empty($account)){
            $this->error = $accountData->error;
            $this->field = "admin_account";
            return false;
        }
        if($account['is_lock'] == 1){
            $this->error = '该用户已锁定,登录失败';
            $this->field = "admin_account";
            return false;
        }
        $confirmPassword=$this->adminHash($param['admin_password']);
        if($account['admin_password'] == $confirmPassword){
            $access = $this->getAccess($account);
            if(empty($access)){
                $this->field = "admin_account";
                return false;
            }
            $this->autoLogin($account);
            return true;
        }else{
            // 验证失败加入计数器
            $ret = (int)$this->failedLoginCounts($param['admin_account']);
            $count = CacheKey::FAILED_LOGIN_TOTAL - $ret;
            $this->error = '管理员密码不正确';
            $this->field = "admin_password";
            return false;
        }
    }

    /**
     * [getAccess 权限判断,如果通过则把角色id存放在session里面]
     * @param  array  $account [description]
     * @return [type]          [description]
     */
    public function getAccess($account = array())
    {
        if(empty($account['role_id'])){
            $this->error = '管理员还没有分配角色，请联系管理员';
            return false;
        }
        // 超级管理员
        $is_super = 0;
        $role_id = $account['role_id'];
        $admin_id = $account['id'];
        $role_id_info = BaiyangAdminRoleData::getInstance()->getOneCahce($role_id);
        if(empty($role_id_info)){
            $this->error = "管理员所在角色不存在，请联系管理员";
            return false;
        }
        if(empty($role_id_info['is_enable'])){
            $this->error = "管理员所在角色被禁用，请联系管理员";
            return false;
        }
        $is_super = $role_id_info['is_super'];
        // 只有统一后台的
        $resource_arr = BaiyangAdminResourceData::getInstance()->getDefaultAll();
        if(empty($resource_arr)){
            $this->error = "系统中没有权限资源，请联系管理员";
            return false;
        }
        if($is_super){
            // $this->session->set('role_id',$role_id);
            // 这样不够实时
            // $this->session->set('is_super',$is_super);
            return true;
        }
        $role_name = $role_id_info['role_name'];
        $rules = $role_id_info['rules'];
        if(empty($rules)){
            $this->error = "管理员所在角色《{$role_name}》还没有分配权限，请联系管理员";
            return false;
        }
        // 用isset 不用in_array()
        $rules = array_flip(explode(',',$rules));
        // 第三级展示菜单
        $has_level_three_show = false;
        foreach ($resource_arr as $key => $value) {
            if(isset($rules[$value['id']])){
                if($value['level'] == 3 && $value['show_nav'] == 1 && $value['route']){
                    $has_level_three_show = true;
                }
            }
        }
        if(empty($has_level_three_show)){
            $this->error = "管理员所在角色《{$role_name}》没有可显示的菜单，请联系管理员";
            return false;
        }
        // $this->session->set('role_id',$role_id);
        // 这样不够实时
        // $this->session->set('is_super',$is_super);
        return true;
    }

    /**
     * [getDefaultUrl 默认跳转链接]
     * @param  integer $role_id  [description]
     * @return [type]            [description]
     */
    public function getDefaultUrl($role_id = 0)
    {
        $this->field = "admin_account";
        $this->error = '权限角色没有可显示的菜单';
        $jump_url = '';
        $resource_arr = BaiyangAdminResourceData::getInstance()->getDefaultAll();
        // 根据id排序
        $ids = array();
        foreach ($resource_arr as $key => $value) {
            $ids[] = $value['id'];
        }
        array_multisort($resource_arr,SORT_ASC,$ids);
        if($role_id && $resource_arr){
            $role_id_info = BaiyangAdminRoleData::getInstance()->getOneCahce($role_id);
            if($role_id_info){
                $rules = $role_id_info['rules'];
                $is_super = $role_id_info['is_super'];
                // 用isset 不用in_array()
                $rules = array_flip(explode(',',$rules));
                if($rules){
                    foreach ($resource_arr as $key => $value) {
                        if($is_super){
                            // 超管第一个显示的菜单
                            if($value['level'] == 3 && $value['show_nav'] == 1){
                                $jump_url = $value['route'];
                                break;
                            }
                        }else{
                            if(isset($rules[$value['id']])){
                                if($value['level'] == 3 && $value['show_nav'] == 1 && $value['route']){
                                    $jump_url = $value['route'];
                                    break;
                                }
                            }
                        }
                    }
                }
            }else{
                $this->error = '权限角色没有可显示的菜单';
                return false;
            }
        }
        // 默认链接是PC的
        if(!empty($this->config->pc_admin_url[$this->config->environment])){
            $pc_admin_url_key = CacheKey::PC_ADMIN_URL;
            $pc_admin_url = $this->config->pc_admin_url[$this->config->environment];
            $route_url = str_ireplace($pc_admin_url_key,$pc_admin_url,$jump_url);
            $jump_url = $route_url;
        }
        return $jump_url;
    }

    /**
     * [autoLogin 记录session]
     * @param  array  $account [description]
     * @return [type]          [description]
     */
    public function autoLogin($account = array())
    {
        $this->session->set('admin_id',$account['id']);
        $this->session->set('admin_account',$account['admin_account']);
        $this->session->set('role_id',$account['role_id']);
        $this->session->set('admin',$account);
        $this->session->set('is_login',1);
        // 兼容旧的
        $this->session->set('username',$account['admin_account']);
        $this->session->set('user_id',$account['id']);
        // 把登录的id放进redis
        $this->cache->selectDb(1);
        $data = $this->cache->getValue(CacheKey::ADMIN_IDS);
        $data[] = $this->session->getId() . "//" .$account['id'];
        $data = $this->cache->setValue(CacheKey::ADMIN_IDS,$data);
    }

    /**
     * [checkIsLogin description]
     * @return [type] [description]
     */
    public function checkIsLogin()
    {
        $adminId = $this->session->get('admin_id');
        if(!$adminId){
            return false;
        }
        // 把登录的id放进redis
        $this->cache->selectDb(1);
        $data = $this->cache->getValue(CacheKey::ADMIN_IDS);
        $data[] = $this->session->getId() . "//" .$adminId;
        $data = $this->cache->setValue(CacheKey::ADMIN_IDS,$data);
        return true;
    }

    /**
     * [logout 退出登录]
     * @return [type] [description]
     */
    public function logout()
    {
        $adminId = $this->session->get('admin_id');
        $this->session->remove("admin_id");
        $this->session->remove("admin_account");
        $this->session->remove("is_login");
        $this->session->remove("rules");
        $this->session->remove("menu");
        $this->session->destroy();
        // 从redis里面删掉
        $this->cache->selectDb(1);
        $data = $this->cache->getValue(CacheKey::ADMIN_IDS);
        $session_admin_id = $this->session->getId() . "//" .$adminId;
        if(!empty($data)){
            foreach ($data as $key => $value) {
                if($value == $session_admin_id){
                    unset($data[$key]);
                }
            }
        }
        $data = $this->cache->setValue(CacheKey::ADMIN_IDS,$data);
    }

    /**
     * 失败登录次数
     * @adminAccount=string
     * @return bool
     */
    protected function chkLoginCounts($adminAccount)
    {
        //切换到redis 1库
        $this->cache->selectDb(1);
        $counts=(int)$this->cache->getValue(CacheKey::AUTH_KEY.$adminAccount);
        if((int)$counts >= CacheKey::FAILED_LOGIN_TOTAL){
            return true;
        }
    }

    /**
     * @param $adminAccount=string
     * 密码验证失败添加失败次数，五次后2小时内禁止用户登录
     * @return int
     */
    protected function failedLoginCounts($adminAccount)
    {
        //切换到redis 1库
        $this->cache->selectDb(1);
        //默认增长1
        $ret=$this->cache->incre(CacheKey::AUTH_KEY.$adminAccount);
        $this->cache->setKeyExpireTime(CacheKey::AUTH_KEY.$adminAccount,CacheKey::LOCK_TIME);
        return $ret;
    }

    /**
     * @param $value=string
     * @return string
     */
    protected function adminHash($value)
    {
        return md5($value);
    }

    /**
     *三级树形结构的数组
     * @return []
     * baiyang_admin_menu表所有数据
     */
    public function getAllMenu()
    {
        $menu=BaiyangRoleData::getInstance()->getAllMenus();
        $menuTree=$this->tree->structureTree($menu,'id','parent_id');
        // var_dump($menuTree);exit;
        return $menuTree;
    }

    /**
     * @param $adminId=int  用户id
     * @return []
     * 根据用户id得到左边菜单栏
     */
    public function getMainMenu($adminId)
    {
        //当前权限路径
        $controllerName=$this->dispatcher->getControllerName();
        $actionName=$this->dispatcher->getActionName();
        $curMenu=$controllerName.'/'.$actionName;
        $roleData=BaiyangRoleData::getInstance();
        //获得用户权限
        $menu=$roleData->getAdminRole($adminId);
        $menuAll=BaiyangRoleData::getInstance()->getAllMenus();
        if(is_array($menuAll) && !empty($menuAll)) {
            $menuTree = $this->tree->structureTree($menuAll, 'id', 'parent_id');
            if(is_array($menu) && !empty($menu)){
                foreach($menuTree as $k=>$v){
                    //修剪一级枝杆
                        if (!in_array($v['id'], $menu['module']?$menu['module']:[])) {
                            unset($menuTree[$k]);
                        }
                    if(isset($v['son']) && !empty($v['son'])) {
                        foreach ($v['son'] as $kk => $vv) {
                            //修剪二级枝杆
                                if (!in_array($vv['id'], $menu['controller']?$menu['controller']:[])) {
                                    unset($menuTree[$k]['son'][$kk]);
                                }
                            if ($controllerName == $vv['menu_path']) {
                                $menuTree[$k]['class'] = 'open';
                                $menuTree[$k]['son'][$kk]['display'] = 'block';
                            }
                            if(isset($vv['son']) && !empty($vv['son'])) {
                                foreach ($vv['son'] as $key => $val) {
                                    //修剪三级枝杆
                                        if (!in_array($val['id'], $menu['action']?$menu['action']:[])) {
                                            unset($menuTree[$k]['son'][$kk]['son'][$key]);
                                        }
                                    if ($val['is_show_left'] || $val['is_show_top']) {
                                                continue;
                                    } else {
                                        unset($menuTree[$k]['son'][$kk]['son'][$key]);
                                    }
                                }
                            }
                        }
                    }
                }
                return $menuTree;
            }
        }
    }

    /**
     * @param $adminId=int
     * @return []
     * 根据用户id得到用户权限
     */
    public function getAdminPermission($adminId)
    {
        $menuValue=$this->getAdminRole($adminId);
        $menu=[];
        if(is_array($menuValue) && !empty($menuValue)) {
            foreach ($menuValue as $k => $v) {
                $menu = array_merge($menu, $v);
            }
        }
        unset($menuValue);
        return $menu;
    }

    /**
     * @param $adminId=int  用户id
     * @return null|[]   返回值是menu表中的id与menu_path的关联数组
     * @author  康涛
     * @date 2016-08-30
     */
    public function getAdminRole($adminId,$isShowSiteId = 0)
    {
        $baiyangRole=BaiyangRoleData::getInstance();
        //先拿到用户与角色对应数据
        $roleUser=$baiyangRole->select('*','Shop\Models\BaiyangAdminRoleUser',[
            'admin_id'=>$adminId
        ],'admin_id=:admin_id:');
        //取到角色表中功能权限ID集合
        if(is_array($roleUser) && !empty($roleUser)){
            $roleId=array_map(function($item){
                return $item['role_id'];
            },$roleUser);
            $roleId=array_values(array_filter($roleId));
            $roles=$baiyangRole->getAllRoles();
            $menuId=array_filter(array_map(function($item)use($roleId){
                if(in_array($item['role_id'],$roleId)){
                    return [$item['site_id']=>$item['menu_id']];
                }
            },$roles));
            $permission=[];
            $menuValue=array_values($menuId);

            //得到siteID
            $siteId=array_unique(array_keys($menuValue[0]));
            if($isShowSiteId == 1) return $siteId;
            foreach($menuValue as $k=>$v){
                foreach($v as $kk=>$vv) {
                    $tmp = explode(',', $vv);
                    $permission = array_merge($permission, $tmp);
                }
            }
            unset($menuId);
            $siteData=SiteData::getInstance()->getAllSites();
            $permission=array_unique($permission);
            $siteMenus=array_filter(array_map(function($item)use($siteId){
                if(in_array($item['site_id'],$siteId)){
                   return explode(',',$item['site_menus']);
                }
            },$siteData));
            $sitePermission=[];
            foreach($siteMenus as $v){
                $sitePermission=array_merge($sitePermission,$v);
            }
            //角色权限与站点权限的交集得到最终个人权限
            $adminPersminssion=array_intersect($sitePermission,$permission);
            //得到最终权限值
            $menu= $baiyangRole->getAllMenus();
            $menuValue=array_filter(array_map(function($item)use($adminPersminssion){
                if(in_array($item['id'],$adminPersminssion)){
                        return [$item['id']=>$item['menu_path']];
                }
            },$menu));
            return $menuValue;
        }
    }

    /**
     * @param $param=[]
     * @return bool
     * @author 康涛
     * @date    2016-09-12
     * admin_menu表添加一条数据
     */
    public function AddAdmin($param)
    {
        if(is_array($param) && !empty($param)){
            $data=$this->getMenuOne([
                'bind'=>['menu_title'=>$param['menu_title']],
                'where'=>'menu_title=:menu_title:'
            ]);
            if(is_array($data) && !empty($data)){
                return 'repeat';
            }
            $ret=BaiyAdminData::getInstance()->insert('Shop\Models\BaiyangAdminMenus',$param);
            BaiyangRoleData::getInstance()->resetMenus();
            return $ret;
        }
    }

    /**
     * @param $param=[]
     * @return []
     * @author  康涛
     * @date 2016-09-13
     * admin_menu表获得一条数据
     */
    public function getMenuOne($param)
    {
        if(is_array($param) && !empty($param)){
            $ret=BaiyAdminData::getInstance()->select('*','Shop\Models\BaiyangAdminMenus',$param['bind'],$param['where']);
            return $ret[0];
        }
    }

    /**
     * @param $param=[]
     * @return []
     * @author 康涛
     * @date 2010-09-14
     */
    public function getMenus($param)
    {
        if(is_array($param) && !empty($param)) {
            $ret = BaiyAdminData::getInstance()->select('*', 'Shop\Models\BaiyangAdminMenus', $param['bind'], $param['where']);
            return $ret;
        }
    }

    /**
     * @param $param=[]
     * @return bool
     * @autor   康涛
     * @date    2016-09-14
     */
    public function updateMenus($param)
    {
        if(is_array($param) && !empty($param)){
            $ret=BaiyAdminData::getInstance()->update($param['set'],'Shop\Models\BaiyangAdminMenus',$param['bind'],$param['where']);
            BaiyangRoleData::getInstance()->resetMenus();
            return $ret;
        }
    }

    /**
     * @param $param=[]
     * @return bool
     * @author 康涛
     * @date 2016-09-18
     */
    public function delMenus($param)
    {
        if(is_array($param) && !empty($param)){

            //检查要删除的数据有没有子孙
            $adminData=BaiyAdminData::getInstance();
            $data=$adminData->select('*','Shop\Models\BaiyangAdminMenus',$param['bind'],$param['where']);
            if(is_array($data) && !empty($data)){
                if($data[0]['has_child']){

                    //找出子孙
                    $child=$adminData->select('*','Shop\Models\BaiyangAdminMenus',[
                        'parent_id'=>$data[0]['id'],
                    ],'parent_id=:parent_id:');

                    //有子孙不能删除老爸
                    if(is_array($child) && !empty($child)){
                        return 'has_child';
                    }
                }
            }
            $ret=$adminData->delete('Shop\Models\BaiyangAdminMenus',$param['bind'],$param['where']);
            BaiyangRoleData::getInstance()->resetMenus();
            return $ret;
        }
    }
    /**
     * @remark后台管理日志
     * @param $param = array()
     * @author 罗毅庭
     */
    public function addLog(){
        $get_param = $this->request->getQuery();
        $post_param = $this->request->getPost();
        $url = $this->request->getURI();
        if(!$get_param && !$post_param ) return;
        //过滤密码
        foreach($post_param as $k => $v) {
            if(strpos($k,'password')!==false) {
                $post_param[$k] = '******';
            }
        }
        unset($get_param['_url']);
        $info = parse_url($url);
        $info_router = explode('/',substr($info['path'],1,mb_strlen($info['path'])));
        $parentRouter = substr($info['path'],1,mb_strlen($info['path']));

        $param['menu_path'] = $parentRouter;
        $where = 'menu_path=:menu_path:';
        $title = BaiyAdminData::getInstance()->select('menu_title', 'Shop\Models\BaiyangAdminMenus', $param, $where);
        $admin_account = $this->session->get('admin_account');
        $ip = $this->request->getClientAddress();

        $get_param = $get_param?json_encode($get_param,JSON_UNESCAPED_UNICODE):"";
        $post_param = $post_param?json_encode($post_param,JSON_UNESCAPED_UNICODE):"";

        $data = array(
            'admin_account' => $admin_account,
            'url' 			=> $url,
            'title'         => $title[0]['menu_title'],
            'get_param' 	=> $get_param,
            'post_param' 	=> $post_param,
            'ip' 			=> $ip,
            'add_time' 		=> time()
        );
        $result = BaseData::getInstance()->insert('\Shop\Models\BaiyangAdminLog', $data);
    }
}
