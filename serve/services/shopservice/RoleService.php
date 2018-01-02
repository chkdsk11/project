<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/31 0031
 * Time: 下午 2:34
 */

namespace Shop\Services;

use Shop\Datas\BaseData;
use Shop\Services\BaseService;
use Shop\Datas\BaiyangRoleData;
use Shop\Datas\SiteData;

class RoleService extends BaseService
{
    protected static $instance=null;

    /**
     * 带有分页的查找
     *  @param $param=['cur_page'=>int]     当前页数(默认1页)
     * @param $param=['com'=>int]           每页显示条数(默认15)
     * @param $param=['url'=>string]        跳转链接前缀(必须)
     * @param $param=['url_back'=>string]   跳转链接后缀(必须)
     * @param $param=['home_page'=>string]  首页链接(必须)
     * @param $param=['size'=>int]          中间显示页数,默认为5(非必须)
     * @return bool|[]
     */
    public function getRoleList($param)
    {
        $roleDataCount = BaseData::getInstance()->countData([
            'table' => '\Shop\Models\BaiyangAdminRole',
            'where' => 'where role_id != :role_id: ',
            'bind' => [
                'role_id'=>1
            ]
        ]);
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $roleDataCount;
        $pages['psize'] = isset($param['psize']) ? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size']) ? $param['size'] : 5;
        $page = $this->page->pageDetail($pages);
        $roleData = BaseData::getInstance()->getData([
            'column' => '*',
            'table' => '\Shop\Models\BaiyangAdminRole',
            'where' => 'where role_id != :role_id: LIMIT '. $page['record'] . ',' . $page['psize'],
            'bind' => [
                'role_id' => 1
            ]
        ]);
        if(isset($roleData) && !empty($roleData)){

            //得到站点id
            $siteID=[];
            foreach($roleData as $v){
                $siteID[]=$v['site_id'];
            }
            //得到站点数据
            $siteValue=SiteData::getInstance()->getSiteById($siteID);
            $site=[];
            foreach($siteValue as $v){
                $site[$v['site_id']]=$v['site_name'];
            }
            if (empty($roleData)) {
                return ['res' => 'error'];
            }
            return ['page'=>$page['page'], 'res' => 'succcess', 'list'=>$roleData,'status'=>true,'site'=>$site,'voltValue' => $param['param']];
        }
    }

    /**
     * @param $param=[]
     * @date 2016-9-1
     * @author  康涛
     * @return bool
     */
    public function updateAdminRole($param)
    {
        if(is_array($param) && !empty($param)) {
            if(isset($param['bind']['menu_id']) && !empty($param['bind']['menu_id'])){
                $roleData=BaiyangRoleData::getInstance();
                //控制器id
                $controllers=$roleData->getControllerId($param['bind']['menu_id']);
                if(count($controllers)) {
                    $controllerId = '';
                    foreach ($controllers as $v) {
                        $controllerId .= $v->readAttribute('0') . ',';
                    }
                    $controllerId = rtrim($controllerId, ',');
                    unset($controllers);
                    //模块id
                    $modules = $roleData->getModuleId($controllerId);
                    $moduleId = '';
                    foreach ($modules as $v) {
                        $moduleId .= $v->readAttribute('0') . ',';
                    }
                    unset($modules);
                    $moduleId=rtrim($moduleId,',');
                }
            }
            $param['bind']['controller_id']=$controllerId;
            $param['bind']['module_id']=$moduleId;
            $ret = BaseData::getInstance()->update($param['set'], 'Shop\Models\BaiyangAdminRole', $param['bind'], $param['where']);
            $roleData->resetRole($param['bind']['role_id']);
            $this->cache->flushDb(1);
            return $ret;
        }
    }

    /**
     * @param $param=[]
     * @return []
     * @date 2016-9-1
     * @author 康涛
     * 得到单个角色所有信息
     */
    public function getRoleOne($param)
    {
        $role=BaseData::getInstance()->select('*','Shop\Models\BaiyangAdminRole',$param['bind'],$param['where']);
        if(count($role)){
            $site=SiteData::getInstance()->getSiteById([$role[0]['site_id']]);
            if(count($site)){
               $roleMenus=explode(',',$role[0]['menu_id']);
                $siteMenus=explode(',',$site[0]['site_menus']);
                $finalMenus=array_intersect($roleMenus,$siteMenus);
                unset($roleMenus);
                unset($siteMenus);
                $role[0]['menu_id']=$finalMenus;
            }
            return $role;
        }
    }

    /**
     * @param $param=[]
     * @return bool|int
     * @author  康涛
     * @date    2016-09-07
     */
    public function addRole($param)
    {
        if(is_array($param) && !empty($param)){

            //判断是否有重复角色名
            $roles=BaseData::getInstance()->select('*','Shop\Models\BaiyangAdminRole',['role_name'=>$param['role_name']],'role_name=:role_name:');
            if(is_array($roles) && !empty($roles)){
                return 'repeat';
            }
            if(!empty($param['menu_id'])){
                $roleData=BaiyangRoleData::getInstance();
                //控制器id
                $controllers=$roleData->getControllerId($param['menu_id']);
                if(count($controllers)) {
                    $controllerId = '';
                    foreach ($controllers as $v) {
                        $controllerId .= $v->readAttribute('0') . ',';
                    }
                    $controllerId = rtrim($controllerId, ',');
                    unset($controllers);
                    //模块id
                    $modules = $roleData->getModuleId($controllerId);
                    $moduleId = '';
                    foreach ($modules as $v) {
                        $moduleId .= $v->readAttribute('0') . ',';
                    }
                    unset($modules);
                    $moduleId=rtrim($moduleId,',');
                }
            }
            $param['controller_id']=$controllerId;
            $param['module_id']=$moduleId;
            $ret=BaiyangRoleData::getInstance()->insert('Shop\Models\BaiyangAdminRole',$param,true);
            $this->cache->flushDb(1);
            return $ret;
        }
    }

    /**
     * 得到角色表所有数据
     */
    public function getAllRole()
    {
        return BaiyangRoleData::getInstance()->getAllRoles();
    }
}