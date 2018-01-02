<?php
/**
 * Created by PhpStorm.
 * @author 梁伟
 * @date: 2016/8/16
 * @Explain:    使用redis库8
 * @Explain：    缓存数据,键：spu_id_5     一个spu信息
 */

namespace Shop\Services;
use Shop\Datas\BaiyangSkuData;
use Shop\Datas\BaiyangSpuData;
use Shop\Datas\BaiyangGoodsData;
use Shop\Datas\BaseData;
use Shop\Services\BaseService;
use Shop\Datas\BaiyangSkuDefaultData;
use Shop\Datas\UpdateCacheSkuData;

class SpuService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;

    /**
     * 获得spu列表信息
     * @author 梁伟
     * @date: 2016/9/5
     */
    public function getAllSpu($param)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangSpu';
        $where = '1=1';
        $data   =   array();
        //组织where语句
        if(isset($param['name']) && $param['name'] != ''){
            if((int)$param['name'] > 0){
                $where .= " AND spu_id = :name:";
                $data['name']   =   (int)$param['name'];
            }else{
                $where .= " AND spu_name LIKE :name:";
                $data['name']   =   '%'.$param['name'].'%';
            }
        }
        if(isset($param['category'])){
            $where .= " AND category_id in(".$param['category'].") ";
//            $data['category']   =   $param['category'];
        }
        $BaiyangSpuData = BaiyangSpuData::getInstance();
        //总记录数
        $counts = $BaiyangSpuData->count($table,$data,$where);
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        //分页
        $pages['page'] = (int)isset($param['page'])?$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url'] = $param['url'];
        $page = $this->page->pageDetail($pages);

        $selections = 'spu_id,spu_name,category_id,update_time';
        $where .= 'order by update_time desc limit '.$page['record'].','.$page['psize'];
        $result = $BaiyangSpuData->select($selections,$table,$data,$where);
        $return = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page']
        ];
        return $return;
    }

    /**
     * @ 获得一个spu信息
     * @param $in int SPU ID
     * return array() spu信息
     * @author 梁伟
     * @date: 2016/9/5
     */
    public function getSpuOne($id)
    {
        if((int)$id <= 0){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangSpu';
        $selections = 'spu_id,spu_name,category_id,brand_id,shop_id, drug_type,goods_image,big_path,small_path,freight_temp_id,category_path';
        $data['id'] =   (int)$id;
        $where = 'spu_id=:id:';
        $result = BaiyangSpuData::getInstance()->select($selections,$table,$data,$where);
        return $this->arrayData('','',$result,'success');
    }

    /**
     * 添加一个spu信息
     * @param
     * @author 梁伟
     * @date: 2016/9/7
     */
    public function addSpu($param)
    {
        if(!isset($param['shop_category']) || !is_array($param['shop_category']) || count($param['shop_category']) < 3){
            return $this->arrayData('请选择分类','','','error');
        }
        if(!isset($param['spu_name']) || empty($param['spu_name'])){
            return $this->arrayData('请填写spu名称','','','error');
        }
        if(!isset($param['brand_id']) || $param['brand_id'] <= 0){
            return $this->arrayData('请选择品牌','','','error');
        }
        if(!isset($param['drug_type']) || $param['drug_type'] <= 0){
            return $this->arrayData('请选择药物类型','','','error');
        }
        $data['spu_name'] = trim($param['spu_name']);
        $categoryID = end($param['shop_category']);
        $data['category_path'] = implode('/',$param['shop_category']);
        $data['category_id'] = $categoryID;
        $data['brand_id'] = (int)$param['brand_id'];
        $data['shop_id'] = (int)$param['shop_id'];
//        $drug_type = implode(',',$param['spu_tag']);
//        $data['spu_tag'] = $drug_type;
        $data['drug_type'] = (int)$param['drug_type'];
        $data['add_time'] = time();
        $data['update_time'] = time();
        $table = '\Shop\Models\BaiyangSpu';
        $res = BaiyangSpuData::getInstance()->insert($table,$data,true);
        if($res){
            return $this->arrayData('添加成功','/spu/edit?id='.$res,$res,'success');
        }else{
            return $this->arrayData('添加失败','',$res,'error');
        }

    }

    /**
     * 修改spu信息
     * @param
     * @author 梁伟
     * @date: 2016/9/7
     */
    public function updateSpu($param)
    {
        if(!(int)$param['id']){
            return $this->arrayData('参数错误','','','error');
        }
        if(!isset($param['spu_name']) || empty($param['spu_name'])){
            return $this->arrayData('请填写spu名称','','','error');
        }
        if(!isset($param['brand_id']) || $param['brand_id'] <= 0){
            return $this->arrayData('请选择品牌','','','error');
        }
        if(!isset($param['shop_id']) || $param['shop_id'] <= 0){
            return $this->arrayData('请选择品牌','','','error');
        }
        if(!isset($param['drug_type']) || $param['drug_type'] <= 0){
            return $this->arrayData('请选择药物类型','','','error');
        }
        $BaiyangSpuData = BaiyangSpuData::getInstance();
        $table = '\Shop\Models\BaiyangSpu';
        $where = "spu_id=:spu_id:";
        $data['spu_id'] = (int)$param['id'];
        $columStr = "
            spu_name=:spu_name:,
            shop_id=:shop_id:,
            drug_type=:drug_type:,
            brand_id=:brand_id:,
            update_time=:update_time:";
        //判断是否修改分类
        $act = false;
        $goods_category_path = '';
        $goods_category_id = 0;
        if(isset($param['shop_category'][2]) && $param['shop_category'][2]){
            $spu = $BaiyangSpuData->select('category_id',$table,$data,$where);
            if(!$spu) return $this->arrayData('信息不存在','','','error');
            if($spu[0]['category_id']!=$param['shop_category'][2]){
                $act = true;
                $data['category_id'] = $param['shop_category'][2];
                $data['category_path'] = implode('/',$param['shop_category']);
                $goods_category_path = implode(',',$param['shop_category']);
                $goods_category_id = $data['category_id'];
                $columStr .= ',category_id=:category_id:,category_path=:category_path:';
            }
        }

        $data['spu_name'] = trim($param['spu_name']);
        $data['brand_id'] = (int)$param['brand_id'];
        $data['shop_id'] = (int)$param['shop_id'];
        $data['drug_type'] = (int)$param['drug_type'];
        $data['update_time'] = time();
        $res = $BaiyangSpuData->update($columStr,$table,$data,$where);
        if($res){
        	//更新所有sku为相同shop_id,brand_id
	        $res = $BaiyangSpuData->updateSkuShopOfSpu($param['id'], $param['shop_id'],$param['brand_id']);
            $success = 'error';
            if($act){
                //清空多品规和参数信息
                $BaiyangSpuData->update('rule_value_id="",attribute_value_id="",rule_value0=0,rule_value1=0,rule_value2=0,category_id=:category_id:,category_path=:category_path:','\Shop\Models\BaiyangGoods',['spu_id'=>(int)$param['id'],'category_id'=>$goods_category_id,'category_path'=>$goods_category_path],$where);
                $success = 'success';
            }
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            $UpdateCacheSkuData->updateSpu((int)$param['id']);
            $UpdateCacheSkuData->updateSkuRules((int)$param['id']);
            $this->updateEsSearch(0,$param['id']);
            return $this->arrayData('修改成功','/spu/edit?id='.(int)$param['id'],$res,$success);
        }else{
            return $this->arrayData('修改失败','',$res,'error');
        }
    }

    /**
     * @desc 判断spu名称是否重复
     * @param string name spu名称
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/9/18
     */
    public function isExitst($param)
    {
        if( (!isset($param['name']) || empty($param['name'])) && (!isset($param['id']) || empty($param['id'])) ){
            return 'error';
        }
        $table = '\Shop\Models\BaiyangSpu';
        $data['name'] = $param['name'];
        $data['id'] = (int)$param['id'];
        $where = 'spu_name=:name: and spu_id!=:id:';
        $counts = BaiyangSpuData::getInstance()->count($table,$data,$where);
        if( $counts > 0 ){
            return true;
        }else{
            return false;
        }
    }
    /************************************ sku 默认值管理 **********************************/
    /**
     * @desc 判断spu下是否有默认值
     * @param int $id spu id
     * @return int 查询条数
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function isSkuDefault($id)
    {
        if($id <= 0){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangSkuDefault';
        $data['id'] = (int)$id;
        $where = 'spu_id=:id: limit 1';
        return BaiyangSkuDefaultData::getInstance()->count($table,$data,$where);
    }
    /**
     * @desc 获取sku默认值
     * @param int $id spu id
     * @return array()
     * @author 梁伟
     * @date: 2016/9/18
     */
    public function getSkuDefault($id)
    {
        if($id <= 0){
            return $this->arrayData('参数错误','','','error');
        }
        $table = '\Shop\Models\BaiyangSkuDefault';
        $data['id'] = $id;
        $where = 'spu_id=:id: limit 1';
        $selections = 'info_id,spu_id,sku_alias_name,sku_pc_name,sku_mobile_name,sku_pc_subheading,sku_mobile_subheading,sku_batch_num,barcode,period,manufacturer,sku_weight,sku_bulk,attribute_value_id,sku_video,bind_gift,meta_title,meta_keyword,meta_description,ad_id_pc,ad_id_mobile,sku_detail_pc,sku_detail_mobile,specifications,sku_usage,sku_label,prod_name_common';
        $default = BaiyangSkuDefaultData::getInstance()->select($selections,$table,$data,$where);

        $default[0]['sku_detail_pc'] =  !empty($default)?$default[0]['sku_detail_pc']:'';
        $default[0]['sku_detail_mobile'] = !empty($default)?$default[0]['sku_detail_mobile']:'';
        $default[0]['ad_id_pc'] =  !empty($default)?$default[0]['ad_id_pc']:'';
        $default[0]['ad_id_mobile'] = !empty($default)?$default[0]['ad_id_mobile']:'';

//        $this->updateEsSearch(0,$id);
        return $this->arrayData('','',$default,'success');
    }
	
	/**
	 * @remark 导入商品列表
	 * @param $fileName=string 文件路径
	 * @return array
	 * @author 罗毅庭
	 */
	public function import($fileName,$import_type)
	{
		$data = $this->excel->importGoodsExcel($fileName,'xlsx');
		//验证相关必填参数
		if($import_type == 1) {
            $spudata = array();
            $goodsdata = array();
			$saleArr = array(
				'下架' => 0,
				'上架' => 1,
			);
			$ynArr = array(
				'是' => 1,
				'否' => 0,
			);
			foreach ($data as $val) {
                             $spudata = array();
                             $goodsdata = array();
				//spu编号
				if (is_null($val['spu编号'])) {
					return $this->arrayData('导入失败，spu编号列有空值！', '', '', 'error');
				} else {
					$spudata['spu_id'] = $val['spu编号'];
					$goodsdata['spu_id'] = $val['spu编号'];
				}
				//spu通用名
				if (is_null($val['spu通用名（必填）'])) {
					return $this->arrayData('导入失败，spu通用名（必填）列有空值！', '', '', 'error');
				} else {
					$spudata['spu_name'] = $val['spu通用名（必填）'];
				}
				//店铺
				if (is_null($val['店铺（必填）'])) {
					return $this->arrayData('导入失败，店铺（必填）列有空值！', '', '', 'error');
				} else {
					$table = '\Shop\Models\BaiyangSpu';
					$where = 'spu_id=:spu_id:';
					$data = array(
						'spu_id' => $goodsdata['spu_id'],
					);
					$selections = 'shop_id';
					$result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
					if(!empty($result) && ($result!==false) && ((int)$result[0]['shop_id']!=$val['店铺（必填）'])) {return $this->arrayData('导入失败，店铺不一致！', '', '', 'error');}
					$goodsdata[ 'supplier_id' ] = $val[ '店铺（必填）' ];
					$spudata[ 'shop_id' ] = $val[ '店铺（必填）' ];
				}
				//运费模板
				if (is_null($val['运费模板（必填）'])) {
					return $this->arrayData('导入失败，运费模板（必填）列有空值！', '', '', 'error');
				} else {
					$goodsdata['pc_freight_temp_id'] = $val['运费模板（必填）'];
				}
				//品牌
				if (is_null($val['品牌（必填）'])) {
					return $this->arrayData('导入失败，品牌（必填）列有空值！', '', '', 'error');
				} else {
					$goodsdata['brand_id'] = $val['品牌（必填）'];
					$spudata['brand_id'] = $val['品牌（必填）'];
				}
                                $goodsdata['category_path'] = 0;
				//三级分类
				if (is_null($val['三级分类（必填）'])) {
					return $this->arrayData('导入失败，三级分类（必填）列有空值！', '', '', 'error');
				} else {
                                    $categoryData = BaseData::getInstance()->getData([
                                        'table'=>'\Shop\Models\BaiyangCategory as c',
                                        'column'=>'c.category_path',
                                        'where'=>"where c.id = '{$val['三级分类（必填）']}'"
                                         ],true);
                                        if($categoryData){
                                            $goodsdata['category_path'] = isset($categoryData['category_path'])?str_replace('/', ",",$categoryData['category_path']):0;
					    $spudata['category_path'] = isset($categoryData['category_path'])?$categoryData['category_path']:0;
                                        }
					$goodsdata['category_id'] = $val['三级分类（必填）'];
					$spudata['category_id'] = $val['三级分类（必填）'];
				}
				if (is_null($val['药物类型（必填）'])) {
					return $this->arrayData('导入失败，药物类型（必填）列有空值！', '', '', 'error');
				} else {
					$goodsdata['medicine_type'] = $val['药物类型（必填）'];
					$spudata['drug_type'] = $val['药物类型（必填）'];
				}
				if (is_null($val['参数名称1（必填）'])) {
					return $this->arrayData ('导入失败，参数名称1（必填）列有空值！', '', '', 'error');
				}else{
					if (is_null($val['参数属性值1（必填）']))
					{
						return $this->arrayData('导入失败，参数属性值1（必填）列有空值！', '', '', 'error');
					}
					$table = '\Shop\Models\BaiyangAttrName';
					$where = 'attr_name=:attr_name: and category_id=:category_id:';
					$data = array(
						'attr_name' => $val['参数名称1（必填）'],
						'category_id' => (int)$goodsdata['category_id'] ? $goodsdata['category_id'] : 0,
					);
					$selections = 'id';
					$result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
					if(empty($result))
					{
						$n1data['category_id'] = (int)$goodsdata['category_id'] ? $goodsdata['category_id'] : 0;
						$n1data['attr_name'] = $val['参数名称1（必填）'];
						$nid = BaiyangGoodsData::getInstance()->insert($table, $n1data, true);
						$v1data['attr_name_id'] = $nid;
						$v1data['attr_value'] = $val['参数属性值1（必填）'];
						$table = '\Shop\Models\BaiyangAttrValue';
						$vid = BaiyangGoodsData::getInstance()->insert($table, $v1data, true);
						$goodsdata['attribute_value_id'] = $nid.':'.$vid;
					}
				}
				
				if (!is_null($val['参数名称2']))
				{
					if (is_null($val['参数属性值2']))
					{
						return $this->arrayData('导入失败，参数属性值2列有空值！', '', '', 'error');
					}
					$table = '\Shop\Models\BaiyangAttrName';
					$where = 'attr_name=:attr_name: and category_id=:category_id:';
					$data = array(
						'attr_name' => $val['参数名称2（必填）'],
						'category_id' => (int)$goodsdata['category_id'] ? $goodsdata['category_id'] : 0,
					);
					$selections = 'id';
					$result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
					if(empty($result))
					{
						$n2data[ 'category_id' ] = (int)$goodsdata[ 'category_id' ] ? $goodsdata[ 'category_id' ] : 0;
						$n2data[ 'attr_name' ] = $val[ '参数名称2' ];
						$nid = BaiyangGoodsData::getInstance ()->insert ($table, $n2data, true);
						$v2data[ 'attr_name_id' ] = $nid;
						$v2data[ 'attr_value' ] = $val[ '参数属性值2' ];
						$table = '\Shop\Models\BaiyangAttrValue';
						$vid = BaiyangGoodsData::getInstance ()->insert ($table, $v2data, true);
						$goodsdata[ 'attribute_value_id' ] = ',' . $nid . ':' . $vid;
					}
				}
				
				if (!is_null($val['参数名称3']))
				{
					if (is_null($val['参数属性值3']))
					{
						return $this->arrayData('导入失败，属性值3列有空值！', '', '', 'error');
					}
					$table = '\Shop\Models\BaiyangAttrName';
					$where = 'attr_name=:attr_name: and category_id=:category_id:';
					$data = array(
						'attr_name' => $val['参数名称3（必填）'],
						'category_id' => (int)$goodsdata['category_id'] ? $goodsdata['category_id'] : 0,
					);
					$selections = 'id';
					$result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
					if(empty($result))
					{
						$n3data[ 'category_id' ] = (int)$goodsdata[ 'category_id' ] ? $goodsdata[ 'category_id' ] : 0;
						$n3data[ 'attr_name' ] = $val[ '参数名称3' ];
						$nid = BaiyangGoodsData::getInstance ()->insert ($table, $n3data, true);
						$v3data[ 'attr_name_id' ] = $nid;
						$v3data[ 'attr_value' ] = $val[ '参数属性值3' ];
						$table = '\Shop\Models\BaiyangAttrValue';
						$vid = BaiyangGoodsData::getInstance ()->insert ($table, $v3data, true);
						$goodsdata[ 'attribute_value_id' ] = ',' . $nid . ':' . $vid;
					}
				}
				
				if (!is_null($val['属性1']))
				{
					if (is_null($val['属性值1']))
					{
						return $this->arrayData('导入失败，属性值1列有空值！', '', '', 'error');
					}
                                        $ruleData = BaseData::getInstance()->getData([
                                        'table'=>'\Shop\Models\BaiyangCategoryProductRule as c',
                                        'column'=>'fc.id',
                                        'join'=>"inner join \Shop\Models\BaiyangProductRule as fc on c.name_id = fc.id and fc.pid=0 ",
                                        'where'=>"where fc.name = '{$val['属性1']}' and c.category_id={$goodsdata['category_id']}"
                                         ],true);
                                        $table = '\Shop\Models\BaiyangProductRule';
                                        if($ruleData){
                                            $vv1data['pid'] = $ruleData['id'];
                                            $vv1data['name'] = $val['属性值1'];
                                            $vv1data['add_time'] = time();
                                            $vid = BaseData::getInstance()->insert($table, $vv1data, true);
                                            $goodsdata['rule_value0'] = $vid;
                                            $goodsdata['rule_value_id'] = $vid;
                                        }else{
                                            $nn1data['name'] = $val['属性1'];
                                            $nn1data['add_time'] = time();
                                            $nid = BaiyangGoodsData::getInstance()->insert($table, $nn1data, true);
                                            $vv1data['pid'] = $nid;
                                            $vv1data['name'] = $val['属性值1'];
                                            $vv1data['add_time'] = time();
                                            $vid = BaiyangGoodsData::getInstance()->insert($table, $vv1data, true);
                                            $goodsdata['rule_value0'] = $vid;
                                            $goodsdata['rule_value_id'] = $vid;
                                        }
				}else{
                                    $goodsdata['rule_value_id'] = "0";
                                }
				if (!is_null($val['属性2']))
				{
					if (is_null($val['属性值2']))
					{
						return $this->arrayData('导入失败，属性值2列有空值！', '', '', 'error');
					}
					$table = '\Shop\Models\BaiyangProductRule';
                                         $ruleData = BaseData::getInstance()->getData([
                                        'table'=>'\Shop\Models\BaiyangCategoryProductRule as c',
                                        'column'=>'fc.id',
                                        'join'=>"inner join \Shop\Models\BaiyangProductRule as fc on c.name_id2 = fc.id and fc.pid=0 ",
                                        'where'=>"where fc.name = '{$val['属性2']}' and c.category_id={$goodsdata['category_id']}"
                                         ],true);
                                        $table = '\Shop\Models\BaiyangProductRule';
                                        if($ruleData){
                                            $vv1data['pid'] = $ruleData['id'];
                                            $vv1data['name'] = $val['属性值2'];
                                            $vv1data['add_time'] = time();
                                            $vid = BaseData::getInstance()->insert($table, $vv1data, true);
                                            $goodsdata['rule_value1'] = $vid;
                                            $goodsdata['rule_value_id'] = $goodsdata['rule_value_id']."+".$vid;
                                        }else{
                                            $nn2data['name'] = $val['属性2'];
                                            $nn2data['add_time'] = time();
                                            $nid = BaiyangGoodsData::getInstance()->insert($table, $nn2data, true);
                                            $vv2data['pid'] = $nid;
                                            $vv2data['name'] = $val['属性值2'];
                                            $vv2data['add_time'] = time();
                                            $vid = BaiyangGoodsData::getInstance()->insert($table, $vv2data, true);
                                            $goodsdata['rule_value1'] = $vid;
                                            $goodsdata['rule_value_id'] = $goodsdata['rule_value_id']."+".$vid;
                                        }
				}else{
                                    $goodsdata['rule_value_id'] = $goodsdata['rule_value_id']."+"."0";
                                }
				if (!is_null($val['属性3']))
				{
					if (is_null($val['属性值3']))
					{
						return $this->arrayData('导入失败，属性值3列有空值！', '', '', 'error');
					}
					$table = '\Shop\Models\BaiyangProductRule';
                                        $ruleData = BaseData::getInstance()->getData([
                                        'table'=>'\Shop\Models\BaiyangCategoryProductRule as c',
                                        'column'=>'fc.id',
                                        'join'=>"inner join \Shop\Models\BaiyangProductRule as fc on c.name_id3 = fc.id and fc.pid=0 ",
                                        'where'=>"where fc.name = '{$val['属性3']}' and c.category_id={$goodsdata['category_id']}"
                                         ],true);
                                        $table = '\Shop\Models\BaiyangProductRule';
                                        if($ruleData){
                                            $vv1data['pid'] = $ruleData['id'];
                                            $vv1data['name'] = $val['属性值3'];
                                            $vv1data['add_time'] = time();
                                            $vid = BaseData::getInstance()->insert($table, $vv1data, true);
                                            $goodsdata['rule_value2'] = $vid;
                                            $goodsdata['rule_value_id'] = $goodsdata['rule_value_id']."+".$vid;
                                        }else{
                                        $nn3data['name'] = $val['属性3'];
					$nn3data['add_time'] = time();
					$nid = BaiyangGoodsData::getInstance()->insert($table, $nn3data, true);
					$vv3data['pid'] = $nid;
					$vv3data['name'] = $val['属性值3'];
					$vv3data['add_time'] = time();
					$vid = BaiyangGoodsData::getInstance()->insert($table, $vv3data, true);
					$goodsdata['rule_value2'] = $vid;
                                        $goodsdata['rule_value_id'] = $goodsdata['rule_value_id']."+".$vid;
                                        }
					
				}else{
                                     $goodsdata['rule_value_id'] = $goodsdata['rule_value_id']."+"."0";
                                }
				
				if (!is_null($val['skuID（非必填，系统可自动生成）'])) {
					$goodsdata['id'] = $val['skuID（非必填，系统可自动生成）'];
				}
				if (is_null($val['ERP编码（必填）'])) {
					return $this->arrayData('导入失败，ERP编码（必填）列有空值！', '', '', 'error');
				} else {
					$where = 'product_code=:product_code:';
					$data = array(
						'product_code' => $val['ERP编码（必填）'],
					);
					$table = '\Shop\Models\BaiyangGoods';
					$selections = 'id';
					$result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
					if(!empty($result)) {   return $this->arrayData('添加失败，存在相同ERP编码！', '', '', 'error'); }
					$goodsdata['product_code'] = $val['ERP编码（必填）'];
				}
				
				#if (!is_null($val['属性值2'])) {
				#	$goodsdata['attribute_value_id'] .= ',' . $val['属性值2'];
				#}
				#if (!is_null($val['属性值3'])) {
				#	$goodsdata['attribute_value_id'] .= ',' . $val['属性值3'];
				#}
				if (is_null($val['销售价（统一端）'])) {
					return $this->arrayData('导入失败，属性值3（必填）列有空值！', '', '', 'error');
				} else {
					$goodsdata['goods_price'] = $val['销售价（统一端）'];
				}
				if (is_null($val['市场价（统一端）'])) {
					return $this->arrayData('导入失败，市场价（统一端）列有空值！', '', '', 'error');
				} else {
					$goodsdata['market_price'] = $val['市场价（统一端）'];
				}
				if (is_null($val['是否为赠品（必填）'])) {
					return $this->arrayData('导入失败，是否为赠品（必填）列有空值！', '', '', 'error');
				} else {
                    if(isset($ynArr[$val['是否为赠品（必填）']])){
                        $goodsdata['gift_yes'] = $ynArr[$val['是否为赠品（必填）']];
                        $goodsdata['product_type'] = $ynArr[$val['是否为赠品（必填）']];
                    }else{
                        return $this->arrayData('导入失败，是否为赠品（必填）列不合法！', '', '', 'error');
                    }
				}
				if (is_null($val['真实库存（必填）'])) {
					return $this->arrayData('导入失败，列有空值！', '', '', 'error');
				} else {
					$goodsdata['v_stock'] = $val['真实库存（必填）'];
				}
				if (!is_null($val['虚拟库存'])) {
					$goodsdata['virtual_stock'] = $val['虚拟库存'];
				}
				if (is_null($val['是否锁定（必填）'])) {
					return $this->arrayData('导入失败，列有空值！', '', '', 'error');
				} else {
                    if(isset($ynArr[$val['是否锁定（必填）']])){
                        $goodsdata['is_lock'] = $ynArr[$val['是否锁定（必填）']];
                    }else{
                        return $this->arrayData('导入失败，是否锁定（必填）列不合法！', '', '', 'error');
                    }
				}
				if (!is_null($val['排序'])) {
					$goodsdata['sort'] = $val['排序'];
				}
				if (!is_null($val['立即上下架'])) {
                    if(isset($saleArr[$val['立即上下架']])){
                        $goodsdata['sale_timing_app'] = $saleArr[$val['立即上下架']];
                        $goodsdata['sale_timing_wap'] = $saleArr[$val['立即上下架']];
                        $goodsdata['is_on_sale'] = $saleArr[$val['立即上下架']];
                        $goodsdata['sale_timing_wechat'] = $saleArr[$val['立即上下架']];
                    }else{
                        return $this->arrayData('导入失败，立即上下架列不合法！', '', '', 'error');
                    }
				}
				//sku_id冲突，sku_id非必填，这里需要sku_id
				/* if (!is_null($val['定时上架时间']) && !is_null($val['定时上架时间'])) {
					 $param['goods-shelves'] = 2;
				 }*/
				if (is_null($val['是否为推荐商品（必填）'])) {
					return $this->arrayData('导入失败，是否为推荐商品（必填）列有空值！', '', '', 'error');
				} else {
                    if(isset($ynArr[$val['是否为推荐商品（必填）']])){
                        $goodsdata['recommend_yes'] = $ynArr[$val['是否为推荐商品（必填）']];
                        $goodsdata['is_recommend'] = $ynArr[$val['是否为推荐商品（必填）']];
                    }else{
                        return $this->arrayData('导入失败，是否为推荐商品列不合法！', '', '', 'error');
                    }
				}
				if (is_null($val['是否为热门商品（必填）'])) {
					return $this->arrayData('导入失败，是否为热门商品（必填）列有空值！', '', '', 'error');
				} else {
                    if(isset($ynArr[$val['是否为热门商品（必填）']])){
                        $goodsdata['hot_yes'] = $ynArr[$val['是否为热门商品（必填）']];
                        $goodsdata['is_hot'] = $ynArr[$val['是否为热门商品（必填）']];
                    }else{
                        return $this->arrayData('导入失败，是否为热门商品（必填）列不合法！', '', '', 'error');
                    }
				}
				if (!is_null($val['别名'])) {
					$goodsdata['sku_alias_name'] = $val['别名'];
					
				}
				if (!is_null($val['商品名称PC端'])) {
					$goodsdata['goods_name'] = $val['商品名称PC端'];
				}
				if (!is_null($val['商品副标题PC端'])) {
					$goodsdata['sku_pc_subheading'] = $val['商品副标题PC端'];
				}
				if (!is_null($val['商品名称移动端'])) {
					$goodsdata['sku_mobile_name'] = $val['商品名称移动端'];
				}
				if (!is_null($val['商品副标题移动端'])) {
					$goodsdata['sku_mobile_subheading'] = $val['商品副标题移动端'];
				}
				if (!is_null($val['产品标签'])) {
					$goodsdata['sku_label'] = $val['产品标签'];
				}
				if (!is_null($val['有效期'])) {
					$goodsdata['period'] = $val['有效期'];
				}
				if (!is_null($val['用法'])) {
					$goodsdata['sku_usage'] = $val['用法'];
				}
				if (is_null($val['批准文号'])) {
					return $this->arrayData('导入失败，批准文号列有空值！', '', '', 'error');
				} else {
					$goodsdata['prod_code'] = $val['批准文号'];
				}
				$goodsdata['goods_ext_id'] = 0;
				$goodsdata['attr_list'] = 0;
				//$goodsdata['category_path'] = 0;
				$goodsdata['goods_name_pinyin'] = '';
				$goodsdata['prod_name_common'] = '';
				$goodsdata['goods_image'] = '';
				$goodsdata['name_desc'] = '';
				$goodsdata['unit'] = '';
				$goodsdata['cost_price'] = 0;
				$goodsdata['min_limit_price'] = 0;
				$goodsdata['guide_price'] = 0;
				$goodsdata['goods_number'] = 0;
				$goodsdata['shoppingcart_min_qty'] = '';
				$goodsdata['shoppingcart_max_qty'] = '';
				$goodsdata['packaging_type'] = '';
				$goodsdata['like_number'] = '';
				$goodsdata['comment_number'] = '';
				$goodsdata['sales_number'] = '';
				$goodsdata['rate_of_praise'] = '';
				$goodsdata['meta_title'] = '';
				$goodsdata['meta_keyword'] = '';
				$goodsdata['meta_description'] = '';
				$goodsdata['is_delete'] = 0;
				$goodsdata['is_has_largess'] = 0;
				$goodsdata['net_ifsell'] = 0;
				$goodsdata['update_time'] = time();
				$goodsdata['add_time'] = time();
				$goodsdata['specifications'] = '';
				$table = '\Shop\Models\BaiyangGoods';
				BaiyangGoodsData::getInstance()->insert($table, $goodsdata, true);
				$sputable = '\Shop\Models\BaiyangSpu';
				BaiyangSpuData::getInstance()->insert($sputable, $spudata, true);
			}
		}elseif($import_type == 2) {
			//说明书
			foreach ($data as $v) {
				if (is_null($v['skuID（必填）'])) {
					return $this->arrayData('导入失败，skuID(必填)列有空值！', '', '', 'error');
				} else {
					$ypdata['sku_id'] = $v['skuID（必填）'];
				}
				if (!is_null($v['通用名称'])) {
					$ypdata['common_name'] = $v['通用名称'];
				}
				if (!is_null($v['英文名称'])) {
					$ypdata['eng_name'] = $v['英文名称'];
				}
				if (!is_null($v['商品名称'])) {
					$ypdata['cn_name'] = $v['商品名称'];
				}
				if (!is_null($v['成份'])) {
					$ypdata['component'] = $v['成份'];
				}
				if (!is_null($v['性状'])) {
					$ypdata['description'] = $v['性状'];
				}
				if (!is_null($v['作用类别'])) {
					$ypdata['functionCategory'] = $v['作用类别'];
				}
				if (!is_null($v['适应症'])) {
					$ypdata['indication'] = $v['适应症'];
				}
				if (!is_null($v['规格'])) {
					$ypdata['form'] = $v['规格'];
				}
				if (!is_null($v['用法用量'])) {
					$ypdata['dosage'] = $v['用法用量'];
				}
				if (!is_null($v['不良反应'])) {
					$ypdata['adverse_reactions'] = $v['不良反应'];
				}				if (!is_null($v['禁忌'])) {
					$ypdata['contraindications'] = $v['禁忌'];
				}
				if (!is_null($v['注意事项'])) {
					$ypdata['precautions'] = $v['注意事项'];
				}
				if (!is_null($v['孕妇及哺乳期妇女用药'])) {
					$ypdata['use_in_pregLact'] = $v['孕妇及哺乳期妇女用药'];
				}
				if (!is_null($v['儿童用药'])) {
					$ypdata['use_in_children'] = $v['儿童用药'];
				}
				if (!is_null($v['老年用药'])) {
					$ypdata['use_in_elderly'] = $v['老年用药'];
				}
				if (!is_null($v['药物相互作用'])) {
					$ypdata['drug_interactions'] = $v['药物相互作用'];
				}
				if (!is_null($v['药物过量'])) {
					$ypdata['overdosage'] = $v['药物过量'];
				}
				if (!is_null($v['临床试验'])) {
					$ypdata['clinicalTrial'] = $v['临床试验'];
				}
				if (!is_null($v['药理毒理'])) {
					$ypdata['mechanismAction'] = $v['药理毒理'];
				}
				if (!is_null($v['药代动力学'])) {
					$ypdata['pharmacokinetics'] = $v['药代动力学'];
				}
				if (!is_null($v['贮藏'])) {
					$ypdata['storage'] = $v['贮藏'];
				}
				if (!is_null($v['包装'])) {
					$ypdata['pack'] = $v['包装'];
				}
				if (!is_null($v['有效期'])) {
					$ypdata['period'] = $v['有效期'];
				}
				if (!is_null($v['执行标准'])) {
					$ypdata['standard'] = $v['执行标准'];
				}
				if (!is_null($v['批准文号'])) {
					$ypdata['approve_code'] = $v['批准文号'];
				}
				if (!is_null($v['生产企业'])) {
					$ypdata['company_name'] = $v['生产企业'];
				}
				if (!is_null($v['商品编码'])) {
					$ypdata['commodity_code'] = $v['商品编码'];
				}

				$table = '\Shop\Models\BaiyangSkuInstruction';
				$bind = '';
				foreach($ypdata as $k=>$v){
				    if($k!='sku_id'){
                        $bind .= ','.$k.'=:'.$k.':';
                    }

                }
                $bind = ltrim($bind,',');
                $BaiyangSkuData = BaiyangSkuData::getInstance();
                $countNum = $BaiyangSkuData->countData(
                    [
                        'table'=>$table,
                        'where'=>'where sku_id='.$ypdata['sku_id']
                    ]);
                if(is_numeric($countNum) && $countNum >0){
                    $BaiyangSkuData->update($bind,$table,$ypdata,'sku_id=:sku_id:');
                }else{
                    $BaiyangSkuData->insert($table, $ypdata, true);
                }


			}
		}elseif($import_type == 3){
            //修改商品信息
            $goodsupdata = array();
            $skuupdata = array();
            $saleArr = array(
                '下架' => 0,
                '上架' => 1,
            );
            $ynArr = array(
                '是' => 1,
                '否' => 0,
            );
            foreach ($data as $val) {
                if (is_null($val['skuID（必填）'])) {
                    return $this->arrayData('导入失败，skuID（必填）列有空值！', '', '', 'error');
                }else{
                    $goodsupdata['id'] = $val['skuID（必填）'];
                }
                if(!is_null($val['ERP编码'])){
                    $goodsupdata['product_code'] = $val['ERP编码'];
                }
                if(!is_null($val['销售价（统一端）'])){
                    $goodsupdata['goods_price'] = $val['销售价（统一端）'];
                 }
                if(!is_null($val['市场价（统一端）'])){
                    $goodsupdata['market_price'] = $val['市场价（统一端）'];
                }
                 if(!is_null($val['是否为赠品'])){
                     if(isset($ynArr[$val['是否为赠品']])){
                         $goodsupdata['gift_yes'] = $ynArr[$val['是否为赠品']];
                         $goodsupdata['product_type'] = $ynArr[$val['是否为赠品']];
                     }else{
                         return $this->arrayData('导入失败，是否为赠品列不合法！', '', '', 'error');
                     }
                 }
                if(!is_null($val['虚拟库存'])){
                    $skuupdata['virtual_stock_default'] = $val['虚拟库存'];
                }
                if(!is_null($val['是否锁定'])){
                    if(isset($ynArr[$val['是否锁定']])){
                        $goodsupdata['is_lock'] = $ynArr[$val['是否锁定']];
                    }else{
                        return $this->arrayData('导入失败，是否锁定列不合法！', '', '', 'error');
                    }
                }
                if (!is_null($val['排序'])) {
                    $goodsupdata['sort'] = $val['排序'];
                }
                if (!is_null($val['立即上下架'])) {
                    if(isset($saleArr[$val['立即上下架']])){
                        $goodsupdata['sale_timing_app'] = $saleArr[$val['立即上下架']];
                        $goodsupdata['sale_timing_wap'] = $saleArr[$val['立即上下架']];;
                        $goodsupdata['is_on_sale'] = $saleArr[$val['立即上下架']];
                        $goodsupdata['sale_timing_wechat'] = $saleArr[$val['立即上下架']];
                    }else{
                        return $this->arrayData('导入失败，立即上下架列不合法！', '', '', 'error');
                    }
                }

                if (!is_null($val['是否为推荐商品'])) {
                    if(isset($ynArr[$val['是否为推荐商品']])){
                        $goodsupdata['recommend_yes'] = $ynArr[$val['是否为推荐商品']];
                        $goodsupdata['is_recommend'] = $ynArr[$val['是否为推荐商品']];
                    }else{
                        return $this->arrayData('导入失败，是否为推荐商品列不合法！', '', '', 'error');
                    }
                }
                if (!is_null($val['是否为热门商品'])) {
                    if(isset($ynArr[$val['是否为热门商品']])){
                        $goodsupdata['hot_yes'] = $ynArr[$val['是否为热门商品']];
                        $goodsupdata['is_hot'] = $ynArr[$val['是否为热门商品']];
                    }else{
                        return $this->arrayData('导入失败，是否为热门商品列不合法！', '', '', 'error');
                    }
                }
                if (!is_null($val['运费模板'])) {
                    $goodsupdata['pc_freight_temp_id'] = $val['运费模板'];
                }
                if (!is_null($val['别名'])) {
                    $goodsupdata['sku_alias_name'] = $val['别名'];
                }
                if (!is_null($val['商品名称PC端'])) {
                    $goodsupdata['goods_name'] = $val['商品名称PC端'];
                }
                if (!is_null($val['商品副标题PC端'])) {
                    $goodsupdata['sku_pc_subheading'] = $val['商品副标题PC端'];
                }
                if (!is_null($val['商品名称移动端'])) {
                    $goodsupdata['sku_mobile_name'] = $val['商品名称移动端'];
                }
                if (!is_null($val['商品副标题移动端'])) {
                    $goodsupdata['sku_mobile_subheading'] = $val['商品副标题移动端'];
                }
                if (!is_null($val['产品标签'])) {
                    $goodsupdata['sku_label'] = $val['产品标签'];
                }
                if (!is_null($val['有效期'])) {
                    $goodsupdata['period'] = $val['有效期'];
                }
                if (!is_null($val['用法'])) {
                    $goodsupdata['sku_usage'] = $val['用法'];
                }
                if (!is_null($val['批准文号'])) {
                    $goodsupdata['prod_code'] = $val['批准文号'];
                }
                $goodsData = BaseData::getInstance()->getData([
                    'table'=>'\Shop\Models\BaiyangGoods as g ',
                    'join'=>'left join \Shop\Models\BaiyangSpu as s on g.spu_id=s.spu_id',
                    'column'=>'g.id,g.attribute_value_id,g.category_id,g.spu_id,s.category_id',
                    'where'=>"where g.id = '{$goodsupdata['id']}'"
                ],true);
               if(!$goodsData){
                   return $this->arrayData('导入失败，ERP编码列含有不存在的ERP编码！', '', '', 'error');
               }
                //属性处理
                if (!is_null($val['参数名称1']) && !is_null($val['参数属性值1'])){
                    $val['参数名称1']=trim($val['参数名称1'],' ');
                    $val['参数属性值1']=trim($val['参数属性值1'],' ');
                    $attr_name_table='\Shop\Models\BaiyangAttrName';
                    $attrName = BaseData::getInstance()->getData([
                        'table'=>$attr_name_table,
                        'column'=>'id',
                        'where'=>"where category_id = '{$goodsData['category_id']}' and attr_name='{$val['参数名称1']}'"
                    ],true);
                    if(!$attrName){
                        //没有属性名
                        $attrNameData=array(
                            'category_id'=>$goodsData['category_id'],
                            'attr_name'=>$val['参数名称1']
                        );
                        //插入属性名
                        $attr_name_inser_id = BaseData::getInstance()->insert($attr_name_table,$attrNameData,true);
                        //插入属性值
                        $attr_value_table='\Shop\Models\BaiyangAttrValue';
                        $attrValueData=array(
                            'attr_name_id'=>$attr_name_inser_id,
                            'attr_value'=>$val['参数属性值1']
                        );
                        $attr_value_inser_id=BaseData::getInstance()->insert($attr_value_table,$attrValueData,true);
                        $goods = BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangGoods',
                            'column'=>'attribute_value_id',
                            'where'=>"where id={$goodsupdata['id']}"
                        ],true);
                        $insert_attribute_value_id=$attr_name_inser_id.':'.$attr_value_inser_id;
                        if($goods['attribute_value_id']){
                            $goods['attribute_value_id'].=','.$insert_attribute_value_id;
                        }else{
                            $goods['attribute_value_id']=$insert_attribute_value_id;
                        }
                        BaseData::getInstance()->update("attribute_value_id='{$goods['attribute_value_id']}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                    }else{
                        //存在属性名
                        $attr_value = BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangAttrValue',
                            'column'=>'*',
                            'where'=>"where attr_name_id={$attrName['id']} and attr_value='{$val['参数属性值1']}'"
                        ],true);
                        //存在属性名，属性值添加
                        if(!$attr_value){
                            $attrValueData=array(
                                'attr_name_id'=>$attrName['id'],
                                'attr_value'=>$val['参数属性值1']
                            );
                            $attr_value_inser_id=BaseData::getInstance()->insert('\Shop\Models\BaiyangAttrValue',$attrValueData,true);
                            $goods = BaseData::getInstance()->getData([
                                'table'=>'\Shop\Models\BaiyangGoods',
                                'column'=>'attribute_value_id',
                                'where'=>"where id={$goodsupdata['id']}"
                            ],true);
                            $insert_attribute_value_id=$attrName['id'].':'.$attr_value_inser_id;
                            if($goods['attribute_value_id']){
                                $goods['attribute_value_id'].=','.$insert_attribute_value_id;
                            }else{
                                $goods['attribute_value_id']=$insert_attribute_value_id;
                            }
                            BaseData::getInstance()->update("attribute_value_id='{$goods['attribute_value_id']}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                        }else{
                            //存在属性名 属性值修改
                            $goods = BaseData::getInstance()->getData([
                                'table'=>'\Shop\Models\BaiyangGoods',
                                'column'=>'attribute_value_id',
                                'where'=>"where id={$goodsupdata['id']}"
                            ],true);
                            $goods_attribute_arr=array();
                            if($goods['attribute_value_id']){
                                $goods['attribute_value_id']=explode(',',$goods['attribute_value_id']);
                                foreach($goods['attribute_value_id'] as $k=>$v){
                                    $name_id=explode(':',$v)[0];
                                    $value_id=explode(':',$v)[1];
                                    $goods_attribute_arr[$name_id]=$value_id;
                                }
                                foreach($goods_attribute_arr as $k=>$v){
                                    $exist_flag=0;
                                    if($attrName['id']==$k){
                                        $exist_flag=1;
                                        $goods_attribute_arr[$k]=$attr_value['id'];
                                    }
                                }
                                if(!$exist_flag){
                                    $goods_attribute_arr[$attrName['id']]=$attr_value['id'];
                                }
                            }else{
                              $goods_attribute_arr[$attrName['id']]=$attr_value['id'];
                            }
                            $goods_attribute_str='';
                            foreach($goods_attribute_arr as $k=>$v){
                                $goods_attribute_str.=$k.':'.$v.',';
                            }
                            $goods_attribute_str=rtrim($goods_attribute_str,',');
                            BaseData::getInstance()->update("attribute_value_id='{$goods_attribute_str}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                        }
                    }
                }elseif(($val['参数名称1'] && !$val['参数属性值1']) || (!$val['参数名称1'] && $val['参数属性值1'])){
                    return $this->arrayData('导入失败，参数名称1或参数属性值1列有空值！', '', '', 'error');
                }
                if (!is_null($val['参数名称2']) && !is_null($val['参数属性值2'])){
                    $val['参数名称2']=trim($val['参数名称2'],' ');
                    $val['参数属性值2']=trim($val['参数属性值2'],' ');
                    $attr_name_table='\Shop\Models\BaiyangAttrName';
                    $attrName = BaseData::getInstance()->getData([
                        'table'=>$attr_name_table,
                        'column'=>'id',
                        'where'=>"where category_id = '{$goodsData['category_id']}' and attr_name='{$val['参数名称2']}'"
                    ],true);
                    if(!$attrName){
                        //没有属性名
                        $attrNameData=array(
                            'category_id'=>$goodsData['category_id'],
                            'attr_name'=>$val['参数名称2']
                        );
                        //插入属性名
                        $attr_name_inser_id = BaseData::getInstance()->insert($attr_name_table,$attrNameData,true);
                        //插入属性值
                        $attr_value_table='\Shop\Models\BaiyangAttrValue';
                        $attrValueData=array(
                            'attr_name_id'=>$attr_name_inser_id,
                            'attr_value'=>$val['参数属性值2']
                        );
                        $attr_value_inser_id=BaseData::getInstance()->insert($attr_value_table,$attrValueData,true);
                        $goods = BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangGoods',
                            'column'=>'attribute_value_id',
                            'where'=>"where id={$goodsupdata['id']}"
                        ],true);
                        $insert_attribute_value_id=$attr_name_inser_id.':'.$attr_value_inser_id;
                        if($goods['attribute_value_id']){
                            $goods['attribute_value_id'].=','.$insert_attribute_value_id;
                        }else{
                            $goods['attribute_value_id']=$insert_attribute_value_id;
                        }
                        BaseData::getInstance()->update("attribute_value_id='{$goods['attribute_value_id']}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                    }else{
                        //存在属性名
                        $attr_value = BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangAttrValue',
                            'column'=>'*',
                            'where'=>"where attr_name_id={$attrName['id']} and attr_value='{$val['参数属性值2']}'"
                        ],true);
                        //存在属性名，属性值添加
                        if(!$attr_value){
                            $attrValueData=array(
                                'attr_name_id'=>$attrName['id'],
                                'attr_value'=>$val['参数属性值2']
                            );
                            $attr_value_inser_id=BaseData::getInstance()->insert('\Shop\Models\BaiyangAttrValue',$attrValueData,true);
                            $goods = BaseData::getInstance()->getData([
                                'table'=>'\Shop\Models\BaiyangGoods',
                                'column'=>'attribute_value_id',
                                'where'=>"where id={$goodsupdata['id']}"
                            ],true);
                            $insert_attribute_value_id=$attrName['id'].':'.$attr_value_inser_id;
                            if($goods['attribute_value_id']){
                                $goods['attribute_value_id'].=','.$insert_attribute_value_id;
                            }else{
                                $goods['attribute_value_id']=$insert_attribute_value_id;
                            }
                            BaseData::getInstance()->update("attribute_value_id='{$goods['attribute_value_id']}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                        }else{
                            //存在属性名 属性值修改
                            $goods = BaseData::getInstance()->getData([
                                'table'=>'\Shop\Models\BaiyangGoods',
                                'column'=>'attribute_value_id',
                                'where'=>"where id={$goodsupdata['id']}"
                            ],true);
                            $goods_attribute_arr=array();
                            if($goods['attribute_value_id']){
                                $goods['attribute_value_id']=explode(',',$goods['attribute_value_id']);
                                foreach($goods['attribute_value_id'] as $k=>$v){
                                    $name_id=explode(':',$v)[0];
                                    $value_id=explode(':',$v)[1];
                                    $goods_attribute_arr[$name_id]=$value_id;
                                }
                                foreach($goods_attribute_arr as $k=>$v){
                                    $exist_flag=0;
                                    if($attrName['id']==$k){
                                        $exist_flag=1;
                                        $goods_attribute_arr[$k]=$attr_value['id'];
                                    }
                                }
                                if(!$exist_flag){
                                    $goods_attribute_arr[$attrName['id']]=$attr_value['id'];
                                }
                            }else{
                                $goods_attribute_arr[$attrName['id']]=$attr_value['id'];
                            }
                            $goods_attribute_str='';
                            foreach($goods_attribute_arr as $k=>$v){
                                $goods_attribute_str.=$k.':'.$v.',';
                            }
                            $goods_attribute_str=rtrim($goods_attribute_str,',');
                            BaseData::getInstance()->update("attribute_value_id='{$goods_attribute_str}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                        }
                    }
                }elseif(($val['参数名称1'] && !$val['参数属性值1']) || (!$val['参数名称1'] && $val['参数属性值1'])){
                    return $this->arrayData('导入失败，参数名称1或参数属性值1列有空值！', '', '', 'error');
                }
                if (!is_null($val['参数名称3']) && !is_null($val['参数属性值3'])){
                    $val['参数名称3']=trim($val['参数名称3'],' ');
                    $val['参数属性值3']=trim($val['参数属性值3'],' ');
                    $attr_name_table='\Shop\Models\BaiyangAttrName';
                    $attrName = BaseData::getInstance()->getData([
                        'table'=>$attr_name_table,
                        'column'=>'id',
                        'where'=>"where category_id = '{$goodsData['category_id']}' and attr_name='{$val['参数名称3']}'"
                    ],true);
                    if(!$attrName){
                        //没有属性名
                        $attrNameData=array(
                            'category_id'=>$goodsData['category_id'],
                            'attr_name'=>$val['参数名称3']
                        );
                        //插入属性名
                        $attr_name_inser_id = BaseData::getInstance()->insert($attr_name_table,$attrNameData,true);
                        //插入属性值
                        $attr_value_table='\Shop\Models\BaiyangAttrValue';
                        $attrValueData=array(
                            'attr_name_id'=>$attr_name_inser_id,
                            'attr_value'=>$val['参数属性值3']
                        );
                        $attr_value_inser_id=BaseData::getInstance()->insert($attr_value_table,$attrValueData,true);
                        $goods = BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangGoods',
                            'column'=>'attribute_value_id',
                            'where'=>"where id={$goodsupdata['id']}"
                        ],true);
                        $insert_attribute_value_id=$attr_name_inser_id.':'.$attr_value_inser_id;
                        if($goods['attribute_value_id']){
                            $goods['attribute_value_id'].=','.$insert_attribute_value_id;
                        }else{
                            $goods['attribute_value_id']=$insert_attribute_value_id;
                        }
                        BaseData::getInstance()->update("attribute_value_id='{$goods['attribute_value_id']}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                    }else{
                        //存在属性名
                        $attr_value = BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangAttrValue',
                            'column'=>'*',
                            'where'=>"where attr_name_id={$attrName['id']} and attr_value='{$val['参数属性值3']}'"
                        ],true);
                        //存在属性名，属性值添加
                        if(!$attr_value){
                            $attrValueData=array(
                                'attr_name_id'=>$attrName['id'],
                                'attr_value'=>$val['参数属性值3']
                            );
                            $attr_value_inser_id=BaseData::getInstance()->insert('\Shop\Models\BaiyangAttrValue',$attrValueData,true);
                            $goods = BaseData::getInstance()->getData([
                                'table'=>'\Shop\Models\BaiyangGoods',
                                'column'=>'attribute_value_id',
                                'where'=>"where id={$goodsupdata['id']}"
                            ],true);
                            $insert_attribute_value_id=$attrName['id'].':'.$attr_value_inser_id;
                            if($goods['attribute_value_id']){
                                $goods['attribute_value_id'].=','.$insert_attribute_value_id;
                            }else{
                                $goods['attribute_value_id']=$insert_attribute_value_id;
                            }
                            BaseData::getInstance()->update("attribute_value_id='{$goods['attribute_value_id']}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                        }else{
                            //存在属性名 属性值修改
                            $goods = BaseData::getInstance()->getData([
                                'table'=>'\Shop\Models\BaiyangGoods',
                                'column'=>'attribute_value_id',
                                'where'=>"where id={$goodsupdata['id']}"
                            ],true);
                            $goods_attribute_arr=array();
                            if($goods['attribute_value_id']){
                                $goods['attribute_value_id']=explode(',',$goods['attribute_value_id']);
                                foreach($goods['attribute_value_id'] as $k=>$v){
                                    $name_id=explode(':',$v)[0];
                                    $value_id=explode(':',$v)[1];
                                    $goods_attribute_arr[$name_id]=$value_id;
                                }
                                foreach($goods_attribute_arr as $k=>$v){
                                    $exist_flag=0;
                                    if($attrName['id']==$k){
                                        $exist_flag=1;
                                        $goods_attribute_arr[$k]=$attr_value['id'];
                                    }
                                }
                                if(!$exist_flag){
                                    $goods_attribute_arr[$attrName['id']]=$attr_value['id'];
                                }
                            }else{
                                $goods_attribute_arr[$attrName['id']]=$attr_value['id'];
                            }
                            $goods_attribute_str='';
                            foreach($goods_attribute_arr as $k=>$v){
                                $goods_attribute_str.=$k.':'.$v.',';
                            }
                            $goods_attribute_str=rtrim($goods_attribute_str,',');
                            BaseData::getInstance()->update("attribute_value_id='{$goods_attribute_str}'",'\Shop\Models\BaiyangGoods',[],"id={$goodsupdata['id']}");
                        }
                    }
                }elseif(($val['参数名称1'] && !$val['参数属性值1']) || (!$val['参数名称1'] && $val['参数属性值1'])){
                    return $this->arrayData('导入失败，参数名称1或参数属性值1列有空值！', '', '', 'error');
                }
                //品规处理
                $categoryRuleData = BaseData::getInstance()->getData([
                    'table'=>'\Shop\Models\BaiyangCategoryProductRule',
                    'column'=>'name_id,name_id2,name_id3',
                    'where'=>"where category_id={$goodsData['category_id']}"
                ],true);
                if (!is_null($val['品规1']) && !is_null($val['品规值1'])){
                    $val['品规1']=trim($val['品规1'],' ');
                    $val['品规值1']=trim($val['品规值1'],' ');
                    if($categoryRuleData['name_id']){
                        //存在品规名
                        $ruleProductData=BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangProductRule',
                            'column'=>'name',
                            'where'=>"where id={$categoryRuleData['name_id']}"
                        ],true);
                        if($ruleProductData['name']!=$val['品规1']){
                            return $this->arrayData('导入失败，品规1列填写不正确！', '', '', 'error');
                        }
                        $ruleProductData=BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangProductRule',
                            'column'=>'id',
                            'where'=>"where pid={$categoryRuleData['name_id']} and name='{$val['品规值1']}'"
                        ],true);
                        if($ruleProductData){
                            //存在品规值
                            $goodsupdata['rule_value0'] = $ruleProductData['id'];
                        }else{
                            //不存在品规值
                            $vv1_data['pid'] = $categoryRuleData['name_id'];
                            $vv1_data['name'] = $val['品规值1'];
                            $vv1_data['add_time'] = time();
                            $vv1_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv1_data, true);
                            $goodsupdata['rule_value0'] =$vv1_id;
                        }
                    }else{
                        //不存在品规名
                        $vv1_pid_data['name'] = $val['品规1'];
                        $vv1_pid_data['pid'] = 0;
                        $vv1_pid_data['add_time'] = time();
                        $vv1_pid_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv1_pid_data, true);
                        BaseData::getInstance()->update("name_id={$vv1_pid_id}",'\Shop\Models\BaiyangCategoryProductRule',[],"category_id={$goodsData['category_id']}");
                        $vv1_data['pid'] = $vv1_pid_id;
                        $vv1_data['name'] = $val['品规值1'];
                        $vv1_data['add_time'] = time();
                        $vv1_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv1_data, true);
                        $goodsupdata['rule_value0'] =$vv1_id;
                    }
                    BaseData::getInstance()->update("rule_value0={$goodsupdata['rule_value0']}",'\Shop\Models\BaiyangGoods',[],"id = '{$goodsupdata['id']}'");
                }else if(($val['品规1'] && !$val['品规值1']) || (!$val['品规1'] && $val['品规值1'])){
                    return $this->arrayData('导入失败，品规1或品规值1列有空值！', '', '', 'error');
                }

                if (!is_null($val['品规2']) && !is_null($val['品规值2'])){
                    $val['品规2']=trim($val['品规2'],' ');
                    $val['品规值2']=trim($val['品规值2'],' ');
                    if($categoryRuleData['name_id2']){
                        //存在品规名
                        $ruleProductData=BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangProductRule',
                            'column'=>'name',
                            'where'=>"where id={$categoryRuleData['name_id2']}"
                        ],true);
                        if($ruleProductData['name']!=$val['品规2']){
                            return $this->arrayData('导入失败，品规2列填写不正确！', '', '', 'error');
                        }
                        $ruleProductData=BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangProductRule',
                            'column'=>'id',
                            'where'=>"where pid={$categoryRuleData['name_id2']} and name='{$val['品规值2']}'"
                        ],true);
                        if($ruleProductData){
                            //存在品规值
                            $goodsupdata['rule_value1'] = $ruleProductData['id'];
                        }else{
                            //不存在品规值
                            $vv2_data['pid'] = $categoryRuleData['name_id2'];
                            $vv2_data['name'] = $val['品规值2'];
                            $vv2_data['add_time'] = time();
                            $vv2_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv2_data, true);
                            $goodsupdata['rule_value1'] =$vv2_id;
                        }
                    }else{
                        //不存在品规名
                        $vv2_pid_data['name'] = $val['品规2'];
                        $vv2_pid_data['pid'] = 0;
                        $vv2_pid_data['add_time'] = time();
                        $vv2_pid_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv2_pid_data, true);
                        BaseData::getInstance()->update("name_id2={$vv2_pid_id}",'\Shop\Models\BaiyangCategoryProductRule',[],"category_id={$goodsData['category_id']}");
                        $vv2_data['pid'] = $vv2_pid_id;
                        $vv2_data['name'] = $val['品规值2'];
                        $vv2_data['add_time'] = time();
                        $vv2_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv2_data, true);
                        $goodsupdata['rule_value1'] =$vv2_id;
                    }
                    BaseData::getInstance()->update("rule_value1={$goodsupdata['rule_value1']}",'\Shop\Models\BaiyangGoods',[],"id = '{$goodsupdata['id']}'");
                }else if(($val['品规2'] && !$val['品规值2']) || (!$val['品规2'] && $val['品规值2'])){
                    return $this->arrayData('导入失败，品规2或品规值2列有空值！', '', '', 'error');
                }

                if (!is_null($val['品规3']) && !is_null($val['品规值3'])){
                    $val['品规3']=trim($val['品规3'],' ');
                    $val['品规值3']=trim($val['品规值3'],' ');
                    if($categoryRuleData['name_id3']){
                        //存在品规名
                        $ruleProductData=BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangProductRule',
                            'column'=>'name',
                            'where'=>"where id={$categoryRuleData['name_id3']}"
                        ],true);
                        if($ruleProductData['name']!=$val['品规3']){
                            return $this->arrayData('导入失败，品规3列填写不正确！', '', '', 'error');
                        }
                        $ruleProductData=BaseData::getInstance()->getData([
                            'table'=>'\Shop\Models\BaiyangProductRule',
                            'column'=>'id',
                            'where'=>"where pid={$categoryRuleData['name_id3']} and name='{$val['品规值3']}'"
                        ],true);
                        if($ruleProductData){
                            //存在品规值
                            $goodsupdata['rule_value2'] = $ruleProductData['id'];
                        }else{
                            //不存在品规值
                            $vv3_data['pid'] = $categoryRuleData['name_id3'];
                            $vv3_data['name'] = $val['品规值3'];
                            $vv3_data['add_time'] = time();
                            $vv3_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv3_data, true);
                            $goodsupdata['rule_value2'] =$vv3_id;
                        }
                    }else{
                        //不存在品规名
                        $vv3_pid_data['name'] = $val['品规3'];
                        $vv3_pid_data['pid'] = 0;
                        $vv3_pid_data['add_time'] = time();
                        $vv3_pid_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv3_pid_data, true);
                        BaseData::getInstance()->update("name_id3={$vv3_pid_id}",'\Shop\Models\BaiyangCategoryProductRule',[],"category_id={$goodsData['category_id']}");
                        $vv3_data['pid'] = $vv3_pid_id;
                        $vv3_data['name'] = $val['品规值3'];
                        $vv3_data['add_time'] = time();
                        $vv3_id = BaseData::getInstance()->insert('\Shop\Models\BaiyangProductRule', $vv3_data, true);
                        $goodsupdata['rule_value2'] =$vv3_id;
                    }
                    BaseData::getInstance()->update("rule_value2={$goodsupdata['rule_value2']}",'\Shop\Models\BaiyangGoods',[],"id = '{$goodsupdata['id']}'");
                }elseif(($val['品规3'] && !$val['品规值3']) || (!$val['品规3'] && $val['品规值3'])){
                    return $this->arrayData('导入失败，品规3或品规值3列有空值！', '', '', 'error');
                }
                $goodsData=BaseData::getInstance()->getData([
                    'table'=>'\Shop\Models\BaiyangGoods',
                    'column'=>'rule_value0,rule_value1,rule_value2',
                    'where'=>"where id = '{$goodsupdata['id']}'"
                ],true);
                $rule_value_id=$goodsData['rule_value0'].'+'.$goodsData['rule_value1'].'+'.$goodsData['rule_value2'];
                BaseData::getInstance()->update("rule_value_id='{$rule_value_id}'",'\Shop\Models\BaiyangGoods', [], "id = '{$goodsupdata['id']}'");
                
                $goodsColumStr="";
                foreach($goodsupdata as $k=>$v){
                    if($k=='id' || $k=='rule_value0' || $k=='rule_value1' || $k=='rule_value2') continue;
                    if($k=='product_code' || $k=='sku_alias_name' || $k=='goods_name' || $k=='sku_pc_subheading' || $k=='sku_mobile_name' || $k=='sku_mobile_subheading' || $k=='sku_label' || $k=='period' || $k=='sku_usage' || $k=='prod_code')
                    {
                        $goodsColumStr.=$k."='".$v."',";continue;
                    }
                    $goodsColumStr.=$k.'='.$v.',';
                }
                $goodsColumStr=rtrim($goodsColumStr,',');

                BaseData::getInstance()->update($goodsColumStr,'\Shop\Models\BaiyangGoods', [], "id = {$goodsupdata['id']}");
                BaseData::getInstance()->update("virtual_stock_default={$skuupdata['virtual_stock_default']}",'\Shop\Models\BaiyangSkuInfo', [], "sku_id = {$goodsupdata['id']}");
            }
        }
		return $this->arrayData('导入成功', '', '', 'success');
	}
	
	/**
	 * 商品列表导出
	 * 罗毅庭
	 */
	public function export($param){
		ini_set('memory_limit','256M');
		$headArray =  array();
		$selections = '';
		$filename = '商品列表'.date('Y-m-d H:i:s');
		if(!empty($param['spu_id'])){
			$selections .='p.'.$param['spu_id'].',';
			$headArray[] = 'SPU编号';
		}
		if(!empty($param['spu_name'])){
			$selections .='p.'.$param['spu_name'].',';
			$headArray[] = 'SPU通用名';
		}
		if(!empty($param['brand_name'])){
			$selections .='b.brand_name,';
			$headArray[] = '品牌';
		}
		if(!empty($param['drug_type'])){
			$selections .='p.'.$param['drug_type'].',';
			$headArray[] = '药品类型';
		}
		if(!empty($param['category_id'])){
			$selections .='p.'.$param['category_id'].',';
			$headArray[] = '所属分类';
		}
		if(!empty($param['attr_list'])){
			$selections .='s.'.$param['attr_list'].',';
			$headArray[] = '属性';
		}
		if(!empty($param['goods_id'])){
			$selections .='s.id,';
			$headArray[] = 'SKUID';
		}
		if(!empty($param['product_code'])){
			$selections .='s.'.$param['product_code'].',';
			$headArray[] = 'ERP编码';
		}
		if(!empty($param['attribute_value_id'])){
			$selections .='s.'.$param['attribute_value_id'].',';
			$headArray[] = '属性值';
		}
		if(!empty($param['goods_price'])){
			$selections .='s.'.$param['goods_price'].',';
			$headArray[] = '销售价';
		}
		if(!empty($param['market_price'])){
			$selections .='s.'.$param['market_price'].',';
			$headArray[] = '市场价';
		}
		if(!empty($param['is_lock'])){
			$selections .='s.'.$param['is_lock'].',';
			$headArray[] = '是否锁定';
		}
		#=============================================#
		#真实库存 stock_1 虚拟库存 stock_2 上架信息 status_1 下架信息 status_2
		if(!empty($param['stock_1'])){
			$selections .='s.v_stock,';
			$headArray[] = '真实库存';
		}
		if(!empty($param['stock_2'])){
			$headArray[] = '统一虚拟库';
			$headArray[] = '虚拟库存pc';
			$headArray[] = '虚拟库存app';
			$headArray[] = '虚拟库存wap';
			$headArray[] = '虚拟库存WeChat';
			$selections .= 'i.virtual_stock_default,';
			$selections .= 'i.virtual_stock_pc,';
			$selections .= 'i.virtual_stock_app,';
			$selections .= 'i.virtual_stock_wap,';
			$selections .= 'i.virtual_stock_wechat,';
		}
		if(!empty($param['status_1'])||!empty($param['status'])){

            $headArray[] = 'pc上下架信息';
            $headArray[] = 'app上下架信息';
            $headArray[] = 'wap上下架信息';
            $headArray[] = 'WeChat上下架信息';
            $selections .= 's.is_on_sale,';
            $selections .= 's.sale_timing_app,';
            $selections .= 's.sale_timing_wap,';
            $selections .= 's.sale_timing_wechat,';
		}
		#==============================================#
		if(!empty($param['sort'])){
			$selections .='s.'.$param['sort'].',';
			$headArray[] = '排序';
		}
		if(!empty($param['gift_yes'])){
			$selections .='s.'.$param['gift_yes'].',';
			$headArray[] = '是否赠品';
		}
		if(!empty($param['sku_alias_name'])){
			$selections .='s.'.$param['sku_alias_name'].',';
			$headArray[] = '别名';
		}
		if(!empty($param['goods_name'])){
			$selections .='s.'.$param['goods_name'].',';
			$headArray[] = '商品名称PC端';
		}
		if(!empty($param['sku_pc_subheading'])){
			$selections .='s.'.$param['sku_pc_subheading'].',';
			$headArray[] = '商品副标题PC端';
		}
		if(!empty($param['sku_mobile_name'])){
			$selections .='s.'.$param['sku_mobile_name'].',';
			$headArray[] = '商品名称移动端';
		}
		if(!empty($param['sku_mobile_subheading'])){
			$selections .='s.'.$param['sku_mobile_subheading'].',';
			$headArray[] = '商品副标题移动端';
		}
		if(!empty($param['sku_label'])){
			$selections .='s.'.$param['sku_label'].',';
			$headArray[] = '产品标签';
		}
		if(!empty($param['period'])){
			$selections .='s.'.$param['period'].',';
			$headArray[] = '有效期';
		}
		if(!empty($param['usage'])){
			$selections .='s.'.$param['usage'].',';
			$headArray[] = '用法';
		}
		/*if(!empty($param['zysx'])){
			$headArray[] = '参数自有属性';
		}*/
		
		if(!empty($param['instruction'])){
			$headArray[] = '说明书商品名';
			$headArray[] = '说明书通用名';
			$headArray[] = '说明书英文名';
			$headArray[] = '说明书成分';
			$headArray[] = '说明书适应症';
			$headArray[] = '说明书规格';
			$headArray[] = '说明书用法用量';
			$headArray[] = '说明书不良反应';
			$headArray[] = '说明书禁忌';
			$headArray[] = '说明书注意事项';
			$headArray[] = '说明书孕妇及哺乳期妇女用药';
			$headArray[] = '说明书老年用药';
			$headArray[] = '说明书儿童用药';
			$headArray[] = '说明书药物相互作用';
			$headArray[] = '说明书药理作用';
			$headArray[] = '说明书药代动力学';
			$headArray[] = '说明书性状';
			$headArray[] = '说明书贮藏';
			$headArray[] = '说明书包装';
			$headArray[] = '说明书有效期';
			$headArray[] = '说明书批准文号';
			$headArray[] = '说明书生产企业';
			$headArray[] = '说明书执行标准';
			$headArray[] = '说明书药物过量';
			$headArray[] = '说明书商品编码';
			$headArray[] = '说明书临床试验';
			$headArray[] = '说明书作用类别';
			$selections .='t.cn_name,t.common_name,t.eng_name,t.component,t.indication,t.form,t.dosage,t.adverse_reactions,t.contraindications,t.precautions,t.use_in_pregLact,t.use_in_elderly,t.use_in_children,t.drug_interactions,t.mechanismAction,t.pharmacokinetics,t.description,t.storage,t.pack,t.period,t.approve_code,t.company_name,t.standard,t.overdosage,t.commodity_code,t.clinicalTrial,t.functionCategory,';
		}
		
		$selections = rtrim($selections,',');
		$tables = array(
			'spuTable' => '\Shop\Models\BaiyangSpu as p',
			'skuTable' => '\Shop\Models\BaiyangGoods as s',
			'stockTable' => '\Shop\Models\BaiyangSkuInfo as i',
			'instructTable' => '\Shop\Models\BaiyangSkuInstruction as t',
			'brandTable'=>'\Shop\Models\BaiyangBrands as b'
		);
		//限制每次取5000条记录
		$type_num = explode('-',$param['type_num']);
		$conditions = array();
		$param = json_decode($param['url'], true);
		$where = ' 1 ';
		$data   =   array();
		//组织where语句
		if(isset($param['name']) && $param['name'] != ''){
			if((int)$param['name'] > 0){
				$where .= " AND s.id = {$param['name']}";
			}else{
				$where .= " AND goods_name LIKE '%{$param['name']}%' ";
			}
		}
		
		if((isset($param['spu_name']) && !empty($param['spu_name']) ) || (isset($param['category']) && $param['category'] > 0) || (isset($param['brand']) && !empty($param['brand'])) || (isset($param['drug_type']) && ($param['drug_type'] > 0))){
			$where_spu  =   ' 1 ';
			$data_spu = [];
			if(isset($param['spu_name']) && !empty($param['spu_name']) ){
				$where_spu .= " AND spu_name LIKE :spu_name: ";
				$data_spu['spu_name']   =   '%'.$param['spu_name'].'%';
			}
			if(isset($param['category'])){
				$where_spu .= " AND category_id in(".$param['category'].") ";
//                $data_spu['category']   =   (int)$param['category'];
			}
			if(isset($param['drug_type']) && $param['drug_type'] >0){
				$where_spu .= " AND drug_type = :drug_type: ";
				$data_spu['drug_type']   =   (int)$param['drug_type'];
			}
			
			if(isset($param['brand']) && !empty($param['brand'])){
				$where_b = " brand_name LIKE :brand: ";
				$data_b['brand']   =   '%'.$param['brand'].'%';
				$result = BaseData::getInstance()->select('id', '\Shop\Models\BaiyangBrands', $data_b, $where_b);//                $brand = array();
				if($result){
					$where_spu .= ' AND (';
					$i = 0;
					$len = count($result);
					for( $i ; $i < $len ; $i++ ){
						$data_spu['brand'.$i] = $result[$i]['id'];
						if( $i+1 != $len ){
							$where_spu .= ' brand_id = :brand'.$i.': or ';
						}else{
							$where_spu .= ' brand_id = :brand'.$i.':';
						}
					}
					$where_spu .= ')';
				}else{
					$data_spu['brand'] = '';
					$where_spu .= ' AND brand_id=:brand:';
				}
			}
			
			$table_spu  =   '\Shop\Models\BaiyangSpu';
			$arr        =   BaiyangSpuData::getInstance()->select('spu_id',$table_spu,$data_spu,$where_spu);
			$where_in   =   '';
			if($arr){
				foreach($arr as $k=>$v){
//				    if(count($arr) != $k+1){
//					    $data['where_in_'.$k]   =  $v['spu_id'].',';
//					    $where_in .= ':where_in_'.$k.':,';
//				    }else{
//					    $data['where_in_'.$k]   =  $v['spu_id'];
//					    $where_in .= ':where_in_'.$k.':';
//				    }
					$where_in .= $v['spu_id'].',';
				}
				$where .= " AND s.spu_id in (".rtrim($where_in,',').")";
			}
		}
		
		if(isset($param['is_hot']) && $param['is_hot'] >= 0){
			$where .= " AND is_hot = {$param['is_hot']}";
		}
		if(isset($param['is_recommend']) && $param['is_recommend'] >= 0){
			$where .= " AND is_recommend = {$param['is_recommend']}";
		}
		if(isset($param['is_on_sale']) && $param['is_on_sale'] >= 0){
			switch ($param['is_on_sale'])
			{
				case 1:
					$where .= " AND is_on_sale = 1";
					break;
				case 2:
					$where .= " AND is_on_sale = 0";
					break;
				case 3:
					$where .= " AND sale_timing_app = 1";
					break;
				case 4:
					$where .= " AND sale_timing_app = 0";
					break;
				case 5:
					$where .= " AND sale_timing_wap = 1";
					break;
				case 6:
					$where .= " AND sale_timing_wap = 0";
					break;
				case 7:
					$where .= " AND sale_timing_wechat = 1";
					break;
				case 8:
					$where .= " AND sale_timing_wechat = 0";
					break;
			}
		}
		$where .= ' and is_global=0 ';
		$phql = "SELECT {$selections} FROM {$tables['skuTable']} LEFT JOIN {$tables['spuTable']} ON s.spu_id = p.spu_id LEFT JOIN {$tables['instructTable']} ON s.id = t.sku_id LEFT JOIN {$tables['brandTable']} ON b.id=s.brand_id LEFT JOIN {$tables['stockTable']} ON i.sku_id = s.id WHERE $where  limit $type_num[0],5000";
		#var_dump($phql);
		#return false;
		$result = $this->modelsManager->executeQuery($phql,$conditions);
		$result = $result->toArray();
		$isarr = array(
			'0'=>'否',
			'1'=>'是'
		);
		$gift_yes = array(
			'1'=>'否',
			'0'=>'是'
		);
		$drugarr = array(
			'1'=>'处方药',
			'2'=>'红色非处方药',
			'3'=>'绿色非处方药',
			'4'=>'非药物',
		);
		foreach($result as $k=>$v){
            if(isset($v['is_on_sale'])){
                $result[$k]['is_on_sale'] = $isarr[$v['is_on_sale']];
                $result[$k]['sale_timing_app'] = $isarr[$v['sale_timing_app']];
                $result[$k]['sale_timing_wap'] = $isarr[$v['sale_timing_wap']];
                $result[$k]['sale_timing_wechat'] = $isarr[$v['sale_timing_wechat']];
            }
		    if(isset($v['gift_yes'])){
                $result[$k]['gift_yes'] = $gift_yes[$v['gift_yes']];
            }
            if(isset($v['is_lock'])){
                $result[$k]['is_lock'] = $isarr[$v['is_lock']];
            }
            if(isset($v['drug_type'])){
                $result[$k]['drug_type'] = $drugarr[$v['drug_type']];
            }

		}

		$this->excel->exportExcel($headArray,$result,$filename,'商品列表','xlsx');
		die();
	}
}
