<?php
/**
 * 管理员角色业务处理
 * Class AdminRoleService
 * Author: edgeto
 * Date: 2017/5/9
 * Time: 15:52
 */

namespace Shop\Services;
use Shop\Datas\BaiyangAdminRoleData;
use Shop\Datas\BaiyangAdminResourceData;
use Shop\Models\CacheKey;
use Shop\Libs\Func;

class AdminRoleService extends BaseService
{

	/**
     * 必须声明此静态属性，单例模式下防止实例对象覆盖
     * @var null
     */
    protected static $instance = null;

    /**
     * [$error 错误提示]
     * @var string
     */
    public $error = '';

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
    	$res = BaiyangAdminRoleData::getInstance()->getPage($where,$conditions,$pageParam);
    	if(empty($res)){
    		$this->error = BaiyangAdminRoleData::getInstance()->error;
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
     */
    public function add($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminRoleData::getInstance()->add($data);
        if(empty($res)){
            $this->error = BaiyangAdminRoleData::getInstance()->error;
            return false;
        }
        return true;
    }

    /**
     * [edit description]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function edit($data = array())
    {
        if(empty($data)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminRoleData::getInstance()->edit($data);
        if(empty($res)){
            $this->error = BaiyangAdminRoleData::getInstance()->error;
            return false;
        }
        return true;
    }

    /**
     * [del description]
     * @param  integer $role_id [description]
     * @return [type]      [description]
     */
    public function del($role_id = 0)
    {
        if(empty($role_id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminRoleData::getInstance()->del($role_id);
        if(empty($res)){
            $this->error = BaiyangAdminRoleData::getInstance()->error;
            return false;
        }
        $this->delCacheSingleAccess($role_id);
        return true;
    }

    /**
     * [getById description]
     * @param  integer $id [description]
     * @return [type]      [description]
     */
    public function getByRoleId($id = 0)
    {
        if(empty($id)){
            $this->error = '参数不完整或者参数错误！';
            return false;
        }
        $res = BaiyangAdminRoleData::getInstance()->getByRoleId($id);
        if(empty($res)){
            $this->error = BaiyangAdminRoleData::getInstance()->error;
            return false;
        }
        return $res;
    }

    /**
     * [getAll 取所有]
     * @return [type] [description]
     */
    public function getAll()
    {
        $res = BaiyangAdminRoleData::getInstance()->getAll();
        if(empty($res)){
            $this->error = BaiyangAdminRoleData::getInstance()->error;
            return false;
        }
        return $res;
    }

    /**
     * [getOneCahce 找单个角色缓存]
     * @param  integer $role_id [description]
     * @return [type]           [description]
     */
    public function getOneCahce($role_id = 0)
    {
        return BaiyangAdminRoleData::getInstance()->getOneCahce($role_id);;
    }

    /**
     * [filterAccess 权限判断]
     * @param  integer $role_id  [description]
     * @param  [type]  $is_super [description]
     * @return [type]            [description]
     */
    public function filterAccess($role_id = 0,$is_super = 0)
    {
        $is_can = false;
        $url = strtolower($this->request->getURI());
        $controller = strtolower($this->dispatcher->getControllerName());
        $action = strtolower($this->dispatcher->getActionName());
        $show_url = $controller . '/' . $action;
        // 过滤器
        $filter  = $this->routerfilter->path;
        $filter_url = $controller . '/' . $action;
        foreach ($filter as $key => $value) {
            if($filter_url == strtolower($value)){
                return true;
            }
        }
        if(empty($role_id)){
            $this->error = "没有操作{$show_url}的权限";
            return $is_can;
        }
        // 先判断有没有这个权限资源
        $resource_id = 0;
        $resource_name = '';
        $list = BaiyangAdminResourceData::getInstance()->getDefaultAll();
        if(empty($list)){
            $this->error = "请先添加权限资源";
            return $is_can;
        }
        // 先判断链接
        foreach ($list as $key => $value) {
            if($url == strtolower($value['route']) || ($controller == strtolower($value['controller']) && $action == strtolower($value['action']))){
                $resource_id = $value['id'];
                $resource_name = isset($value['name']) ? $value['name'] : '';
            }
        }   
        if(empty($resource_id)){
            $this->error = "请先添加{$show_url}权限资源";
            return $is_can;
        }
        $rules = '';
        $role_id_info = BaiyangAdminRoleData::getInstance()->getOneCahce($role_id);
        $is_super = isset($role_id_info['is_super']) ? $role_id_info['is_super'] : $is_super;
        if($is_super){
            return true;
        }
        if(empty($role_id_info['is_enable']) || empty($role_id_info['rules'])){
            // 实时角色被禁用或权限被清除
            $this->error = "角色不存在或被禁用或权限被清除";
            return -1;
        }
        $rules = $role_id_info['rules'];
        // 用isset 不用in_array()
        $rules = array_flip(explode(',',$rules));
        if(isset($rules[$resource_id])){
            $is_can = true;
        }
        if(empty($is_can)){
            $this->error = "没有操作--{$resource_name}:{$show_url}--的权限";
        }
        return $is_can;
    }

    /**
     * [assignMenu 可视菜单]
     * @param  integer $role_id  [description]
     * @param  integer $is_super [description]
     * @return [type]            [description]
     */
    public function assignMenu($role_id = 0,$is_super = 0)
    {
        $url = strtolower($this->request->getURI());
        $controller = strtolower($this->dispatcher->getControllerName());
        $action = strtolower($this->dispatcher->getActionName());
        $rules = '';
        $role_id_info = BaiyangAdminRoleData::getInstance()->getOneCahce($role_id);
        if($role_id_info){
            $rules = $role_id_info['rules'];
        }
        // 不是超管
        if(empty($rules) && empty($is_super)){
            $this->error = "所在角色没有权限";
            return false;
        }
        // 当前路由
        $resource_id = 0;
        // 当前路由路径
        $resource_path = '';
        $resource = array();
        $list = BaiyangAdminResourceData::getInstance()->getDefaultAll();
        if(empty($list)){
            $this->error = "请先添加权限资源";
            return false;
        }
        $admin_id = $this->session->get('admin_id');
        $Func = new Func();
        $admin_to_admin_key = CacheKey::ADMIN_TO_ADMIN;
        $pc_admin_url_key = CacheKey::PC_ADMIN_URL;
        $rules = array_flip(explode(',',$rules));
        foreach ($list as $key => $value) {
            $route_url = $value['route'];
            if($url == strtolower($value['route']) || ($controller == strtolower($value['controller']) && $action == strtolower($value['action']))){
                $resource_path = $value['route_path'];
                $resource_id = $value['id'];
            }
            // 其他后台链接
            if(!empty($this->config->pc_admin_url[$this->config->environment])){
                $pc_admin_url = $this->config->pc_admin_url[$this->config->environment];
                $route_url = str_ireplace($pc_admin_url_key,$pc_admin_url,$route_url);
                $value['route'] = $route_url;
            }
            if($is_super){
                $resource[$value['id']] = $value;
            }else{
                if(isset($rules[$value['id']])){
                    $resource[$value['id']] = $value;
                }
            }
        }
        if(empty($resource_id)){
            $this->error = $controller . "/" . $action . "权限资源不存在";
            return false;
        }
        if(empty($resource)){
            $this->error = "请先给角色分配权限";
            return false;
        }
        if(empty($resource_path)){
            $this->error = "权限资源路径不对";
            return false;
        }
        $current_ids = explode('/',$resource_path);
        $resource = $this->tree->menuStructureTree($resource,'id','pid','son',$resource_id,$current_ids);
        $has_level_three = false;
        $main_menu = $current_menu = $bread_crumb = array();
        $main_menu = $resource;
        foreach ($main_menu as $key => $value) {
            if($value['show_nav']){
                $has_level_three = true;
            }
            if($value['current'] && !empty($value['son'])){
                $current_menu = $value['son'];
            }
            if($value['level'] != 1){
                // 头部显示一级
                unset($main_menu[$key]);
            }
        }
        // 面包屑
        foreach ($list as $key => $value) {
            if(in_array($value['id'],$current_ids)){
                $bread_crumb[] = $value;
            }
        }
        $bread_crumb = $this->tree->structureTree($bread_crumb,'id','pid','son');
        if(empty($has_level_three)){
            $this->error = "没有可视的权限资源，请先编辑权限资源";
            return false;
        }
        return array('main_menu'=>$main_menu,'current_menu'=>$current_menu,'bread_crumb'=>$bread_crumb);
    }

    /**
     * [cacheSingleAccess 更新单个角色权限缓存]
     * @param  array  $data [description]
     * @return [type]       [description]
     */
    public function cacheSingleAccess($data = array())
    {
        if($data){
            $role_id = $data['role_id'];
            $role_id_key = CacheKey::ADMIN_ROLE_KEY.$role_id;
            $this->cache->selectDb(1);
            $rules = $this->cache->setValue($role_id_key,$data['rules']);
        }
    }

    /**
     * [delCacheSingleAccess 删除单个角色权限缓存]
     * @param  integer $role_id [description]
     * @return [type]           [description]
     */
    public function delCacheSingleAccess($role_id = 0)
    {
        if($role_id){
            $role_id_key = CacheKey::ADMIN_ROLE_KEY.$role_id;
            $this->cache->selectDb(1);
            $rules = $this->cache->delete($role_id_key);
        }
    }

}