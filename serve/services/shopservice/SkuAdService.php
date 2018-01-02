<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：SkuAd_id_5     一个skuad信息
 */

namespace Shop\Services;
use Shop\Datas\BaiyangSkuAdData;
use Shop\Datas\UpdateCacheSkuData;
use Shop\Models\CacheGoodsKey;
use Shop\Services\BaseService;

class SkuAdService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 添加SkuAd信息
     * User: 梁伟
     * Date: 2016/9/8
     * Time: 18:27
     */
    public function addSkuAd($param)
    {
        if(!isset($param['ad_name']) || empty($param['ad_name'])){
            return $this->arrayData('广告名不能为空','','','error');
        }
        if(count($param['ad_name'])>20){
            return $this->arrayData('广告名太长！','','','error');
        }
        if(!isset($param['content']) || empty($param['content'])){
            return $this->arrayData('广告内容不能为空','','','error');
        }
        $data['ad_name'] = $param['ad_name'];
        $data['content'] = htmlspecialchars_decode($param['content']);
        $data['is_show'] = (int)$param['is_show'];
        $data['platform'] = $param['platform'];
        $data['add_time'] = time();
        $data['update_time'] = time();
        $table = '\Shop\Models\BaiyangSkuAd';
        $res = BaiyangSkuAdData::getInstance()->insert($table,$data,true);
        if($res){
            //更新缓存
            UpdateCacheSkuData::getInstance()->updateSkuAd($res);
            return $this->arrayData('添加成功','/sku/adlist',$res,'success');
        }else{
            return $this->arrayData('添加失败','',$res,'error');
        }
    }

    /**
     * 修改SkuAd信息
     * User: 梁伟
     * Date: 2016/9/8
     * Time: 18:27
     */
    public function updateSkuAd($param)
    {
        if(!(int)$param['id']){
            return $this->arrayData('参数错误','','','error');
        }
        if(!isset($param['ad_name']) || empty($param['ad_name'])){
            return $this->arrayData('广告名不能为空','','','error');
        }
        if(count($param['ad_name'])>20){
            return $this->arrayData('广告名太长！','','','error');
        }
        if(!isset($param['content']) || empty($param['content'])){
            return $this->arrayData('广告内容不能为空','','','error');
        }
        $selections = "ad_name=:ad_name:,content=:content:,update_time=:update_time:";
        $data['ad_name'] = $param['ad_name'];
        $data['content'] = htmlspecialchars_decode($param['content']);
        if(isset($param['is_show'])){
            $data['is_show'] = (int)$param['is_show'];
            $selections .= ',is_show=:is_show:';
        }
        if(isset($param['platform']) && !empty($param['platform'])) {
            $data['platform'] = $param['platform'];
            $selections .= ',platform=:platform:';
        }
        $data['update_time'] = time();
        $data['id'] = $param['id'];
        $table = '\Shop\Models\BaiyangSkuAd';
        $where = "id=:id:";
        $res = BaiyangSkuAdData::getInstance()->update($selections,$table,$data,$where);
        if($res){
            //更新缓存
            UpdateCacheSkuData::getInstance()->updateSkuAd($param['id']);
            return $this->arrayData('修改成功','/sku/adlist',$res,'success');
        }else{
            return $this->arrayData('修改失败','',$res,'error');
        }
    }

    /**
     * 删除SkuAd信息
     * User: 梁伟
     * Date: 2016/9/8
     * Time: 18:27
     */
    public function delSkuAd($id)
    {
        if(!(int)$id){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangSkuAd';
        $data['id'] = (int)$id;
        $where = "id=:id:";
        $res = BaiyangSkuAdData::getInstance()->delete($table,$data,$where);
        if($res){
            //删除缓存
            $this->RedisCache->delete(CacheGoodsKey::SKU_AD.(int)$id);
            return $this->arrayData('删除成功','/sku/adlist',$res,'success');
        }else{
            return $this->arrayData('删除失败','',$res,'error');
        }
    }

    /**
     * 广告启用|暂停切换
     * User: 梁伟
     * Date: 2016/11/10
     */
    public function isShowAd($id,$is_show)
    {
        if(!(int)$id){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangSkuAd';
        $data['id'] = (int)$id;
        $data['is_show'] = (int)$is_show;
        $where = "id=:id:";
        $res = BaiyangSkuAdData::getInstance()->update('is_show=:is_show:',$table,$data,$where);
        if($res){
            //更新缓存
            UpdateCacheSkuData::getInstance()->updateSkuAd($id);
        }
        return $res;
    }

    /**
     * 获取一个SkuAd信息
     * @param int $id
     * @param bool $platform 平台,默认不区分
     * User: 梁伟
     * Date: 2016/9/8
     * Time: 18:27
     */
    public function getOneSkuAd($id,$platform = false)
    {
        if(!(int)$id){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangSkuAd';
        $data['id'] = (int)$id;
        $where = "id=:id:";
        if($platform){
            $data['platform'] = $platform;
            $data['is_show'] = 1;
            $where .= ' and platform=:platform: and is_show=:is_show:';
        }
        $selections = 'id,ad_name,is_show,platform,content';
        $res = BaiyangSkuAdData::getInstance()->select($selections,$table,$data,$where);
        if($res){
            $res[0]['content'] = $res[0]['content'];
            return $this->arrayData('','',$res,'success');
        }else{
            return $this->arrayData('','',$res,'error');
        }
    }

    /**
     * 获取SkuAd信息列表
     * User: 梁伟
     * Date: 2016/9/8
     * Time: 18:27
     */
    public function getAllSkuAd($param)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangSkuAd';
        $where = ' 1 ';
        $data   =   array();
        //组织where语句
        if(isset($param['ad_name']) && $param['ad_name'] != ''){
                $where .= " AND ad_name LIKE :ad_name:";
                $data['ad_name']   =   '%'.$param['ad_name'].'%';
        }
        $BaiyangSkuAdData = BaiyangSkuAdData::getInstance();
        //总记录数
        $counts = $BaiyangSkuAdData->count($table,$data,$where);
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        //分页
        $pages['page'] = isset($param['page'])?(int)$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);
        $selections = 'id,ad_name,content,is_show,platform,update_time,add_time';
        $where .= 'order by update_time desc limit '.$page['record'].','.$page['psize'];
        $result = $BaiyangSkuAdData->select($selections,$table,$data,$where);
        $return = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    /**
     * 获取全部SkuAd信息
     * @param string $platform 平台
     * @return array()
     * User: 梁伟
     * Date: 2016/9/21
     * Time: 18:27
     */
    public function getAdAll($platform)
    {
        if(empty($platform)){
            return $this->arrayData('参数错误','','','error');
        }
        //查询条件
        $table = '\Shop\Models\BaiyangSkuAd';
        $where = 'is_show=:is_show: and platform=:platform:';
        $data   =   array(
            'is_show'   =>  1,
            'platform'  =>  $platform,
        );
        $selections = 'id,ad_name,content,update_time,add_time';
        $where .= 'order by update_time desc ';
        $result = BaiyangSkuAdData::getInstance()->select($selections,$table,$data,$where);
        return $result;
    }

    /**
     * @remark 根据sku商品id或商品名称搜索  获取30条
     * @param $goods_name=string ku商品id或商品名称
     * @return array
     * @author 杨永坚
     */
    public function searchSku($goods_name)
    {
        $data['goods_name'] = "%{$goods_name}%";
        $where = "goods_name LIKE :goods_name: OR id LIKE :goods_name: limit 30";
        $result = BaseData::getInstance()->select('id,goods_name', '\Shop\Models\BaiyangGoods', $data, $where);
        return $result ? $this->arrayData('请求成功！', '', $result) : $this->arrayData('此商品不存在！', '', '', 'error');
    }
}
