<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/8/17 0017
 * Time: 下午 4:43
 */

namespace Shop\Services;

use Phalcon\Annotations\Exception;
use Shop\Datas\BaiyangRoleData;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\SiteData;
use Shop\Datas\AdminRuleData;

class SiteService extends BaseService
{
    protected static $instance=null;

    /**
     * @return []
     * 获取站点所有信息
     */
    public function getAllSite()
    {
        $siteData=SiteData::getInstance()->select('*','Shop\Models\BaiyangAdminSite');
        return $siteData;
    }

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
    public function getSiteList($param)
    {
        $siteDataCount = BaseData::getInstance()->countData([
           'table' => '\Shop\Models\BaiyangAdminSite'
        ]);
        $pages['page'] = isset($param['page']) ? $param['page'] : 1;//当前页
        $pages['counts'] = $siteDataCount;
        $pages['psize'] = isset($param['psize']) ? $param['psize'] : 15;
        $pages['url'] = $param['url'];
        $pages['url_back'] = $param['url_back'];
        $pages['home_page'] = $param['home_page'];
        $pages['size'] = isset($param['size']) ? $param['size'] : 5;
        $page = $this->page->pageDetail($pages);
        $siteData = BaseData::getInstance()->getData([
            'column' => '*',
            'table' => '\Shop\Models\BaiyangAdminSite',
            'where' => 'LIMIT '. $page['record'] . ',' . $page['psize']
        ]);
        if(isset($siteData) && !empty($siteData)){
            return ['res' => 'succcess','page'=>$page['page'], 'list'=>$siteData,'status'=>true,'voltValue' => $param['param']];
        }
    }

    /**
     * @param $param=[
     *          'bind'=>[]  关联数组，必须
     *          'where'=>string 必须
     *      ]
     * 根据条件获得一个站点数据
     */
    public function getSiteOne($param)
    {
        if(is_array($param) && !empty($param)) {
            $ret = SiteData::getInstance()->select('*', 'Shop\Models\BaiyangAdminSite', $param['bind'], $param['where']);
            if(count($ret)>0){
                $ret[0]['site_menus']=explode(',',$ret[0]['site_menus']);
                return $ret[0];
            }
        }
    }

    /**
     * @param $param=[
     *              'set'=>string,  example:site_name=:site_name:,site_menus=:site_menus:
     *              'bind'=>[], //关联数组
     *              'where'=>string        example:site_id=:site_id:
     *         ]
     */
    public function updateSite($param)
    {
        if(is_array($param) && !empty($param)){
            $roleData=BaiyangRoleData::getInstance();
            //控制器id
            $controllers=$roleData->getControllerId($param['bind']['site_menus']);
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
                $moduleId = rtrim($moduleId, ',');
            }
                $param['bind']['site_controllers'] = $controllerId;
                $param['bind']['site_module'] = $moduleId;
                $ret = BaseData::getInstance()->update($param['set'], 'Shop\Models\BaiyangAdminSite', $param['bind'], $param['where']);
                //更新成功后重置site缓存
                SiteData::getInstance()->resetCache();
                $this->cache->flushDb(1);
                return $ret;
        }
    }

    /**
     * @param $param=[]     参数是site_menus一维关联数组
     * @return bool
     *
     */
    public function addSite($param)
    {
        if(is_array($param) && !empty($param)) {

            //判断是否有重名站点
            $siteValue=BaseData::getInstance()->select('*','Shop\Models\BaiyangAdminSite',['site_name'=>$param['site_name']],'site_name=:site_name:');
            if(is_array($siteValue) && !empty($siteValue)){
                return 'repeat';
            }
            $roleData=BaiyangRoleData::getInstance();
            //控制器id
            $controllers=$roleData->getControllerId($param['site_menus']);
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
                $moduleId=rtrim($moduleId);
            }
            $param['site_controllers']=$controllerId;
            $param['site_module']=$moduleId;
            $ret = BaseData::getInstance()->insert('Shop\Models\BaiyangAdminSite', $param, true);
            SiteData::getInstance()->resetCache();
            return $ret;
        }
    }
}