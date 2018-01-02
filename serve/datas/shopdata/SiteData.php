<?php
/**
 * Created by PhpStorm.
 * User: 康涛
 * Date: 2016/8/17 0017
 * Time: 下午 4:04
 */

namespace Shop\Datas;
use Shop\Datas\BaseData;
use Shop\Models\BaiyangAdminSite;
use Phalcon\Paginator\Adapter\Model as PagerModel;
use Shop\Models\CacheKey;

class SiteData extends BaseData
{
    protected static $instance=null;

    /**
     * baiyang_site表带有分页的查找
     * @param $param=['limit'=>int,'cur_page'=>int]
     * @return class
     * @author 康涛
     */
    public function getSiteList($param)
    {
        $ret=new PagerModel([
            'data'=>BaiyangAdminSite::find(),
            'limit'=>$param['limit'],
            'page'=>$param['cur_page'],
        ]);
        return $ret;
    }

    /**
     *  得到baiyang_admin_site表所有数据
     * @return []
     * @date 2016-08-30
     * @author 康涛
     */
    public function getAllSites()
    {
        $this->cache->selectDb(1);
        $ret=$this->cache->getValue(CacheKey::ADMIN_SITE_KEY);
        if($ret){
            return $ret;
        }else{
            $ret=BaiyangAdminSite::find();
            if(count($ret)){
                $ret=$ret->toArray();
                $this->cache->setValue(CacheKey::ADMIN_SITE_KEY,$ret);
                return $ret;
            }
        }
    }

    /**
     * 重置site表缓存
     * @author 康涛
     * @date 2016-08-31
     */
    public function resetCache()
    {
        $this->cache->selectDb(1);
        $this->cache->delete(CacheKey::ADMIN_SITE_KEY);
        $this->getAllSites();
    }

    /**
     * @param $siteID=[] 一维数组site_id
     * @return []
     * 根据站点ID取得站点数据
     */
    public function getSiteById($siteID)
    {
        $id=implode(',',$siteID);
        $phql="select * from Shop\Models\BaiyangAdminSite where site_id in($id)";
        $ret=$this->modelsManager->executeQuery($phql);
        if(count($ret)){
            return $ret->toArray();
        }
    }
}