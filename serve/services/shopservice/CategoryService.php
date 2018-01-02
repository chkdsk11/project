<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：CategoryAll     全部分类信息
 * @Explain：    缓存数据,键：Category_id_5     一个分类信息
 * @Explain：    缓存数据,键：Category_pid_5     同一个父下所有分类信息
 * @Explain：    缓存数据,键：ProductRule_id_5     获取ID的品规信息
 * @Explain：    缓存数据,键：ProductRule_pid_5     获取同一品规的值
 * @Explain：    缓存数据,键：CategoryProductRule_id_5     根据分类ID获取该分类的品规信息
 */

namespace Shop\Services;

use Shop\Datas\BaseData;
use Shop\Models\BaiyangCategory;
use Shop\Services\BaseService;
use Shop\Datas\BaiyangCategoryData;
use Shop\Datas\BaiyangProductRuleData;
use Shop\Datas\BaiyangCategoryProductRuleData;
use Shop\Datas\BaiyangSkuData;
use Shop\Datas\UpdateCacheSkuData;

class CategoryService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 获取分类信息列表
     * @return array()
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:21
     */
    public function categoryLists()
    {
        $selections = 'id,category_name,pid,level,is_enable';
        $result = BaiyangCategoryData::getInstance()->select($selections,'\Shop\Models\BaiyangCategory','','');
        return $this->tree->structureTree($result,'id','pid');
    }

    /**
     * 获取单个或同一父亲的分类信息
     * @param $id int 要查找的值
     * @param $type string 要根据查找的类型(id|pid)
     * @param $act bool 是否获取品规信息 (默认true)
     * @return array()
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:58
     */
    public function getCategory($id = 0,$type = 'pid',$act = true,$enable = false)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangCategory';
        if($type == 'id'){
            if($id < 1){
                return $this->arrayData('参数错误','/category/list','','error');
            }
            $where = ' id=:id:';
        }else{
            $where = ' pid=:id:';
        }
        if($enable){
            $where .= ' and is_enable = 0 ';
        }
        $data['id'] = (int)$id;
        $selections = 'id,category_name,pid,level,alias,meta_title,meta_keyword,meta_description,is_enable,category_path';
        $result = BaiyangCategoryData::getInstance()->select($selections,$table,$data,$where);
        //获取品规信息
        if( $type == 'id' && $result[0]['level'] == 3 && $act){
            $res = $this->getCategoryProductRule($result[0]['id']);
            if($res[0]['name_id'] > 0){
                $resRule = $this->getProductRule($res[0]['name_id'],'id');
                $result[0]['productRule']['name_id'] = $resRule[0];
            }
            if($res[0]['name_id2'] > 0){
                $resRule = $this->getProductRule($res[0]['name_id2'],'id');
                $result[0]['productRule']['name_id2'] = $resRule[0];
            }
            if($res[0]['name_id3'] > 0){
                $resRule = $this->getProductRule($res[0]['name_id3'],'id');
                $result[0]['productRule']['name_id3'] = $resRule[0];
            }
        }
        return $this->arrayData('','',$result,'success');
    }
    /**
     * 获取品规名或品规值信息
     * @param $id int 要查找的值
     * @param $type string 要根据查找的类型(id|pid)
     * @return array()
     * User: 梁伟
     * Date: 2016/8/26
     * Time: 14:23
     */
    public function getProductRule($id = 0,$type = 'pid')
    {
        if($type == 'id'){
            if($id < 1){
                return $this->arrayData('参数错误','/category/list','','error');
            }
            $where = ' id=:id: ';
        }else{
            $where = ' pid=:id: ';
        }
        //查询条件
        $table = '\Shop\Models\BaiyangProductRule';
        $data['id'] = isset($id)?$id:0;
        $selections = 'id,name,pid,is_main,sort';
        $result = BaiyangProductRuleData::getInstance()->select($selections,$table,$data,$where);
        return $result;
    }

    /**
     * 根据分类ID获取品规信息
     * @param id  int, //分类ID
     * @return array()
     * User: 梁伟
     * Date: 2016/8/26
     * Time: 10:51
     */
    public function getCategoryProductRule($id)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangCategoryProductRule';
        $data = array('id' => $id);
        $where = ' category_id=:id: ';
        $result = BaiyangCategoryProductRuleData::getInstance()->select('id,category_id,name_id,name_id2,name_id3',$table,$data,$where);
        return $result;
    }

    /**
     * 新增一个分类信息
     * @param array(
       )
     * @return bool 是否添加成功
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:58
     */
    public function insertCategory($param)
    {
        if(!isset($param['category_name']) || empty(trim($param['category_name']))){
            return $this->arrayData('必须填写分类名称','','','error');
        }
        //添加分类信息
        $table = '\Shop\Models\BaiyangCategory';
        $CategoryData = BaiyangCategoryData::getInstance();

        //判断父分类信息的级别
        if((int)$param['pid']){
            $level = $CategoryData->select('id,level,category_path',$table,array('id'=>(int)$param['pid']),' id=:id: ');
        }
        $category_path  =   isset($level[0]["category_path"])?$level[0]["category_path"]."/":'';
        $data['category_name'] = $param['category_name'];
        $data['alias'] = trim($param['alias']);
        $data['pid'] = (int)$param['pid'];
        $data['level'] = isset($level)?$level[0]['level']+1:1;
        $data['has_child'] = 0;
        $data['meta_title'] = $param['meta_title'];
        $data['meta_keyword'] = $param['meta_keyword'];
        $data['meta_description'] = $param['meta_description'];
        $data['add_time'] = time();
        $category = $CategoryData->insert($table,$data,true);;
        if(empty($category)){
            return $this->arrayData('添加失败','','','error');
        }else{
            $CategoryData->update('category_path=:category_path:',$table,array('category_path'=>$category_path.$category,'id'=>$category),' id=:id: ');
            //更新缓存
            $categoryCate = UpdateCacheSkuData::getInstance();
            $categoryCate->updateCategory($category);
            $categoryCate->updateSonCategory($category);
            return $this->arrayData('添加成功','/category/list','','success');
        }
    }

    /**
     * 修改分类信息
     * @param array(
        )
     * @return bool
     * User: 梁伟
     * Date: 2016/8/24
     * Time: 14:58
     */
    public function updateCategory($param)
    {
        if(!isset($param['id']) || empty((int)$param['id'])){
            return $this->arrayData('参数丢失','','','error');
        }
        if(!isset($param['category_name']) || empty(trim($param['category_name']))){
            return $this->arrayData('必须填写分类名称','','','error');
        }
        //修改分类信息
        $table = '\Shop\Models\BaiyangCategory';
        $CategoryData = BaiyangCategoryData::getInstance();

        $data['category_name']      =   trim($param['category_name']);
        $data['alias']              =   trim($param['alias']);
//        $data['pid']                =   (int)$param['pid'];
        $data['meta_title']         =   $param['meta_title'];
        $data['meta_keyword']       =   $param['meta_keyword'];
        $data['meta_description']   =   $param['meta_description'];
        $data['id']                 =   $param['id'];
        $columStr = "category_name=:category_name:,alias=:alias:,meta_title=:meta_title:,meta_keyword=:meta_keyword:,meta_description=:meta_description:";
        $res = $CategoryData->update($columStr,$table,$data,"id=:id:");
        if($res){
            //更新缓存
            $categoryCate = UpdateCacheSkuData::getInstance();
            $categoryCate->updateCategory((int)$param['id']);
            $categoryCate->updateSonCategory((int)$param['id']);
            return $this->arrayData('修改成功','/category/edit?id='.(int)$param['id'],'','success');
        }else{
            return $this->arrayData('修改失败','','','error');
        }
    }

    /**
     * @desc 启用|禁用 切换
     * @param $id int 要切换的分类ID
     * User: 梁伟
     * Date: 2016/9/5
     * Time: 10:13
     */
    public function isSwitch($id,$is_enable)
    {
        if(empty((int)$id)){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangCategory';
        $data['id'] = (int)$id;
        $is_enable = (int)$is_enable;
        if(!$is_enable){
            $data['is_enable']   = 1;
            $succ = "禁用成功";
            $err = "禁用失败";
            $goodsCountRrr = '禁止前，请将该类目的商品合理的分到其他类目。';
        }else{
            $data['is_enable']   = 0;
            $succ = "启用成功";
            $err = "启用失败";
        }
        $where = " id=:id: ";
        $columStr = "is_enable=:is_enable:";

        if($data['is_enable'] == 1){
            $categoryIdArray  = self::getCategorySonsByCids($id);
            $categoryIds = is_array($categoryIdArray) && !empty($categoryIdArray)? implode(',',$categoryIdArray) : $id;

            $goodsCount = BaseData::getInstance()->countData([
                'table'=>'\Shop\Models\BaiyangGoods',

                'where'=>'where category_id in ('.$categoryIds.')'
            ]);
            if($goodsCount <= 0){
                $res = BaiyangCategoryData::getInstance()->update($columStr,$table,$data,$where);
            }else{
                return $this->arrayData($goodsCountRrr,'','','error');
            }
        }else{
            $res = BaiyangCategoryData::getInstance()->update($columStr,$table,$data,$where);
        }



        if($res){
            //更新缓存
            UpdateCacheSkuData::getInstance()->updateCategory((int)$id);
            return $this->arrayData($succ,'','','success');
        }else{
            return $this->arrayData($err,'','','error');
        }
    }

    /**
     * @desc 获取分类所有父级信息
     * @param $id int 要查找的分类ID
     * User: 梁伟
     * Date: 2016/8/23
     * Time: 17:37
     */
    public function getFatherCategory($id)
    {
        $id = (int)$id;
        $res = $this->getFatherCategoryOne($id);
        ksort($res);
        return $res;
    }
    /**
     * 递归获取分类信息
     */
    public function getFatherCategoryOne($id,$arr = array()){
        $catagory = $this->getCategory($id,'id',false);
        $arr[$catagory['data'][0]['level']]['id'] = $catagory['data'][0]['id'];
        $arr[$catagory['data'][0]['level']]['category_name'] = $catagory['data'][0]['category_name'];
        if($catagory['data'][0]['pid'] > 0){
            return $arr = $this->getFatherCategoryOne($catagory['data'][0]['pid'],$arr);
        }else{
            return $arr;
        }
    }

    public function getCategorySonsByCid($cid,$arr = array())
    {
        $table = '\Shop\Models\BaiyangCategory';
        $data = array('pid' => $cid);
        $where = ' pid = :pid: ';
        if(empty($arr)){
            $arr[] = $cid;
        }
        $ret = BaiyangCategoryData::getInstance()->select('id',$table,$data,$where);
        if($ret){
            foreach ($ret as $item){
                $arr[] = $item['id'];
                $arr = self::getCategorySonsByCid($item['id'],$arr);
            }
        }
        return $arr;
    }

    public function getCategorySonsByCids($cids)
    {
        $cid_box = explode(',',$cids);
        $array = [];
        foreach ($cid_box as $cid){
           $arr =  self::getCategorySonsByCid($cid);
           array_map(function ($v) use (&$array){
               $array[] = $v;
           },$arr);
        }
        return array_unique($array);
    }
}
