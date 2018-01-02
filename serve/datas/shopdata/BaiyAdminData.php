<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2016/8/4 0004
 * Time: 下午 5:02
 */
namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangAdmin;
use Shop\Models\BaiyangAdminAuthGroupAccess;
use Shop\Models\BaiyangAdminAuthGroup;
use Shop\Models\BaiyangAdministratorLog;
use Shop\Models\BaiyangSite;
use Phalcon\Paginator\Adapter\Model as PagerModel;
use Shop\Models\CacheKey;

class BaiyAdminData extends BaseData
{
    protected static $instance=null;

    /**
     * @param $param=[column=>string,where=>string,bind=>关联数组]
     * @return []
     * @example
     */
    public function getOneAdmin($param)
    {
        $phql="select {$param['column']} from \\Shop\\Models\\BaiyangAdmin where {$param['where']}";
        $ret=$this->modelsManager->executeQuery($phql,$param['bind']);
        if(count($ret)>0){
            $ret=$ret->toArray();
            return $ret;
        }
    }

    /**
     * @return \Shop\Models\BaiyangSite[]
     * 得到BaiyangSite表所有数据
     */
    public function getAllSites()
    {
        $ret=$this->cache->getValue(CacheKey::SITE_KEY);
        if($ret){
            return $ret;
        }else{
            $ret=BaiyangSite::find();
            if(count($ret)>0){
                $ret=$ret->toArray();
            }
            $this->cache->setValue(CacheKey::SITE_KEY,$ret);
            return $ret;
        }
    }

    /**
     * baiyang_admin表带有分页的查找
     * @param $param=['limit'=>int,'cur_page'=>int]
     * @return class
     * @author 康涛
     */
    public function getAdminList($param)
    {
        //$ret = new
        $ret=new PagerModel([
            'data'=>BaiyangAdmin::find([
                'conditions'=>'site_id!=:site_id:',
                'bind'=>[
                    'site_id'=>0
                ]
            ]),
            'limit'=>$param['limit'],
            'page'=>$param['cur_page'],
        ]);
        return $ret;
    }

    /**
     * 获取后台用户信息
     * @param $param
     *              - userId 后台用户ID
     * @return array|bool
     * @author Chensonglu
     */
    public function getAdminInfo($param)
    {
        if (!isset($param['userId']) || !$param['userId']) {
            return false;
        }
        $join = "LEFT JOIN Shop\Models\BaiyangAdminAuthGroupAccess aaga ON aaga.uid = a.id"
            . " LEFT JOIN Shop\Models\BaiyangAdminAuthGroup aag ON aag.id = aaga.group_id";
        return $this->getData([
            'column' => 'a.id user_id,a.admin_account username,aag.title',
            'table' => 'Shop\Models\BaiyangAdmin a',
            'join' => $join,
            'where' => "WHERE a.id = :userId:",
            'bind' => $param,
        ], true);
    }
}