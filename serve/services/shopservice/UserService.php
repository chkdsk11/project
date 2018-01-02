<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/9/7 0007
 * Time: 上午 10:57
 */

namespace Shop\Services;

use Shop\Datas\BaiyangRoleData;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyAdminData;
use Shop\Datas\SiteData;

class UserService extends BaseService
{
    protected static $instance=null;

    /**
     * 带有分页的查找运营用户
     *  @param $param=['cur_page'=>int]     当前页数(默认1页)
     * @param $param=['com'=>int]           每页显示条数(默认15)
     * @param $param=['url'=>string]        跳转链接前缀(必须)
     * @param $param=['url_back'=>string]   跳转链接后缀(必须)
     * @param $param=['home_page'=>string]  首页链接(必须)
     * @param $param=['size'=>int]          中间显示页数,默认为5(非必须)
     * @return bool|[]
     */
    public function getAdminUserList($param)
    {
        $adminDataCount = BaseData::getInstance()->countData([
           //'column' => 'id,site_id',
            'table' => '\Shop\Models\BaiyangAdmin',
            'where' => 'where site_id != :site_id: ',
            'bind' => [
                'site_id' => 0
            ]
        ]);
        if (empty($adminDataCount)) {
            return ['res' => 'error', 'list' => '', 'voltValue' => $param['param']];
            exit;
        }
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $adminDataCount;
        $pages['psize'] = isset($param['psize']) ? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size']) ? $param['size'] : 5;
        $page = $this->page->pageDetail($pages);
        $adminData = BaseData::getInstance()->getData([
            'column' => '*',
            'table' => '\Shop\Models\BaiyangAdmin',
            'where' => 'where site_id != :site_id: LIMIT '. $page['record'] . ',' . $page['psize'],
            'bind' => [
                'site_id' => 0
            ]
        ]);

        if(isset($adminData) && !empty($adminData)){
            //得到站点id
            $siteID=[];
            $adminId=[];
            foreach($adminData as $v){
                $siteID[]=$v['site_id'];
                $adminId[]=$v['id'];
            }
            //站点数据处理
            $siteValue=SiteData::getInstance()->getSiteById($siteID);
            $site=[];
            if(is_array($siteValue) && !empty($siteValue)) {
                foreach ($siteValue as $v) {
                    $site[$v['site_id']] = $v['site_name'];
                }
            }

            //角色数据处理
            $adminIdValue=implode(',',$adminId);
            unset($adminId);
            $roleUser=BaseData::getInstance()->select('role_id,admin_id','Shop\Models\BaiyangAdminRoleUser',[],"admin_id in($adminIdValue)");
            if(is_array($roleUser) && !empty($roleUser)) {
                $roleIdValue = array_filter(array_map(function ($item) {
                    return $item['role_id'];
                }, $roleUser));
                $roleId = implode(',', $roleIdValue);
                $roleValue = BaseData::getInstance()->select('role_id,role_name', 'Shop\Models\BaiyangAdminRole', [], "role_id in($roleId)");
                $adminRole = array_filter(array_map(function ($item) use ($roleValue) {
                    foreach ($roleValue as $v) {
                        if ($item['role_id'] == $v['role_id']) {
                            return ['admin_id' => $item['admin_id'], 'role_name' => $v['role_name']];
                        }
                    }
                }, $roleUser));
                $roles = array_column($adminRole, 'role_name', 'admin_id');
            }
            if (empty($adminData)) {
                return ['res' => 'error'];
            }
            return ['res' => 'succcess', 'list' => $adminData, 'page' => $page['page'], 'status'=>true,'voltValue' => $param['param'],'site'=>$site,'role'=>$roles];
            //return ['data'=>$adminData->getPaginate()->items,'page'=>$page['page'],'status'=>true,'site'=>$site,'role'=>$roles];
        }
    }

    /**
     * @param $param=[]
     * @return []
     * @author 康涛
     * @date 2016-09-07
     */
    public function getAdminOne($param)
    {
        if(is_array($param) && !empty($param)) {
            $ret = BaiyAdminData::getInstance()->select($param['column'], 'Shop\Models\BaiyangAdmin', $param['bind'], $param['where']);
            if(count($ret)){
                return $ret[0];
            }
        }
    }

    /**
     * 更新baiyang_admin,role_user表
     * @param $paramAdmin=['set'=>'','bind'=>'','where'=>'']
     * @param $paramRoleUser=['set'=>'','bind'=>'','where'=>'']
     * @return bool
     * @author 康涛
     * @date 2016-09-08
     */
    public function updateAdmin($paramAdmin,$paramRoleUser)
    {
        $role=BaiyangRoleData::getInstance()->select('site_id','Shop\Models\BaiyangAdminRole',
            ['role_id'=>$paramRoleUser['bind']['role_id']],'role_id=:role_id:');
        $paramAdmin['bind']['site_id']=intval($role?$role[0]['site_id']:'');
        if(!$paramAdmin['bind']['admin_password']){
            unset($paramAdmin['bind']['admin_password']);
        }else{
            $paramAdmin['bind']['admin_password']=md5($paramAdmin['bind']['admin_password']);
        }

        //开启事务
        $this->dbWrite->begin();
        $baseData=BaseData::getInstance();

        //admin表提交
        $adminRet=$baseData->update($paramAdmin['set'],'Shop\Models\BaiyangAdmin',$paramAdmin['bind'],$paramAdmin['where']);
        if(!$adminRet){
            $this->dbWrite->rollback();
            return false;
        }

        //roleuser表提交
        $roleUserRet=$baseData->update($paramRoleUser['set'],'Shop\Models\BaiyangAdminRoleUser',$paramRoleUser['bind'],$paramRoleUser['where']);
        if(!$roleUserRet){
            $this->dbWrite->rollback();
            return false;
        }

        //事务提交
        $this->dbWrite->commit();

        //清除角色缓存
        BaiyangRoleData::getInstance()->resetRole($paramAdmin['bind']['id']);
        return true;
    }

    /**
     * 获取角色与用户的关联
     * @param $param=[]
     * @return []
     * @author 康涛
     * @date 2016-09-08
     */
    public function getRoleUser($param)
    {
        $roleUser=BaseData::getInstance()->select('*','Shop\Models\BaiyangAdminRoleUser',$param['bind'],$param['where']);
        if(count($roleUser)){
            return $roleUser;
        }
    }

    /**
     * @param $param=[]
     * @return bool
     * @autor   康涛
     * @date 2016-09-09
     */
    public function addAdminUser($paramAdmin,$paramRoleUser)
    {
        if(is_array($paramAdmin) && !empty($paramAdmin) && is_array($paramRoleUser) && !empty($paramRoleUser)){

            //检查用户名是否重复
            $admin=BaiyAdminData::getInstance()->select('*','Shop\Models\BaiyangAdmin',['admin_account'=>$paramAdmin['admin_account']],'admin_account=:admin_account:');
            if(is_array($admin) && !empty($admin)){
                return 'repeat';
            }

            //得到站点id
            $siteId=BaseData::getInstance()->select('site_id','Shop\Models\BaiyangAdminRole',[
                'role_id'=>$paramRoleUser['role_id'],
            ],'role_id=:role_id:');
            $paramAdmin['site_id']=intval(isset($siteId[0]['site_id'])?$siteId[0]['site_id']:'');
            $paramAdmin['add_time']=time();

            //开启事务
            $this->dbWrite->begin();
            $adminId=BaiyAdminData::getInstance()->insert('Shop\Models\BaiyangAdmin',$paramAdmin,true);
            if($adminId){
                $paramRoleUser['admin_id']=$adminId;
                $ret=BaseData::getInstance()->insert('Shop\Models\BaiyangAdminRoleUser',$paramRoleUser);
                if(!$ret){
                    $this->dbWrite->rollback();
                    return false;
                }else{
                    //事务提交
                    $this->dbWrite->commit();
                    return true;
                }
            }else{
                $this->dbWrite->rollback();
                return false;
            }
        }
    }
}