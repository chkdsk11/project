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

use Shop\Models\BaiyangCategory;
use Shop\Services\BaseService;
use Shop\Datas\BaseData;
use Shop\Datas\BaiyangFrontCateData;
use Shop\Datas\BaiyangCategoryData;
use Shop\Datas\BaiyangProductRuleData;
use Shop\Datas\BaiyangCategoryProductRuleData;
use Shop\Datas\BaiyangSkuData;
use Shop\Datas\UpdateCacheSkuData;

class FrontCategoryService extends BaseService
{
	//必须声明此静态属性，单例模式下防止内存地址覆盖
	protected static $instance=null;
	private $tables = [
		'pc'    =>  '\Shop\Models\BaiyangMainCategory',
		'app'   =>  '\Shop\Models\BaiyangAppCategory'
	];
	
	public function getProType ()
	{
		//查询条件
		$table = '\Shop\Models\ProductsTypes';
		$selections = '*';
		$result = BaiyangCategoryData::getInstance()->select($selections,$table);
		return $result;
	}
	
	/**
	 * 获取分类信息列表
	 * @return array()
	 * User: 梁伟
	 * Date: 2016/8/24
	 * Time: 14:21
	 */
	public function categoryLists($param)
	{
		if (!empty($param))
		{
			$table = $this->tables[$param['type']];
			if ($param['type'] == 'pc')
			{
				$selections = 'id,category_name,pid,level,enable, sort, category_link, id as cid';
			}elseif($param['type'] = 'app'){
				$selections = 'category_id as id, sort, category_name,parent_id as pid,level,enable, product_category_id as cid';
			}
		}else{
			return $this->arrayData('参数错误',"/frontcategory/{$param}list",'','error');
		}
		//获取数量
		#$cateNums = (BaiyangFrontCateData::getInstance())->getCateNums();
		#$temp = [];
		#foreach ($cateNums as $item)
		#{
		#if (!in_array($item['category_id'], array_keys($temp)))
		#	{
		#		$temp[$item['category_id']] = (int)$item['nums'];
		#	}else{
		#		$temp[$item['category_id']] += (int)$item['nums'];
		#	}
		#}
		$result = BaiyangFrontCateData::getInstance()->select($selections, $table,'','','order by sort desc');
		#$cids = array_keys($temp);
		#foreach ($result as &$item)
		#{
		#	$cid = $item['cid'];
		#	if (in_array($cid, $cids))
		#	{
		#		$numArr = ['nums'=>$temp[$cid]];
		#	}else{
		#		$numArr = ['nums'=>0];
		#	}
		#	$item = array_merge($item, $numArr);
		#}
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
	 * 获取单个或同一父亲的分类信息
	 * @param $id int 要查找的值
	 * @param $type string 要根据查找的类型(id|pid)
	 * @param $act bool 是否获取品规信息 (默认true)
	 * @return array()
	 * User: 梁伟
	 * Date: 2016/8/24
	 * Time: 14:58
	 */
	public function getCategoryApp($id = 0)
	{
		//查询条件
		$table = '\Shop\Models\BaiyangAppCategory';
		if($id < 1){
			return $this->arrayData('参数错误','/frontcate/list','','error');
		}
		$where = ' category_id =:id:';
		$data['id'] = (int)$id;
		$selections = 'category_id id,category_name,parent_id pid,level,sort, main_category,picture,image, product_type_id, product_category_id,nickname,seo_title,seo_keywords,seo_description,enable,category_path';
		$result = BaiyangCategoryData::getInstance()->select($selections,$table,$data,$where);
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
		$table = '\Shop\Models\BaiyangAppCategory';
		$CategoryData = BaiyangFrontCateData::getInstance();
		
		//判断父分类信息的级别
		if((int)$param['pid']){
			$level = $CategoryData->select('category_id,level,category_path',$table,array('category_id'=>(int)$param['pid']),' category_id=:category_id: ');
		}
		$category_path  =   isset($level[0]["category_path"])?$level[0]["category_path"]:'';
		$data['category_name'] = $param['category_name'];
		$data['nickname'] = $param['nickname'];
		$data['category_path'] = $category_path;
		$data['parent_id'] = (int)$param['pid'];
		$data['level'] = isset($level)?$level[0]['level']+1:1;
		$data['picture'] = isset($param['picture'])?$param['picture']:'';
		$data['image'] = isset($param['image'])?$param['image']:'';
		$data['sort'] = $param['sort'];
		$data['enable'] = $param['enable'];
		$data['main_category'] = $param['main_category'];
		#$data['has_child'] = 0;
		$data['product_type_id'] = $param['product_type_id'];
		$data['product_category_id'] = $param['product_category_id'];
		$data['seo_title'] = $param['seo_title'];
		$data['seo_keywords'] = $param['seo_keywords'];
		$data['seo_description'] = $param['seo_description'];
		$data['class_icon'] = isset($param['class_icon'])?$param['class_icon']:'';
		#$data['add_time'] = time();
		$category = $CategoryData->insert($table,$data,true);
		if(empty($category)){
			return $this->arrayData('添加失败','','','error');
		}else{
			if ($category_path == '')
			{
				$category_path = $category;
			}else{
				$category_path = $category_path.'-'.$category;
			}
			$CategoryData->update('category_path=:category_path:',$table,array('category_path'=>$category_path,'category_id'=>$category),' category_id=:category_id: ');
			//更新缓存
			$categoryCate = UpdateCacheSkuData::getInstance();
			$categoryCate->updateCategory($category);
			$categoryCate->updateSonCategory($category);
			return $this->arrayData('添加成功','/frontcate/applist','','success');
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
		$table = '\Shop\Models\BaiyangAppCategory';
		$CategoryData = BaiyangCategoryData::getInstance();
		if((int)$param['id']){
			$level = $CategoryData->select('category_id,level,category_path',$table,array('category_id'=>(int)$param['id']),' category_id=:category_id: ');
		}
		#$category_path  =   isset($level[0]["category_path"])?$level[0]["category_path"]:'';
		#return $category_path;
		#$data['category_path'] = $category_path;
		$data['category_name'] = $param['category_name'];
		$data['nickname'] = $param['nickname'];
		$data['picture'] = isset($param['picture'])?$param['picture']:'';
		$data['image'] = isset($param['image'])?$param['image']:'';
		$data['sort'] = $param['sort'];
		$data['enable'] = $param['enable'];
		$data['main_category'] = $param['main_category'];
		#$data['has_child'] = 0;
		$data['product_type_id'] = $param['product_type_id'];
		$data['product_category_id'] = $param['product_category_id'];
		$data['seo_title'] = $param['seo_title'];
		$data['seo_keywords'] = $param['seo_keywords'];
		$data['seo_description'] = $param['seo_description'];
		$data['class_icon'] = isset($param['class_icon'])?$param['class_icon']:'';
		$data['id']    =   $param['id'];
		$columStr = "category_name=:category_name:,nickname=:nickname:,sort=:sort:,enable=:enable:,main_category=:main_category:,
		product_type_id=:product_type_id:,seo_title=:seo_title:,seo_keywords=:seo_keywords:,seo_description=:seo_description:,
		class_icon=:class_icon:,picture=:picture:,image=:image:,product_category_id=:product_category_id:";
		$res = $CategoryData->update($columStr,$table,$data,"category_id=:id:");
		if($res){
			//更新缓存
			$categoryCate = UpdateCacheSkuData::getInstance();
			$categoryCate->updateCategory((int)$param['id']);
			$categoryCate->updateSonCategory((int)$param['id']);
			return $this->arrayData('修改成功','/frontcate/applist','success');
		}else{
			return $this->arrayData('修改失败','','','error');
		}
	}

    /**
     * 更新排序
     * @param $param
     * @return array
     */
    public function editSort($param)
    {
        if(!isset($param['id']) || empty((int)$param['id'])){
            return $this->arrayData('参数丢失','','','error');
        }

        //修改分类信息
        $table = '\Shop\Models\BaiyangAppCategory';
        $CategoryData = BaiyangCategoryData::getInstance();
        $data['sort'] = $param['sort'];
        $data['id']    =   $param['id'];
        $columStr = "sort=:sort:";
        $res = $CategoryData->update($columStr,$table,$data,"category_id=:id:");
        if($res){
            //更新缓存
            $categoryCate = UpdateCacheSkuData::getInstance();
            $categoryCate->updateCategory((int)$param['id']);
            $categoryCate->updateSonCategory((int)$param['id']);
            return $this->arrayData('修改成功','/frontcate/applist','success');
        }else{
            return $this->arrayData('修改失败','','','error');
        }
    }

	/**
	 * @desc 启用|禁用 切换
	 * @param $param array 要切换的分类ID
	 */
	public function isSwitch(array $param)
	{
		if(empty((int)$param['id'])){
			return $this->arrayData('参数错误','','','error');
		}
		if ($param['type'] == 'pc')
		{
			$table = '\Shop\Models\BaiyangMainCategory';
			$where = "id=:id: ";
		}else{
			$table = '\Shop\Models\BaiyangAppCategory';
			$where = "category_id=:id: ";
		}
		$data['id'] = (int)$param['id'];
		$is_enable = (int)$param['is_enable'];
		if($is_enable){
			$data['is_enable']   = 0;
			$succ = "禁用成功";
			$err = "禁用失败";
		}else{
			$data['is_enable']   = 1;
			$succ = "启用成功";
			$err = "启用失败";
		}
		$columStr = "enable=:is_enable:";
		$res = (BaiyangFrontCateData::getInstance())->update($columStr,$table,$data,$where);
		if($res){
			//更新缓存
			UpdateCacheSkuData::getInstance()->updateCategory((int)$param['id']);
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
