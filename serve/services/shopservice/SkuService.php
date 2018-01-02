<?php
/**
 * Created by PhpStorm.
 * @author 梁伟
 * @date: 2016/8/16
 */

namespace Shop\Services;
use Shop\Datas\BaiyangSkuData;
use Shop\Datas\BaiyangSpuData;
use Shop\Datas\BaiyangProductRuleData;
use Shop\Datas\BaiyangCategoryProductRuleData;
use Shop\Services\BaseService;
use Shop\Datas\BaiyangGoodsExtensionData;
use Shop\Datas\BaiyangSkuInfoData;
use Shop\Datas\BaiyangSkuImagesData;
use Shop\Datas\BaiyangSkuDefaultData;
use Shop\Datas\BaiyangSkuTimingData;
use Shop\Datas\BaseData;
use Shop\Datas\UpdateCacheSkuData;
use Shop\Models\CacheGoodsKey;
use Shop\Datas\BaiyangGoodsData;


class SkuService extends BaseService
{
    //必须声明此静态属性，单例模式下防止内存地址覆盖
    protected static $instance=null;
	
	/**
	 * 获取sku详情数据
	 * @param int $id sku id
	 * @author 梁伟
	 * @date: 2016/9/18
	 */
	public function getCode($id)
	{
		$data['id'] = (int)$id;
		$table = '\Shop\Models\BaiyangGoods';
		$selections = 'product_code';
		$where = 'id=:id: ';
		$res = BaiyangSkuInfoData::getInstance()->select($selections,$table,$data,$where);
//        if(isset($res[0]['sku_detail_pc']) && !empty($res[0]['sku_detail_pc']) && !strpos($res[0]['sku_detail_pc'],'static.baiy')){
//            $res[0]['sku_detail_pc'] = str_replace('<img src="','<img src="'.$this->config['domain']['img'],htmlspecialchars_decode($res[0]['sku_detail_pc']));
//        }
//        if(isset($res[0]['sku_detail_mobile']) && !empty($res[0]['sku_detail_mobile']) && !strpos($res[0]['sku_detail_mobile'],'static.baiy')){
//            $res[0]['sku_detail_mobile'] = str_replace('<img src="','<img src="'.$this->config['domain']['img'],htmlspecialchars_decode($res[0]['sku_detail_mobile']));
//        }
		return $res;
	}
    
    /**
     * @desc    按条件获取spu列表信息
     * @param   array $param
     *              -string name 商品id或商品名
     *              -string spu_name spu名称或spu id
     *              -int category 分类id
     *              -int drug_type 药物类型
     *              -int brand 品牌id
     *              -int is_hot 是否热销
     *              -int is_recommend 是否推荐
     *              -int is_on_sale 个端上下架信息(1 pc端上架，2 pc端下架，3 app端上架，4 app端下架，5 wap端上架，6 wap端下架)
     * @return  array 商品列表信息
     * @author  梁伟
     */
    public function getAllSku($param)
    {
        //查询条件
        $table = '\Shop\Models\BaiyangGoods';
        $where = ' 1 ';
        $data   =   array();
        //组织where语句
        if(isset($param['name']) && $param['name'] != ''){
            if((int)$param['name'] > 0){
                $where .= " AND id = :name:";
                $data['name']   =   (int)$param['name'];
            }else{
                $where .= " AND goods_name LIKE :name:";
                $data['name']   =   '%'.$param['name'].'%';
            }
        }

        if((isset($param['spu_name']) && !empty($param['spu_name']) ) || (isset($param['category']) && $param['category'] > 0) || (isset($param['brand']) && !empty($param['brand'])) || (isset($param['drug_type']) && ($param['drug_type'] > 0))){
            $where_spu  =   ' 1 ';

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
                    if(count($arr) != $k+1){
                        $data['where_in_'.$k]   =  $v['spu_id'].',';
                        $where_in .= ':where_in_'.$k.':,';
                    }else{
                        $data['where_in_'.$k]   =  $v['spu_id'];
                        $where_in .= ':where_in_'.$k.':';
                    }

                }
                $where .= " AND spu_id in (".$where_in.")";
            }else{
                $return = [
                    'res'  => 'success',
                    'list' => 0,
                ];
                return $return;
            }
        }

        if(isset($param['is_hot']) && $param['is_hot'] >= 0){
            $where .= " AND is_hot = :is_hot:";
            $data['is_hot']   =   $param['is_hot'];
        }
        if(isset($param['is_recommend']) && $param['is_recommend'] >= 0){
            $where .= " AND is_recommend = :is_recommend:";
            $data['is_recommend']   =   $param['is_recommend'];
        }
        if(isset($param['is_on_sale']) && $param['is_on_sale'] >= 0){
            switch ($param['is_on_sale'])
            {
                case 1:
                        $where .= " AND is_on_sale = :is_on_sale:";
                        $data['is_on_sale']   =   1;
                      break;
                case 2:
                    $where .= " AND is_on_sale = :is_on_sale:";
                    $data['is_on_sale']   =   0;
                      break;
                case 3:
                    $where .= " AND sale_timing_app = :is_on_sale:";
                    $data['is_on_sale']   =   1;
                    break;
                case 4:
                    $where .= " AND sale_timing_app = :is_on_sale:";
                    $data['is_on_sale']   =   0;
                    break;
                case 5:
                    $where .= " AND sale_timing_wap = :is_on_sale:";
                    $data['is_on_sale']   =   1;
                    break;
                case 6:
                    $where .= " AND sale_timing_wap = :is_on_sale:";
                    $data['is_on_sale']   =   0;
                    break;
                case 7:
                    $where .= " AND sale_timing_wechat = :is_on_sale:";
                    $data['is_on_sale']   =   1;
                    break;
                case 8:
                    $where .= " AND sale_timing_wechat = :is_on_sale:";
                    $data['is_on_sale']   =   0;
                    break;
            }
        }
        $where .= ' and is_global=0 ';
        //总记录数
        $skuData = BaiyangSkuData::getInstance();
        $counts  = $skuData->count($table,$data,$where);
        if(empty($counts)){
            return array('res' => 'success','list' => 0);
        }
        $tmp_count = $counts/5000;
        $tmp_count = ceil($tmp_count);
        $numarr  = array();
        for($i=0;$i<$tmp_count;$i++){
            $numarr[]=$i*5000 .'-'.($i+1)*5000;
        }
        //分页
        $pages['page']   = isset($param['page'])?(int)$param['page']:1;//当前页
        $pages['counts'] = $counts;
        $pages['url']    = $param['url'];
        $page = $this->page->pageDetail($pages);
        $selections = 'id,goods_name,sku_mobile_name,is_on_sale,is_hot,is_recommend,sort,spu_id,small_path,sale_timing_wap,sale_timing_app,sale_timing_wechat,is_lock';
        $where .= 'order by update_time desc limit '.$page['record'].','.$page['psize'];
        $result = $skuData->select($selections,$table,$data,$where);
        $return = [
            'res'  => 'success',
            'list' => $result,
            'page' => $page['page'],
            'numarr' => $numarr,
        ];
        return $return;
    }

    /**
     * @desc 根据spu id获取sku数据
     * @param int $id spu id
     * @return array() 商品信息
     * @author  梁伟
     */
    public function getOneSku($id){
        if(!(int)$id){
            return $this->arrayData('参数错误','','','error');
        }
        $where = 'spu_id=:spu_id:';
        $data = array(
            'spu_id' => (int)$id,
        );
        $table = '\Shop\Models\BaiyangGoods';
        $selections = 'id,barcode,prod_code,goods_name,prod_name_common,goods_image,big_path,small_path,name_desc,introduction,gift_yes,price,packing,virtual_stock,unit,goods_price,market_price,min_limit_price,guide_price,goods_number,v_stock,is_use_stock,attr_list,weight,size,meta_title,meta_keyword,meta_description,is_on_sale,is_hot,is_recommend,product_type,manufacturer,medicine_type,freight_temp_id,video_id,spu_id,rule_value_id,is_unified_price,bind_gift,sku_alias_name,sku_mobile_name,sku_pc_subheading,sku_mobile_subheading,attribute_value_id,sale_timing_app,sale_timing_wap,sale_timing_wechat';
        $result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $result;
    }

    /**
     * 自增一条商品
     * author 罗毅庭
     */

    public function addOneGoods($spu_id,$erp_id, $shop_id = 0,$brand_id=0,$category_id=0,$category_path=0,$id=0){
	    $where = 'product_code=:product_code:';
	    $data = array(
		    'product_code' => $erp_id,
	    );
	    $table = '\Shop\Models\BaiyangGoods';
	    $selections = 'id';
	    $result = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
	    if(!empty($result)) {   return $this->arrayData('添加失败，存在相同ERP编码！', '', '', 'error'); }
            if($id>0){
                 $data['id'] = $id;
            }
        $data['spu_id'] = $spu_id;
        $data['supplier_id'] = $shop_id;
        $data['product_code'] = $erp_id;
        $data['goods_ext_id'] = 0;
        $data['category_id'] = $category_id;
        $data['category_path'] = $category_path;
        $data['goods_name_pinyin'] = 0;
        $data['brand_id'] = $brand_id;
        $data['prod_code'] = 0;
        $data['goods_name'] = ' ';
        $data['prod_name_common'] = 0;
        $data['goods_image'] = 0;
        $data['name_desc'] = 0;
        $data['unit'] = 0;
        $data['cost_price'] = 0;
        $data['goods_price'] = 0;
        $data['market_price'] = 0;
        $data['min_limit_price'] = 0;
        $data['guide_price'] = 0;
        $data['goods_number'] = 0;
        $data['v_stock'] = 0;
        $data['shoppingcart_min_qty'] = 0;
        $data['shoppingcart_max_qty'] = 0;
        $data['attr_list'] = 0;
        $data['packaging_type'] = 0;
        $data['like_number'] = 0;
        $data['comment_number'] = 0;
        $data['sales_number'] = 0;
        $data['rate_of_praise'] = 0;
        $data['meta_title'] = 0;
        $data['meta_keyword'] = 0;
        $data['meta_description'] = 0;
        $data['is_on_sale'] = 0;
        $data['is_hot'] = 0;
        $data['is_recommend'] = 0;
        $data['is_delete'] = 0;
        $data['is_has_largess'] = 0;
        $data['net_ifsell'] = 0;
        $data['product_type'] = 0;
        $data['update_time'] = time();
        $data['add_time'] = time();
        $data['specifications'] = '';
        BaiyangSkuData::getInstance()->insert('\Shop\Models\BaiyangGoods',$data);
        $table = new \Shop\Models\BaiyangGoods;
        $lastInsertId = $table->getWriteConnection()->lastInsertId($table->getSource());
        return $lastInsertId;
    }

    /**
     * @desc 修改sku关联spu信息
     * @param   array $param
     *              -int id 商品id
     *              -int spu_id spu id
     * @return bool true|false 是否修改成功
     * @author 梁伟
     */
    public function setSkuSpu($param)
    {
        if(!isset($param['id']) || !isset($param['spu_id']) || $param['id'] <= 0 || empty($param['spu_id'])){
            return $this->arrayData('参数错误','','','error');
        }
        $selections = 'spu_id=:spu_id:,rule_value_id=:rule_value_id:,rule_value0=:rule_value0:,rule_value1=:rule_value1:,rule_value2=:rule_value2:,add_rule_time=:add_rule_time:';
        $table = '\Shop\Models\BaiyangGoods';
        $where = ' id=:id: ';
        $data = array(
            'id'=>(int)$param['id'],
            'spu_id'=>(int)$param['spu_id'],
            'rule_value_id'=>'0+0+0',
            'rule_value0'=>'0',
            'rule_value1'=>'0',
            'rule_value2'=>'0',
            'add_rule_time'=>time(),
        );
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $res = $BaiyangSkuData->update($selections,$table,$data,$where);
        if($res){
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            $UpdateCacheSkuData->updateSkuInfo((int)$param['id']);
            $UpdateCacheSkuData->updateSkuRules((int)$param['spu_id']);
            $this->updateEsSearch((int)$param['id']);
        }
        return $res;
    }

    /**
     * @desc 修改sku表单一字段数据
     * @param   array $param
     *              -int id 商品id
     *              -string field 要修改的字段名
     *              -string act 要改变的值
     * @return bool true|false 是否修改成功
     * @author 梁伟
     */
    public function setSkuOne($param)
    {
        if($param['id'] <= 0 || empty($param['field'])){
            return $this->arrayData('参数错误','','','error');
        }

        $data['id']  = (int)$param['id'];
        $data['act'] = $param['act'];
        $table = '\Shop\Models\BaiyangGoods';
        $selections = $param['field'].'=:act:';
        $where = ' id=:id: ';
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $res = $BaiyangSkuData->update($selections,$table,$data,$where);

        if($res){
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            $UpdateCacheSkuData->updateSkuInfo((int)$param['id']);
            $this->updateEsSearch((int)$param['id']);
        }
        return $res;
    }

    /**
     * @desc 批量修改sku上下架信息
     * @param   array $param
     *              -int shelves
     *              -string stock_checkbox
     * @return bool true|false 是否修改成功
     * @author 梁伟
     */
    public function setSkuSales($param)
    {
        if( isset($param) && is_array($param) ){
            $where = '';
            $info = '';
            if( $param[1] <= 0){
                return $this->arrayData('请选择商品','','','error');
            }
            if(!isset($param['stock_checkbox']) || !is_array($param['stock_checkbox'])){
                return $this->arrayData('请选择平台','','','error');
            }

            $updateRedis = array();
            foreach($param as $k=>$v){
                if( $k != 'shelves' && $k != 'stock_checkbox' ){
                    if($param['shelves']){
                        $tmp = $this->selectIsImg($v);
                        if($tmp){
                            $where .= ' id=:id'.$k.': or';
                            $data['id'.$k] = $v;
                            $updateRedis[] = $v;
                        }else{
                            $info .= $v.',';
                        }
                    }else{
                        $where .= ' id=:id'.$k.': or';
                        $data['id'.$k] = $v;
                        $updateRedis[] = $v;
                    }

                }else if( $k == 'shelves' ){
                    $data['shelves'] = $v;
                }
            }
        }else{
            return $this->arrayData('参数错误','','','error');
        }
        $where = trim($where,'or ');
        $table = '\Shop\Models\BaiyangGoods';
        $selections = '';
        if( is_array($param['stock_checkbox']) ){
            foreach($param['stock_checkbox'] as $v){
                if( $v == 'pc' ){
                    $selections .= ',is_on_sale=:shelves:';
                }else if( $v == 'app' ){
                    $selections .= ',sale_timing_app=:shelves:';
                }else if( $v == 'wap' ){
                    $selections .= ',sale_timing_wap=:shelves:';
                }else if( $v == 'wechat' ){
                    $selections .= ',sale_timing_wechat=:shelves:';
                }
            }
            $selections = trim($selections,',');
            $res = false;
            if(!empty($where)){
                $res = BaiyangSkuData::getInstance()->update($selections,$table,$data,$where);
            }

            if($res || empty($where)){
                if(!empty($info)){
                    $info .= '以上商品没有主图,无法上架！';
                }

                //更新缓存
                $esSkuId = '';
                $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
                foreach($updateRedis as $v){
                    $tmp = $UpdateCacheSkuData->updateSkuInfo($v,true);
                    if($tmp && $tmp[0]['spu_id']){
                        $UpdateCacheSkuData->updateSkuRules($tmp[0]['spu_id']);
                        $UpdateCacheSkuData->getHotSku();
                        $UpdateCacheSkuData->getRecommendSku();
                        $esSkuId .= $v.',';
                    }
                }
                $this->updateEsSearch($esSkuId);
                return $this->arrayData($info,'','');
            }
        }
        return $this->arrayData('参数错误','','','error');
    }

    /**
     * @desc 查看商品是否有主图信息
     * @param  int $id sku id
     * @return bool true|false 是否修改成功
     * @author 梁伟
     */
    private function selectIsImg($sku_id)
    {
        $BaseService = BaseData::getInstance();
        $img = $BaseService->select('spu_id,goods_image,big_path,small_path','\Shop\Models\BaiyangGoods',['id'=>(int)$sku_id],'id=:id:');
        if(isset($img) && !empty($img[0]['goods_image']) && !empty($img[0]['big_path']) && !empty($img[0]['small_path'])){
            return true;
        }
        if(!$img || $img[0]['spu_id'] <= 0){
            return false;
        }
        $img = $BaseService->select('goods_image','\Shop\Models\BaiyangSpu',['id'=>(int)$img[0]['spu_id']],'spu_id=:id:');
        if(isset($img) && !empty($img[0]['goods_image'])){
            return true;
        }
        return false;

    }

    /**
     * sku删除与spu关联
     * @param int $id
     * @return bool true|false 是否修改成功
     * @author 梁伟
     * @date: 2016/9/19
     */
    public function delSku($id)
    {
        if( $id < 0 ){
            return false;
        }
        $data['id'] = (int)$id;
        $data['spu_id'] = 0;
        $data['rule_value_id'] = '';
        $table = '\Shop\Models\BaiyangGoods';
        $selections = 'spu_id=:spu_id:,rule_value_id=:rule_value_id:';
        $where = ' id=:id: ';
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        //查询数据以更新多品规缓存
        $sku = $BaiyangSkuData->select('spu_id',$table,['id'=>$data['id']],'id=:id:');
        //更新数据
        $res = $BaiyangSkuData->update($selections,$table,$data,$where);
        if($res){
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            $UpdateCacheSkuData->updateSkuInfo((int)$id);
            $UpdateCacheSkuData->updateSkuRules($sku[0]['spu_id']);
            $this->updateEsSearch((int)$id);
        }
        return $res;
    }

    /**
     * 获取spu下的sku数据
     * @param int $spuId spu id
     * @author 梁伟
     * @date: 2016/9/6
     */
    public function getSpuSku($spuId)
    {
        if( !isset($spuId) || $spuId <= 0 ){
            return $this->arrayData('参数错误','','','error');
        }
        $data['spu_id'] = (int)$spuId;
        $table = '\Shop\Models\BaiyangGoods';
        $selections = 'id,supplier_id,goods_price,is_hot,is_recommend,goods_number,market_price,v_stock,is_use_stock,is_on_sale,is_has_largess,spu_id,rule_value_id,is_unified_price,product_type,pc_freight_temp_id,goods_image,big_path,small_path,sale_timing_app,sale_timing_wap,sale_timing_wechat,is_lock,prod_name_common,sort';
        $where = ' spu_id=:spu_id: order by add_rule_time asc';
        $res = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $res;
    }

    public function getSupplier($id)
    {
        $data['id'] = (int)$id;
        if( !$data['id'] ){
            return '';
        }
        $table = '\Shop\Models\BaiyangSkuSupplier';
        $selections = '*';
        $where = 'id=:id: limit 1';
        $res = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $res;
    }


    /**
     * 获取单一sku数据
     * @param int $id sku id
     * @author 梁伟
     * @date: 2016/9/6
     */
    public function getSkuOne($id)
    {
        if( !isset($id) || $id <= 0 ){
            return $this->arrayData('参数错误222','','','error');
        }
        $data['id'] = (int)$id;
        $table = '\Shop\Models\BaiyangGoods';
        $selections = 'id,spu_id,goods_name,barcode,prod_code,goods_number,attr_list,weight,size,meta_title,meta_keyword,meta_description,manufacturer,period,freight_temp_id,video_id,bind_gift,sku_alias_name,sku_mobile_name,sku_pc_subheading,sku_mobile_subheading,attribute_value_id,goods_price,market_price,v_stock,is_use_stock,is_on_sale,is_has_largess,spu_id,rule_value_id,is_unified_price,product_type,goods_image,big_path,small_path,specifications,sku_usage,is_hot,is_recommend,sale_timing_app,sale_timing_wap,sku_label,is_lock,prod_name_common';
        $where = ' id=:id: and is_global=0';
        $res = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $res;
    }
    
     /**
     * 获取单一sku数据
     * @param int $id sku id
     * @author 梁伟
     * @date: 2016/9/6
     */
    public function getSkuOneByErp($erp_id,$id=0)
    {
        if( !isset($erp_id) || $erp_id <= 0 ){
            return $this->arrayData('参数错误','','','error');
        }
        if($id){
         $data['id'] = $id;  
        }else{
         $data['id'] = -1;  
        }
        $data['erp_id'] = $erp_id;
        $table = '\Shop\Models\BaiyangGoods';
        $selections = 'id,spu_id,goods_name,barcode,prod_code,goods_number,attr_list,weight,size,meta_title,meta_keyword,meta_description,manufacturer,period,freight_temp_id,video_id,bind_gift,sku_alias_name,sku_mobile_name,sku_pc_subheading,sku_mobile_subheading,attribute_value_id,goods_price,market_price,v_stock,is_use_stock,is_on_sale,is_has_largess,spu_id,rule_value_id,is_unified_price,product_type,goods_image,big_path,small_path,specifications,sku_usage,is_hot,is_recommend,sale_timing_app,sale_timing_wap,sku_label,is_lock,prod_name_common';
        $where = ' (product_code=:erp_id: or id=:id:)  and is_global=0';
        $res = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $res;
    }
    
    /**
     * 获取单一spu数据
     * @param int $id sku id
     * @author 吴荣飞
     * @date: 2016/9/6
     */
    public function getSpuOne($id)
    {
        if( !isset($id) || $id <= 0 ){
            return array();
            //return $this->arrayData('参数错误','','','error');
        }
        $data['id'] = (int)$id;
        $table = '\Shop\Models\BaiyangSpu';
        $selections = 'category_id,category_path';
        $where = ' spu_id=:id:';
        $res = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $res;
    }

    /**
     * 获取sku详情数据
     * @param int $id sku id
     * @author 梁伟
     * @date: 2016/9/18
     */
    public function getSkuInfo($id)
    {
        $data['id'] = (int)$id;
        $table = '\Shop\Models\BaiyangSkuInfo';
        $selections = '*';
        $where = 'sku_id=:id: ';
        $res = BaiyangSkuInfoData::getInstance()->select($selections,$table,$data,$where);
//        if(isset($res[0]['sku_detail_pc']) && !empty($res[0]['sku_detail_pc']) && !strpos($res[0]['sku_detail_pc'],'static.baiy')){
//            $res[0]['sku_detail_pc'] = str_replace('<img src="','<img src="'.$this->config['domain']['img'],htmlspecialchars_decode($res[0]['sku_detail_pc']));
//        }
//        if(isset($res[0]['sku_detail_mobile']) && !empty($res[0]['sku_detail_mobile']) && !strpos($res[0]['sku_detail_mobile'],'static.baiy')){
//            $res[0]['sku_detail_mobile'] = str_replace('<img src="','<img src="'.$this->config['domain']['img'],htmlspecialchars_decode($res[0]['sku_detail_mobile']));
//        }
        return $res;
    }

    /**
     * 获取sku数据
     * @param int $id sku id
     * @author 梁伟
     * @date: 2016/9/18
     */
    public function getSku($id)
    {
        $data['id'] = (int)$id;
        $table = '\Shop\Models\BaiyangGoods';
        $selections = '*';
        $where = ' id=:id: ';
        $res = BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
        return $res;
    }

    /**
     * 修改sku库存
     * @param array []
     * @author 梁伟
     * @date: 2016/9/18
     */
    public function setStockSku($param)
    {
        if( isset($param['stock']) && $param['stock'] == 1 ){
            $sku['is_use_stock'] =  1;
        }else if( isset($param['stock']) && $param['stock'] ==2 ){
            $sku['is_use_stock'] =  2;
            if( isset($param['virtual_stock_default']) ){
                $data['virtual_stock_default'] = (int)$param['virtual_stock_default'];
            }else{
                return $this->arrayData('请填写虚拟公共库存','','','error');
            }
            $columStr = 'virtual_stock_default=:virtual_stock_default:';
        }else if( isset($param['stock']) && $param['stock'] ==3 ){
            $sku['is_use_stock'] =  3;
            if( isset($param['virtual_stock_pc']) ){
                $data['virtual_stock_pc'] = (int)$param['virtual_stock_pc'];
            }else{
                return $this->arrayData('请填写虚拟pc库存','','','error');
            }
            if( isset($param['virtual_stock_app']) ){
                $data['virtual_stock_app'] = (int)$param['virtual_stock_app'];
            }else{
                return $this->arrayData('请填写虚拟app库存','','','error');
            }
            if( isset($param['virtual_stock_wap']) ){
                $data['virtual_stock_wap'] = (int)$param['virtual_stock_wap'];
            }else{
                return $this->arrayData('请填写虚拟wap库存','','','error');
            }
            if( isset($param['virtual_stock_wechat']) ){
                $data['virtual_stock_wechat'] = (int)$param['virtual_stock_wechat'];
            }else{
                return $this->arrayData('请填写虚拟WeChat库存','','','error');
            }
            $columStr = 'virtual_stock_pc=:virtual_stock_pc:,virtual_stock_app=:virtual_stock_app:,virtual_stock_wap=:virtual_stock_wap:,virtual_stock_wechat=:virtual_stock_wechat:';
        }else{
            return $this->arrayData('参数错误','','','error');
        }
        $sku['id'] = (int)$param['id'];
        $data['sku_id'] = (int)$param['id'];
        //事务处理
        $this->dbWrite->begin();
        $skuRes =   BaiyangSkuData::getInstance()->update('is_use_stock=:is_use_stock:','\Shop\Models\BaiyangGoods',$sku,'id=:id:');
        if(!$skuRes){
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','','error');
        }
        if( $param['stock'] != 1 ){
            $SkuInfoData = BaiyangSkuInfoData::getInstance();
            //查询是否存在
            $con = $SkuInfoData->count('\Shop\Models\BaiyangSkuInfo',array('sku_id'=>$param['id']),'sku_id=:sku_id: limit 1');
            if($con){
                $where  =   'sku_id=:sku_id:';
                $skuInfoRes =   $SkuInfoData->update($columStr,'\Shop\Models\BaiyangSkuInfo',$data,$where);
                if(!$skuInfoRes){
                    $this->dbWrite->rollback();
                    return $this->arrayData('修改失败','','','error');
                }
            }else{
                $skuInfoRes =   $SkuInfoData->insert('\Shop\Models\BaiyangSkuInfo',$data);
                if(!$skuInfoRes){
                    $this->dbWrite->rollback();
                    return $this->arrayData('修改失败','','','error');
                }
            }
        }
        //事务提交
        $this->dbWrite->commit();
        //更新缓存
        UpdateCacheSkuData::getInstance()->updateSkuInfo((int)$param['id']);
        $this->updateEsSearch((int)$param['id']);
        return $this->arrayData('修改成功','',$param,'success');
    }

    /**
     * 添加sku数据
     * @param = []
     * @return bool true|false 是否修改成功
     * @author 梁伟
     * @date: 2016/9/6
     */
    public function addSku($param)
    {
        //判断数据是否合法
        if(!isset($param['spu_id']) || empty($param['spu_id']) || !isset($param['sku_id']) || empty($param['sku_id'])){
            return $this->arrayData('参数错误','','','error');
        }
        //判断价格信息是否正确
        if(isset($param['is_unified_price']) && $param['is_unified_price'] == 1){
            //不使用统一价
            if(!isset($param['goods_price_pc']) || $param['goods_price_pc'] <= 0){
                return $this->arrayData('请填写pc端销售价格','','','error');
            }
            if(!isset($param['market_price_pc']) || $param['market_price_pc'] <= 0){
                return $this->arrayData('请填写pc端市场价格','','','error');
            }
            if(!isset($param['goods_price_app']) || $param['goods_price_app'] <= 0){
                return $this->arrayData('请填写app端销售价格','','','error');
            }
            if(!isset($param['market_price_app']) || $param['market_price_app'] <= 0){
                return $this->arrayData('请填写app端市场价格','','','error');
            }
            if(!isset($param['goods_price_wap']) || $param['goods_price_wap'] <= 0){
                return $this->arrayData('请填写wap端销售价格','','','error');
            }
            if(!isset($param['market_price_wap']) || $param['market_price_wap'] <= 0){
                return $this->arrayData('请填写wap端市场价格','','','error');
            }
            if(!isset($param['goods_price_wechat']) || $param['goods_price_wechat'] <= 0){
                return $this->arrayData('请填写WeChat端销售价格','','','error');
            }
            if(!isset($param['market_price_wechat']) || $param['market_price_wechat'] <= 0){
                return $this->arrayData('请填写WeChat端市场价格','','','error');
            }
        }else{
            //使用统一价
            if(!isset($param['goods_price']) || $param['goods_price'] <= 0){
                return $this->arrayData('请填写统一销售价','','','error');
            }
            if(!isset($param['market_price']) || $param['market_price'] <= 0){
                return $this->arrayData('请填写统一市场价','','','error');
            }
        }
        //判断排序信息
//        if(empty($param['sort'])){
//            return $this->arrayData('请填写排序','','','error');
//        }
        //处理品规值信息
        if(isset($param['rule_value']) && isset($param['rule_pid'])){
            if(!is_array($param['rule_value'])){
                $param['rule_value'] = explode('+',$param['rule_value']);
            }
            if(!is_array($param['rule_pid'])){
                $param['rule_pid'] = explode('+',$param['rule_pid']);
            }
            //查询品规值是否存在
            $ProductRuleData = BaiyangProductRuleData::getInstance();
            $ruleTable = '\Shop\Models\BaiyangProductRule';
            foreach($param['rule_value'] as $k=>$v){
                if(!empty($v) && !empty($param['rule_pid'][$k])){
                    $ruleDate = array('name'=>$v,'pid'=>$param['rule_pid'][$k]);
                    $ruleId[$k] = $ProductRuleData->select('id',$ruleTable,$ruleDate,'name=:name: and pid=:pid: limit 1')[0]['id'];
                    if(!$ruleId[$k]){
                        $tmp = $ProductRuleData->insert($ruleTable,$ruleDate,true);
                        $ruleId[$k] = (int)$tmp;
                    }
                }else{
                    $ruleId[$k] = 0;
                }
            }
            $rule = implode('+',$ruleId);
        }else{
            $rule   =   '';
        }
        //赠品设置
        $data1['whether_is_gift']   =   isset($param['set_whether_is_gift'])?$param['set_whether_is_gift']:0;       //赠品是否分端
        $data['sku_id']             =   $param['sku_id'];
        $data['rule_value_id']      =   trim($rule,'+');
        $data['rule_value0']      =   (isset($ruleId) && isset($ruleId[0]) && !empty($ruleId[0]))?$ruleId[0]:0;
        $data['rule_value1']      =   (isset($ruleId) && isset($ruleId[1]) && !empty($ruleId[1]))?$ruleId[1]:0;
        $data['rule_value2']      =   (isset($ruleId) && isset($ruleId[2]) && !empty($ruleId[2]))?$ruleId[2]:0;
        $data['is_unified_price']   =   $param['is_unified_price'];                                         //是否使用统一价 0 使用 1 不使用
        $data['goods_price']        =   isset($param['goods_price'])?$param['goods_price']:0;               //统一销售价
        $data['market_price']       =   isset($param['market_price'])?$param['market_price']:0;             //统一市场价
        $data['is_lock']            =   isset($param['is_lock'])?$param['is_lock']:0;                      //是否锁定
        $data['sort']               =   (isset($param['sort']) && ($param['sort']!=''))?(int)$param['sort']:1;                             //排序
        $data1['goods_price_pc']    =   isset($param['goods_price_pc'])?$param['goods_price_pc']:0;         //商品价格 pc
        $data1['market_price_pc']   =   isset($param['market_price_pc'])?$param['market_price_pc']:0;       //市场价格 pc
        $data1['goods_price_app']   =   isset($param['goods_price_app'])?$param['goods_price_app']:0;       //商品价格 app
        $data1['market_price_app']  =   isset($param['market_price_app'])?$param['market_price_app']:0;     //市场价格 app
        $data1['goods_price_wap']   =   isset($param['goods_price_wap'])?$param['goods_price_wap']:0;       //商品价格 wap
        $data1['market_price_wap']  =   isset($param['market_price_wap'])?$param['market_price_wap']:0;     //市场价格 wap
        $data1['goods_price_wechat']   =   isset($param['goods_price_wechat'])?$param['goods_price_wechat']:0;       //商品价格 wap
        $data1['market_price_wechat']  =   isset($param['market_price_wechat'])?$param['market_price_wechat']:0;     //市场价格 wap
        $data1['gift_pc']           =   isset($param['gift_pc'])?$param['gift_pc']:0;                       //是否为赠品 pc
        $data1['gift_app']          =   isset($param['gift_app'])?$param['gift_app']:0;                     //是否为赠品 app
        $data1['gift_wap']          =   isset($param['gift_wap'])?$param['gift_wap']:0;                     //是否为赠品 wap
        $data1['gift_wechat']       =   isset($param['gift_wechat'])?$param['gift_wechat']:0;               //是否为赠品 WeChat
        $data1['sku_id']            =   $param['sku_id'];
        //事务处理
        $this->dbWrite->begin();
        //sku表数据提交
        $columStr =  'rule_value_id=:rule_value_id:,is_unified_price=:is_unified_price:,goods_price=:goods_price:,market_price=:market_price:,rule_value0=:rule_value0:,rule_value1=:rule_value1:,rule_value2=:rule_value2:,is_lock=:is_lock:,sort=:sort:';
        if(isset($param['set_whether_is_gift']) && $param['set_whether_is_gift'] == 0){
            $data['product_type']       =   isset($param['is_gift'])?$param['is_gift']:0;
            $columStr .= ',product_type=:product_type:';
        }
        $where  =   'id=:sku_id:';
        $skuRes =   BaiyangSkuData::getInstance()->update($columStr,'\Shop\Models\BaiyangGoods',$data,$where);
        if(!$skuRes){
            $this->dbWrite->rollback();
            return $this->arrayData('修改失败','','','error');
        }
        //skuInfo表数据提交
        //查询是否存在
        $SkuInfoData = BaiyangSkuInfoData::getInstance();
        $con = $SkuInfoData->count('\Shop\Models\BaiyangSkuInfo',array('sku_id'=>$param['sku_id']),'sku_id=:sku_id: limit 1');
        if($con){
            $columStr1 =  'goods_price_pc=:goods_price_pc:,market_price_pc=:market_price_pc:,goods_price_app=:goods_price_app:,market_price_app=:market_price_app:,goods_price_wap=:goods_price_wap:,market_price_wap=:market_price_wap:,goods_price_wechat=:goods_price_wechat:,market_price_wechat=:market_price_wechat:,whether_is_gift=:whether_is_gift:,gift_pc=:gift_pc:,gift_app=:gift_app:,gift_wap=:gift_wap:,gift_wechat=:gift_wechat:';
            $where1  =   'sku_id=:sku_id:';
            $skuInfoRes =   $SkuInfoData->update($columStr1,'\Shop\Models\BaiyangSkuInfo',$data1,$where1);
            if(!$skuInfoRes){
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','','','error');
            }
        }else{
            $skuInfoRes =   $SkuInfoData->insert('\Shop\Models\BaiyangSkuInfo',$data1);
            if(!$skuInfoRes){
                $this->dbWrite->rollback();
                return $this->arrayData('修改失败','','','error');
            }
        }
        //事务提交
        $this->dbWrite->commit();
        //更新缓存
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        $UpdateCacheSkuData->updateSkuInfo((int)$param['sku_id']);
        $UpdateCacheSkuData->updateSkuRules((int)$param['spu_id']);
        $this->updateEsSearch((int)$param['sku_id']);
        return $this->arrayData('修改成功','','','success');
    }

    /**
     * 修改sku上下架，推荐，热门，运费模板信息
     * $param []
     * @return bool true|false 是否修改成功
     * @author 梁伟
     * @date: 2016/9/22
     */
    public function setTiming($param)
    {
        if($param['goods-shelves'] == 1){
            $timing = $this->setSkuShelves($param);
        }elseif($param['goods-shelves'] == 2){
            $timing = $this->setSkuTiming($param);
        }else{
            return $this->arrayData('参数错误','','','error');
        }

        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        //取消spu下商品的热门和推荐
        if( isset($param['spu_id']) && $param['spu_id'] > 0 ){
            $res = $this->setHotRecommend($param);
            if(!$res){
                $UpdateCacheSkuData->getHotSku();
                $UpdateCacheSkuData->getRecommendSku();
                return $this->arrayData('修改失败','','','error');
            }
        }

        //热门
        if( !empty($param['goods_hot'])){
            $res = $this->setHotAll($param);
            if(!$res){
                $UpdateCacheSkuData->getHotSku();
                $UpdateCacheSkuData->getRecommendSku();
                return $this->arrayData('修改失败','','','error');
            }
        }

        //推荐
        if( !empty($param['goods_recommend'])){
            $res = $this->setRecommendAll($param);
            if(!$res){
                $UpdateCacheSkuData->getHotSku();
                $UpdateCacheSkuData->getRecommendSku();
                return $this->arrayData('修改失败','','','error');
            }
        }

        //运费
        if( !empty($param['freight'])){
            $res = $this->setSpuFreight($param);
            if(!$res){
                return $this->arrayData('修改失败','','','error');
            }
        }

        //退换设置
        if( !empty($param['returned_goods_act'])) {
            $res = $this->setReturnedGoods($param);
            if (!$res) {
                return $this->arrayData('修改失败', '', '', 'error');
            }
        }

        $UpdateCacheSkuData->updateSkuRules((int)$param['spu_id']);
        $UpdateCacheSkuData->getHotSku();
        $UpdateCacheSkuData->getRecommendSku();
        //修改spu下所有商品的缓存
        $this->updateSkuCache((int)$param['spu_id']);
        $this->updateEsSearch(0,(int)$param['spu_id']);
        return $this->arrayData('修改成功','',$timing,'error');
    }

    //退货设置
    public function setReturnedGoods($param)
    {
        $selections = 'returned_goods_time=:returned_goods_time:';
        $table      = '\Shop\Models\BaiyangSkuInfo';
        $where      = 'sku_id=:id:';
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        foreach( $param['sku_id'] as $k=>$v){
            $data['id']             = $v;
            if($param['returned_goods_act'][$k] == 0){
                $data['returned_goods_time']      = 0;
            }else{
                $data['returned_goods_time']      = $param['returned_goods_value'][$k];
            }
            $res = $BaiyangSkuData->update($selections,$table,$data,$where);
            if(!$res){
                return $res;
            }
        }
        return $res;
    }

    public function updateSkuCache($spuId)
    {
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $table      = '\Shop\Models\BaiyangGoods';
        $skuAll = $BaiyangSkuData->select('id',$table,['id'=>$spuId],'spu_id=:id:');
        foreach($skuAll as $v){
            $UpdateCacheSkuData->updateSkuInfo($v['id']);
        }
    }
	
	public function updateCache($param)
	{
		if(!isset($param['goodsIds']) || empty($param['goodsIds'])){
			return $this->arrayData('请填写数据!','','','error');
		}
		$match = preg_match('/[^0-9,]$/',$param['goodsIds']);
		if($match){
			return $this->arrayData('请检查数据是否正确！','','','error');
		}
		$param['goodsIds'] = trim($param['goodsIds'],',');
		$goods = explode(',',$param['goodsIds']);
		if(!is_array($goods)){
			return $this->arrayData('请检查数据是否正确！','','','error');
		}
		//更新缓存
		$key = CacheGoodsKey::SKU_INFO;
		foreach($goods as $v){
			$this->RedisCache->delete($key.(int)$v);
		}
		
		//更新es引擎
		$data['goodNameId'] = $param['goodsIds'];
		$requestData = http_build_query($data);
		$esUrl = $this->config['domain']['updateEsSearch'].'pces/searchDataUpdate.do';
		$res = $this->curl->sendPost($esUrl, $requestData);
		$res = json_decode($res,true);
		$info = '';
		if($res['code'] != 200){
			$info .= '商品'.$res['error'].'更新es搜索引擎失败！';
		}
		
		//更新pc前台缓存
		$res = $this->curl->sendPost($this->config->pc_url[$this->config->environment].'/shop/goods/deleteCacheFile/', 'goodsIds='.$param['goodsIds']);
		$res = json_decode($res);
		if($res->status != 200){
			$info .= '商品';
			foreach($res->data as $v){
				$info .= $v.',';
			}
			$info .= '更新pc前台缓存失败！';
		}
		if(empty($info)){
			$info = '更新成功！';
		}
		return $this->arrayData($info,'','','error');
	}
    
    /**
     * 批量操作上下架信息
     * @param = [
     *      'goods-shelves'     =>   int,//上下架类型，1为立即上下架，2为定时上下架
     *      ‘sku_id’            =>  array(),//要修改的sku
     *      'is_on_sale'        =>  array(),//要修改sku上下架pc端，以sku_id为键
     *      'sale_timing_wap'   =>  array(),//要修改sku上下架wap端，以sku_id为键
     *      'sale_timing_app'   =>  array(),//要修改sku上下架app端，以sku_id为键
     *      ’spu_id‘            =>  int，//spu id
     * ]
     * @return bool true|false;
     * @author 梁伟
     * @date: 2016/10/9
     */
    private function setSkuShelves($param)
    {
        $selections = 'is_on_sale=:is_on_sale:,sale_timing_wap=:sale_timing_wap:,sale_timing_app=:sale_timing_app:,sale_timing_wechat=:sale_timing_wechat:';
        $table      = '\Shop\Models\BaiyangGoods';
        $where      = 'id=:id:';
        $info =array();
        $i = 0;
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        foreach( $param['sku_id'] as $k=>$v){
            $data['is_on_sale']      = $param['is_on_sale'][$k];
            $data['sale_timing_wap'] = $param['sale_timing_wap'][$k];
            $data['sale_timing_app'] = $param['sale_timing_app'][$k];
            $data['sale_timing_wechat'] = $param['sale_timing_wechat'][$k];
            if($data['is_on_sale'] == 1 || $data['sale_timing_wap'] == 1 || $data['sale_timing_app'] == 1 || $data['sale_timing_wechat'] == 1){
                $tmp = $this->selectIsImg($v);
                if(!$tmp){
                    $info[$i]['sku_id'] = $v;
                    $info[$i]['sale'] = $BaiyangSkuData->select('is_on_sale,sale_timing_wap,sale_timing_app,sale_timing_wechat',$table,['id'=>$v],'id=:id:')[0];
                    $i++;
                    continue;
                }
            }
            $data['id']             = $v;
            $res = $BaiyangSkuData->update($selections,$table,$data,$where);
            if(!$res){
                return $res;
            }
            $UpdateCacheSkuData->updateSkuInfo((int)$param['sku_id']);
        }
        $UpdateCacheSkuData->updateSkuRules((int)$param['spu_id']);
        $UpdateCacheSkuData->getHotSku();
        $UpdateCacheSkuData->getRecommendSku();
        if(empty($info)){
            return $res;
        }else{
            $info['num'] = $i;
            return $info;
        }
    }

    /**
     * 批量操作定时上下架信息
     * @param = [
     *      'goods-shelves'     =>   int,//上下架类型，1为立即上下架，2为定时上下架
     *      ‘sku_id’            =>  array(),//要修改的sku
     *      'is_on_sale'        =>  array(),//要修改sku上下架pc端，以sku_id为键
     *      'sale_timing_wap'   =>  array(),//要修改sku上下架wap端，以sku_id为键
     *      'sale_timing_app'   =>  array(),//要修改sku上下架app端，以sku_id为键
     *      'time_start'        =>  string,//上架时间
     *      'time_end'          =>  string,//下架时间
     * ]
     * @return bool true|false;
     * @author 梁伟
     * @date: 2016/10/9
     */
    public function setSkuTiming($param)
    {
        $key = CacheGoodsKey::SKU_TIMING_TIME;
        //定时信息存入redis
        $timingTime = $this->RedisCache->getValue($key);
        $info = array();
        $i = 0;
        $j = 0;
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        $table      = '\Shop\Models\BaiyangGoods';
        $data = array();
        $datas = array();
        $datas['allTime'] = $param['setTimeEnd'];
        foreach( $param['sku_id'] as $k=>$v){
            //判断是否可以上架
            if($param['time_start'][$i] || $param['time_start_pc'][$i] || $param['time_start_app'][$i] || $param['time_start_wap'][$i] || $param['time_start_wechat'][$i] ){
                $tmp = $this->selectIsImg($v);
                if(!$tmp){
                    $info[$j]['sku_id'] = $v;
                    $info[$j]['sale'] = isset($timing[$v])?$timing[$v]:$BaiyangSkuData->select('is_on_sale,sale_timing_wap,sale_timing_app,sale_timing_wechat',$table,['id'=>$v],'id=:id:')[0];
                    $data = $info[$i]['sale'];
                    $j++;
                    continue;
                }
                $datas['time'][$v]['time_start'] = empty($param['time_start'][$i])?0:strtotime($param['time_start'][$i]);
                $datas['time'][$v]['time_end'] = empty($param['time_end'][$i])?0:strtotime($param['time_end'][$i]);
                $datas['time'][$v]['time_start_app'] = empty($param['time_start_app'][$i])?0:strtotime($param['time_start_app'][$i]);
                $datas['time'][$v]['time_start_wap'] = empty($param['time_start_wap'][$i])?0:strtotime($param['time_start_wap'][$i]);
                $datas['time'][$v]['time_start_pc'] = empty($param['time_start_pc'][$i])?0:strtotime($param['time_start_pc'][$i]);
                $datas['time'][$v]['time_start_wechat'] = empty($param['time_start_wechat'][$i])?0:strtotime($param['time_start_wechat'][$i]);
                $datas['time'][$v]['time_end_pc'] = empty($param['time_end_pc'][$i])?0:strtotime($param['time_end_pc'][$i]);
                $datas['time'][$v]['time_end_app'] = empty($param['time_end_app'][$i])?0:strtotime($param['time_end_app'][$i]);
                $datas['time'][$v]['time_end_wap'] = empty($param['time_end_wap'][$i])?0:strtotime($param['time_end_wap'][$i]);
                $datas['time'][$v]['time_end_wechat'] = empty($param['time_end_wechat'][$i])?0:strtotime($param['time_end_wechat'][$i]);
            }else{
                $datas['time'][$v]['time_start'] = empty($param['time_start'][$i])?0:strtotime($param['time_start'][$i]);
                $datas['time'][$v]['time_end'] = empty($param['time_end'][$i])?0:strtotime($param['time_end'][$i]);
                $datas['time'][$v]['time_start_app'] = empty($param['time_start_app'][$i])?0:strtotime($param['time_start_app'][$i]);
                $datas['time'][$v]['time_start_wap'] = empty($param['time_start_wap'][$i])?0:strtotime($param['time_start_wap'][$i]);
                $datas['time'][$v]['time_start_pc'] = empty($param['time_start_pc'][$i])?0:strtotime($param['time_start_pc'][$i]);
                $datas['time'][$v]['time_start_wechat'] = empty($param['time_start_wechat'][$i])?0:strtotime($param['time_start_wechat'][$i]);
                $datas['time'][$v]['time_end_pc'] = empty($param['time_end_pc'][$i])?0:strtotime($param['time_end_pc'][$i]);
                $datas['time'][$v]['time_end_app'] = empty($param['time_end_app'][$i])?0:strtotime($param['time_end_app'][$i]);
                $datas['time'][$v]['time_end_wap'] = empty($param['time_end_wap'][$i])?0:strtotime($param['time_end_wap'][$i]);
                $datas['time'][$v]['time_end_wechat'] = empty($param['time_end_wechat'][$i])?0:strtotime($param['time_end_wechat'][$i]);
            }
            $i++;
        }
        $timingTime[$param['spu_id']] = $datas;
        $this->RedisCache->setValue($key,$timingTime);
        if(!empty($info)){
            $info['num'] = $j;
            return $info;
        }else{
            return true;
        }
    }
    /**
     * 根据spu_id取消热门和推荐
     * @param = ['spu_id'=>int]
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/10/9
     */
    public function setHotRecommend($param)
    {
//        $data = array();
        $selections = 'is_recommend=:is_recommend:,is_hot=:is_hot:';
        $table = '\Shop\Models\BaiyangGoods';
        $data['is_recommend'] = 0;
        $data['is_hot'] = 0;
        $sku = $this->getSpuSku((int)$param['spu_id']);
        $where = 'id=:id:';
        if($sku){
            foreach($sku as $v){
                $data['id'] = $v['id'];
                $res = BaiyangSkuData::getInstance()->update($selections,$table,$data,$where);
                if(empty($res)){
                    return $res;
                }
            }
        }
        return $res;
    }

    /**
     * 批量设置热门商品
     * @param = [
     *      'goods_hot'        =>      array(),//设置为热门sku id
     * ]
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/10/9
     */
    public function setHotAll($param)
    {
//        $data = array();
//        //推荐
        $selections = 'is_hot=:is_hot:';
        $table = '\Shop\Models\BaiyangGoods';
        $data['is_hot'] = 1;
        $where = '';
        $i = 0;
        $len = count($param['goods_hot']);
        for( $i; $i < $len; $i++ ){
            if( $i == 0 ){
                $where .= 'id=:id'.$i.':';
                $data['id'.$i] = $param['goods_hot'][$i];
            }else{
                $where .= ' or id=:id'.$i.':';
                $data['id'.$i] = $param['goods_hot'][$i];
            }
        }
        $res = BaiyangSkuData::getInstance()->update($selections,$table,$data,$where);
        if($res){
            //清除缓存
            $this->cache->selectDb(8);
            $this->cache->delete(CacheGoodsKey::SKU_HOT);
        }
        return $res;
    }

    /**
     * 批量设置推荐商品
     * @param = [
     *      'goods_recommend'        =>      array(),//设置为热门sku id
     * ]
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/10/9
     */
    public function setRecommendAll($param)
    {
        //推荐
        $selections = 'is_recommend=:is_recommend:';
        $table = '\Shop\Models\BaiyangGoods';
        $data['is_recommend'] = 1;
        $where = '';
        $i = 0;
        $len = count($param['goods_recommend']);
        for( $i; $i < $len; $i++ ){
            if( $i == 0 ){
                $where .= 'id=:id'.$i.':';
                $data['id'.$i] = $param['goods_recommend'][$i];
            }else{
                $where .= ' or id=:id'.$i.':';
                $data['id'.$i] = $param['goods_recommend'][$i];
            }
        }
        $res = BaiyangSkuData::getInstance()->update($selections,$table,$data,$where);
        if($res){
            //清除缓存
            $this->cache->selectDb(8);
            $this->cache->delete(CacheGoodsKey::SKU_RECOMMEND);
        }
        return $res;
    }

    /**
     * 根据spu_id设置统一运费模板
     * @param = [
     *      'spu_id'        =>      int,//统一设置的spu id
     *      'freight'       =>      int,//运费模板id
     * ]
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/10/9
     */
    public function setSpuFreight($param)
    {
        $selections     = 'pc_freight_temp_id=:pc_freight_temp_id:';
        $table          = '\Shop\Models\BaiyangGoods';
        $BaiyangSkuData = BaiyangSkuData::getInstance();
        foreach( $param['sku_id'] as $k=>$v) {
            $data['pc_freight_temp_id'] = $param['freight'][$k];
            $data['id'] = $v;
            $where = 'id=:id:';
            $res = $BaiyangSkuData->update($selections, $table, $data, $where);
            if(!$res) return $res;
        }
        return $res;
    }
/**************************************** sku图片信息管理 ********************************************************/
    /**
     * 根据sku_id或spu_id获取sku图片信息
     * @param array() []
     * @return array()
     * @author 梁伟
     * @date: 2016/9/6
     */
    public function getSkuImg($param)
    {
        foreach( $param as $k=>$v ){
            $data[$k]   = (int)$v;
            $where      = $k . '=:' . $k . ':';
        }
        $where .= ' and is_default!=1 order by sort';
        return BaiyangSkuImagesData::getInstance()->selectImg($data,$where);
    }

    /**
     * 添加sku图片信息
     * @param array() 图片信息
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function insertSkuImg($param)
    {
        if( $param['id'] <= 0 && $param['spu_id'] <= 0 ){
            return false;
        }
        $data['goods_id']     = (int)$param['id'];
        $data['is_default'] = 0;
        $data['sort']       = 100;
        $data['spu_id']     = (int)$param['spu_id'];
        $table              = '\Shop\Models\BaiyangGoodsImages';
        $SkuImagesData      = BaseData::getInstance();
        //查询是否有默认商品
        foreach($param['data'] as $v ){
            $data['goods_big_image'] =   $v['thumb'][0];
            $data['goods_middle_image'] =   $v['thumb'][1];
            $data['goods_image'] =   $v['thumb'][2];
            $res = $SkuImagesData->insert($table,$data,true);
            if(!$res){
                return $this->arrayData('图片上传失败','','','error');
            }else{
                $tmp[] = $res;
            }
        }
        //更新缓存
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        if( $param['id'] > 0 ){
            $UpdateCacheSkuData->updateSkuImg((int)$param['id']);
        }else{
            $UpdateCacheSkuData->updateSpuImg((int)$param['spu_id']);
        }
        return $tmp;
    }

    /**
     * 修改sku主图信息
     * @param array() 图片信息
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function updateSkuImgMain($param)
    {
        if( $param['id'] <= 0 && $param['spu_id'] <= 0 ){
            return $this->arrayData('信息丢失','','','error');
        }
        if( isset($param['id']) && $param['id'] > 0){
            $dataImg['id'] = (int)$param['id'];
            $whereImg = 'id=:id:';
            $tableImg = '\Shop\Models\BaiyangGoods';
        }else{
            $dataImg['spu_id'] = (int)$param['spu_id'];
            $whereImg = 'spu_id=:spu_id:';
            $tableImg = '\Shop\Models\BaiyangSpu';
        }
        $dataImg['big_path']    =   $param['data'][0]['thumb'][0];
        $dataImg['goods_image'] =   $param['data'][0]['thumb'][1];
        $dataImg['small_path']  =   $param['data'][0]['thumb'][2];
        $columStrImg = 'big_path=:big_path:,goods_image=:goods_image:,small_path=:small_path:';
        $res = BaiyangSkuData::getInstance()->update($columStrImg,$tableImg,$dataImg,$whereImg);
        if($res){
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            if(isset($param['id']) && $param['id'] > 0){
                $UpdateCacheSkuData->updateSkuInfo((int)$param['id']);
                $this->updateEsSearch((int)$param['id']);
            }else{
                $UpdateCacheSkuData->updateSpu((int)$param['spu_id']);
                $this->updateEsSearch(0,(int)$param['spu_id']);
            }
        }
        return $res;
    }

    /**
     * 设置sku图片信息排序
     * @param array $id 图片id
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function setSkuImgSort($param)
    {
        $table      = '\Shop\Models\BaiyangGoodsImages';
        $columStr   = 'sort=:sort:';
        $where      = 'id=:id:';
        $BaiyangSkuImages    = BaiyangSkuImagesData::getInstance();
        foreach( $param as $k => $v ){
            $data['id']     = $v;
            $data['sort']   = $k;
            $res = $BaiyangSkuImages->update($columStr,$table,$data,$where);
            if(!$res){
                return $this->arrayData(false);
            }
        }
        $sku = $BaiyangSkuImages->select('goods_id,spu_id',$table,['id'=>$data['id']],$where);
        $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
        if($sku[0]['goods_id'] > 0){
            $UpdateCacheSkuData->updateSkuImg((int)$sku[0]['goods_id']);
        }else if($sku[0]['spu_id'] > 0){
            $UpdateCacheSkuData->updateSpuImg((int)$sku[0]['spu_id']);
        }
        return true;
    }

//    /**
//     * 修改sku默认图片信息(不确定还用不用)
//     * @param int $id 图片id
//     * @return bool true|false
//     * @author 梁伟
//     * @date: 2016/9/20
//     */
//    public function updateSkuImg($id)
//    {
//        $SkuImagesData  = BaiyangSkuImagesData::getInstance();
//        $table          = '\Shop\Models\BaiyangSkuImages';
//        $res            = $SkuImagesData->select('id,sku_id,sku_image,sku_middle_image,sku_big_image',$table,array('id'=>(int)$id),'id=:id:');
//        if( $res ){
//            //修改默认图片
//            $r = $SkuImagesData->update('is_default=:is_default:',$table,array('sku_id'=>$res[0]['sku_id'],'is_default'=>0),'sku_id=:sku_id:');
//            if($r){
//                $r = $SkuImagesData->update('is_default=:is_default:',$table,array('id'=>(int)$id,'is_default'=>1),'id=:id:');
//                if( $r ){
//                    //修改sku默认图片路径
//                    $dataImg['big_path']    =   $res[0]['sku_big_image'];
//                    $dataImg['goods_image'] =   $res[0]['sku_middle_image'];
//                    $dataImg['small_path']  =   $res[0]['sku_image'];
//                    $dataImg['id']          =   (int)$res[0]['sku_id'];
//                    $columStrImg            =   'big_path=:big_path:,goods_image=:goods_image:,small_path=:small_path:';
//                    $whereImg               =   'id=:id:';
//                    $tableImg               =   '\Shop\Models\BaiyangSku';
//                    $r = BaiyangSkuData::getInstance()->update($columStrImg,$tableImg,$dataImg,$whereImg);
//                    if( $r ){
//                        return true;
//                    }else{
//                        return false;
//                    }
//                }else{
//                    return false;
//                }
//            }else{
//                return false;
//            }
//        }else{
//            return false;
//        }
//    }

    /**
     * 删除sku图片信息
     * @param int $id 图片id
     * @return bool true|false
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function delSkuImg($id)
    {
        //获取图片信息
        $SkuImagesData  = BaiyangSkuImagesData::getInstance();
        $table          = '\Shop\Models\BaiyangGoodsImages';
        $img            = $SkuImagesData->select('id,goods_id,goods_image sku_image,goods_middle_image sku_middle_image,goods_big_image sku_big_image,is_default,spu_id',$table,array('id'=>(int)$id),'id=:id:');
        $res            = $SkuImagesData->delete($table,array('id'=>(int)$id),'id=:id:');
        if( $res ){
            @$this->FastDfs->deleteFile(str_replace('G1/','',$img[0]['sku_image']),'G1');
            @$this->FastDfs->deleteFile(str_replace('G1/','',$img[0]['sku_middle_image']),'G1');
            @$this->FastDfs->deleteFile(str_replace('G1/','',$img[0]['sku_big_image']),'G1');
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            if($img[0]['goods_id'] > 0){
                $UpdateCacheSkuData->updateSkuImg((int)$img[0]['goods_id']);
            }else if($img[0]['spu_id'] > 0){
                $UpdateCacheSkuData->updateSpuImg((int)$img[0]['spu_id']);
            }
        }
        return $res;
    }

    /**
     * 根据名称或id获取赠品信息
     * @param array() $param = ['name'=>'搜索名称','id'=>'搜索id']
     * @return array()
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function getBindGift($param)
    {
        $data['id']             = (int)$param['test'];
        $data['name']           = '%'.$param['test'].'%';
        $data['shop_id'] = (int)$param['shop_id'];
//        $data['whether_is_gift']   = 2;
        $table                  = '\Shop\Models\BaiyangGoods';
        $selections             = 'id,goods_name,rule_value_id';
        $where                  = '(id=:id: or goods_name like :name:) and supplier_id = :shop_id: ';
        return BaiyangSkuData::getInstance()->select($selections,$table,$data,$where);
    }

    /**
     * 编辑sku详情
     * @param array() $param = ['product-hide-id'=>'修改类型']
     * @return array()
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function editSkuInfo($param)
    {
        $attr = '';
        if(isset($param['product_other']) && is_array($param['product_other'])){
            foreach($param['product_other'] as $k=>$v){
                if( $v ){
                    $attr .=$k.':'.$v.',';
                }
            }
        }
        $bind_gift = array();
        if(isset($param['bind_gift']) && is_array($param['bind_gift'])){
            foreach($param['bind_gift'] as $k=>$v){
                if((int)$param['bind_gift_num'][$k] <= 0){
                    return $this->arrayData('请填写正确的赠品数量','','','error');
                }
//                $bind_gift .= $v.':'.(int)$param['bind_gift_num'][$k].',';
                $bind_gift[$k]['id'] = $v;
                $bind_gift[$k]['num'] = (int)$param['bind_gift_num'][$k];
            }
        }
        $sku_label = '';
        if(isset($param['sku_label']) && is_array($param['sku_label'])){
            $sku_label = implode(',',$param['sku_label']);
        }
        $data['sku_alias_name']         =   $param['sku_alias_name'];        //商品别名
        $data['sku_pc_subheading']      =   $param['sku_mobile_subheading'];  //商品副标题pc
        $data['sku_mobile_name']        =   $param['sku_mobile_name'];      //商品名移动端
        $data['sku_mobile_subheading']  =   $param['sku_mobile_subheading'];//商品副标题移动端
        $data['barcode']                =   $param['barcode'];//条形码
        $data['period']                 =   $param['period'];//有效期
        $data['manufacturer']           =   $param['manufacturer'];//生产企业
        $data['attribute_value_id']     =   $attr;          //属性值集合
        $data['bind_gift']              =   empty($bind_gift)?'':json_encode($bind_gift);                   //绑定赠品
        $data['specifications']         =   $param['specifications'];//规格
        $data['sku_usage']              =   $param['sku_usage'];//用法
        $data['sku_label']              =   $sku_label;//标签
        $data['meta_title']             =   $param['meta_title'];
        $data['meta_keyword']           =   $param['meta_keyword'];
        $data['meta_description']       =   $param['meta_description'];
        $data['prod_name_common']       =   $param['prod_name_common'];
        if($param['sku_id'] > 0){
            //编辑sku详情信息
            $data['goods_name']         =   $param['sku_mobile_name'];//商品名pc
            $data['prod_code']          =   $param['sku_batch_num'];//批准文号
            $data['weight']             =   $param['sku_weight'];//重量
            $data['size']               =   $param['sku_bulk'];//体积
            $data['video_id']           =   empty($param['sku_video'])?0:(int)$param['sku_video'];//视频
            $data['sku_id']             =   $param['sku_id'];
            //查看是否有sku详情信息
            $table = '\Shop\Models\BaiyangGoods';
            $res = BaiyangSkuInfoData::getInstance()->count($table,array('sku_id'=>(int)$param['sku_id']),'id=:sku_id: limit 1');
            if($res){
                $columStr   = "sku_label=:sku_label:,sku_usage=:sku_usage:,specifications=:specifications:,sku_alias_name=:sku_alias_name:,goods_name=:goods_name:,sku_mobile_name=:sku_mobile_name:,sku_pc_subheading=:sku_pc_subheading:,sku_mobile_subheading=:sku_mobile_subheading:,prod_code=:prod_code:,barcode=:barcode:,period=:period:,manufacturer=:manufacturer:,weight=:weight:,size=:size:,attribute_value_id=:attribute_value_id:,video_id=:video_id:,bind_gift=:bind_gift:,meta_title=:meta_title:,meta_keyword=:meta_keyword:,meta_description=:meta_description:,prod_name_common=:prod_name_common:";
                $where      = "id=:sku_id:";
                $res        = BaiyangSkuDefaultData::getInstance()->update($columStr,$table,$data,$where);
            }else{
                return $this->arrayData('参数错误','','','error');
            }
        }else{
            //编辑sku默认信息
            if($param['spu_id'] <= 0){
                return $this->arrayData('参数错误','','','error');
            }
            $data['sku_pc_name']        =   $param['sku_pc_name'];//商品名pc
            $data['spu_id']             =   (int)$param['spu_id'];
            $data['sku_batch_num']      =   $param['sku_batch_num'];//批准文号
            $data['sku_weight']         =   $param['sku_weight'];//重量
            $data['sku_bulk']           =   $param['sku_bulk'];//体积
            $data['sku_video']          =   $param['sku_video'];//视频
            //判断添加或者修改
            $table          = '\Shop\Models\BaiyangSkuDefault';
            $DefaultData    = BaiyangSkuDefaultData::getInstance();
            $res            = $DefaultData->count($table,array('spu_id'=>(int)$param['spu_id']),'spu_id=:spu_id: limit 1');
            if($res){
                $columStr   = "sku_label=:sku_label:,sku_usage=:sku_usage:,specifications=:specifications:,sku_alias_name=:sku_alias_name:,sku_pc_name=:sku_pc_name:,sku_mobile_name=:sku_mobile_name:,sku_pc_subheading=:sku_pc_subheading:,sku_mobile_subheading=:sku_mobile_subheading:,sku_batch_num=:sku_batch_num:,barcode=:barcode:,period=:period:,manufacturer=:manufacturer:,sku_weight=:sku_weight:,sku_bulk=:sku_bulk:,attribute_value_id=:attribute_value_id:,sku_video=:sku_video:,bind_gift=:bind_gift:,meta_title=:meta_title:,meta_keyword=:meta_keyword:,meta_description=:meta_description:,prod_name_common=:prod_name_common:";
                $where      = "spu_id=:spu_id:";
                $res        = $DefaultData->update($columStr,$table,$data,$where);
            }else{
                $res        = $DefaultData->insert($table,$data);
            }
        }

        if($res){
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            if($param['sku_id'] > 0){
                $UpdateCacheSkuData->updateSkuInfo($param['sku_id']);
                $this->updateEsSearch($param['sku_id']);
            }else{
                $UpdateCacheSkuData->updateSkuDefault($param['spu_id']);
                $this->updateEsSearch(0,$param['spu_id']);
            }
            return $this->arrayData('修改成功','','','error');
        }else{
            return $this->arrayData('修改失败','','','error');
        }
    }

    /**
     * 获取sku定时表数据
     * @param int $id sku id
     * @return array()
     * @author 梁伟
     * @date: 2016/9/25
     */
    public function getSkuTiming($id){;
        $table      = '\Shop\Models\BaiyangSkuTiming';
        $selections = 'id,sku_id,time_start,time_end,is_enable';
        $where      = ' sku_id=:id:';
        return  BaiyangSkuTimingData::getInstance()->select($selections,$table,array('id'=>(int)$id),$where);
    }

    /**
     * 根据sku_id获取sku定时表数据
     * @param int $id spu id
     * @return array()
     * @author 梁伟
     * @date: 2016/9/25
     */
    public function getSkuSpuTiming($id){
        $timingTime = $this->RedisCache->getValue(CacheGoodsKey::SKU_TIMING_TIME);
        if(!isset($timingTime[$id]) || empty($timingTime[$id])){
            return ['allTime'=>1,'time'=>false];
        }
        $array = array();
        $array['allTime'] = $timingTime[$id]['allTime'];
        foreach($timingTime[$id]['time'] as $k=>$v){
            $array['time'][$k]['time_start'] = (isset($v['time_start'])&&$v['time_start'])?date('Y-m-d H:i:s',$v['time_start']):'';
            $array['time'][$k]['time_end'] = (isset($v['time_end'])&&$v['time_end'])?date('Y-m-d H:i:s',$v['time_end']):'';
            $array['time'][$k]['time_start_pc'] = (isset($v['time_start_pc'])&&$v['time_start_pc'])?date('Y-m-d H:i:s',$v['time_start_pc']):'';
            $array['time'][$k]['time_start_app'] = (isset($v['time_start_app'])&&$v['time_start_app'])?date('Y-m-d H:i:s',$v['time_start_app']):'';
            $array['time'][$k]['time_start_wap'] = (isset($v['time_start_wap'])&&$v['time_start_wap'])?date('Y-m-d H:i:s',$v['time_start_wap']):'';
            $array['time'][$k]['time_start_wechat'] = (isset($v['time_start_wechat'])&&$v['time_start_wechat'])?date('Y-m-d H:i:s',$v['time_start_wechat']):'';
            $array['time'][$k]['time_end_pc'] = (isset($v['time_end_pc'])&&$v['time_end_pc'])?date('Y-m-d H:i:s',$v['time_end_pc']):'';
            $array['time'][$k]['time_end_app'] = (isset($v['time_end_app'])&&$v['time_end_app'])?date('Y-m-d H:i:s',$v['time_end_app']):'';
            $array['time'][$k]['time_end_wap'] = (isset($v['time_end_wap'])&&$v['time_end_wap'])?date('Y-m-d H:i:s',$v['time_end_wap']):'';
            $array['time'][$k]['time_end_wechat'] = (isset($v['time_end_wechat'])&&$v['time_end_wechat'])?date('Y-m-d H:i:s',$v['time_end_wechat']):'';
        }
        return $array;
    }

    /**
     * 编辑详情和广告模板
     * @param array() $param = []
     * @return array()
     * @author 梁伟
     * @date: 2016/9/20
     */
    public function editSkuModel($param)
    {
        $ad = '';
        if( !empty($param['ad']) ){
            $ad .= implode(',',$param['ad']);
        }

        if( !empty($param['btom-ad']) ){
            $ad .= ':'.implode(',',$param['btom-ad']);
        }
        $param['content'] = htmlspecialchars_decode($param['content']);
        if($param['is-pc'] == 'pc'){
            //修改pc端
            if($param['goods-id'] > 0){
                $table                  =   '\Shop\Models\BaiyangSkuInfo';
                $SkuInfoData            =   BaiyangSkuInfoData::getInstance();
                $res                    =   $SkuInfoData->count($table,array('sku_id'=>(int)$param['goods-id']),'sku_id=:sku_id: limit 1');
                $data['ad_id_pc']       =   $ad;
                $data['sku_detail_pc']  =   $param['content'];
                $data['sku_id']         =   (int)$param['goods-id'];
                if($res){
                    $columStr           =   'ad_id_pc=:ad_id_pc:,sku_detail_pc=:sku_detail_pc:';
                    $res                =   $SkuInfoData->update($columStr,$table,$data,'sku_id=:sku_id:');
                }else{
                    $res                =   $SkuInfoData->insert($table,$data);
                }
            }else if($param['spu_id']){
                //修改默认
                $table                  =   '\Shop\Models\BaiyangSkuDefault';
                $SkuDefaultData         =   BaiyangSkuDefaultData::getInstance();
                $res                    =   $SkuDefaultData->count($table,array('spu_id'=>(int)$param['spu_id']),'spu_id=:spu_id: limit 1');
                $data['ad_id_pc']       =   $ad;
                $data['sku_detail_pc']  =   $param['content'];
                $data['spu_id']         =   (int)$param['spu_id'];
                if($res){
                    $columStr           =   'ad_id_pc=:ad_id_pc:,sku_detail_pc=:sku_detail_pc:';
                    $res                =   $SkuDefaultData->update($columStr,$table,$data,'spu_id=:spu_id:');
                }else{
                    $res                =   $SkuDefaultData->insert($table,$data);
                }
            }else{
                return $this->arrayData('参数失败','','','error');
            }
        }else if($param['is-pc'] == 'mobile'){
            //修改移动端
            if($param['goods-id'] > 0){
                $table                  =   '\Shop\Models\BaiyangSkuInfo';
                $SkuInfoData            =   BaiyangSkuInfoData::getInstance();
                $res                    =   $SkuInfoData->count($table,array('sku_id'=>(int)$param['goods-id']),'sku_id=:sku_id: limit 1');
                $data['ad_id_mobile']   =   $ad;
                $data['sku_detail_mobile']  =   $param['content'];
                $data['sku_id']         =   (int)$param['goods-id'];
                if($res){
                    $columStr           =   'ad_id_mobile=:ad_id_mobile:,sku_detail_mobile=:sku_detail_mobile:';
                    $res                =   $SkuInfoData->update($columStr,$table,$data,'sku_id=:sku_id:');
                }else{
                    $res                =   $SkuInfoData->insert($table,$data);
                }
            }else if($param['spu_id']){
                //修改默认
                $table                  =   '\Shop\Models\BaiyangSkuDefault';
                $SkuDefaultData         =   BaiyangSkuDefaultData::getInstance();
                $res                    =   $SkuDefaultData->count($table,array('spu_id'=>(int)$param['spu_id']),'spu_id=:spu_id: limit 1');
                $data['ad_id_mobile']   =   $ad;
                $data['sku_detail_mobile']  =   $param['content'];
                $data['spu_id']         =   (int)$param['spu_id'];
                if($res){
                    $columStr           =   'ad_id_mobile=:ad_id_mobile:,sku_detail_mobile=:sku_detail_mobile:';
                    $res                =   $SkuDefaultData->update($columStr,$table,$data,'spu_id=:spu_id:');
                }else{
                    $res                =   $SkuDefaultData->insert($table,$data);
                }
            }else{
                return $this->arrayData('参数错误','','','error');
            }
        }
        if($res){
            //更新缓存
            $UpdateCacheSkuData = UpdateCacheSkuData::getInstance();
            if($param['goods-id'] > 0){
                $UpdateCacheSkuData->updateSkuInfo($param['goods-id']);
                $this->updatePc($param['goods-id']);
            }else{
                $UpdateCacheSkuData->updateSkuDefault($param['spu_id']);
            }

            return $this->arrayData('修改成功','','','error');
        }else{
            return $this->arrayData('修改失败','','','error');
        }
    }

    /**
     * 编辑说明
     * @param array() $param = []
     * @return array()
     * @author 梁伟
     * @date: 2016/11/8
     */
    public function editSkuInstruction($param)
    {
        if(!isset($param['goods-id']) || $param['goods-id'] <= 0){
            return $this->arrayData('参数错误','','','error');
        }
        $table                      = '\Shop\Models\BaiyangSkuInstruction';
        $BaiyangSkuInfoData         = BaiyangSkuInfoData::getInstance();
        $res                        = $BaiyangSkuInfoData->count($table,array('sku_id'=>(int)$param['goods-id']),'sku_id=:sku_id: limit 1');
        $data                       = $param;
        $data['sku_id']             = (int)$param['goods-id'];
        unset($data['goods-id']);
        if($res){
            $columStr               = 'cn_name=:cn_name:,common_name=:common_name:,eng_name=:eng_name:,component=:component:,indication=:indication:,form=:form:,dosage=:dosage:,adverse_reactions=:adverse_reactions:,contraindications=:contraindications:,precautions=:precautions:,use_in_pregLact=:use_in_pregLact:,use_in_elderly=:use_in_elderly:,use_in_children=:use_in_children:,drug_interactions=:drug_interactions:,mechanismAction=:mechanismAction:,pharmacokinetics=:pharmacokinetics:,description=:description:,storage=:storage:,pack=:pack:,period=:period:,approve_code=:approve_code:,company_name=:company_name:,standard=:standard:,overdosage=:overdosage:,commodity_code=:commodity_code:,clinicalTrial=:clinicalTrial:,functionCategory=:functionCategory:';
            $res = $BaiyangSkuInfoData->update($columStr,$table,$data,'sku_id=:sku_id:');
        }else{
            $res = $BaiyangSkuInfoData->insert($table,$data);
        }
        if($res){
            //更新缓存
            UpdateCacheSkuData::getInstance()->updateSkuInstruction((int)$param['goods-id']);
            $this->updateEsSearch((int)$param['goods-id']);
            return $this->arrayData('修改成功','','','error');
        }else{
            return $this->arrayData('修改失败','','','error');
        }
    }



    //获取说明书
    public function getSkuInstruction($id)
    {
        if( $id <= 0){
            return $this->arrayData('参数错误','','','error');
        }
        $res = BaseData::getInstance()->select('*','\Shop\Models\BaiyangSkuInstruction',array('id'=>$id),'sku_id=:id:');
        if(isset($res[0]) && !empty($res[0]))
            return $res[0];
        return;
    }

    /**
     * 获取多品规值
     * @param $param string 多品规字符串
     * return array
     */
    public function getRuleValue($param)
    {
        $ProductRuleData = BaiyangProductRuleData::getInstance();
        $table = '\Shop\Models\BaiyangProductRule';
        $where = 'id=:id:';
        $arr = array();
        foreach(explode('+',$param) as $k=>$v){
            if(!empty($v)){
                $data['id']= $v;
                $arr[$k] = $ProductRuleData->select('id,name,pid',$table,$data,$where)[0];
            }
        }
        return $arr;
    }

    /**
     * 获取多品规显示信息（显示2个）
     * @param array 品规信息
     * @return array()
     * return string
     */
    public function getRuleShow($param)
    {
        $i = 0;
        $arr = array();
        foreach($param as $v){
            if($v['id'] > 0){
                $arr[] = $v['name'];
                $i++;
                if($i >= 2){
                    break;
                }
            }
        }
        return implode('+',$arr);
    }

    /**
     * @remark 根据sku商品id或商品名称搜索  获取30条
     * @param $goods_name=string ku商品id或商品名称
     * @return array
     * @author 杨永坚
     */
    public function searchSku($goods_name)
    {
        $where = '';
        $data = '';
        $table = array(
            'goodsTable' => '\Shop\Models\BaiyangGoods as g',
            'skuInfoTable' => '\Shop\Models\BaiyangSkuInfo as s'
        );
        if (ctype_digit($goods_name) && $goods_name) {
            $where  = 'g.id=:goods_id:';
            $data['goods_id'] = $goods_name;
        } else {
            $data['goods_name'] = "%{$goods_name}%";
            $where = 'g.goods_name LIKE :goods_name:';
        }
        $where .= ' AND g.is_global=:is_global: limit 30';
        //$data['goods_name'] = "%{$goods_name}%";
        $data['is_global'] = 0;
        //$where = "(goods_name LIKE :goods_name: OR id LIKE :goods_name:) AND is_global=:is_global: limit 30";
        $selections = 'g.id,g.goods_name,g.goods_price price,g.is_unified_price,s.goods_price_pc,s.goods_price_app,s.goods_price_wap,s.goods_price_wechat';
        $result = BaiyangGoodsData::getInstance()->selectJoin($selections, $table, $data, $where);
        //$result = BaseData::getInstance()->select('id,goods_name,price', '\Shop\Models\BaiyangGoods', $data, $where);
        return $result ? $this->arrayData('请求成功！', '', $result) : $this->arrayData('此商品不存在！', '', '', 'error');
    }

	

}
