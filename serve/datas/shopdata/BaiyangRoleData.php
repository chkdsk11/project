<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/24 0024
 * Time: 上午 10:18
 */

namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangAdminRole;
use Shop\Models\BaiyangAdminRoleUser;
use Shop\Models\BaiyangAdminMenus;
use Phalcon\Paginator\Adapter\Model as PagerModel;
use Shop\Models\BaiyangAdminSite;
use Shop\Models\CacheKey;

class BaiyangRoleData extends BaseData
{
    protected static $instance=null;


    /**
     * @param $param=[]
     * @return []
     * redis主动缓存
     * role表所有数据
     */
    public function getAllRoles()
    {
        $this->cache->selectDb(1);
        $ret=$this->cache->getValue(CacheKey::ROLE_KEY);
        if($ret){
            return $ret;
        }else {
            $ret = BaiyangAdminRole::find();
            if (count($ret)) {
                $ret = $ret->toArray();
                $this->cache->setValue(CacheKey::ROLE_KEY,$ret);
                return $ret;
            }
        }
    }

    /**
     *  得到adminmenus表所有信息
     */
    public function getAllMenus()
    {
        $this->cache->selectDb(1);
        $menus=$this->cache->getValue(CacheKey::MENU_KEY);
        if($menus){
            return $menus;
        }else{
            $menus=BaiyangAdminMenus::find();
            if(count($menus)){
                $menus=$menus->toArray();
                $this->cache->setValue(CacheKey::MENU_KEY,$menus);
                return $menus;
            }
        }
    }

    /**
     *  清除并重置menus表缓存
     */
    public function resetMenus()
    {
        $this->cache->selectDb(1);
        $this->cache->delete(CacheKey::MENU_KEY);
    }

    /**
     * baiyang_site表带有分页的查找
     * @param $param=['limit'=>int,'cur_page'=>int]
     * @return class
     * @author 康涛
     */
    public function getRoleList($param)
    {
        $ret=new PagerModel([
            'data'=>BaiyangAdminRole::find([
                'conditions'=>'role_id!=:role_id:',
                'bind'=>[
                    'role_id'=>1
                ]
            ]),
            'limit'=>$param['limit'],
            'page'=>$param['cur_page'],
        ]);
        return $ret;
    }

    /**
     * 根据方法ID得到控制器ID集合
     * @param $actiionId=string
     * @return []
     */
    public function getControllerId($actionId)
    {
        $phql="select distinct(parent_id) from Shop\Models\BaiyangAdminMenus where id in({$actionId}) and menu_level=3";
        $ret=$this->modelsManager->executeQuery($phql);
        if(count($ret)){
            return $ret->toArray();
        }
    }
    /**
     * 根据控制器id得到模块ID集合
     *  @param $controllerId=string
     * @return []
     */
    public function getModuleId($controllerId)
    {
        $phql="select distinct(parent_id) from Shop\Models\BaiyangAdminMenus where id in({$controllerId}) and menu_level=2";
        $ret=$this->modelsManager->executeQuery($phql);
        if(count($ret)){
            return $ret->toArray();
        }
    }

    /**
     *  清除role表缓存
     */
    public function resetRole($adminId='')
    {
        $this->cache->selectDb(1);
        if(!empty($adminId)){
            $this->cache->delete(CacheKey::MENU_KEY.$adminId);
        }
        return $this->cache->delete(CacheKey::ROLE_KEY);
    }

    /**
     * @param $adminId=int
     * @return []
     */
    public function getAdminRole($adminId,$code = 0)
    {
        $key=CacheKey::MENU_KEY.$adminId;
        $this->cache->selectDb(1);
        $data=$this->cache->getValue($key);
        if($data){
           return $data;
       }
//        $roleUser=BaiyangAdminRoleUser::find([
//            'conditions'=>'admin_id=:admin_id:',
//            'bind'=>[
//                'admin_id'=>$adminId
//            ]
//        ]);
        $Base = BaseData::getInstance();
        $roleUser = $Base->getData(array(
            'table' => '\Shop\Models\BaiyangAdminRoleUser',
            'column' => '*',
            'where' => 'where admin_id=:admin_id:',
            'bind' => array('admin_id'=>$adminId),
        ));

        //老用户没有权限
        if(empty($roleUser)){
            return 'no_auth';
        }
        //角色没有权限
        if($code == 1){
            $isRoleUseable = $Base->countData([
                'table' => '\Shop\Models\BaiyangAdminRole',
                'where' => 'where role_id=:role_id: AND is_enable = 1',
                'bind' => array('role_id'=>$roleUser[0]['role_id'])
            ]);
            if($isRoleUseable < 1){
                return 'no_role_auth';
            }
        }
        $roleId='';

        //得到角色id
        foreach($roleUser as $v){
            $roleId.=$v['role_id'].',';
        }


        $roleId=rtrim($roleId,',');
        $phql="select * from Shop\Models\BaiyangAdminRole where role_id in($roleId) and is_enable=1";
        $roles=$this->modelsManager->executeQuery($phql);
        $roles=$roles->toArray();
        //得到站点id
        $siteId='';
        foreach($roles as $v){
            $siteId.=$v['site_id'].',';
        }
        $siteId=rtrim($siteId,',');
        $siteMenu=$this->getSiteMenus([
            'column'=>'site_id',
            'value'=>$siteId,
        ]);

        $menuId=[];
        $controllerId=[];
        $moduleId=[];
        if(is_array($roles) && !empty($roles)){
            foreach($roles as $v){
                $menuId=array_intersect(array_merge($menuId,explode(',',$v['menu_id'])),$siteMenu['site_action']);
                $controllerId=array_intersect(array_merge($controllerId,explode(',',$v['controller_id'])),$siteMenu['site_controller']);
                $moduleId=array_intersect(array_merge($moduleId,explode(',',$v['module_id'])),$siteMenu['site_module']);
            }
            $this->cache->setValue($key,['controller'=>$controllerId,'module'=>$moduleId,'action'=>$menuId]);
            return ['controller'=>$controllerId,'module'=>$moduleId,'action'=>$menuId];
        }
    }

    /**
     * @param $param
     * @return []
     * 得到站点的权限与角色权限求交集
     */
    private function getSiteMenus($param)
    {
        if(!empty($param['value'])) {
            $phql = "select * from Shop\Models\BaiyangAdminSite where {$param['column']} in({$param['value']}) and is_enable=1";
            $siteValue = $this->modelsManager->executeQuery($phql);
            if (count($siteValue)) {
                $sites = $siteValue->toArray();
                unset($siteValue);
                $siteMenus = [];
                $siteController = [];
                $siteAction = [];
                $siteModule = [];
                foreach ($sites as $k => $v) {
                    $siteController = array_unique(array_merge($siteController, explode(',', $v['site_controllers'])));
                    $siteAction = array_unique(array_merge($siteAction, explode(',', $v['site_menus'])));
                    $siteModule = array_unique(array_merge($siteModule, explode(',', $v['site_module'])));
                }
                return ['site_module' => $siteModule, 'site_controller' => $siteController, 'site_action' => $siteAction];
            }
        }
    }
}