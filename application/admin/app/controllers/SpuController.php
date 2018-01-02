<?php
/**
 * Created by PhpStorm.
 * User: lw
 * Date: 2016/8/16
 * Time: 15:50
 */

namespace Shop\Admin\Controllers;
use Shop\Services\CategoryService;
use Shop\Services\SpuService;
use Shop\Services\BrandsService;
use Shop\Services\SkuService;
use Shop\Services\AttrNameService;
use Shop\Services\VideoService;
use Shop\Services\SkuAdService;
use Shop\Services\FreightTempService;
use Shop\Models\CacheGoodsKey;
use Shop\Services\BaseService;
use Shop\Services\SupplierService;

class SpuController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        $this->view->setVar('management','goods');
    }

    //SPU列表
    public function listAction()
    {
        $CategoryService = CategoryService::getInstance();
        $SpuService = SpuService::getInstance();
        $category = $CategoryService->getCategory();
        $this->view->setVar('category',$category['data']);
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
        $param['page']  =   $this->request->get('page','trim',1);
        $param['url'] = $this->automaticGetUrl();
        $categoryID = $this->request->get('shop_category');
        if(!empty($categoryID)){
            $categoryTmp = array();
            foreach($categoryID as $v){
                if($v>0) $categoryTmp[] = $v;
            }
            if(!empty($categoryTmp)){
                $cate = $categoryTmp[count($categoryTmp)-1];
                $act = count($categoryTmp);
                $categoryAll = $CategoryService->categoryLists();
                $param['category'] = '';
                foreach($categoryAll as $v){
                    if($act == 1){
                        if($v['id']==$cate){
                            foreach($v['son'] as $v1){
                                foreach($v1['son'] as $v2){
                                    $param['category'] .= $v2['id'].',';
                                }
                            }
                            break;
                        }
                    }else if($act == 2){
                        foreach($v['son'] as $v1){
                            if($v1['id'] == $cate){
                                foreach($v1['son'] as $v2){
                                    $param['category'] .= $v2['id'].',';
                                }
                                break;
                            }
                        }
                    }else{
                        $param['category'] = $cate;
                        break;
                    }
                }
                $param['category'] = trim($param['category'],',');
            }
        }
        $list = $SpuService->getAllSpu($param);
//        if($list['list']){
//            foreach($list['list'] as $k=>$v){
//                if($v['category_id'] >   0){
//                    $Category_one   =   $CategoryService->getCategory($v['category_id'],'id');
//                    $list['list'][$k]['productRule']   =   isset($Category_one['data'][0]['productRule'])?$Category_one['data'][0]['productRule']['name_id']['name'].':'.$Category_one['data'][0]['productRule']['name_id2']['name'].':'.$Category_one['data'][0]['productRule']['name_id3']['name']:'';
//                    $list['list'][$k]['productRule'] = trim(trim($list['list'][$k]['productRule'],':'),':');
////                    var_dump($list['list'][$k]['productRule']);die;
//                }
//            }
//        }
        $this->view->setVar('list',$list);
        $this->view->setVar('name',isset($param['name'])?$param['name']:'');
        $catagory_is = array();
        if(is_array($categoryID)){
            if($categoryID[0]>0){
                for($i=0;$i<count($categoryID)-1;$i++){
                    $tmp = $CategoryService->getCategory($categoryID[$i],'pid',false);
                    $catagory_is[] = $tmp['data'];
                }
            }else{
                unset($categoryID);
                $categoryID[] = 0;
            }
        }else{
            $categoryID[] = 0;
        }
        $this->view->setVar('category_is',$catagory_is);
        $this->view->setVar('categoryID',$categoryID);
    }
    /**
     * sku商品列表
     * User: lw
     * Date: 2016/8/16
     * Time: 15:50
     */
    public function skuListAction()
    {
        $category = CategoryService::getInstance()->getCategory();
        $this->view->setVar('category',$category['data']);
        //过滤
        foreach($this->request->get() as $k=>$v){
            if($k != 'shop_category'){
                $param[$k]  =   $this->getParam($k,'trim');
            }
        }
	    $param['page']  =   $this->getParam('page','int',1);
        $param['url'] = $this->automaticGetUrl();
        $categoryID = $this->request->get('shop_category');
	    if(!empty($categoryID)){
            $categoryTmp = array();
            foreach($categoryID as $v){
                if($v>0) $categoryTmp[] = $v;
            }
            if(!empty($categoryTmp)){
                $cate = $categoryTmp[count($categoryTmp)-1];
                $act = count($categoryTmp);
                $categoryAll = CategoryService::getInstance()->categoryLists();
                $param['category'] = '';
                foreach($categoryAll as $v){
                    if($act == 1){
                        if($v['id']==$cate){
                            foreach($v['son'] as $v1){
                                foreach($v1['son'] as $v2){
                                    $param['category'] .= $v2['id'].',';
                                }
                            }
                            break;
                        }
                    }else if($act == 2){
                        foreach($v['son'] as $v1){
                            if($v1['id'] == $cate){
                                foreach($v1['son'] as $v2){
                                    $param['category'] .= $v2['id'].',';
                                }
                                break;
                            }
                        }
                    }else{
                        $param['category'] = $cate;
                        break;
                    }
                }
                $param['category'] = trim($param['category'],',');
            }
        }
	    $thisurl = json_encode($param);
	    $list = SkuService::getInstance()->getAllSku($param);
        $array = array();
        $category_sup = array();
        $SpuService = SpuService::getInstance();
        $CategoryService = CategoryService::getInstance();
        if($list['list']){
            //处理分类和药物类型信息
            foreach($list['list'] as $k=>$v){
                if($v['spu_id'] > 0) {
                    if (!in_array($v['spu_id'], $array)) {
                        $array[] = $v['spu_id'];
                        $spu = $SpuService->getSpuOne($v['spu_id']);
                        if (isset($spu['data']) && $spu['data'][0]['category_id'] > 0) {
                            $aa = $CategoryService->getCategory($spu['data'][0]['category_id'], 'id');
                            $list['list'][$k]['category_name'] = $aa['data'][0]['category_name'];
                        }
                        switch ($spu['data'][0]['drug_type']) {
                            case 1:
                                $list['list'][$k]['drug_type'] = '处方药';
                                break;
                            case 2:
                                $list['list'][$k]['drug_type'] = '红色非处方药';
                                break;
                            case 3:
                                $list['list'][$k]['drug_type'] = '绿色非处方药';
                                break;
                            case 4:
                                $list['list'][$k]['drug_type'] = '非药物';
                                break;
                            case 5:
                                $list['list'][$k]['drug_type'] = '虚拟商品';
                                break;
                        }
                        $category_sup[$v['spu_id']]['drug_type'] = $list['list'][$k]['drug_type'];
                        $category_sup[$v['spu_id']]['category_name'] = $list['list'][$k]['category_name'];
                    } else {
                        $list['list'][$k]['category_name'] = $category_sup[$v['spu_id']]['category_name'];
                        $list['list'][$k]['drug_type'] = $category_sup[$v['spu_id']]['drug_type'];
                    }
                }else{
                    $list['list'][$k]['category_name'] = '';
                    $list['list'][$k]['drug_type'] = '';
                }
            }
        }
        $this->view->setVar('list',$list);
        //组织搜索条件,显示在前端页面
        $this->view->setVar('name',isset($param['name'])?$param['name']:'');
        $this->view->setVar('spu_name',isset($param['spu_name'])?$param['spu_name']:'');
        $this->view->setVar('drug_type',(isset($param['drug_type'])&&$param['drug_type']>0)?$param['drug_type']:'');
        $this->view->setVar('brand',isset($param['brand'])?$param['brand']:'');
        $this->view->setVar('is_hot',isset($param['is_hot'])?$param['is_hot']:'-1');
        $this->view->setVar('is_recommend',isset($param['is_recommend'])?$param['is_recommend']:'-1');
        $this->view->setVar('is_on_sale',isset($param['is_on_sale'])?$param['is_on_sale']:'-1');
        $catagory_is = array();
        if(is_array($categoryID)){
            if($categoryID[0]>0){
                for($i=0;$i<count($categoryID)-1;$i++){
                    if($categoryID[$i] > 0){
                        $tmp = CategoryService::getInstance()->getCategory($categoryID[$i]);
                        $catagory_is[] = $tmp['data'];
                    }
                }
            }else{
                unset($categoryID);
                $categoryID[]=0;
            }
        }else{
            $categoryID[] = 0;
        }
        $this->view->setVar('category_is',$catagory_is);
        $this->view->setVar('categoryID',$categoryID);
        $this->view->setVar('thisurl',$thisurl);
        $this->view->pick('spu/skuList');
    }
    //修改spu
    public function editAction()
    {
        //判断是否post提交
        if($this->request->isPost() || $this->request->isAjax()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SpuService::getInstance()->updateSpu($param);
            $this->view->disable();
//            if($res['url']){
//                header("Location: ".$res['url']);
//            }
//            var_dump($res);die;
            return $this->response->setJsonContent($res);
        }else{
            $id = (int)$this->request->get('id','trim');
            if($id <= 0){
                header("Location: /spu/add");
                return ;
            }
            $SpuService = SpuService::getInstance();
            $CategoryService = CategoryService::getInstance();
            $SkuService= SkuService::getInstance();
            $spu = $SpuService->getSpuOne((int)$id);
            if(!$spu || $spu['status'] != 'success' || !$spu['data']){
                header("Location: /spu/add");
                return ;
            }
            /*************************************** spu 管理 *****************************************/
            $category = $CategoryService->getCategory();
            $this->view->setVar('category',$category['data']);
            $spu_category = $CategoryService->getFatherCategory($spu['data'][0]['category_id']);
            if( isset($spu_category[3]) && $spu_category[3] > 0 ){
                //三级分类信息
                $spuId3 = $spu_category[3]['id'];
            }
            foreach($spu_category as $v){
                $categoryID[] = $v['id'];
            }
            if(is_array($categoryID)){
                $catagory_is = array();
                for($i=0;$i<count($categoryID)-1;$i++){
                    if($categoryID[$i] > 0){
                        $tmp = $CategoryService->getCategory($categoryID[$i]);
                        $catagory_is[] = $tmp['data'];
                    }
                }
            }else{
                $categoryID[] = 0;
            }
            //品牌信息
            $brand = BrandsService::getInstance()->getBrandAll();
            if($brand['status'] == 'success'){
                $this->view->setVar('brand',$brand['data']);
            }else{
                $this->view->setVar('brand',array());
            }
            //禁止分类下拉框选择
            $this->view->setVar('disabled',0);
            $this->view->setVar('category_is',$catagory_is);
            $this->view->setVar('categoryID',$categoryID);
            $this->view->setVar('spu',$spu['data'][0]);
            $this->view->setVar('act',1);
            $this->view->setVar('topTo',0);
            /*************************************** spu 管理 end *****************************************/

            /*************************************** sku 管理 *****************************************/
            //获得品规信息
            $categoryrule = $CategoryService->getCategory($spuId3,'id');
            $this->view->setVar('productRule', isset($categoryrule['data'][0]['productRule'])?$categoryrule['data'][0]['productRule']:'');
            //获取sku信息
            $sku = $SkuService->getSpuSku($spu['data'][0]['spu_id']);
            //获取sku其他信息
            $array = array();
            if(!$sku){
                $sku = array();
            }
            foreach($sku as $k=>&$v){
                //获取详情信息
                $tmp = $SkuService->getSkuInfo($v['id']);
                $code = $SkuService->getCode($v['id']);
                $v['product_code'] = $code[0]['product_code'];
                if($v['product_type']==2 && $tmp[0]['whether_is_gift']==0){
                    $tmp[0]['whether_is_gift'] = 2;
                }
                $v['supplier_name'] = '诚仁堂商城';
                if($v['supplier_id']){
                    $supplier = $SkuService->getSupplier($v['supplier_id']);
                    if(!empty($supplier)) $v['supplier_name'] = $supplier[0]['name'];
                }
                $array[$k] = $v;
                $array[$k]['info'] = $tmp[0];
                //获取品规值信息
                if(!empty($v['rule_value_id'])){
                    $tmp = $SkuService->getRuleValue($v['rule_value_id']);
                    $array[$k]['rule'] = $tmp;
                    //修改品规显示
                    $array[$k]['rule_value_id'] = $SkuService->getRuleShow($tmp);

                }else{
                    $array[$k]['rule_value_id'] = '';
                }
                //转换图片路径
                $array[$k]['goods_image'] = $v['goods_image'];
                $array[$k]['big_path'] = $v['big_path'];
                $array[$k]['small_path'] = $v['small_path'];
            }
            //给模板引擎添加自定义函数
            $volt = $this->di->get("volt", [$this->view, $this->di]);
            $compiler = $volt->getCompiler();
            $compiler->addFunction('is_array', 'is_array');
            $this->view->setVar('skuinfo',$array);
            $this->view->setVar('sku',$sku);
            $skuInfoSave = $array;
            /*************************************** sku 管理 end *****************************************/

            /*************************************** sku 图片管理  *****************************************/
            $array = array();
            foreach($skuInfoSave as $k=>$v){
                //获取图片信息
                $tmp = $SkuService->getSkuImg(array('goods_id'=>$v['id']));
                //去除第单个品规值
                $array[$k]  =   $v;
                $array[$k]['img']   =   $tmp;
            }
            //获取spu图片信息
            $spuIng = $SkuService->getSkuImg( array( 'spu_id' => $id ) );
            $this->view->setVar('skuimg',$array);
            $this->view->setVar('spuImg',$spuIng);
            /*************************************** sku 图片管理 end *****************************************/

            /*************************************** sku 属性管理 *****************************************/
            //获取默认属性值
            $skuDefaultNum = $SpuService->isSkuDefault($spu['data'][0]['spu_id']);
            if($skuDefaultNum){
                $skuDefault = $SpuService->getSkuDefault($spu['data'][0]['spu_id']);
            }else{
                $skuDefault = array();
            }
            if( $skuDefault && $skuDefault['data'][0]){
                $skuDefault['data'][0]['sku_label'] = explode(',',$skuDefault['data'][0]['sku_label']);
                $this->view->setVar('skuDefault',$skuDefault['data'][0]);
                if(!empty($skuDefault['data'][0]['attribute_value_id'])){
                    $attrValue = explode(',',$skuDefault['data'][0]['attribute_value_id']);
                    foreach($attrValue as $s){
                        if(!empty($s)){
                            $aa = explode(':',$s);
                            $tmpAttr[$aa[0]] = $aa[1];
                        }
                    }
                    $this->view->setVar('attrValue',$tmpAttr);
                }
                //获得赠品信息
                $giftArr = array();
                $gift = json_decode($skuDefault['data'][0]['bind_gift']);
                if(!empty($gift)){
                    foreach($gift as $k=>$s){
                        if(!empty($s)){
                            $giftTmp = $SkuService->getSkuOne($s->id);
                            $giftTmp[0]['num'] = $s->num;
                            $giftArr[] =  $giftTmp[0];
                        }
                    }
                }
                $this->view->setVar('giftArr',$giftArr);
            }else{
                $this->view->setVar('skuDefault',array());
            }
            //获取分类属性
            $categoryAttr = AttrNameService::getInstance()->getCategoryAttrAll($spuId3);
            $this->view->setVar('categoryAttr',$categoryAttr['data']);

            //获取视频信息
            if(isset($skuDefault['data'][0]['sku_video']) && $skuDefault['data'][0]['sku_video'] > 0){
                $video = VideoService::getInstance()->getVideoInfo($skuDefault['data'][0]['sku_video']);
            }else{
                $video = array();
            }
            $this->view->setVar('video',$video);
            /*************************************** sku 属性管理 end *****************************************/

            /*************************************** sku 说明书管理  *****************************************/
            $instruction = '';
            if(isset($skuInfoSave[0]['id']) && $skuInfoSave[0]['id'] > 0){
                $instruction = $SkuService->getSkuInstruction($skuInfoSave[0]['id']);
            }
            if(!$instruction){
                $instruction = array(
                    'cn_name'                   =>      '',
                    'drug_interactions'         =>      '',
                    'common_name'               =>      '',
                    'eng_name'                  =>      '',
                    'component'                 =>      '',
                    'overdosage'                =>      '',
                    'description'               =>      '',
                    'clinicalTrial'             =>      '',
                    'functionCategory'          =>      '',
                    'mechanismAction'           =>      '',
                    'indication'                =>      '',
                    'pharmacokinetics'          =>      '',
                    'form'                      =>      '',
                    'storage'                   =>      '',
                    'dosage'                    =>      '',
                    'pack'                      =>      '',
                    'adverse_reactions'         =>      '',
                    'period'                    =>      '',
                    'contraindications'         =>      '',
                    'standard'                  =>      '',
                    'precautions'               =>      '',
                    'approve_code'              =>      '',
                    'use_in_pregLact'           =>      '',
                    'company_name'              =>      '',
                    'use_in_children'           =>      '',
                    'commodity_code'            =>      '',
                    'use_in_elderly'            =>      '',
                );
            }
            $this->view->setVar('instruction',is_array($instruction)?$instruction:get_object_vars($instruction));
            /*************************************** sku 说明书管理 end *****************************************/

            /*************************************** sku 详情管理  *****************************************/
            $ad_pc = explode(':',isset($skuDefault['data'][0]['ad_id_pc'])?$skuDefault['data'][0]['ad_id_pc']:'');

            $ad = array();
            $btomAd = array();
            $SkuAdService = SkuAdService::getInstance();
            if(!empty($ad_pc[0])){
                $ad_s = explode(',',$ad_pc[0]);
                foreach($ad_s as $v){
                    $tmp = $SkuAdService->getOneSkuAd($v,'pc');
                    if( $tmp['data'][0] ){
                        $ad[] =  $tmp['data'][0];
                    }
                }
            }
            if(!empty($ad_pc[1])){
                $ad_x = explode(',',$ad_pc[1]);
                foreach($ad_x as $v){
                    $tmp = $SkuAdService->getOneSkuAd($v,'pc');
                    if( $tmp['data'][0] ){
                        $btomAd[] =  $tmp['data'][0];
                    }
                }
            }
            $this->view->setVar('ad',$ad);
            $this->view->setVar('btomAd',$btomAd);
            /*************************************** sku 详情管理 end *****************************************/

            /*************************************** sku 上下架管理管理 *****************************************/
            //获取商品定时上下架信息
            $timingTime = $this->RedisCache->getValue(CacheGoodsKey::SKU_TIMING_TIME);
            if(isset($timingTime[$id]) && !empty($timingTime[$id])){
                $setTime = $timingTime[$id];
            }else{
                $setTime = ['allTime'=>1];
            }
	        //商家
	        $supplier = SupplierService::getInstance()->getAllSupplier();
	        $this->view->setVar('supplier', $supplier);
            //获取全部运费模板
            //获取当前spu的运费模板
            $freight = FreightTempService::getInstance()->getFreightAll();
            $this->view->setVar('freight',$freight);
            $this->view->setVar('setTime',$setTime);
            /*************************************** sku 上下架管理管理 end *****************************************/
            $this->view->pick('spu/add');
        }
    }
	
	/**
	 * 获取sku信息
	 * @param int spu_id spu ID
	 * @param int id  商品id
	 * @param int force 是否强制转换
	 * @return array
	 */
	public function updateCacheAction()
	{
		if($this->request->isPost()){
			$param  = $this->postParam($this->request->getPost(), 'trim', '');
			$res = SkuService::getInstance()->updateCache($param);
			$this->view->disable();
			return $this->response->setJsonContent($res);
		}
	}
    
    //获取spu下商品上下架信息
    public function getIsOnSaleAction()
    {
        $param  = $this->postParam($this->request->getPost(), 'trim', '');
        $SkuService = SkuService::getInstance();
        $res = $SkuService->getSpuSku($param['spu_id']);
        if( $res ){
            foreach( $res as $k=>$v){
                if( $param['is_on_sale'] == 2  ){
                    $tmpTime = $SkuService->getSkuTiming($v['id']);
                    $res[$k]['time'] = $tmpTime[0];
                    if( $tmpTime[0]['time_start'] > 0 ){
                        $this_start = date('m/d/Y H:i',$tmpTime[0]['time_start']);
                        $tmp = explode(' ',$this_start);
                        $tmp1 = explode(':',$tmp[1]);
                        $this_start = $tmp[0].' ';
                        if( $tmp1[0] > 12 ){
                            $this_start .= ($tmp1[0]-12).":".$tmp1[1].' '.'PM';
                        }else{
                            $this_start .= $tmp1[0].":".$tmp1[1].' '.'AM';
                        }
                        $res[$k]['time']['time_start'] = $this_start;
                    }
                    if( $tmpTime[0]['time_end'] > 0 ){
                        $this_end = date('m/d/Y H:i',$tmpTime[0]['time_end']);
                        $tmp = explode(' ',$this_end);
                        $tmp1 = explode(':',$tmp[1]);
                        $this_end = $tmp[0].' ';
                        if( $tmp1[0] > 12 ){
                            $this_end .= ($tmp1[0]-12).":".$tmp1[1].' '.'PM';
                        }else{
                            $this_end .= $tmp1[0].":".$tmp1[1].' '.'AM';
                        }
                        $res[$k]['time']['time_end'] = $this_end;
                    }
                }
            }
        }
        return $this->response->setJsonContent($res);
    }
    //获取商品详情信息
    public function getMobileAction()
    {
        $type = $this->request->getPost('type','trim');
        $skuId = $this->request->getPost('skuId','trim');
        $spuId = $this->request->getPost('spuId','trim');
        $SpuService = SpuService::getInstance();
        $SkuService = SkuService::getInstance();
        $SkuAdService = SkuAdService::getInstance();
        if($skuId > 0){
            $skuDefault['data'] = $SkuService->getSkuInfo((int)$skuId);
            if( empty($skuDefault['data']) ){
                if($spuId > 0) {
                    $skuDefault = $SpuService->getSkuDefault((int)$spuId);
                }
            }
            $skuDefaults = $SpuService->getSkuDefault((int)$spuId);
            foreach( $skuDefault['data'][0] as $k=>$v ){
                if( $k == 'ad_id_pc' || $k == 'ad_id_mobile' || $k == 'sku_detail_pc' || $k == 'sku_detail_mobile' || $k == 'instructions_pc' || $k == 'instructions_mobile' )
                {
                    if( empty($v) ){
                        $skuDefault['data'][0][$k] = isset($skuDefaults['data'][0][$k])?$skuDefaults['data'][0][$k]:'';
                    }
                }
            }
        }else if($spuId > 0){
            $skuDefault = $SpuService->getSkuDefault((int)$spuId);
            if(!$skuDefault){
                return $this->response->setJsonContent(false);
            }
        }else{
            return $this->response->setJsonContent(false);
        }
        $array = array();
        $array['info'] = $skuDefault['data'][0];
        //广告
        $ad_pc = explode(':',$skuDefault['data'][0]['ad_id_'.$type]);
        $ad = array();
        $btomAd = array();
        if(!empty($ad_pc[0])){
            $ad_s = explode(',',$ad_pc[0]);
            foreach($ad_s as $v){
                $tmp = $SkuAdService->getOneSkuAd($v,$type);
                //判断数据是否存在
               if($tmp['data'][0]){
                   $ad[] =  $tmp['data'][0];
               }
            }
        }
        if(!empty($ad_pc[1])){
            $ad_x = explode(',',$ad_pc[1]);
            foreach($ad_x as $v){
                $tmp = $SkuAdService->getOneSkuAd($v,$type);
                if($tmp['data'][0]){
                    $btomAd[] =  $tmp['data'][0];
                }
            }
        }
        $array['ad'] = $ad;
        $array['btomAd'] = $btomAd;
        $this->view->disable();
        return $this->response->setJsonContent($array);
    }
    //获取商品属性信息
    public function getAttrAllAction()
    {
        $id = $this->request->get('id','trim');
        $spu_id = $this->request->get('spu_id','trim');
        $SpuService = SpuService::getInstance();
        $SkuService = SkuService::getInstance();
        $spu = $SpuService->getSpuOne((int)$spu_id);
        //获取默认属性值
        if($id > 0){
            $skuDefault['data'] = $SkuService->getSkuOne((int)$id);
            if(!$skuDefault){
                $skuDefaultNum = $SpuService->isSkuDefault($spu_id);
                if($skuDefaultNum){
                    $skuDefault = $SpuService->getSkuDefault($spu_id);
                }else{
                    $skuDefault = array();
                }
            }else{
                //转换数据
                $skuDefault['data'][0]['sku_pc_name'] = $skuDefault['data'][0]['goods_name'];
                $skuDefault['data'][0]['sku_batch_num'] = $skuDefault['data'][0]['prod_code'];
                $skuDefault['data'][0]['sku_weight'] = $skuDefault['data'][0]['weight'];
//                $skuDefault['data'][0]['sku_weight'] = $skuDefault['data'][0]['weight'];
                $skuDefault['data'][0]['sku_bulk'] = $skuDefault['data'][0]['size'];
                $skuDefault['data'][0]['sku_video'] = $skuDefault['data'][0]['video_id'];

                $skuDefaults = $SpuService->getSkuDefault($spu_id);
                foreach( $skuDefault['data'][0] as $k=>$v ){
                    if( $k != 'id' || $k != 'spu_id' ){
                        if( empty($v) ){
                            $skuDefault['data'][0][$k] = isset($skuDefaults['data'][0][$k])?$skuDefaults['data'][0][$k]:'';
                        }
                    }
                }
            }
        }else{
            $skuDefaultNum = $SpuService->isSkuDefault($spu_id);
            if($skuDefaultNum){
                $skuDefault = $SpuService->getSkuDefault($spu_id);
            }else{
                $skuDefault = array();
            }
        }
        if($skuDefault['data'][0]){
            $skuDefault['data'][0]['sku_label'] = explode(',',$skuDefault['data'][0]['sku_label']);
            $arraySkuInfo['sku'] = $skuDefault['data'][0];
            $this->view->setVar('skuDefault',$skuDefault['data'][0]);
            if(!empty($skuDefault['data'][0]['attribute_value_id'])){
                $attrValue = explode(',',$skuDefault['data'][0]['attribute_value_id']);
                foreach($attrValue as $s){
                    if(!empty($s)){
                        $aa = explode(':',$s);
                        $tmpAttr[$aa[0]] = $aa[1];
                    }
                }
                $arraySkuInfo['attrValue'] = $tmpAttr;
            }
            //获得赠品信息
            $giftArr = array();
            $gift = json_decode($skuDefault['data'][0]['bind_gift']);
            if(!empty($gift)){
//                $gift = explode(',',$skuDefault['data'][0]['bind_gift']);
                foreach($gift as $k=>$s){
                    if(!empty($s)){
                        $giftTmp = $SkuService->getSkuOne($s->id);
                        $giftTmp[0]['num'] = $s->num;
                        $giftArr[] =  $giftTmp[0];
                    }
                }
            }
            $arraySkuInfo['giftArr'] = $giftArr;
        }else{
            $arraySkuInfo['sku'] = array();
        }
        $spu_category = CategoryService::getInstance()->getFatherCategory($spu['data'][0]['category_id']);
        if( isset($spu_category[3]) && $spu_category[3] > 0 ){
            //三级分类信息
            //获取分类属性
            $categoryAttr = AttrNameService::getInstance()->getCategoryAttrAll($spu_category[3]['id']);
            $arraySkuInfo['categoryAttr'] = $categoryAttr['data'];
        }else{
            $arraySkuInfo['categoryAttr'] = array();
        }

        //获取视频信息
        if(isset($skuDefault['data'][0]['sku_video']) && $skuDefault['data'][0]['sku_video'] > 0){
            $video = VideoService::getInstance()->getVideoInfo($skuDefault['data'][0]['sku_video']);
            $video = $video['data'][0];
        }else{
            $video = '';
        }
        $arraySkuInfo['video'] = $video;
        $this->view->disable();
        return $this->response->setJsonContent($arraySkuInfo);
    }
    //spu编辑
    public function addAction()
    {
        //判断是否post提交
        if($this->request->isPost() || $this->request->isAjax()){
            $param  = $this->postParam($this->request->getPost(), 'trim', '');
            $res = SpuService::getInstance()->addSpu($param);
            $this->view->disable();
            return $this->response->setJsonContent($res);
        }else{
            //主分类信息
            $category = CategoryService::getInstance()->getCategory(0,'pid',false,true);
            $this->view->setVar('category',$category['data']);
            $this->view->setVar('productRuleNum', 0);

            //品牌信息
            $brand = BrandsService::getInstance()->getBrandAll();
            //商家
	        $supplier = SupplierService::getInstance()->getAllSupplier();
            if($brand['status'] == 'success'){
                $this->view->setVar('brand',$brand['data']);
            }else{
                $this->view->setVar('brand',array());
            }
            $this->view->setVar('supplier', $supplier);
            $this->view->pick('spu/edit');
        }
    }
    public function delAction()
    {

    }

    /**
     * @desc 判断spu名称是否重复
     * @type get
     * @param string name spu名称
     * @return bool true|false
     * @author 梁伟
     * Date: 2016/9/18
     */
    public function exitstAction(){
        $param['name'] = $this->request->get('name', 'trim');
        $param['id'] = $this->request->get('id', 'trim');
        $res = SpuService::getInstance()->isExitst($param);
        return $res;
    }

    /**
     * @remark 导入商品属性
     * @return json
     * @author 罗毅庭
     */
    public function importAction()
    {
        $import_type = $this->request->getPost('import_type', 'trim');
        if ($this->request->hasFiles())
        {
            $res = BaseService::getInstance()->filesUpload($this->request, $this->config['application']['uploadDir'].'csv/', '', 'xlsx');
            if($res['status'] == 'success'){
                $result = SpuService::getInstance()->import($res['data'][0]['filePath']. $res['data'][0]['fileName'],$import_type);
                return $this->response->setJsonContent($result);
            }
            return $this->response->setJsonContent($res);
        }
    }

    public function exportAction(){
        $param['spu_id'] = $this->request->getPost('spu_id', 'trim');
        $param['spu_name'] = $this->request->getPost('spu_name', 'trim');
        $param['brand_name'] = $this->request->getPost('brand_name', 'trim');
        $param['drug_type'] = $this->request->getPost('drug_type', 'trim');
        $param['category_id'] = $this->request->getPost('category_id', 'trim');
        $param['attr_list'] = $this->request->getPost('attr_list', 'trim');
        $param['goods_id'] = $this->request->getPost('goods_id', 'trim');
        $param['product_code'] = $this->request->getPost('product_code', 'trim');
        $param['attribute_value_id'] = $this->request->getPost('attribute_value_id', 'trim');
//        $param['price'] = $this->request->getPost('price', 'trim');
        $param['goods_price'] = $this->request->getPost('goods_price', 'trim');
        $param['market_price'] = $this->request->getPost('market_price', 'trim');
        $param['stock_1'] = $this->request->getPost('stock_1', 'trim');
        $param['stock_2'] = $this->request->getPost('stock_2', 'trim');
        $param['is_lock'] = $this->request->getPost('is_lock', 'trim');
        $param['sort'] = $this->request->getPost('sort', 'trim');
        $param['gift_yes'] = $this->request->getPost('gift_yes', 'trim');
        $param['status_1'] = $this->request->getPost('status_1', 'trim');
        $param['status'] = $this->request->getPost('status', 'trim');
        $param['sku_alias_name'] = $this->request->getPost('sku_alias_name', 'trim');
        $param['goods_name'] = $this->request->getPost('goods_name', 'trim');
        $param['sku_pc_subheading'] = $this->request->getPost('sku_pc_subheading', 'trim');
        $param['sku_mobile_name'] = $this->request->getPost('sku_mobile_name', 'trim');
        $param['sku_mobile_subheading'] = $this->request->getPost('sku_mobile_subheading', 'trim');
        $param['sku_label'] = $this->request->getPost('sku_label', 'trim');
        $param['period'] = $this->request->getPost('period', 'trim');
        $param['usage'] = $this->request->getPost('usage', 'trim');
        $param['zysx'] = $this->request->getPost('zysx', 'trim');
        $param['instruction'] = $this->request->getPost('instruction', 'trim');
        $param['type'] = $this->request->getPost('type', 'trim');
        $param['type_num'] = $this->request->getPost('type_num', 'trim');
        $param['url'] = $this->request->getPost('thisurl', 'trim');
        $res = SpuService::getInstance()->export($param);
        #var_dump($res);exit();
    }
}